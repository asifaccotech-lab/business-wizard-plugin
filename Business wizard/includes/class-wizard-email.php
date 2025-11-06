<?php
/**
 * Email Notification Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_Email {
    
    public function send_user_email($user_data, $order_data) {
        $to = $user_data['email'];
        
        $email_settings = get_option('biz_wizard_email_settings', array());
        $subject = isset($email_settings['biz_wizard_user_email_subject']) ? 
                  $email_settings['biz_wizard_user_email_subject'] : 
                  'Welcome - Order Confirmation';
        
        $message = $this->get_user_email_template($user_data, $order_data);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    public function send_admin_email($submission_id, $user_data, $order_data) {
        $email_settings = get_option('biz_wizard_email_settings', array());
        
        $admin_emails = isset($email_settings['biz_wizard_admin_emails']) ? 
                       $email_settings['biz_wizard_admin_emails'] : 
                       get_option('admin_email');
        $to = array_map('trim', explode(',', $admin_emails));
        
        $subject = isset($email_settings['biz_wizard_admin_email_subject']) ? 
                  $email_settings['biz_wizard_admin_email_subject'] : 
                  'New Business Wizard Submission';
        $subject .= ' - ' . $order_data['business_type'] . ' - ' . $order_data['package_name'];
        
        $message = $this->get_admin_email_template($submission_id, $user_data, $order_data);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    private function get_user_email_template($user_data, $order_data) {
        $site_name = get_bloginfo('name');
        $name = $user_data['fullName'];
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #6c5ce7; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
                .summary-box { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .summary-row { display: flex; justify-content: space-between; margin: 10px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
                .button { display: inline-block; background: #6c5ce7; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; margin: 20px 0; }
                .next-steps { margin: 20px 0; }
                .next-steps li { margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Welcome to <?php echo esc_html($site_name); ?>!</h1>
                    <p>Thank you for choosing us</p>
                </div>
                
                <div class="content">
                    <p>Dear <?php echo esc_html($name); ?>,</p>
                    
                    <p>Thank you for completing your business onboarding. Your account has been successfully set up!</p>
                    
                    <div class="summary-box">
                        <h2>Order Summary</h2>
                        <div class="summary-row">
                            <span>Business Type:</span>
                            <strong><?php echo esc_html($order_data['business_type']); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Turnover:</span>
                            <strong><?php echo esc_html($order_data['turnover']); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Package:</span>
                            <strong><?php echo esc_html($order_data['package_name']); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Monthly Price:</span>
                            <strong>£<?php echo number_format($order_data['price'], 2); ?>/month</strong>
                        </div>
                        <div class="summary-row" style="border-top: 2px solid #6c5ce7; padding-top: 10px; margin-top: 10px;">
                            <span>Total (inc. VAT):</span>
                            <strong style="color: #6c5ce7; font-size: 18px;">£<?php echo number_format($order_data['total'], 2); ?>/month</strong>
                        </div>
                    </div>
                    
                    <h3>What happens next?</h3>
                    <ul class="next-steps">
                        <li>✓ Your dedicated accountant will contact you within 2 business days</li>
                        <li>✓ Access to your client portal has been set up</li>
                        <li>✓ Direct Debit mandate will be sent for approval</li>
                        <li>✓ We'll guide you through the next steps</li>
                    </ul>
                    
                    <p>If you have any questions, please don't hesitate to contact us.</p>
                    
                    <p>Best regards,<br>The <?php echo esc_html($site_name); ?> Team</p>
                </div>
                
                <div class="footer">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    private function get_admin_email_template($submission_id, $user_data, $order_data) {
        $site_name = get_bloginfo('name');
        $admin_url = admin_url('admin.php?page=business-wizard-submissions&id=' . $submission_id);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background: #23282d; color: white; padding: 20px; }
                .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
                th { background: #f8f9fa; font-weight: 600; }
                .button { display: inline-block; background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>New Business Wizard Submission</h1>
                    <p>Submission ID: #<?php echo $submission_id; ?> | <?php echo date('F j, Y g:i a'); ?></p>
                </div>
                
                <div class="content">
                    <h2>Package Information</h2>
                    <table>
                        <tr>
                            <th>Business Type</th>
                            <td><?php echo esc_html($order_data['business_type']); ?></td>
                        </tr>
                        <tr>
                            <th>Turnover Range</th>
                            <td><?php echo esc_html($order_data['turnover']); ?></td>
                        </tr>
                        <tr>
                            <th>Package Selected</th>
                            <td><?php echo esc_html($order_data['package_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Monthly Price</th>
                            <td>£<?php echo number_format($order_data['price'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Total (inc. VAT)</th>
                            <td><strong>£<?php echo number_format($order_data['total'], 2); ?>/month</strong></td>
                        </tr>
                        <tr>
                            <th>Payment Method</th>
                            <td><?php echo esc_html($order_data['payment_method']); ?></td>
                        </tr>
                    </table>
                    
                    <h2>Customer Information</h2>
                    <table>
                        <tr>
                            <th>Full Name</th>
                            <td><?php echo esc_html($user_data['fullName']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo esc_html($user_data['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo esc_html($user_data['phone'] ?? 'Not provided'); ?></td>
                        </tr>
                        <?php if (isset($user_data['companyNumber'])) : ?>
                        <tr>
                            <th>Company Number</th>
                            <td><?php echo esc_html($user_data['companyNumber']); ?></td>
                        </tr>
                        <tr>
                            <th>Company Name</th>
                            <td><?php echo esc_html($user_data['companyName'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Registered Address</th>
                            <td><?php echo esc_html($user_data['registeredAddress'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Director Name</th>
                            <td><?php echo esc_html($user_data['directorName'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Director Email</th>
                            <td><?php echo esc_html($user_data['directorEmail'] ?? ''); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <?php if (!empty($order_data['signature_path'])) : ?>
                    <h3>Digital Signature</h3>
                    <p>Signature file: <?php echo esc_html($order_data['signature_path']); ?></p>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url($admin_url); ?>" class="button">View Full Submission</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
