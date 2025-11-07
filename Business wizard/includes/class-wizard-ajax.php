<?php
/**
 * AJAX Request Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_Ajax {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_get_wizard_packages', array($this, 'get_packages'));
        add_action('wp_ajax_nopriv_get_wizard_packages', array($this, 'get_packages'));
        
        add_action('wp_ajax_search_company', array($this, 'search_company'));
        add_action('wp_ajax_nopriv_search_company', array($this, 'search_company'));
        
        add_action('wp_ajax_submit_wizard', array($this, 'submit_wizard'));
        add_action('wp_ajax_nopriv_submit_wizard', array($this, 'submit_wizard'));
        
        add_action('wp_ajax_get_company_details', array($this, 'get_company_details'));
        add_action('wp_ajax_nopriv_get_company_details', array($this, 'get_company_details'));
        
        add_action('wp_ajax_update_submission_status', array($this, 'update_submission_status'));
    }
    
    public function get_packages() {
        check_ajax_referer('biz_wizard_nonce', 'nonce');
        
        $business_type_id = isset($_POST['business_type']) ? intval($_POST['business_type']) : 0;
        $turnover_id = isset($_POST['turnover']) ? intval($_POST['turnover']) : 0;
        
        if (!$business_type_id || !$turnover_id) {
            wp_send_json_error(array('message' => 'Invalid parameters'));
        }
        
        $packages = get_posts(array(
            'post_type' => 'biz_package',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $filtered_packages = array();
        
        foreach ($packages as $package) {
            $assignments = get_post_meta($package->ID, '_package_assignments', true);
            $key = $business_type_id . '_' . $turnover_id;
            
            if (is_array($assignments) && isset($assignments[$key]) && $assignments[$key] == '1') {
                $price = get_post_meta($package->ID, '_package_price', true);
                $features = get_post_meta($package->ID, '_package_features', true);
                $featured = get_post_meta($package->ID, '_package_featured', true);
                
                $filtered_packages[] = array(
                    'id' => $package->ID,
                    'name' => $package->post_title,
                    'description' => $package->post_content,
                    'price' => $price,
                    'features' => is_array($features) ? array_filter($features) : array(),
                    'featured' => $featured == '1'
                );
            }
        }
        
        wp_send_json_success($filtered_packages);
    }
    
    public function search_company() {
        check_ajax_referer('biz_wizard_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        if (strlen($query) < 2) {
            wp_send_json_error(['message' => __('Please enter at least 2 characters', 'business-wizard')]);
        }
        
        $api = new BIZ_WIZARD_Companies_House_API();
        $results = $api->search_company($query);
        
        if (is_wp_error($results)) {
            wp_send_json_error(['message' => $results->get_error_message()]);
        }

        wp_send_json_success($results);
    }
    
    public function submit_wizard() {
        check_ajax_referer('biz_wizard_nonce', 'nonce');
        
        // Validate required fields
        $required_fields = array('businessType', 'turnover', 'packageId', 'email');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => 'Missing required field: ' . $field));
            }
        }
        
        // Sanitize data
        $business_type = sanitize_text_field($_POST['businessType']);
        $turnover = sanitize_text_field($_POST['turnover']);
        $package_id = intval($_POST['packageId']);
        $package_name = sanitize_text_field($_POST['packageName']);
        $price = floatval($_POST['price']);
        $email = sanitize_email($_POST['email']);
        $payment_method = sanitize_text_field($_POST['paymentMethod']);
        
        // Build user data array
        $user_data = array(
            'fullName' => sanitize_text_field($_POST['fullName'] ?? ''),
            'email' => $email,
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        );
        
        // Add company data if provided
        if (!empty($_POST['companyNumber'])) {
            $user_data['companyNumber'] = sanitize_text_field($_POST['companyNumber']);
            $user_data['companyName'] = sanitize_text_field($_POST['companyName'] ?? '');
            $user_data['registeredAddress'] = sanitize_textarea_field($_POST['registeredAddress'] ?? '');
            $user_data['companyType'] = sanitize_text_field($_POST['companyType'] ?? '');
            $user_data['incorporationDate'] = sanitize_text_field($_POST['incorporationDate'] ?? '');
            $user_data['directorName'] = sanitize_text_field($_POST['directorName'] ?? '');
            $user_data['directorEmail'] = sanitize_email($_POST['directorEmail'] ?? '');
            $user_data['directorPhone'] = sanitize_text_field($_POST['directorPhone'] ?? '');
        }
        
        // Handle signature upload
        $signature_path = '';
        if (!empty($_POST['signature'])) {
            $signature_path = $this->save_signature($_POST['signature'], $email);
        }
        
        // Calculate total (price + VAT)
        $vat = $price * 0.20;
        $total = $price + $vat;
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'business_wizard_submissions';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'submission_date' => current_time('mysql'),
                'business_type' => $business_type,
                'turnover_range' => $turnover,
                'package_id' => $package_id,
                'package_name' => $package_name,
                'package_price' => $price,
                'user_data' => json_encode($user_data),
                'signature_image' => $signature_path,
                'payment_method' => $payment_method,
                'total_amount' => $total,
                'status' => 'pending'
            ),
            array('%s', '%s', '%s', '%d', '%s', '%f', '%s', '%s', '%s', '%f', '%s')
        );
        
        if (!$result) {
            wp_send_json_error(array('message' => 'Failed to save submission'));
        }
        
        $submission_id = $wpdb->insert_id;
        
        // Send emails
        $email_settings = get_option('biz_wizard_email_settings', array());
        $email_enabled = isset($email_settings['biz_wizard_email_enabled']) ? $email_settings['biz_wizard_email_enabled'] : '0';
        if ($email_enabled == '1') {
            $email_handler = new BIZ_WIZARD_Email();
            $email_handler->send_user_email($user_data, array(
                'package_name' => $package_name,
                'price' => $price,
                'total' => $total,
                'business_type' => $business_type,
                'turnover' => $turnover
            ));
            $email_handler->send_admin_email($submission_id, $user_data, array(
                'package_name' => $package_name,
                'price' => $price,
                'total' => $total,
                'business_type' => $business_type,
                'turnover' => $turnover,
                'payment_method' => $payment_method,
                'signature_path' => $signature_path
            ));
        }
        
        wp_send_json_success(array(
            'message' => 'Submission successful',
            'submission_id' => $submission_id
        ));
    }
    
    private function save_signature($base64_data, $email) {
        // Remove data:image/png;base64, prefix
        $image_data = str_replace('data:image/png;base64,', '', $base64_data);
        $image_data = str_replace(' ', '+', $image_data);
        $decoded = base64_decode($image_data);
        
        // Create directory structure
        $upload_dir = wp_upload_dir();
        $signature_dir = $upload_dir['basedir'] . '/wizard-signatures/' . date('Y') . '/' . date('m');
        
        if (!file_exists($signature_dir)) {
            wp_mkdir_p($signature_dir);
        }
        
        // Generate filename
        $filename = 'signature-' . sanitize_file_name($email) . '-' . time() . '.png';
        $filepath = $signature_dir . '/' . $filename;
        
        // Save file
        file_put_contents($filepath, $decoded);
        
        // Return relative path
        return str_replace($upload_dir['basedir'], '', $filepath);
    }
    
    public function get_company_details() {
        check_ajax_referer('biz_wizard_nonce', 'nonce');
        
        $company_number = isset($_POST['company_number']) ? sanitize_text_field($_POST['company_number']) : '';
        
        if (empty($company_number)) {
            wp_send_json_error(array('message' => 'Company number required'));
        }
        
        $api = new BIZ_WIZARD_Companies_House_API();
        $details = $api->get_company_details($company_number);
        
        if (is_wp_error($details)) {
            wp_send_json_error(array('message' => $details->get_error_message()));
        }
        
        // Also get officers (directors)
        $officers = $api->get_company_officers($company_number);
        if (!is_wp_error($officers) && !empty($officers)) {
            $details['officers'] = $officers;
        }
        
        wp_send_json_success($details);
    }
    
    public function update_submission_status() {
        check_ajax_referer('biz_wizard_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (!$submission_id || !$status) {
            wp_send_json_error(array('message' => 'Invalid parameters'));
        }
        
        $db = BIZ_WIZARD_Database::get_instance();
        $result = $db->update_status($submission_id, $status);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Status updated'));
        } else {
            wp_send_json_error(array('message' => 'Update failed'));
        }
    }
}
