<?php
/**
 * Admin Settings Page
 * Path: admin/pages/settings.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get active tab, default to general
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap">
    <h1><?php _e('Business Wizard Settings', 'business-wizard'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=business-wizard-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General', 'business-wizard'); ?>
        </a>
        <a href="?page=business-wizard-settings&tab=api" class="nav-tab <?php echo $active_tab == 'api' ? 'nav-tab-active' : ''; ?>">
            <?php _e('API Settings', 'business-wizard'); ?>
        </a>
        <a href="?page=business-wizard-settings&tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Email Settings', 'business-wizard'); ?>
        </a>
        <a href="?page=business-wizard-settings&tab=advanced" class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Advanced', 'business-wizard'); ?>
        </a>
    </h2>
    
    <form method="post" action="options.php">
        <?php
        if ($active_tab == 'general') {
            settings_fields('biz_wizard_general_settings');
        } elseif ($active_tab == 'api') {
            settings_fields('biz_wizard_api_settings');
        } elseif ($active_tab == 'email') {
            settings_fields('biz_wizard_email_settings');
        } elseif ($active_tab == 'advanced') {
            settings_fields('biz_wizard_advanced_settings');
        }
        ?>
        
        <?php if ($active_tab == 'general') : ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="biz_wizard_enabled"><?php _e('Enable Wizard', 'business-wizard'); ?></label>
                </th>
                <td>
                    <label>
<?php
$general_settings = get_option('biz_wizard_general_settings', array());
$enabled = isset($general_settings['biz_wizard_enabled']) ? $general_settings['biz_wizard_enabled'] : '0';
?>
                        <input type="checkbox" id="biz_wizard_enabled" name="biz_wizard_general_settings[biz_wizard_enabled]" value="1" <?php checked($enabled, '1'); ?>>
                        <?php _e('Enable the business wizard on frontend', 'business-wizard'); ?>
                    </label>
                    <p class="description"><?php _e('Uncheck this to disable the wizard temporarily without removing the shortcode.', 'business-wizard'); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="biz-settings-info-box">
            <h3><?php _e('Shortcode Usage', 'business-wizard'); ?></h3>
            <p><?php _e('Add this shortcode to any page where you want to display the wizard:', 'business-wizard'); ?></p>
            <code style="display: block; padding: 15px; background: #f5f5f5; margin: 10px 0;">[business_wizard]</code>
            
            <h4><?php _e('Available Attributes:', 'business-wizard'); ?></h4>
            <ul>
                <li><code>redirect_url</code> - <?php _e('URL to redirect after completion (default: homepage)', 'business-wizard'); ?></li>
                <li><code>show_sidebar</code> - <?php _e('Show progress sidebar (true/false, default: true)', 'business-wizard'); ?></li>
            </ul>
            
            <p><strong><?php _e('Example:', 'business-wizard'); ?></strong></p>
            <code style="display: block; padding: 15px; background: #f5f5f5; margin: 10px 0;">[business_wizard redirect_url="/thank-you" show_sidebar="true"]</code>
        </div>
        
        <?php elseif ($active_tab == 'api') : ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="biz_wizard_companies_house_api"><?php _e('Companies House API Key', 'business-wizard'); ?></label>
                </th>
                <td>
<?php 
$api_settings = get_option('biz_wizard_api_settings', array());
$api_key = isset($api_settings['biz_wizard_companies_house_api']) ? $api_settings['biz_wizard_companies_house_api'] : '';
?>
                    <input type="text" id="biz_wizard_companies_house_api" name="biz_wizard_api_settings[biz_wizard_companies_house_api]" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Get your free API key from:', 'business-wizard'); ?> 
                        <a href="https://developer.company-information.service.gov.uk/" target="_blank">https://developer.company-information.service.gov.uk/</a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="biz_wizard_api_timeout"><?php _e('API Timeout (seconds)', 'business-wizard'); ?></label>
                </th>
                <td>
                    <input type="number" id="biz_wizard_api_timeout" name="biz_wizard_api_settings[biz_wizard_api_timeout]" value="<?php echo esc_attr(isset($api_settings['biz_wizard_api_timeout']) ? $api_settings['biz_wizard_api_timeout'] : 10); ?>" min="5" max="30" class="small-text">
                    <p class="description"><?php _e('How long to wait for API response before timeout.', 'business-wizard'); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="biz-settings-info-box">
            <h3><?php _e('How to Get Your API Key:', 'business-wizard'); ?></h3>
            <ol>
                <li><?php _e('Visit', 'business-wizard'); ?> <a href="https://developer.company-information.service.gov.uk/" target="_blank">Companies House Developer Portal</a></li>
                <li><?php _e('Create a free account', 'business-wizard'); ?></li>
                <li><?php _e('Register a new application', 'business-wizard'); ?></li>
                <li><?php _e('Copy your API key and paste it above', 'business-wizard'); ?></li>
                <li><?php _e('Save settings', 'business-wizard'); ?></li>
            </ol>
            <p><strong><?php _e('Note:', 'business-wizard'); ?></strong> <?php _e('The wizard will still work without an API key, but users will need to manually enter company details.', 'business-wizard'); ?></p>
        </div>
        
        <?php elseif ($active_tab == 'email') : ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="biz_wizard_email_enabled"><?php _e('Enable Email Notifications', 'business-wizard'); ?></label>
                </th>
                <td>
                    <label>
<?php
$email_settings = get_option('biz_wizard_email_settings', array());
$email_enabled = isset($email_settings['biz_wizard_email_enabled']) ? $email_settings['biz_wizard_email_enabled'] : '0';
?>
                        <input type="checkbox" id="biz_wizard_email_enabled" name="biz_wizard_email_settings[biz_wizard_email_enabled]" value="1" <?php checked($email_enabled, '1'); ?>>
                        <?php _e('Send email notifications on form submission', 'business-wizard'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="biz_wizard_admin_emails"><?php _e('Admin Email Addresses', 'business-wizard'); ?></label>
                </th>
                <td>
                    <textarea id="biz_wizard_admin_emails" name="biz_wizard_email_settings[biz_wizard_admin_emails]" rows="3" class="large-text"><?php echo esc_textarea(isset($email_settings['biz_wizard_admin_emails']) ? $email_settings['biz_wizard_admin_emails'] : get_option('admin_email')); ?></textarea>
                    <p class="description"><?php _e('Enter one email per line. These addresses will receive notifications when a submission is completed.', 'business-wizard'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="biz_wizard_user_email_subject"><?php _e('User Email Subject', 'business-wizard'); ?></label>
                </th>
                <td>
                    <input type="text" id="biz_wizard_user_email_subject" name="biz_wizard_email_settings[biz_wizard_user_email_subject]" value="<?php echo esc_attr(isset($email_settings['biz_wizard_user_email_subject']) ? $email_settings['biz_wizard_user_email_subject'] : 'Welcome - Order Confirmation'); ?>" class="large-text">
                    <p class="description"><?php _e('Subject line for confirmation email sent to users.', 'business-wizard'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="biz_wizard_admin_email_subject"><?php _e('Admin Email Subject', 'business-wizard'); ?></label>
                </th>
                <td>
                    <input type="text" id="biz_wizard_admin_email_subject" name="biz_wizard_email_settings[biz_wizard_admin_email_subject]" value="<?php echo esc_attr(isset($email_settings['biz_wizard_admin_email_subject']) ? $email_settings['biz_wizard_admin_email_subject'] : 'New Business Wizard Submission'); ?>" class="large-text">
                    <p class="description"><?php _e('Subject line for notification email sent to admins. Business type and package name will be appended automatically.', 'business-wizard'); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="biz-settings-info-box">
            <h3><?php _e('Email Templates', 'business-wizard'); ?></h3>
            <p><?php _e('Email templates are professionally designed with your branding. They include:', 'business-wizard'); ?></p>
            <ul>
                <li><?php _e('User confirmation email with order summary and next steps', 'business-wizard'); ?></li>
                <li><?php _e('Admin notification email with all submission details', 'business-wizard'); ?></li>
            </ul>
            <p><?php _e('For advanced customization, edit:', 'business-wizard'); ?> <code>includes/class-wizard-email.php</code></p>
        </div>
        
        <?php elseif ($active_tab == 'advanced') : ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="biz_wizard_debug_mode"><?php _e('Debug Mode', 'business-wizard'); ?></label>
                </th>
                <td>
                    <label>
<?php
$advanced_settings = get_option('biz_wizard_advanced_settings', array());
$debug_mode = isset($advanced_settings['biz_wizard_debug_mode']) ? $advanced_settings['biz_wizard_debug_mode'] : '0';
?>
                        <input type="checkbox" id="biz_wizard_debug_mode" name="biz_wizard_advanced_settings[biz_wizard_debug_mode]" value="1" <?php checked($debug_mode, '1'); ?>>
                        <?php _e('Enable debug logging', 'business-wizard'); ?>
                    </label>
                    <p class="description"><?php _e('Enable this only for troubleshooting. Check your debug.log file for error messages.', 'business-wizard'); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="biz-settings-info-box">
            <h3><?php _e('Database Information', 'business-wizard'); ?></h3>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'business_wizard_submissions';
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            ?>
            <p><strong><?php _e('Table Name:', 'business-wizard'); ?></strong> <code><?php echo $table_name; ?></code></p>
            <p><strong><?php _e('Total Records:', 'business-wizard'); ?></strong> <?php echo number_format($count); ?></p>
            <p><strong><?php _e('Signature Directory:', 'business-wizard'); ?></strong> <code>/wp-content/uploads/wizard-signatures/</code></p>
        </div>
        
        <div class="biz-settings-danger-zone">
            <h3 style="color: #dc3232;"><?php _e('Danger Zone', 'business-wizard'); ?></h3>
            <p><?php _e('These actions cannot be undone.', 'business-wizard'); ?></p>
            <button type="button" class="button button-danger" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to delete ALL submissions? This cannot be undone!', 'business-wizard'); ?>')) { alert('This feature will be available in a future update.'); }">
                <?php _e('Delete All Submissions', 'business-wizard'); ?>
            </button>
        </div>
        
        <?php endif; ?>
        
        <p class="submit">
            <input type="submit" name="biz_wizard_settings_submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'business-wizard'); ?>">
        </p>
    </form>
</div>
