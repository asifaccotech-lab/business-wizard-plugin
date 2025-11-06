<?php
/**
 * Database Operations Handler
 * Path: includes/class-wizard-database.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_Database {
    
    private static $instance = null;
    private $table_name;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'business_wizard_submissions';
    }
    
    /**
     * Get submission by ID
     */
    public function get_submission($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Get all submissions with filters
     */
    public function get_submissions($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'business_type' => '',
            'status' => '',
            'search' => '',
            'orderby' => 'submission_date',
            'order' => 'DESC',
            'per_page' => 20,
            'page' => 1
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        
        if (!empty($args['business_type'])) {
            $where[] = $wpdb->prepare("business_type = %s", $args['business_type']);
        }
        
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }
        
        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare("(package_name LIKE %s OR user_data LIKE %s)", $search, $search);
        }
        
        $where_clause = implode(' AND ', $where);
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE {$where_clause} 
                  ORDER BY {$args['orderby']} {$args['order']} 
                  LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($query, $args['per_page'], $offset));
    }
    
    /**
     * Get submission count
     */
    public function get_submission_count($args = array()) {
        global $wpdb;
        
        $where = array('1=1');
        
        if (!empty($args['business_type'])) {
            $where[] = $wpdb->prepare("business_type = %s", $args['business_type']);
        }
        
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }
        
        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare("(package_name LIKE %s OR user_data LIKE %s)", $search, $search);
        }
        
        $where_clause = implode(' AND ', $where);
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}");
    }
    
    /**
     * Update submission status
     */
    public function update_status($id, $status) {
        global $wpdb;
        return $wpdb->update(
            $this->table_name,
            array('status' => sanitize_text_field($status)),
            array('id' => intval($id)),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Delete submission
     */
    public function delete_submission($id) {
        global $wpdb;
        
        // Get submission to delete signature file
        $submission = $this->get_submission($id);
        if ($submission && !empty($submission->signature_image)) {
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'] . $submission->signature_image;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        return $wpdb->delete($this->table_name, array('id' => intval($id)), array('%d'));
    }
    
    /**
     * Get statistics
     */
    public function get_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total submissions
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // This month
        $stats['this_month'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} 
             WHERE MONTH(submission_date) = MONTH(CURRENT_DATE()) 
             AND YEAR(submission_date) = YEAR(CURRENT_DATE())"
        );
        
        // By status
        $stats['pending'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'pending'");
        $stats['completed'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'completed'");
        
        // Popular packages
        $stats['popular_packages'] = $wpdb->get_results(
            "SELECT package_name, COUNT(*) as count 
             FROM {$this->table_name} 
             GROUP BY package_name 
             ORDER BY count DESC 
             LIMIT 5"
        );
        
        return $stats;
    }
}
