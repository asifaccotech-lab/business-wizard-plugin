<?php
/**
 * Custom Post Types and Taxonomies
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_CPT {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'register_post_types'), 0);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
    }
    
    public function register_post_types() {
        // Business Packages
        register_post_type('biz_package', array(
            'labels' => array(
                'name' => __('Packages', 'business-wizard'),
                'singular_name' => __('Package', 'business-wizard'),
                'add_new' => __('Add New', 'business-wizard'),
                'add_new_item' => __('Add New Package', 'business-wizard'),
                'edit_item' => __('Edit Package', 'business-wizard'),
                'new_item' => __('New Package', 'business-wizard'),
                'view_item' => __('View Package', 'business-wizard'),
                'view_items' => __('View Packages', 'business-wizard'),
                'search_items' => __('Search Packages', 'business-wizard'),
                'not_found' => __('No packages found', 'business-wizard'),
                'not_found_in_trash' => __('No packages found in trash', 'business-wizard'),
                'menu_name' => __('Packages', 'business-wizard'),
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'true',
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'query_var' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'supports' => array('title', 'editor'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'packages'),
        ));
        
        // Business Types
        register_post_type('biz_type', array(
            'labels' => array(
                'name' => __('Business Types', 'business-wizard'),
                'singular_name' => __('Business Type', 'business-wizard'),
                'add_new' => __('Add New', 'business-wizard'),
                'add_new_item' => __('Add New Business Type', 'business-wizard'),
                'edit_item' => __('Edit Business Type', 'business-wizard'),
                'new_item' => __('New Business Type', 'business-wizard'),
                'view_item' => __('View Business Type', 'business-wizard'),
                'search_items' => __('Search Business Types', 'business-wizard'),
                'not_found' => __('No business types found', 'business-wizard'),
                'not_found_in_trash' => __('No business types found in trash', 'business-wizard'),
                'menu_name' => __('Business Types', 'business-wizard'),
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'true',
            'menu_position' => 6,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'query_var' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'supports' => array('title', 'thumbnail'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'business-types'),
        ));
        
        // Turnover Ranges
        register_post_type('biz_turnover', array(
            'labels' => array(
                'name' => __('Turnover Ranges', 'business-wizard'),
                'singular_name' => __('Turnover Range', 'business-wizard'),
                'add_new' => __('Add New', 'business-wizard'),
                'add_new_item' => __('Add New Turnover Range', 'business-wizard'),
                'edit_item' => __('Edit Turnover Range', 'business-wizard'),
                'new_item' => __('New Turnover Range', 'business-wizard'),
                'view_item' => __('View Turnover Range', 'business-wizard'),
                'search_items' => __('Search Turnover Ranges', 'business-wizard'),
                'not_found' => __('No turnover ranges found', 'business-wizard'),
                'not_found_in_trash' => __('No turnover ranges found in trash', 'business-wizard'),
                'menu_name' => __('Turnover Ranges', 'business-wizard'),
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'true',
            'menu_position' => 7,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'query_var' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'supports' => array('title'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'turnover-ranges'),
        ));
        
        // Payment Methods
        register_post_type('biz_payment', array(
            'labels' => array(
                'name' => __('Payment Methods', 'business-wizard'),
                'singular_name' => __('Payment Method', 'business-wizard'),
                'add_new' => __('Add New', 'business-wizard'),
                'add_new_item' => __('Add New Payment Method', 'business-wizard'),
                'edit_item' => __('Edit Payment Method', 'business-wizard'),
                'new_item' => __('New Payment Method', 'business-wizard'),
                'view_item' => __('View Payment Method', 'business-wizard'),
                'search_items' => __('Search Payment Methods', 'business-wizard'),
                'not_found' => __('No payment methods found', 'business-wizard'),
                'not_found_in_trash' => __('No payment methods found in trash', 'business-wizard'),
                'menu_name' => __('Payment Methods', 'business-wizard'),
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'true',
            'menu_position' => 8,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'query_var' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'payment-methods'),
        ));
    }
    public function add_meta_boxes() {
        // Package meta boxes
        add_meta_box(
            'biz_package_details',
            __('Package Details', 'business-wizard'),
            array($this, 'render_package_details'),
            'biz_package',
            'normal',
            'high'
        );
        
        add_meta_box(
            'biz_package_assignments',
            __('Package Assignments', 'business-wizard'),
            array($this, 'render_package_assignments'),
            'biz_package',
            'normal',
            'high'
        );
        
        // Turnover meta boxes
        add_meta_box(
            'biz_turnover_details',
            __('Turnover Details', 'business-wizard'),
            array($this, 'render_turnover_details'),
            'biz_turnover',
            'normal',
            'high'
        );
        
        // Payment method meta boxes
        add_meta_box(
            'biz_payment_details',
            __('Payment Method Details', 'business-wizard'),
            array($this, 'render_payment_details'),
            'biz_payment',
            'normal',
            'high'
        );
    }
    
    public function render_package_details($post) {
        wp_nonce_field('biz_package_meta', 'biz_package_meta_nonce');
        
        $price = get_post_meta($post->ID, '_package_price', true);
        $features = get_post_meta($post->ID, '_package_features', true);
        $featured = get_post_meta($post->ID, '_package_featured', true);
        
        if (!is_array($features)) {
            $features = array('', '', '', '', '');
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label for="package_price"><?php _e('Price (£/month)', 'business-wizard'); ?></label></th>
                <td><input type="number" id="package_price" name="package_price" value="<?php echo esc_attr($price); ?>" step="0.01" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label><?php _e('Features', 'business-wizard'); ?></label></th>
                <td>
                    <?php for ($i = 0; $i < 5; $i++) : ?>
                        <input type="text" name="package_features[]" value="<?php echo esc_attr(isset($features[$i]) ? $features[$i] : ''); ?>" class="regular-text" style="margin-bottom: 8px;" /><br>
                    <?php endfor; ?>
                </td>
            </tr>
            <tr>
                <th><label for="package_featured"><?php _e('Featured Package', 'business-wizard'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="package_featured" name="package_featured" value="1" <?php checked($featured, '1'); ?> />
                        <?php _e('Mark as featured (Most Popular)', 'business-wizard'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function render_package_assignments($post) {
        $assignments = get_post_meta($post->ID, '_package_assignments', true);
        if (!is_array($assignments)) {
            $assignments = array();
        }
        
        $business_types = get_posts(array('post_type' => 'biz_type', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC'));
        $turnovers = get_posts(array('post_type' => 'biz_turnover', 'posts_per_page' => -1, 'orderby' => 'meta_value_num', 'meta_key' => '_min_amount', 'order' => 'ASC'));
        ?>
        <p><?php _e('Select which business types and turnover ranges this package should be available for:', 'business-wizard'); ?></p>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Business Type', 'business-wizard'); ?></th>
                    <?php foreach ($turnovers as $turnover) : ?>
                        <th><?php echo esc_html($turnover->post_title); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($business_types as $type) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($type->post_title); ?></strong></td>
                        <?php foreach ($turnovers as $turnover) : ?>
                            <td style="text-align: center;">
                                <?php
                                $key = $type->ID . '_' . $turnover->ID;
                                $checked = isset($assignments[$key]) ? $assignments[$key] : false;
                                ?>
                                <input type="checkbox" name="package_assignments[<?php echo $key; ?>]" value="1" <?php checked($checked, '1'); ?> />
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    public function render_turnover_details($post) {
        wp_nonce_field('biz_turnover_meta', 'biz_turnover_meta_nonce');
        
        $min_amount = get_post_meta($post->ID, '_min_amount', true);
        $max_amount = get_post_meta($post->ID, '_max_amount', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="min_amount"><?php _e('Minimum Amount (£)', 'business-wizard'); ?></label></th>
                <td><input type="number" id="min_amount" name="min_amount" value="<?php echo esc_attr($min_amount); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="max_amount"><?php _e('Maximum Amount (£)', 'business-wizard'); ?></label></th>
                <td><input type="number" id="max_amount" name="max_amount" value="<?php echo esc_attr($max_amount); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }
    
    public function render_payment_details($post) {
        wp_nonce_field('biz_payment_meta', 'biz_payment_meta_nonce');
        
        $gateway_type = get_post_meta($post->ID, '_gateway_type', true);
        $recommended = get_post_meta($post->ID, '_recommended', true);
        $api_key = get_post_meta($post->ID, '_api_key', true);
        $api_secret = get_post_meta($post->ID, '_api_secret', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="gateway_type"><?php _e('Gateway Type', 'business-wizard'); ?></label></th>
                <td>
                    <select id="gateway_type" name="gateway_type" class="regular-text">
                        <option value="stripe" <?php selected($gateway_type, 'stripe'); ?>>Stripe</option>
                        <option value="paypal" <?php selected($gateway_type, 'paypal'); ?>>PayPal</option>
                        <option value="gocardless" <?php selected($gateway_type, 'gocardless'); ?>>GoCardless</option>
                        <option value="debit" <?php selected($gateway_type, 'debit'); ?>>Debit Card</option>
                        <option value="manual" <?php selected($gateway_type, 'manual'); ?>>Manual</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="recommended"><?php _e('Recommended', 'business-wizard'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" id="recommended" name="recommended" value="1" <?php checked($recommended, '1'); ?> />
                        <?php _e('Show as recommended payment method', 'business-wizard'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="api_key"><?php _e('API Key / Client ID', 'business-wizard'); ?></label></th>
                <td><input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="api_secret"><?php _e('API Secret', 'business-wizard'); ?></label></th>
                <td><input type="password" id="api_secret" name="api_secret" value="<?php echo esc_attr($api_secret); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }
    
    public function save_meta_boxes($post_id) {
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save package meta
        if (isset($_POST['biz_package_meta_nonce']) && wp_verify_nonce($_POST['biz_package_meta_nonce'], 'biz_package_meta')) {
            if (isset($_POST['package_price'])) {
                update_post_meta($post_id, '_package_price', sanitize_text_field($_POST['package_price']));
            }
            if (isset($_POST['package_features'])) {
                update_post_meta($post_id, '_package_features', array_map('sanitize_text_field', $_POST['package_features']));
            }
            update_post_meta($post_id, '_package_featured', isset($_POST['package_featured']) ? '1' : '0');
            
            if (isset($_POST['package_assignments'])) {
                update_post_meta($post_id, '_package_assignments', $_POST['package_assignments']);
            } else {
                update_post_meta($post_id, '_package_assignments', array());
            }
        }
        
        // Save turnover meta
        if (isset($_POST['biz_turnover_meta_nonce']) && wp_verify_nonce($_POST['biz_turnover_meta_nonce'], 'biz_turnover_meta')) {
            if (isset($_POST['min_amount'])) {
                update_post_meta($post_id, '_min_amount', absint($_POST['min_amount']));
            }
            if (isset($_POST['max_amount'])) {
                update_post_meta($post_id, '_max_amount', absint($_POST['max_amount']));
            }
        }
        
        // Save payment meta
        if (isset($_POST['biz_payment_meta_nonce']) && wp_verify_nonce($_POST['biz_payment_meta_nonce'], 'biz_payment_meta')) {
            if (isset($_POST['gateway_type'])) {
                update_post_meta($post_id, '_gateway_type', sanitize_text_field($_POST['gateway_type']));
            }
            update_post_meta($post_id, '_recommended', isset($_POST['recommended']) ? '1' : '0');
            if (isset($_POST['api_key'])) {
                update_post_meta($post_id, '_api_key', sanitize_text_field($_POST['api_key']));
            }
            if (isset($_POST['api_secret'])) {
                update_post_meta($post_id, '_api_secret', sanitize_text_field($_POST['api_secret']));
            }
        }
    }
}
// Initialize the custom post types
BIZ_WIZARD_CPT::get_instance();
