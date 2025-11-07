<?php
/**
 * Plugin Name: Business Onboarding Wizard
 * Plugin URI: https://example.com/business-wizard
 * Description: A comprehensive multi-step wizard for business onboarding with package selection, company lookup, and payment processing.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: business-wizard
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BIZ_WIZARD_VERSION', '1.0.0');
define('BIZ_WIZARD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BIZ_WIZARD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BIZ_WIZARD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Business_Onboarding_Wizard {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-activator.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-cpt.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-admin.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-frontend.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-ajax.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-companies-house-api.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-email.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-database.php';
        require_once BIZ_WIZARD_PLUGIN_DIR . 'includes/class-wizard-pdf.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array('BIZ_WIZARD_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('BIZ_WIZARD_Activator', 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('business-wizard', false, dirname(BIZ_WIZARD_PLUGIN_BASENAME) . '/languages');
    }
    
    public function init() {
        // Initialize components
        BIZ_WIZARD_CPT::get_instance();
        BIZ_WIZARD_Admin::get_instance();
        BIZ_WIZARD_Frontend::get_instance();
        BIZ_WIZARD_Ajax::get_instance();
    }
}

// Initialize the plugin
Business_Onboarding_Wizard::get_instance();
