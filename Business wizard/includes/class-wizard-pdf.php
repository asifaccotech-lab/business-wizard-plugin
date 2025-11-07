<?php
/**
 * PDF Receipt Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_PDF {

    /**
     * Generate PDF receipt
     *
     * @param int $submission_id Submission ID
     * @return string|WP_Error Path to PDF file or error
     */
    public static function generate_receipt($submission_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'business_wizard_submissions';

        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $submission_id
        ));

        if (!$submission) {
            return new WP_Error('no_submission', __('Submission not found', 'business-wizard'));
        }

        $user_data = json_decode($submission->user_data, true);

        // Create uploads directory for receipts
        $upload_dir = wp_upload_dir();
        $receipt_dir = $upload_dir['basedir'] . '/wizard-receipts';

        if (!file_exists($receipt_dir)) {
            wp_mkdir_p($receipt_dir);
        }

        $filename = 'receipt-' . $submission_id . '-' . time() . '.pdf';
        $filepath = $receipt_dir . '/' . $filename;

        // Generate HTML content
        $html = self::generate_receipt_html($submission, $user_data);

        // Check if we can use external libraries
        if (class_exists('TCPDF')) {
            // Use TCPDF if available
            return self::generate_with_tcpdf($html, $filepath);
        } elseif (class_exists('Dompdf\Dompdf')) {
            // Use Dompdf if available
            return self::generate_with_dompdf($html, $filepath);
        } else {
            // Fall back to basic HTML-based PDF (using browser print to PDF)
            return self::generate_basic_pdf($html, $filepath, $submission_id);
        }
    }

    /**
     * Generate receipt HTML
     */
    private static function generate_receipt_html($submission, $user_data) {
        $company_name = get_bloginfo('name');
        $date = date('d F Y', strtotime($submission->submission_date));

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Receipt - ' . $submission->id . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 40px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 40px;
                    border-bottom: 3px solid #0066cc;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #0066cc;
                    margin: 0 0 10px 0;
                    font-size: 32px;
                }
                .receipt-info {
                    background: #f5f5f5;
                    padding: 20px;
                    margin-bottom: 30px;
                    border-radius: 5px;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }
                .info-label {
                    font-weight: bold;
                    color: #666;
                }
                .section {
                    margin-bottom: 30px;
                }
                .section h2 {
                    color: #0066cc;
                    border-bottom: 2px solid #e0e0e0;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                th, td {
                    padding: 12px;
                    text-align: left;
                    border-bottom: 1px solid #e0e0e0;
                }
                th {
                    background: #f8f8f8;
                    font-weight: bold;
                    color: #333;
                }
                .total-row {
                    background: #f0f7ff;
                    font-weight: bold;
                    font-size: 16px;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                    border-top: 2px solid #e0e0e0;
                    padding-top: 20px;
                }
                .status {
                    display: inline-block;
                    padding: 5px 15px;
                    border-radius: 3px;
                    font-weight: bold;
                    background: #4CAF50;
                    color: white;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . esc_html($company_name) . '</h1>
                <p><strong>PAYMENT RECEIPT</strong></p>
            </div>

            <div class="receipt-info">
                <div class="info-row">
                    <span class="info-label">Receipt Number:</span>
                    <span>#' . str_pad($submission->id, 6, '0', STR_PAD_LEFT) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span>' . $date . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status">' . strtoupper(esc_html($submission->status)) . '</span>
                </div>
            </div>

            <div class="section">
                <h2>Customer Information</h2>
                <table>
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>' . esc_html($user_data['fullName'] ?? $user_data['directorName'] ?? 'N/A') . '</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>' . esc_html($user_data['email'] ?? $user_data['directorEmail'] ?? 'N/A') . '</td>
                    </tr>';

        if (!empty($user_data['companyNumber'])) {
            $html .= '
                    <tr>
                        <td><strong>Company Name:</strong></td>
                        <td>' . esc_html($user_data['companyName'] ?? 'N/A') . '</td>
                    </tr>
                    <tr>
                        <td><strong>Company Number:</strong></td>
                        <td>' . esc_html($user_data['companyNumber']) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Registered Address:</strong></td>
                        <td>' . esc_html($user_data['registeredAddress'] ?? 'N/A') . '</td>
                    </tr>';
        }

        $html .= '
                </table>
            </div>

            <div class="section">
                <h2>Service Details</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Business Type</th>
                            <th>Turnover</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>' . esc_html($submission->package_name) . '</td>
                            <td>' . esc_html($submission->business_type) . '</td>
                            <td>' . esc_html($submission->turnover_range) . '</td>
                            <td style="text-align: right;">£' . number_format($submission->package_price, 2) . '</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                            <td style="text-align: right;">£' . number_format($submission->package_price, 2) . '</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>VAT (20%):</strong></td>
                            <td style="text-align: right;">£' . number_format($submission->package_price * 0.20, 2) . '</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right;"><strong>TOTAL AMOUNT:</strong></td>
                            <td style="text-align: right;"><strong>£' . number_format($submission->total_amount, 2) . '</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>Payment Information</h2>
                <table>
                    <tr>
                        <td><strong>Payment Method:</strong></td>
                        <td>' . esc_html($submission->payment_method) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Status:</strong></td>
                        <td>' . ucfirst(esc_html($submission->status)) . '</td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                <p><strong>' . esc_html($company_name) . '</strong></p>
                <p>Thank you for your business!</p>
                <p>This is an automatically generated receipt. For any queries, please contact us.</p>
                <p>' . esc_html(get_bloginfo('url')) . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Generate basic PDF (HTML file that can be printed as PDF)
     */
    private static function generate_basic_pdf($html, $filepath, $submission_id) {
        // For now, save as HTML file
        // In production, you would integrate a proper PDF library
        $html_path = str_replace('.pdf', '.html', $filepath);
        file_put_contents($html_path, $html);

        return $html_path;
    }

    /**
     * Get download URL for receipt
     */
    public static function get_download_url($submission_id) {
        return admin_url('admin-ajax.php?action=download_receipt&submission_id=' . $submission_id . '&nonce=' . wp_create_nonce('download_receipt_' . $submission_id));
    }

    /**
     * Handle receipt download
     */
    public static function handle_download() {
        if (!isset($_GET['submission_id']) || !isset($_GET['nonce'])) {
            wp_die(__('Invalid request', 'business-wizard'));
        }

        $submission_id = intval($_GET['submission_id']);
        $nonce = sanitize_text_field($_GET['nonce']);

        if (!wp_verify_nonce($nonce, 'download_receipt_' . $submission_id)) {
            wp_die(__('Security check failed', 'business-wizard'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'business_wizard_submissions';

        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $submission_id
        ));

        if (!$submission) {
            wp_die(__('Submission not found', 'business-wizard'));
        }

        $user_data = json_decode($submission->user_data, true);
        $html = self::generate_receipt_html($submission, $user_data);

        // Set headers for HTML download that can be printed as PDF
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="receipt-' . $submission_id . '.html"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        echo $html;
        exit;
    }
}

// Register AJAX handler for receipt download
add_action('wp_ajax_download_receipt', array('BIZ_WIZARD_PDF', 'handle_download'));
add_action('wp_ajax_nopriv_download_receipt', array('BIZ_WIZARD_PDF', 'handle_download'));
