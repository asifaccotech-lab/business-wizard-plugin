<?php
/**
 * Frontend Wizard Display
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('business_wizard', array($this, 'render_wizard'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function enqueue_frontend_assets() {
        if (has_shortcode(get_post()->post_content ?? '', 'business_wizard')) {
            wp_enqueue_style('biz-wizard-style', BIZ_WIZARD_PLUGIN_URL . 'public/css/wizard-style.css', array(), BIZ_WIZARD_VERSION);
            wp_enqueue_script('biz-wizard-script', BIZ_WIZARD_PLUGIN_URL . 'public/js/wizard-script.js', array('jquery'), BIZ_WIZARD_VERSION, true);
            
            wp_localize_script('biz-wizard-script', 'bizWizard', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('biz_wizard_nonce'),
                'siteUrl' => home_url(),
            ));
        }
    }
    
    public function render_wizard($atts) {
        $atts = shortcode_atts(array(
            'redirect_url' => home_url(),
            'show_sidebar' => 'true',
        ), $atts);
        
        // Check if wizard is enabled
        $general_settings = get_option('biz_wizard_general_settings', array());
        $wizard_enabled = isset($general_settings['biz_wizard_enabled']) ? $general_settings['biz_wizard_enabled'] : '0';
        if ($wizard_enabled != '1') {
            return '<p>' . __('The wizard is currently disabled.', 'business-wizard') . '</p>';
        }
        
        ob_start();
        include BIZ_WIZARD_PLUGIN_DIR . 'public/templates/wizard-template.php';
        return ob_get_clean();
    }
    
    public static function get_business_types() {
        return get_posts(array(
            'post_type' => 'biz_type',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_status' => 'publish'
        ));
    }
    
    public static function get_turnover_ranges() {
        return get_posts(array(
            'post_type' => 'biz_turnover',
            'posts_per_page' => -1,
            'orderby' => 'meta_value_num',
            'meta_key' => '_min_amount',
            'order' => 'ASC',
            'post_status' => 'publish'
        ));
    }
    
    public static function get_payment_methods() {
        return get_posts(array(
            'post_type' => 'biz_payment',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_status' => 'publish'
        ));
    }
}
