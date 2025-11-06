<?php
/**
 * Admin Submissions Page
 * Path: admin/pages/submissions.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get filter values
$business_type = isset($_GET['business_type']) ? sanitize_text_field($_GET['business_type']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// View single submission
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $db = BIZ_WIZARD_Database::get_instance();
    $submission = $db->get_submission(intval($_GET['id']));
    
    if ($submission) {
        $user_data = json_decode($submission->user_data, true);
        $upload_dir = wp_upload_dir();
        ?>
        <div class="wrap">
            <h1><?php _e('View Submission', 'business-wizard'); ?> #<?php echo $submission->id; ?></h1>
            <a href="<?php echo admin_url('admin.php?page=business-wizard-submissions'); ?>" class="button">← <?php _e('Back to Submissions', 'business-wizard'); ?></a>
            
            <div class="biz-submission-details">
                <div class="biz-detail-section">
                    <h2><?php _e('Package Information', 'business-wizard'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Submission Date', 'business-wizard'); ?></th>
                            <td><?php echo date('F j, Y g:i a', strtotime($submission->submission_date)); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Business Type', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($submission->business_type); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Turnover Range', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($submission->turnover_range); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Package Selected', 'business-wizard'); ?></th>
                            <td><strong><?php echo esc_html($submission->package_name); ?></strong></td>
                        </tr>
                        <tr>
                            <th><?php _e('Package Price', 'business-wizard'); ?></th>
                            <td>£<?php echo number_format($submission->package_price, 2); ?>/month</td>
                        </tr>
                        <tr>
                            <th><?php _e('Total Amount (inc. VAT)', 'business-wizard'); ?></th>
                            <td><strong style="color: #6c5ce7; font-size: 18px;">£<?php echo number_format($submission->total_amount, 2); ?>/month</strong></td>
                        </tr>
                        <tr>
                            <th><?php _e('Payment Method', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($submission->payment_method); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Status', 'business-wizard'); ?></th>
                            <td>
                                <select id="submission-status" data-id="<?php echo $submission->id; ?>">
                                    <option value="pending" <?php selected($submission->status, 'pending'); ?>><?php _e('Pending', 'business-wizard'); ?></option>
                                    <option value="completed" <?php selected($submission->status, 'completed'); ?>><?php _e('Completed', 'business-wizard'); ?></option>
                                    <option value="cancelled" <?php selected($submission->status, 'cancelled'); ?>><?php _e('Cancelled', 'business-wizard'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="biz-detail-section">
                    <h2><?php _e('Customer Information', 'business-wizard'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Full Name', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['fullName'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Email', 'business-wizard'); ?></th>
                            <td><a href="mailto:<?php echo esc_attr($user_data['email'] ?? ''); ?>"><?php echo esc_html($user_data['email'] ?? 'N/A'); ?></a></td>
                        </tr>
                        <tr>
                            <th><?php _e('Phone', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['phone'] ?? 'N/A'); ?></td>
                        </tr>
                        
                        <?php if (isset($user_data['companyNumber'])) : ?>
                        <tr>
                            <th colspan="2" style="background: #f5f5f5; padding: 15px;"><strong><?php _e('Company Details', 'business-wizard'); ?></strong></th>
                        </tr>
                        <tr>
                            <th><?php _e('Company Number', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['companyNumber']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Company Name', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['companyName'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Registered Address', 'business-wizard'); ?></th>
                            <td><?php echo nl2br(esc_html($user_data['registeredAddress'] ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Company Type', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['companyType'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Incorporation Date', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['incorporationDate'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th colspan="2" style="background: #f5f5f5; padding: 15px;"><strong><?php _e('Director Information', 'business-wizard'); ?></strong></th>
                        </tr>
                        <tr>
                            <th><?php _e('Director Name', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['directorName'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Director Email', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['directorEmail'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Director Phone', 'business-wizard'); ?></th>
                            <td><?php echo esc_html($user_data['directorPhone'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <?php if (!empty($submission->signature_image)) : ?>
                <div class="biz-detail-section">
                    <h2><?php _e('Digital Signature', 'business-wizard'); ?></h2>
                    <div class="biz-signature-display">
                        <img src="<?php echo esc_url($upload_dir['baseurl'] . $submission->signature_image); ?>" alt="<?php esc_attr_e('Signature', 'business-wizard'); ?>" style="max-width: 400px; border: 2px solid #ddd; padding: 10px; background: white;">
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="biz-detail-actions">
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=business-wizard-submissions&action=delete&id=' . $submission->id), 'delete_submission_' . $submission->id); ?>" 
                       class="button button-danger" 
                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this submission?', 'business-wizard'); ?>')">
                        <?php _e('Delete Submission', 'business-wizard'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
        return;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Submissions', 'business-wizard'); ?></h1>
    <hr class="wp-header-end">
    
    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" action="">
                <input type="hidden" name="page" value="business-wizard-submissions">
                
                <select name="business_type" id="business_type">
                    <option value=""><?php _e('All Business Types', 'business-wizard'); ?></option>
                    <?php
                    $types = get_posts(array('post_type' => 'biz_type', 'posts_per_page' => -1));
                    foreach ($types as $type) {
                        echo '<option value="' . esc_attr($type->post_title) . '" ' . selected($business_type, $type->post_title, false) . '>' . esc_html($type->post_title) . '</option>';
                    }
                    ?>
                </select>
                
                <select name="status" id="status">
                    <option value=""><?php _e('All Statuses', 'business-wizard'); ?></option>
                    <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'business-wizard'); ?></option>
                    <option value="completed" <?php selected($status, 'completed'); ?>><?php _e('Completed', 'business-wizard'); ?></option>
                    <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php _e('Cancelled', 'business-wizard'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'business-wizard'); ?>">
                
                <a href="<?php echo admin_url('admin.php?page=business-wizard-submissions&action=export'); ?>" class="button">
                    <?php _e('Export to CSV', 'business-wizard'); ?>
                </a>
            </form>
        </div>
        
        <div class="alignright actions">
            <form method="get" action="">
                <input type="hidden" name="page" value="business-wizard-submissions">
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search...', 'business-wizard'); ?>">
                <input type="submit" class="button" value="<?php esc_attr_e('Search', 'business-wizard'); ?>">
            </form>
        </div>
    </div>
    
    <!-- Submissions Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 50px;"><?php _e('ID', 'business-wizard'); ?></th>
                <th><?php _e('Date', 'business-wizard'); ?></th>
                <th><?php _e('Name/Email', 'business-wizard'); ?></th>
                <th><?php _e('Business Type', 'business-wizard'); ?></th>
                <th><?php _e('Package', 'business-wizard'); ?></th>
                <th><?php _e('Amount', 'business-wizard'); ?></th>
                <th><?php _e('Status', 'business-wizard'); ?></th>
                <th><?php _e('Actions', 'business-wizard'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($submissions)) : ?>
                <?php foreach ($submissions as $submission) : 
                    $user_data = json_decode($submission->user_data, true);
                ?>
                <tr>
                    <td><strong>#<?php echo $submission->id; ?></strong></td>
                    <td><?php echo date('M j, Y', strtotime($submission->submission_date)); ?><br><small><?php echo date('g:i a', strtotime($submission->submission_date)); ?></small></td>
                    <td>
                        <strong><?php echo esc_html($user_data['fullName'] ?? 'N/A'); ?></strong><br>
                        <small><?php echo esc_html($user_data['email'] ?? ''); ?></small>
                    </td>
                    <td><?php echo esc_html($submission->business_type); ?></td>
                    <td><?php echo esc_html($submission->package_name); ?></td>
                    <td><strong>£<?php echo number_format($submission->total_amount, 2); ?></strong></td>
                    <td>
                        <span class="biz-status-badge biz-status-<?php echo esc_attr($submission->status); ?>">
                            <?php echo ucfirst($submission->status); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=business-wizard-submissions&action=view&id=' . $submission->id); ?>" class="button button-small">
                            <?php _e('View', 'business-wizard'); ?>
                        </a>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=business-wizard-submissions&action=delete&id=' . $submission->id), 'delete_submission_' . $submission->id); ?>" 
                           class="button button-small button-link-delete" 
                           onclick="return confirm('<?php esc_attr_e('Are you sure?', 'business-wizard'); ?>')">
                            <?php _e('Delete', 'business-wizard'); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <div style="font-size: 16px; color: #666;">
                            <?php _e('No submissions found', 'business-wizard'); ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1) : ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $total_pages,
                'current' => $current_page
            ));
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>
