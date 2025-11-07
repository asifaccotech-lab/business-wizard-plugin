<?php
/**
 * Admin Interface
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_activation_redirect'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Business Wizard', 'business-wizard'),
            __('Business Wizard', 'business-wizard'),
            'manage_options',
            'business-wizard',
            array($this, 'render_dashboard'),
            'dashicons-clipboard',
            30
        );
        
        add_submenu_page(
            'business-wizard',
            __('Dashboard', 'business-wizard'),
            __('Dashboard', 'business-wizard'),
            'manage_options',
            'business-wizard',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'business-wizard',
            __('Packages', 'business-wizard'),
            __('Packages', 'business-wizard'),
            'manage_options',
            'edit.php?post_type=biz_package'
        );
        
        add_submenu_page(
            'business-wizard',
            __('Business Types', 'business-wizard'),
            __('Business Types', 'business-wizard'),
            'manage_options',
            'edit.php?post_type=biz_type'
        );
        
        add_submenu_page(
            'business-wizard',
            __('Turnover Ranges', 'business-wizard'),
            __('Turnover Ranges', 'business-wizard'),
            'manage_options',
            'edit.php?post_type=biz_turnover'
        );
        
        add_submenu_page(
            'business-wizard',
            __('Payment Methods', 'business-wizard'),
            __('Payment Methods', 'business-wizard'),
            'manage_options',
            'edit.php?post_type=biz_payment'
        );
        
        add_submenu_page(
            'business-wizard',
            __('Submissions', 'business-wizard'),
            __('Submissions', 'business-wizard'),
            'manage_options',
            'business-wizard-submissions',
            array($this, 'render_submissions')
        );
        
        add_submenu_page(
            'business-wizard',
            __('Settings', 'business-wizard'),
            __('Settings', 'business-wizard'),
            'manage_options',
            'business-wizard-settings',
            array($this, 'render_settings')
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'business-wizard') !== false || strpos($hook, 'biz_') !== false) {
            wp_enqueue_style('biz-wizard-admin', BIZ_WIZARD_PLUGIN_URL . 'admin/css/admin-style.css', array(), BIZ_WIZARD_VERSION);
            wp_enqueue_script('biz-wizard-admin', BIZ_WIZARD_PLUGIN_URL . 'admin/js/admin-script.js', array('jquery'), BIZ_WIZARD_VERSION, true);
            
            wp_localize_script('biz-wizard-admin', 'bizWizardAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('biz_wizard_admin'),
            ));
        }
    }
    
    public function register_settings() {
        // General Settings
        register_setting(
            'biz_wizard_general_settings',
            'biz_wizard_general_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_general_settings')
            )
        );
        
        // Email Settings  
        register_setting(
            'biz_wizard_email_settings',
            'biz_wizard_email_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_email_settings')
            )
        );
        
        // API Settings
        register_setting(
            'biz_wizard_api_settings', 
            'biz_wizard_api_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_api_settings')
            )
        );
        
        // Advanced Settings
        register_setting(
            'biz_wizard_advanced_settings',
            'biz_wizard_advanced_settings', 
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_advanced_settings')
            )
        );
    }
    
    public function sanitize_general_settings($input) {
        $output = array();
        $output['biz_wizard_enabled'] = isset($input['biz_wizard_enabled']) ? '1' : '0';
        return $output;
    }
    
    public function sanitize_email_settings($input) {
        $output = array();
        $output['biz_wizard_email_enabled'] = isset($input['biz_wizard_email_enabled']) ? '1' : '0';
        $output['biz_wizard_admin_emails'] = sanitize_textarea_field($input['biz_wizard_admin_emails']);
        $output['biz_wizard_user_email_subject'] = sanitize_text_field($input['biz_wizard_user_email_subject']);
        $output['biz_wizard_admin_email_subject'] = sanitize_text_field($input['biz_wizard_admin_email_subject']);
        return $output;
    }
    
    public function sanitize_api_settings($input) {
        $output = array();
        $output['biz_wizard_companies_house_api'] = sanitize_text_field($input['biz_wizard_companies_house_api']);
        $output['biz_wizard_api_timeout'] = absint($input['biz_wizard_api_timeout']);
        return $output;
    }
    
    public function sanitize_advanced_settings($input) {
        $output = array();
        $output['biz_wizard_debug_mode'] = isset($input['biz_wizard_debug_mode']) ? '1' : '0';
        return $output;
    }
    
    public function handle_activation_redirect() {
        if (get_transient('biz_wizard_activation_redirect')) {
            delete_transient('biz_wizard_activation_redirect');
            if (!isset($_GET['activate-multi'])) {
                wp_redirect(admin_url('admin.php?page=business-wizard&welcome=1'));
                exit;
            }
        }
    }
    
    public function render_dashboard() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'business_wizard_submissions';
        
        // Get statistics
        $total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $this_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE MONTH(submission_date) = MONTH(CURRENT_DATE()) AND YEAR(submission_date) = YEAR(CURRENT_DATE())"
        ));
        
        $popular_packages = $wpdb->get_results("SELECT package_name, COUNT(*) as count FROM $table_name GROUP BY package_name ORDER BY count DESC LIMIT 5");
        $recent_submissions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submission_date DESC LIMIT 10");
        
        include BIZ_WIZARD_PLUGIN_DIR . 'admin/pages/dashboard.php';
    }
    
    public function render_submissions() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'business_wizard_submissions';
        
        // Handle actions
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            check_admin_referer('delete_submission_' . $_GET['id']);
            $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
            echo '<div class="notice notice-success"><p>' . __('Submission deleted successfully.', 'business-wizard') . '</p></div>';
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'export') {
            $this->export_submissions_csv();
            exit;
        }
        
        // Get filter parameters
        $business_type = isset($_GET['business_type']) ? sanitize_text_field($_GET['business_type']) : '';
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Build query
        $where = array('1=1');
        if ($business_type) {
            $where[] = $wpdb->prepare("business_type = %s", $business_type);
        }
        if ($status) {
            $where[] = $wpdb->prepare("status = %s", $status);
        }
        if ($search) {
            $where[] = $wpdb->prepare("(package_name LIKE %s OR user_data LIKE %s)", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where_clause");
        $total_pages = ceil($total_items / $per_page);
        
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where_clause ORDER BY submission_date DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        include BIZ_WIZARD_PLUGIN_DIR . 'admin/pages/submissions.php';
    }
    
    public function render_settings() {
        include BIZ_WIZARD_PLUGIN_DIR . 'admin/pages/settings.php';
    }
    
    private function export_submissions_csv() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'business_wizard_submissions';
        
        $submissions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submission_date DESC");
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="wizard-submissions-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, array('ID', 'Date', 'Business Type', 'Turnover', 'Package', 'Price', 'Total', 'Status', 'User Data'));
        
        foreach ($submissions as $submission) {
            fputcsv($output, array(
                $submission->id,
                $submission->submission_date,
                $submission->business_type,
                $submission->turnover_range,
                $submission->package_name,
                $submission->package_price,
                $submission->total_amount,
                $submission->status,
                $submission->user_data
            ));
        }
        
        fclose($output);
    }
}
