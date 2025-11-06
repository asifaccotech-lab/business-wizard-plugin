<?php
/**
 * Admin Dashboard Page
 * Path: admin/pages/dashboard.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$db = BIZ_WIZARD_Database::get_instance();
$stats = $db->get_stats();
$recent = $db->get_submissions(array('per_page' => 10, 'page' => 1));
?>

<div class="wrap">
    <h1><?php _e('Business Wizard Dashboard', 'business-wizard'); ?></h1>
    
    <?php if (isset($_GET['welcome']) && $_GET['welcome'] == '1') : ?>
    <div class="notice notice-success is-dismissible">
        <h2><?php _e('Welcome to Business Onboarding Wizard!', 'business-wizard'); ?></h2>
        <p><?php _e('Thank you for installing the plugin. Here\'s how to get started:', 'business-wizard'); ?></p>
        <ol>
            <li><?php _e('Configure your settings', 'business-wizard'); ?> - <a href="<?php echo admin_url('admin.php?page=business-wizard-settings'); ?>"><?php _e('Go to Settings', 'business-wizard'); ?></a></li>
            <li><?php _e('Add your first package', 'business-wizard'); ?> - <a href="<?php echo admin_url('post-new.php?post_type=biz_package'); ?>"><?php _e('Create Package', 'business-wizard'); ?></a></li>
            <li><?php _e('Add shortcode to a page:', 'business-wizard'); ?> <code>[business_wizard]</code></li>
        </ol>
    </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="biz-wizard-stats">
        <div class="biz-stat-card">
            <div class="biz-stat-icon" style="background: #6c5ce7;">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="biz-stat-content">
                <h3><?php echo number_format($stats['total']); ?></h3>
                <p><?php _e('Total Submissions', 'business-wizard'); ?></p>
            </div>
        </div>
        
        <div class="biz-stat-card">
            <div class="biz-stat-icon" style="background: #27ae60;">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="biz-stat-content">
                <h3><?php echo number_format($stats['this_month']); ?></h3>
                <p><?php _e('This Month', 'business-wizard'); ?></p>
            </div>
        </div>
        
        <div class="biz-stat-card">
            <div class="biz-stat-icon" style="background: #f39c12;">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="biz-stat-content">
                <h3><?php echo number_format($stats['pending']); ?></h3>
                <p><?php _e('Pending', 'business-wizard'); ?></p>
            </div>
        </div>
        
        <div class="biz-stat-card">
            <div class="biz-stat-icon" style="background: #3498db;">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="biz-stat-content">
                <h3><?php echo number_format($stats['completed']); ?></h3>
                <p><?php _e('Completed', 'business-wizard'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="biz-wizard-dashboard-grid">
        <!-- Recent Submissions -->
        <div class="biz-dashboard-section">
            <h2><?php _e('Recent Submissions', 'business-wizard'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'business-wizard'); ?></th>
                        <th><?php _e('Date', 'business-wizard'); ?></th>
                        <th><?php _e('Business Type', 'business-wizard'); ?></th>
                        <th><?php _e('Package', 'business-wizard'); ?></th>
                        <th><?php _e('Amount', 'business-wizard'); ?></th>
                        <th><?php _e('Status', 'business-wizard'); ?></th>
                        <th><?php _e('Actions', 'business-wizard'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent)) : ?>
                        <?php foreach ($recent as $submission) : 
                            $user_data = json_decode($submission->user_data, true);
                        ?>
                        <tr>
                            <td><strong>#<?php echo $submission->id; ?></strong></td>
                            <td><?php echo date('M j, Y', strtotime($submission->submission_date)); ?></td>
                            <td><?php echo esc_html($submission->business_type); ?></td>
                            <td><?php echo esc_html($submission->package_name); ?></td>
                            <td>£<?php echo number_format($submission->total_amount, 2); ?></td>
                            <td>
                                <span class="biz-status-badge biz-status-<?php echo esc_attr($submission->status); ?>">
                                    <?php echo ucfirst($submission->status); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=business-wizard-submissions&action=view&id=' . $submission->id); ?>" class="button button-small">
                                    <?php _e('View', 'business-wizard'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <?php _e('No submissions yet', 'business-wizard'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p>
                <a href="<?php echo admin_url('admin.php?page=business-wizard-submissions'); ?>" class="button">
                    <?php _e('View All Submissions', 'business-wizard'); ?> →
                </a>
            </p>
        </div>
        
        <!-- Popular Packages -->
        <div class="biz-dashboard-sidebar">
            <div class="biz-dashboard-widget">
                <h3><?php _e('Popular Packages', 'business-wizard'); ?></h3>
                <?php if (!empty($stats['popular_packages'])) : ?>
                    <ul class="biz-popular-list">
                        <?php foreach ($stats['popular_packages'] as $package) : ?>
                        <li>
                            <span class="package-name"><?php echo esc_html($package->package_name); ?></span>
                            <span class="package-count"><?php echo $package->count; ?> <?php _e('submissions', 'business-wizard'); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php _e('No data available', 'business-wizard'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="biz-dashboard-widget">
                <h3><?php _e('Quick Actions', 'business-wizard'); ?></h3>
                <ul class="biz-quick-actions">
                    <li>
                        <a href="<?php echo admin_url('post-new.php?post_type=biz_package'); ?>" class="button button-primary button-large" style="width: 100%;">
                            <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add New Package', 'business-wizard'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=business-wizard-submissions'); ?>" class="button button-large" style="width: 100%;">
                            <span class="dashicons dashicons-list-view"></span> <?php _e('View Submissions', 'business-wizard'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=business-wizard-settings'); ?>" class="button button-large" style="width: 100%;">
                            <span class="dashicons dashicons-admin-settings"></span> <?php _e('Settings', 'business-wizard'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="biz-dashboard-widget">
                <h3><?php _e('Shortcode', 'business-wizard'); ?></h3>
                <p><?php _e('Add this shortcode to any page:', 'business-wizard'); ?></p>
                <code style="display: block; padding: 10px; background: #f5f5f5; margin: 10px 0;">[business_wizard]</code>
                <p><?php _e('With options:', 'business-wizard'); ?></p>
                <code style="display: block; padding: 10px; background: #f5f5f5; margin: 10px 0; font-size: 11px;">[business_wizard redirect_url="/thank-you" show_sidebar="true"]</code>
            </div>
        </div>
    </div>
</div>
