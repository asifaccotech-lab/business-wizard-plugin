<?php
/**
 * Plugin Activation and Deactivation Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_Activator {
    
    public static function activate() {
        // Create database tables
        self::create_tables();
        
        // Create upload directories
        self::create_directories();
        
        // Set default options
        self::set_default_options();
        
        // Create default data
        self::create_default_data();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag for welcome screen
        set_transient('biz_wizard_activation_redirect', true, 30);
    }
    
    public static function deactivate() {
        flush_rewrite_rules();
    }
    
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'business_wizard_submissions';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            business_type varchar(100) NOT NULL,
            turnover_range varchar(100) NOT NULL,
            package_id bigint(20) NOT NULL,
            package_name varchar(255) NOT NULL,
            package_price decimal(10,2) NOT NULL,
            user_data longtext NOT NULL,
            signature_image varchar(255) DEFAULT NULL,
            payment_method varchar(100) DEFAULT NULL,
            additional_services longtext DEFAULT NULL,
            total_amount decimal(10,2) NOT NULL,
            status varchar(50) DEFAULT 'pending',
            notes text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY submission_date (submission_date),
            KEY business_type (business_type),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private static function create_directories() {
        $upload_dir = wp_upload_dir();
        $wizard_dir = $upload_dir['basedir'] . '/wizard-signatures';
        
        if (!file_exists($wizard_dir)) {
            wp_mkdir_p($wizard_dir);
            
            // Create .htaccess for security
            $htaccess_content = "Options -Indexes\n<FilesMatch '\.(jpg|jpeg|png|gif)$'>\n    Order Allow,Deny\n    Allow from all\n</FilesMatch>";
            file_put_contents($wizard_dir . '/.htaccess', $htaccess_content);
            
            // Create index.php
            file_put_contents($wizard_dir . '/index.php', '<?php // Silence is golden');
        }
    }
    
    private static function set_default_options() {
        $default_general = array(
            'biz_wizard_enabled' => '1'
        );
        
        $default_email = array(
            'biz_wizard_email_enabled' => '1',
            'biz_wizard_admin_emails' => get_option('admin_email'),
            'biz_wizard_user_email_subject' => 'Welcome to ' . get_bloginfo('name') . ' - Order Confirmation',
            'biz_wizard_admin_email_subject' => 'New Business Wizard Submission'
        );
        
        $default_api = array(
            'biz_wizard_companies_house_api' => '',
            'biz_wizard_api_timeout' => '10'
        );
        
        $default_advanced = array(
            'biz_wizard_debug_mode' => '0'
        );
        
        if (false === get_option('biz_wizard_general_settings')) {
            add_option('biz_wizard_general_settings', $default_general);
        }
        
        if (false === get_option('biz_wizard_email_settings')) {
            add_option('biz_wizard_email_settings', $default_email);
        }
        
        if (false === get_option('biz_wizard_api_settings')) {
            add_option('biz_wizard_api_settings', $default_api);
        }
        
        if (false === get_option('biz_wizard_advanced_settings')) {
            add_option('biz_wizard_advanced_settings', $default_advanced);
        }
    }
    
    private static function create_default_data() {
        // Create default business types
        $business_types = array(
            'Sole Trader',
            'Partnership',
            'Limited Company',
            'Limited Liability Partnership'
        );
        
        foreach ($business_types as $type) {
            if (!get_page_by_title($type, OBJECT, 'biz_type')) {
                wp_insert_post(array(
                    'post_title' => $type,
                    'post_type' => 'biz_type',
                    'post_status' => 'publish'
                ));
            }
        }
        
        // Create default turnover ranges
        $turnover_ranges = array(
            '£0 - £50,000' => array('min' => 0, 'max' => 50000),
            '£50,000 - £100,000' => array('min' => 50000, 'max' => 100000),
            '£100,000 - £250,000' => array('min' => 100000, 'max' => 250000),
            '£250,000+' => array('min' => 250000, 'max' => 999999999)
        );
        
        foreach ($turnover_ranges as $title => $range) {
            if (!get_page_by_title($title, OBJECT, 'biz_turnover')) {
                $post_id = wp_insert_post(array(
                    'post_title' => $title,
                    'post_type' => 'biz_turnover',
                    'post_status' => 'publish'
                ));
                
                if ($post_id) {
                    update_post_meta($post_id, '_min_amount', $range['min']);
                    update_post_meta($post_id, '_max_amount', $range['max']);
                }
            }
        }
        
        // Create default payment methods
        $payment_methods = array(
            'Stripe Payment' => array('gateway' => 'stripe', 'desc' => 'Set up monthly payments via Stripe'),
            'PayPal' => array('gateway' => 'paypal', 'desc' => 'Set up monthly payments via PayPal'),
            'GoCardless' => array('gateway' => 'gocardless', 'desc' => 'Set up monthly payments via GoCardless'),
            'Debit Card' => array('gateway' => 'debit', 'desc' => 'Set up monthly payments with your debit card', 'recommended' => true)
        );
        
        foreach ($payment_methods as $title => $data) {
            if (!get_page_by_title($title, OBJECT, 'biz_payment')) {
                $post_id = wp_insert_post(array(
                    'post_title' => $title,
                    'post_content' => $data['desc'],
                    'post_type' => 'biz_payment',
                    'post_status' => 'publish'
                ));
                
                if ($post_id) {
                    update_post_meta($post_id, '_gateway_type', $data['gateway']);
                    if (isset($data['recommended']) && $data['recommended']) {
                        update_post_meta($post_id, '_recommended', '1');
                    }
                }
            }
        }
    }
}
