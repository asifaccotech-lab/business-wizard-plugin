<?php
/**
 * Wizard Frontend Template
 * Path: public/templates/wizard-template.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$business_types = BIZ_WIZARD_Frontend::get_business_types();
$turnovers = BIZ_WIZARD_Frontend::get_turnover_ranges();
$payment_methods = BIZ_WIZARD_Frontend::get_payment_methods();
?>

<div class="wizard-container" data-redirect="<?php echo esc_attr($atts['redirect_url']); ?>">
    <!-- Main Content Column -->
    <main class="wizard-main">
        <!-- Step 1: Package Selection -->
        <section id="step-1" class="step-content active">
            <div class="step-header">
                <h1 class="step-title"><?php _e('Select Your Package', 'business-wizard'); ?></h1>
                <p class="step-subtitle"><?php _e('Choose the options that best suit your business needs', 'business-wizard'); ?></p>
            </div>

            <!-- Business Type Selector -->
            <div class="form-section">
                <h2 class="section-title"><?php _e('Business Type', 'business-wizard'); ?></h2>
                <div class="business-type-grid">
                    <?php foreach ($business_types as $index => $type) : ?>
                        <label class="radio-card">
                            <input type="radio" name="businessType" 
                                   value="<?php echo esc_attr($type->ID); ?>" 
                                   data-name="<?php echo esc_attr($type->post_title); ?>"
                                   <?php echo $index === 0 ? 'checked' : ''; ?>
                                   aria-label="<?php echo esc_attr($type->post_title); ?>">
                            <span class="radio-content">
                                <span class="radio-label"><?php echo esc_html($type->post_title); ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Turnover Selector -->
            <div class="form-section">
                <h2 class="section-title"><?php _e('Annual Turnover', 'business-wizard'); ?></h2>
                <div class="turnover-grid">
                    <?php foreach ($turnovers as $index => $turnover) : ?>
                        <label class="radio-card">
                            <input type="radio" name="turnover" 
                                   value="<?php echo esc_attr($turnover->ID); ?>"
                                   data-name="<?php echo esc_attr($turnover->post_title); ?>"
                                   <?php echo $index === count($turnovers) - 1 ? 'checked' : ''; ?>
                                   aria-label="<?php echo esc_attr($turnover->post_title); ?>">
                            <span class="radio-content">
                                <span class="radio-label"><?php echo esc_html($turnover->post_title); ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Package Selection Cards -->
            <div class="form-section">
                <h2 class="section-title"><?php _e('Choose your Package', 'business-wizard'); ?></h2>
                <div class="packages-grid" id="packages-container">
                    <div class="packages-loading"><?php _e('Loading packages...', 'business-wizard'); ?></div>
                </div>
            </div>

            <div class="step-actions">
                <button class="btn btn-secondary" onclick="previousStep()" disabled aria-label="<?php esc_attr_e('Go to previous step', 'business-wizard'); ?>">← <?php _e('Back', 'business-wizard'); ?></button>
                <button class="btn btn-primary" onclick="nextStep()" aria-label="<?php esc_attr_e('Continue to next step', 'business-wizard'); ?>"><?php _e('Continue to Personal details', 'business-wizard'); ?> →</button>
            </div>
        </section>

        <!-- Step 2: Personal Details -->
        <section id="step-2" class="step-content">
            <div class="step-header">
                <h1 class="step-title"><?php _e('Personal Details', 'business-wizard'); ?></h1>
                <p class="step-subtitle"><?php _e('Please provide your information to continue', 'business-wizard'); ?></p>
            </div>

            <!-- Company Search (hidden by default, shown for non-sole traders) -->
            <div class="form-section" id="company-search-section" style="display: none;">
                <h2 class="section-title"><?php _e('Company Lookup', 'business-wizard'); ?></h2>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" id="companySearchQuery" class="form-input" placeholder="<?php esc_attr_e('Enter company name or number', 'business-wizard'); ?>" style="flex: 1;">
                    <button type="button" class="btn btn-primary" onclick="searchCompany()"><?php _e('Search', 'business-wizard'); ?></button>
                </div>
                <div id="company-search-results"></div>
            </div>

            <div class="form-section">
                <!-- Sole Trader Fields -->
                <div id="sole-trader-fields">
                    <div class="form-group">
                        <label for="fullName" class="form-label"><?php _e('Full Name', 'business-wizard'); ?> <span class="required">*</span></label>
                        <input type="text" id="fullName" name="fullName" class="form-input" placeholder="John Smith" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label"><?php _e('Email Address', 'business-wizard'); ?> <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="john.smith@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label"><?php _e('Phone Number', 'business-wizard'); ?> <span class="optional">(<?php _e('Optional', 'business-wizard'); ?>)</span></label>
                        <input type="tel" id="phone" name="phone" class="form-input" placeholder="+44 7700 900000">
                    </div>
                </div>

                <!-- Company Fields (hidden by default) -->
                <div id="company-fields" style="display: none;">
                    <div class="form-group">
                        <label for="companyNumber" class="form-label"><?php _e('Company Registration Number', 'business-wizard'); ?> <span class="required">*</span></label>
                        <input type="text" id="companyNumber" name="companyNumber" class="form-input" placeholder="12345678">
                    </div>

                    <div class="form-group">
                        <label for="companyName" class="form-label"><?php _e('Company Name', 'business-wizard'); ?> <span class="required">*</span></label>
                        <input type="text" id="companyName" name="companyName" class="form-input" placeholder="Company Ltd">
                    </div>

                    <div class="form-group">
                        <label for="registeredAddress" class="form-label"><?php _e('Registered Address', 'business-wizard'); ?> <span class="required">*</span></label>
                        <textarea id="registeredAddress" name="registeredAddress" class="form-input" rows="3" placeholder="Street, City, Postcode"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="companyType" class="form-label"><?php _e('Company Type', 'business-wizard'); ?></label>
                        <input type="text" id="companyType" name="companyType" class="form-input" placeholder="Limited Company">
                    </div>

                    <div class="form-group">
                        <label for="incorporationDate" class="form-label"><?php _e('Incorporation Date', 'business-wizard'); ?></label>
                        <input type="date" id="incorporationDate" name="incorporationDate" class="form-input">
                    </div>

                    <h3 style="margin: 30px 0 20px 0; font-size: 18px;"><?php _e('Director Information', 'business-wizard'); ?></h3>

                    <div class="form-group">
                        <label for="directorName" class="form-label"><?php _e('Primary Director Name', 'business-wizard'); ?> <span class="required">*</span></label>
                        <input type="text" id="directorName" name="directorName" class="form-input" placeholder="John Smith">
                    </div>

                    <div class="form-group">
                        <label for="directorEmail" class="form-label"><?php _e('Director Email Address', 'business-wizard'); ?> <span class="required">*</span></label>
                        <input type="email" id="directorEmail" name="directorEmail" class="form-input" placeholder="director@company.com">
                    </div>

                    <div class="form-group">
                        <label for="directorPhone" class="form-label"><?php _e('Director Phone Number', 'business-wizard'); ?> <span class="optional">(<?php _e('Optional', 'business-wizard'); ?>)</span></label>
                        <input type="tel" id="directorPhone" name="directorPhone" class="form-input" placeholder="+44 7700 900000">
                    </div>
                </div>
            </div>

            <div class="step-actions">
                <button class="btn btn-secondary" onclick="previousStep()" aria-label="<?php esc_attr_e('Go to previous step', 'business-wizard'); ?>">← <?php _e('Back', 'business-wizard'); ?></button>
                <button class="btn btn-primary" onclick="nextStep()" aria-label="<?php esc_attr_e('Continue to next step', 'business-wizard'); ?>"><?php _e('Continue to Fees', 'business-wizard'); ?> →</button>
            </div>
        </section>

        <!-- Step 3: Tax Return Services & Fee Calculation -->
        <section id="step-3" class="step-content">
            <div class="step-header">
                <h1 class="step-title"><?php _e('Tax Return Services & Fee Calculation', 'business-wizard'); ?></h1>
                <p class="step-subtitle"><?php _e('Select your service period and review fees', 'business-wizard'); ?></p>
            </div>

            <!-- Company Summary Card (for non-sole traders) -->
            <div class="company-summary-card" id="company-summary-card" style="display: none;">
                <h3 class="summary-card-title"><?php _e('Company Summary', 'business-wizard'); ?></h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label"><?php _e('Company Number:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-company-number">—</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label"><?php _e('Director Name:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-director-name">—</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label"><?php _e('Last Accounts Made:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-year-end">—</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label"><?php _e('Next Accounts Due:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-next-due">—</span>
                    </div>
                </div>
            </div>

            <!-- Tax Year Service Selection -->
            <div class="form-section">
                <h2 class="section-title"><?php _e('Select Tax Return Service', 'business-wizard'); ?></h2>

                <!-- Service Option 1: Full Year -->
                <label class="service-radio-card">
                    <input type="radio" name="service_type" value="full_year" checked>
                    <div class="service-card-content">
                        <div class="service-card-header">
                            <span class="service-card-title"><?php _e('Full Year', 'business-wizard'); ?></span>
                            <span class="service-card-price">£249.00</span>
                        </div>
                        <p class="service-card-description"><?php _e('Complete tax return for a full fiscal year', 'business-wizard'); ?></p>
                    </div>
                </label>
                <div class="service-dropdown" id="full-year-dropdown" style="margin-top: 10px;">
                    <label for="tax-year-select" class="form-label"><?php _e('Select Fiscal Year:', 'business-wizard'); ?></label>
                    <select id="tax-year-select" class="form-input">
                        <option value=""><?php _e('Select year...', 'business-wizard'); ?></option>
                    </select>
                </div>

                <!-- Service Option 2: Partial Year -->
                <label class="service-radio-card">
                    <input type="radio" name="service_type" value="partial_year">
                    <div class="service-card-content">
                        <div class="service-card-header">
                            <span class="service-card-title"><?php _e('Partial Year', 'business-wizard'); ?></span>
                            <span class="service-card-price">£299.00</span>
                        </div>
                        <p class="service-card-description"><?php _e('Tax return for a specific date range within the year', 'business-wizard'); ?></p>
                    </div>
                </label>
                <div class="service-dropdown hidden" id="partial-year-dropdown" style="margin-top: 10px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="from-month-select" class="form-label"><?php _e('From Month:', 'business-wizard'); ?></label>
                            <select id="from-month-select" class="form-input">
                                <option value=""><?php _e('Select month...', 'business-wizard'); ?></option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </div>
                        <div>
                            <label for="to-month-select" class="form-label"><?php _e('To Month:', 'business-wizard'); ?></label>
                            <select id="to-month-select" class="form-input">
                                <option value=""><?php _e('Select month...', 'business-wizard'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Service Option 3: Single Month -->
                <label class="service-radio-card">
                    <input type="radio" name="service_type" value="single_month">
                    <div class="service-card-content">
                        <div class="service-card-header">
                            <span class="service-card-title"><?php _e('Single Month', 'business-wizard'); ?></span>
                            <span class="service-card-price">£99.00</span>
                        </div>
                        <p class="service-card-description"><?php _e('Tax return for one specific month only', 'business-wizard'); ?></p>
                    </div>
                </label>
                <div class="service-dropdown hidden" id="single-month-dropdown" style="margin-top: 10px;">
                    <label for="single-month-select" class="form-label"><?php _e('Select Month:', 'business-wizard'); ?></label>
                    <select id="single-month-select" class="form-input">
                        <option value=""><?php _e('Select month...', 'business-wizard'); ?></option>
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="May">May</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Summary Card -->
            <div class="service-summary-card hidden" id="service-summary-card">
                <h3 class="summary-card-title"><?php _e('Service Summary', 'business-wizard'); ?></h3>
                <div class="summary-details">
                    <div class="summary-row">
                        <span class="summary-label"><?php _e('Company:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-service-company">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label"><?php _e('Selected Period:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-service-period">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label"><?php _e('Base Fee:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-base-fee">£249.00</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label"><?php _e('Extra Fee:', 'business-wizard'); ?></span>
                        <span class="summary-value" id="summary-extra-fee">£0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span class="summary-label"><strong><?php _e('Total Fee:', 'business-wizard'); ?></strong></span>
                        <span class="summary-value"><strong id="summary-total-fee">£249.00</strong></span>
                    </div>
                </div>
            </div>

            <!-- Monthly Subscription Fee (Original) -->
            <div class="form-section">
                <h2 class="section-title"><?php _e('Monthly Subscription', 'business-wizard'); ?></h2>

                <div class="fee-breakdown">
                    <div class="fee-row">
                        <span class="fee-label"><?php _e('Monthly fee (excl. VAT)', 'business-wizard'); ?></span>
                        <span class="fee-amount" id="fee-base">£0.00</span>
                    </div>
                    <div class="fee-row">
                        <span class="fee-label"><?php _e('VAT (20%)', 'business-wizard'); ?></span>
                        <span class="fee-amount" id="fee-vat">£0.00</span>
                    </div>
                    <div class="fee-row total">
                        <span class="fee-label"><?php _e('Total Per Month', 'business-wizard'); ?></span>
                        <span class="fee-amount" id="fee-total">£0.00</span>
                    </div>
                </div>
            </div>

            <div class="step-actions">
                <button class="btn btn-secondary" onclick="previousStep()" aria-label="<?php esc_attr_e('Go to previous step', 'business-wizard'); ?>">← <?php _e('Back', 'business-wizard'); ?></button>
                <button class="btn btn-primary" onclick="nextStep()" aria-label="<?php esc_attr_e('Continue to next step', 'business-wizard'); ?>"><?php _e('Continue to Agreement', 'business-wizard'); ?> →</button>
            </div>
        </section>

        <!-- Step 4: Agreement -->
        <section id="step-4" class="step-content">
            <div class="step-header">
                <h1 class="step-title"><?php _e('Service Agreement', 'business-wizard'); ?></h1>
                <p class="step-subtitle"><?php _e('Please review and sign the agreement to continue', 'business-wizard'); ?></p>
            </div>

            <div class="form-section">
                <h2 class="section-title"><?php _e('Digital Signature', 'business-wizard'); ?></h2>
                
                <div class="signature-box">
                    <canvas id="signatureCanvas" width="600" height="200"></canvas>
                    <p class="signature-placeholder" id="signature-placeholder"><?php _e('Sign here with your mouse or touch', 'business-wizard'); ?></p>
                </div>
                
                <button class="btn btn-secondary btn-small" onclick="clearSignature()" aria-label="<?php esc_attr_e('Clear signature', 'business-wizard'); ?>"><?php _e('Clear signature', 'business-wizard'); ?></button>
            </div>

            <div class="form-section">
                <label class="checkbox-label">
                    <input type="checkbox" id="termsAgree" name="termsAgree" required aria-label="<?php esc_attr_e('I agree to terms and conditions', 'business-wizard'); ?>">
                    <span class="checkbox-text"><?php _e('I have read and agree to the', 'business-wizard'); ?> <span class="link"><?php _e('Terms and conditions', 'business-wizard'); ?></span> <?php _e('outlined in the Service Agreement. I confirm that all information provided is accurate and complete.', 'business-wizard'); ?></span>
                </label>
            </div>

            <div class="step-actions">
                <button class="btn btn-secondary" onclick="previousStep()" aria-label="<?php esc_attr_e('Go to previous step', 'business-wizard'); ?>">← <?php _e('Back', 'business-wizard'); ?></button>
                <button class="btn btn-primary" onclick="nextStep()" aria-label="<?php esc_attr_e('Continue to next step', 'business-wizard'); ?>"><?php _e('Continue to Payment', 'business-wizard'); ?> →</button>
            </div>
        </section>

        <!-- Step 5: Payment Method -->
        <section id="step-5" class="step-content">
            <div class="step-header">
                <h1 class="step-title"><?php _e('Payment Method', 'business-wizard'); ?></h1>
                <p class="step-subtitle"><?php _e("Choose how you'd like to pay", 'business-wizard'); ?></p>
            </div>

            <div class="payment-summary">
                <h3 class="payment-title"><?php _e('Monthly Payment', 'business-wizard'); ?></h3>
                <div class="payment-amount" id="payment-amount">£0.00<span class="payment-period">/month</span></div>
                <p class="payment-date"><?php _e('Starting from', 'business-wizard'); ?> <?php echo date('F Y'); ?></p>
            </div>

            <div class="form-section">
                <h2 class="section-title"><?php _e('Select Payment Method', 'business-wizard'); ?></h2>
                
                <?php foreach ($payment_methods as $index => $method) : 
                    $gateway = get_post_meta($method->ID, '_gateway_type', true);
                    $recommended = get_post_meta($method->ID, '_recommended', true);
                ?>
                    <label class="payment-method-card <?php echo $recommended == '1' ? 'recommended' : ''; ?>">
                        <input type="radio" name="paymentMethod" 
                               value="<?php echo esc_attr($method->ID); ?>"
                               data-name="<?php echo esc_attr($method->post_title); ?>"
                               <?php echo $index === 0 ? 'checked' : ''; ?>
                               aria-label="<?php echo esc_attr($method->post_title); ?>">
                        <?php if ($recommended == '1') : ?>
                            <span class="recommended-badge"><?php _e('Recommended', 'business-wizard'); ?></span>
                        <?php endif; ?>
                        <span class="payment-method-content">
                            <span class="payment-method-icon <?php echo esc_attr($gateway); ?>-icon">
                                <?php if (has_post_thumbnail($method->ID)) : ?>
                                    <?php echo get_the_post_thumbnail($method->ID, 'thumbnail'); ?>
                                <?php else : ?>
                                    <?php echo strtoupper(substr($method->post_title, 0, 2)); ?>
                                <?php endif; ?>
                            </span>
                            <span class="payment-method-text">
                                <span class="payment-method-name"><?php echo esc_html($method->post_title); ?></span>
                                <span class="payment-method-desc"><?php echo esc_html($method->post_content); ?></span>
                            </span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="note-box">
                <p><strong><?php _e('Note:', 'business-wizard'); ?></strong> <?php _e('I have read and agree to the terms and conditions outlined in the Service Agreement above. I confirm that all information provided is accurate and complete.', 'business-wizard'); ?></p>
            </div>

            <div class="step-actions">
                <button class="btn btn-secondary" onclick="previousStep()" aria-label="<?php esc_attr_e('Go to previous step', 'business-wizard'); ?>">← <?php _e('Back', 'business-wizard'); ?></button>
                <button class="btn btn-primary" onclick="submitWizard()" id="submit-btn" aria-label="<?php esc_attr_e('Complete payment', 'business-wizard'); ?>"><?php _e('Proceed to Payment', 'business-wizard'); ?> →</button>
            </div>
        </section>

        <!-- Step 6: Confirmation -->
        <section id="step-6" class="step-content">
            <div class="confirmation-header">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h1 class="step-title"><?php _e('Payment Successful!', 'business-wizard'); ?></h1>
                <p class="step-subtitle"><?php printf(__('Thank you for choosing %s. Your account has been set up successfully.', 'business-wizard'), get_bloginfo('name')); ?></p>
            </div>

            <div class="form-section">
                <h2 class="section-title"><?php _e('Order Summary', 'business-wizard'); ?></h2>
                
                <div class="summary-box">
                    <div class="summary-section">
                        <h3 class="summary-heading"><?php _e('Package Details', 'business-wizard'); ?></h3>
                        <div class="summary-row">
                            <span class="summary-label"><?php _e('Business Type:', 'business-wizard'); ?></span>
                            <span class="summary-value" id="summaryBusinessType"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label"><?php _e('Turnover:', 'business-wizard'); ?></span>
                            <span class="summary-value" id="summaryTurnover"></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label"><?php _e('Package:', 'business-wizard'); ?></span>
                            <span class="summary-value" id="summaryPackage"></span>
                        </div>
                    </div>

                    <div class="summary-section">
                        <h3 class="summary-heading"><?php _e('Services Selected', 'business-wizard'); ?></h3>
                        <div class="summary-row">
                            <span class="summary-label"><?php _e('Monthly Subscription', 'business-wizard'); ?></span>
                            <span class="summary-value" id="summaryPrice"></span>
                        </div>
                    </div>

                    <div class="summary-section total">
                        <div class="summary-row">
                            <span class="summary-label"><?php _e('Total Paid Today', 'business-wizard'); ?></span>
                            <span class="summary-value" id="summaryTotal"></span>
                        </div>
                    </div>
                </div>

                <div class="what-happens-next">
                    <h3 class="section-title"><?php _e('What happens next?', 'business-wizard'); ?></h3>
                    <ul class="next-steps-list">
                        <li>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"></circle></svg>
                            <?php _e('Confirmation email sent to', 'business-wizard'); ?> <span id="confirmationEmail"></span>
                        </li>
                        <li>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"></circle></svg>
                            <?php _e('Your dedicated accountant will contact you within 2 business days', 'business-wizard'); ?>
                        </li>
                        <li>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"></circle></svg>
                            <?php _e('Access to your client portal has been set up', 'business-wizard'); ?>
                        </li>
                        <li>
                            <svg class="step-icon" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"></circle></svg>
                            <?php _e('Direct Debit mandate will be sent for approval', 'business-wizard'); ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="step-actions">
                <button class="btn btn-secondary" onclick="downloadReceipt()" aria-label="<?php esc_attr_e('Download receipt', 'business-wizard'); ?>">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <?php _e('Download Receipt', 'business-wizard'); ?>
                </button>
                <button class="btn btn-primary" onclick="goHome()" aria-label="<?php esc_attr_e('Go to home', 'business-wizard'); ?>"><?php _e('Go To Home', 'business-wizard'); ?> →</button>
            </div>
        </section>
    </main>

    <?php if ($atts['show_sidebar'] === 'true') : ?>
    <!-- Sidebar -->
    <aside class="wizard-sidebar">
        <div class="sidebar-content">
            <h2 class="sidebar-title"><?php _e('Checkout', 'business-wizard'); ?></h2>
            
            <!-- Stepper -->
            <ol class="stepper">
                <li class="step-item completed" data-step="1">
                    <div class="step-dot"><span class="step-number">✓</span></div>
                    <div class="step-info">
                        <div class="step-label"><?php _e('Package', 'business-wizard'); ?></div>
                        <div class="step-status" id="status-1"><?php _e('Select package', 'business-wizard'); ?></div>
                    </div>
                </li>
                <li class="step-item active" data-step="2">
                    <div class="step-dot"><span class="step-number">2</span></div>
                    <div class="step-info">
                        <div class="step-label"><?php _e('Details', 'business-wizard'); ?></div>
                        <div class="step-status"><?php _e('Your information', 'business-wizard'); ?></div>
                    </div>
                </li>
                <li class="step-item" data-step="3">
                    <div class="step-dot"><span class="step-number">3</span></div>
                    <div class="step-info">
                        <div class="step-label"><?php _e('Fees', 'business-wizard'); ?></div>
                        <div class="step-status"><?php _e('Calculate costs', 'business-wizard'); ?></div>
                    </div>
                </li>
                <li class="step-item" data-step="4">
                    <div class="step-dot"><span class="step-number">4</span></div>
                    <div class="step-info">
                        <div class="step-label"><?php _e('Agreement', 'business-wizard'); ?></div>
                        <div class="step-status"><?php _e('Review & sign', 'business-wizard'); ?></div>
                    </div>
                </li>
                <li class="step-item" data-step="5">
                    <div class="step-dot"><span class="step-number">5</span></div>
                    <div class="step-info">
                        <div class="step-label"><?php _e('Payment', 'business-wizard'); ?></div>
                        <div class="step-status"><?php _e('Complete purchase', 'business-wizard'); ?></div>
                    </div>
                </li>
                <li class="step-item" data-step="6">
                    <div class="step-dot"><span class="step-number">6</span></div>
                    <div class="step-info">
                        <div class="step-label"><?php _e('Confirmation', 'business-wizard'); ?></div>
                        <div class="step-status"><?php _e('All done!', 'business-wizard'); ?></div>
                    </div>
                </li>
            </ol>
        </div>
    </aside>
    <?php endif; ?>
</div>
