/**
 * Business Wizard Frontend JavaScript
 * Path: public/js/wizard-script.js
 */

(function($) {
    'use strict';
    
    // Wizard state with default values
    const wizardState = {
        currentStep: 1,
        businessType: null,
        businessTypeName: '',
        turnover: null,
        turnoverName: '',
        packageId: null,
        packageName: '',
        price: 0,
        signature: null,
        termsAgreed: false,
        // Company details
        companyNumber: '',
        companyName: '',
        registeredAddress: '',
        companyType: '',
        incorporationDate: '',
        directorName: '',
        directorEmail: '',
        directorPhone: '',
        // Personal details
        fullName: '',
        email: '',
        phone: ''
    };

    // Load saved state if exists
    const savedState = localStorage.getItem('wizardState');
    if (savedState) {
        Object.assign(wizardState, JSON.parse(savedState));
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        console.log('[Wizard] Initialized');
        initializeWizard();
    });
    
    function initializeWizard() {
        // Set initial business type and turnover
        wizardState.businessType = $('input[name="businessType"]:checked').val();
        wizardState.businessTypeName = $('input[name="businessType"]:checked').data('name');
        wizardState.turnover = $('input[name="turnover"]:checked').val();
        wizardState.turnoverName = $('input[name="turnover"]:checked').data('name');
        
        // Load initial packages
        loadPackages();
        
        // Setup event listeners
        setupEventListeners();
        
        // Setup signature canvas
        setupSignatureCanvas();
        
        // Update sidebar
        updateSidebar();
    }
    
    function setupEventListeners() {
        // Business type change
        $('input[name="businessType"]').on('change', function() {
            wizardState.businessType = $(this).val();
            wizardState.businessTypeName = $(this).data('name');
            loadPackages();
            updateFormFields();
        });
        
        // Turnover change
        $('input[name="turnover"]').on('change', function() {
            wizardState.turnover = $(this).val();
            wizardState.turnoverName = $(this).data('name');
            loadPackages();
        });
        
        // Package selection (delegated event for dynamically loaded packages)
        $(document).on('click', '.btn-package', function() {
            const $card = $(this).closest('.package-card');
            wizardState.packageId = $card.data('package-id');
            wizardState.packageName = $card.data('package-name');
            wizardState.price = parseFloat($card.data('price'));
            
            // Update visual selection
            $('.package-card').css('opacity', '0.6');
            $card.css('opacity', '1');
            
            updateSidebar();
            updateFees();
            console.log('[Wizard] Package selected:', wizardState.packageName);
        });
        
        // Terms checkbox
        $('#termsAgree').on('change', function() {
            wizardState.termsAgreed = $(this).is(':checked');
        });
        
        // Payment method change
        $('input[name="paymentMethod"]').on('change', function() {
            wizardState.paymentMethod = $(this).data('name');
            updateSidebar();
        });
    }
    
    function loadPackages() {
        console.log('[Wizard] Loading packages for:', wizardState.businessType, wizardState.turnover);
        
        $('#packages-container').html('<div class="packages-loading">Loading packages...</div>');
        
        $.ajax({
            url: bizWizard.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_wizard_packages',
                nonce: bizWizard.nonce,
                business_type: wizardState.businessType,
                turnover: wizardState.turnover
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    renderPackages(response.data);
                } else {
                    $('#packages-container').html(
                        '<div class="no-packages-message">' +
                        '<p>No packages available for this combination. Please contact us for custom pricing.</p>' +
                        '</div>'
                    );
                }
            },
            error: function() {
                $('#packages-container').html(
                    '<div class="error-message">Error loading packages. Please refresh the page.</div>'
                );
            }
        });
    }
    
    function renderPackages(packages) {
        let html = '';
        
        packages.forEach(function(pkg, index) {
            const isFeatured = pkg.featured ? 'featured' : '';
            const featuredBadge = pkg.featured ? '<div class="package-badge">MOST POPULAR</div>' : '';
            
            html += `
                <div class="package-card ${isFeatured}" data-package-id="${pkg.id}" data-package-name="${pkg.name}" data-price="${pkg.price}">
                    ${featuredBadge}
                    <div class="package-header">
                        <h3 class="package-name">${pkg.name}</h3>
                        <div class="package-price">
                            <span class="currency">£</span><span class="amount">${pkg.price}</span><span class="period">/month</span>
                        </div>
                    </div>
                    <p class="package-description">${pkg.description}</p>
                    <ul class="package-features">
            `;
            
            if (pkg.features && pkg.features.length > 0) {
                pkg.features.forEach(function(feature) {
                    html += `
                        <li>
                            <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            ${feature}
                        </li>
                    `;
                });
            }
            
            html += `
                    </ul>
                    <button class="btn btn-package ${pkg.featured ? 'btn-package-primary' : ''}" aria-label="Choose ${pkg.name} plan">
                        Choose plan
                    </button>
                </div>
            `;
        });
        
        $('#packages-container').html(html);
    }
    
    function updateFormFields() {
        const isCompany = wizardState.businessTypeName !== 'Sole Trader';
        
        if (isCompany) {
            $('#company-search-section').show();
            $('#sole-trader-fields').hide();
            $('#company-fields').show();
            
            // Set required attributes
            $('#companyNumber, #companyName, #registeredAddress, #directorName, #directorEmail').prop('required', true);
            $('#fullName, #email').prop('required', false);
            
            // Restore company form data if it exists in state
            if (wizardState.companyNumber) {
                $('#companyNumber').val(wizardState.companyNumber);
                $('#companyName').val(wizardState.companyName);
                $('#registeredAddress').val(wizardState.registeredAddress);
                $('#directorName').val(wizardState.directorName);
                $('#directorEmail').val(wizardState.directorEmail);
                $('#directorPhone').val(wizardState.directorPhone);
            }
        } else {
            $('#company-search-section').hide();
            $('#sole-trader-fields').show();
            $('#company-fields').hide();
            
            // Set required attributes
            $('#fullName, #email').prop('required', true);
            $('#companyNumber, #companyName, #registeredAddress, #directorName, #directorEmail').prop('required', false);
            
            // Restore sole trader form data if it exists in state
            if (wizardState.fullName) {
                $('#fullName').val(wizardState.fullName);
                $('#email').val(wizardState.email);
                $('#phone').val(wizardState.phone);
            }
        }
        
        // Save state after form update
        saveState();
    }
    
    // Company search functionality with improved feedback
    let searchTimeout = null;
    
    $('#companySearchQuery').on('input', function() {
        const query = $(this).val().trim();
        const $results = $('#company-search-results');
        
        // Clear previous timeout and hide results if query too short
        if (searchTimeout) clearTimeout(searchTimeout);
        
        if (query.length < 3) {
            $results.html('<div class="no-results">Type at least 3 characters to search</div>').show();
            return;
        }
        
        // Set new timeout to prevent too many API calls
        searchTimeout = setTimeout(function() {
            $results.html('<div class="loading">Searching companies...</div>').show();
            
            $.ajax({
                url: bizWizard.ajaxurl,
                type: 'POST',
                data: {
                    action: 'search_company',
                    nonce: bizWizard.nonce,
                    query: query
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        renderCompanyResults(response.data);
                    } else {
                        $results.html(
                            '<div class="no-results">' +
                            'No companies found. Try:<br>' +
                            '• Check spelling<br>' +
                            '• Use company number<br>' +
                            '• Try full company name<br>' +
                            '• Enter details manually below' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        $results.html(
                            '<div class="error-message">' +
                            'API key not configured. Please check Settings → Business Wizard.' +
                            '</div>'
                        );
                    } else {
                        $results.html(
                            '<div class="error-message">' +
                            'Error searching. Please try again or enter details manually.' +
                            '</div>'
                        );
                    }
                }
            });
        }, 500);
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#company-search-section').length) {
            $('#company-search-results').hide();
        }
    });
    
    function renderCompanyResults(results) {
        let html = '<div class="company-results">';
        
        results.forEach(function(company) {
            html += `
                <div class="company-result-item" onclick="selectCompany('${company.company_number}')">
                    <strong>${company.company_name}</strong><br>
                    <small>${company.company_number} - ${company.company_status}</small><br>
                    <small>${company.address}</small>
                </div>
            `;
        });
        
        html += '</div>';
        $('#company-search-results').html(html);
    }
    
    window.selectCompany = function(companyNumber) {
        console.log('[Wizard] Fetching company details:', companyNumber);
        
        $.ajax({
            url: bizWizard.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_company_details',
                nonce: bizWizard.nonce,
                company_number: companyNumber
            },
            success: function(response) {
                if (response.success) {
                    fillCompanyDetails(response.data);
                }
            }
        });
    };
    
    function fillCompanyDetails(details) {
        $('#companyNumber').val(details.company_number);
        $('#companyName').val(details.company_name);
        $('#registeredAddress').val(details.registered_address);
        $('#companyType').val(details.company_type);
        $('#incorporationDate').val(details.date_of_creation);
        
        $('#company-search-results').html(
            '<div class="success-message">✓ Company details loaded successfully</div>'
        );
        
        // Scroll to company info
        $('#company-info-box').show();
        $('#company-info-number').text('Company Number: ' + details.company_number);
        $('#company-info-name').text('Company Name: ' + details.company_name);
        $('#company-info-type').text('Company Type: ' + details.company_type);
    }
    
    function updateFees() {
        const basePrice = wizardState.price;
        const vat = basePrice * 0.20;
        const total = basePrice + vat;
        
        $('#fee-base').text('£' + basePrice.toFixed(2));
        $('#fee-vat').text('£' + vat.toFixed(2));
        $('#fee-total').text('£' + total.toFixed(2));
        $('#payment-amount').html('£' + total.toFixed(2) + '<span class="payment-period">/month</span>');
    }
    
    // Signature canvas setup
    function setupSignatureCanvas() {
        const canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let hasSignature = false;
        
        function startDrawing(e) {
            isDrawing = true;
            hasSignature = true;
            $('#signature-placeholder').hide();
            const rect = canvas.getBoundingClientRect();
            ctx.beginPath();
            
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            ctx.moveTo(x, y);
        }
        
        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            
            ctx.lineTo(x, y);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#333';
            ctx.stroke();
        }
        
        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                wizardState.signature = canvas.toDataURL('image/png');
                console.log('[Wizard] Signature saved');
            }
        }
        
        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        
        // Touch events
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
    }
    
    window.clearSignature = function() {
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        wizardState.signature = null;
        $('#signature-placeholder').show();
        console.log('[Wizard] Signature cleared');
    };
    
    // Step navigation
    window.nextStep = function() {
        if (!validateStep(wizardState.currentStep)) {
            return;
        }
        
        if (wizardState.currentStep < 6) {
            wizardState.currentStep++;
            renderStep(wizardState.currentStep);
            updateSidebar();
            scrollToTop();
        }
    };
    
    window.previousStep = function() {
        if (wizardState.currentStep > 1) {
            wizardState.currentStep--;
            renderStep(wizardState.currentStep);
            updateSidebar();
            scrollToTop();
        }
    };
    
    function renderStep(stepNumber) {
        console.log('[Wizard] Rendering step:', stepNumber);
        
        $('.step-content').removeClass('active');
        $('#step-' + stepNumber).addClass('active');
        
        // Update back button
        $('.btn-secondary[onclick="previousStep()"]').prop('disabled', stepNumber === 1);
        
        // Populate data on step 3
        if (stepNumber === 3) {
            updateFees();
            if (wizardState.businessTypeName !== 'Sole Trader') {
                $('#company-info-box').show();
            }
        }
        
        // Reset signature placeholder on step 4
        if (stepNumber === 4) {
            if (!wizardState.signature) {
                $('#signature-placeholder').show();
            }
        }
    }
    
    function validateStep(stepNumber) {
        console.log('[Wizard] Validating step:', stepNumber);
        
        // Save state before validation
        saveState();
        
        switch (stepNumber) {
            case 1:
                if (!wizardState.packageId) {
                    alert('Please select a package');
                    return false;
                }
                return true;
                
            case 2:
                if (wizardState.businessTypeName === 'Sole Trader') {
                    const name = $('#fullName').val().trim();
                    const email = $('#email').val().trim();
                    
                    if (!name) {
                        alert('Please enter your full name');
                        $('#fullName').focus();
                        return false;
                    }
                    
                    if (!email || !isValidEmail(email)) {
                        alert('Please enter a valid email address');
                        $('#email').focus();
                        return false;
                    }
                } else {
                    const companyNumber = $('#companyNumber').val().trim();
                    const companyName = $('#companyName').val().trim();
                    const address = $('#registeredAddress').val().trim();
                    const directorName = $('#directorName').val().trim();
                    const directorEmail = $('#directorEmail').val().trim();
                    
                    if (!companyNumber) {
                        alert('Please enter company registration number');
                        $('#companyNumber').focus();
                        return false;
                    }
                    
                    if (!companyName) {
                        alert('Please enter company name');
                        $('#companyName').focus();
                        return false;
                    }
                    
                    if (!address) {
                        alert('Please enter registered address');
                        $('#registeredAddress').focus();
                        return false;
                    }
                    
                    if (!directorName) {
                        alert('Please enter director name');
                        $('#directorName').focus();
                        return false;
                    }
                    
                    if (!directorEmail || !isValidEmail(directorEmail)) {
                        alert('Please enter a valid director email');
                        $('#directorEmail').focus();
                        return false;
                    }
                }
                return true;
                
            case 4:
                if (!$('#termsAgree').is(':checked')) {
                    alert('Please agree to the terms and conditions');
                    return false;
                }
                
                if (!wizardState.signature) {
                    alert('Please provide your signature');
                    return false;
                }
                return true;
                
            default:
                return true;
        }
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // Save wizard state to localStorage
    function saveState() {
        localStorage.setItem('wizardState', JSON.stringify(wizardState));
    }

    // Submit wizard with improved state management
    window.submitWizard = function() {
        console.log('[Wizard] Submitting form...');
        
        const $btn = $('#submit-btn');
        $btn.prop('disabled', true).text('Processing...');
        
        // Update state with latest form values
        if (wizardState.businessTypeName === 'Sole Trader') {
            wizardState.fullName = $('#fullName').val();
            wizardState.email = $('#email').val();
            wizardState.phone = $('#phone').val();
        } else {
            wizardState.directorName = $('#directorName').val();
            wizardState.directorEmail = $('#directorEmail').val();
            wizardState.directorPhone = $('#directorPhone').val();
        }
        
        // Save final state
        saveState();
        
        // Collect all form data
        const formData = {
            action: 'submit_wizard',
            nonce: bizWizard.nonce,
            businessType: wizardState.businessTypeName,
            turnover: wizardState.turnoverName,
            packageId: wizardState.packageId,
            packageName: wizardState.packageName,
            price: wizardState.price,
            paymentMethod: wizardState.paymentMethod,
            signature: wizardState.signature
        };
        
        // Add personal or company data
        if (wizardState.businessTypeName === 'Sole Trader') {
            formData.fullName = $('#fullName').val();
            formData.email = $('#email').val();
            formData.phone = $('#phone').val();
        } else {
            formData.companyNumber = $('#companyNumber').val();
            formData.companyName = $('#companyName').val();
            formData.registeredAddress = $('#registeredAddress').val();
            formData.companyType = $('#companyType').val();
            formData.incorporationDate = $('#incorporationDate').val();
            formData.directorName = $('#directorName').val();
            formData.directorEmail = $('#directorEmail').val();
            formData.directorPhone = $('#directorPhone').val();
            formData.fullName = $('#directorName').val();
            formData.email = $('#directorEmail').val();
        }
        
        $.ajax({
            url: bizWizard.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    console.log('[Wizard] Submission successful');
                    wizardState.currentStep = 6;
                    renderStep(6);
                    populateConfirmation(formData);
                    updateSidebar();
                    scrollToTop();
                } else {
                    alert('Error: ' + (response.data.message || 'Submission failed'));
                    $btn.prop('disabled', false).text('Proceed to Payment →');
                }
            },
            error: function() {
                alert('Error communicating with server. Please try again.');
                $btn.prop('disabled', false).text('Proceed to Payment →');
            }
        });
    };
    
    function populateConfirmation(data) {
        $('#summaryBusinessType').text(data.businessType);
        $('#summaryTurnover').text(data.turnover);
        $('#summaryPackage').text(data.packageName);
        
        const total = data.price * 1.20;
        $('#summaryPrice').text('£' + total.toFixed(2) + '/month');
        $('#summaryTotal').text('£' + total.toFixed(2));
        $('#confirmationEmail').text(data.email);
    }
    
    function updateSidebar() {
        // Update step states
        $('.step-item').each(function(index) {
            const stepNum = index + 1;
            $(this).removeClass('active completed');
            
            if (stepNum === wizardState.currentStep) {
                $(this).addClass('active');
            } else if (stepNum < wizardState.currentStep) {
                $(this).addClass('completed');
                $(this).find('.step-number').text('✓');
            } else {
                $(this).find('.step-number').text(stepNum);
            }
        });
        
        // Update step 1 status
        if (wizardState.packageName) {
            $('#status-1').text(wizardState.packageName + ' - £' + wizardState.price + '/mo');
        }
    }
    
    function scrollToTop() {
        $('html, body').animate({ scrollTop: 0 }, 300);
    }
    
    // Download receipt
    window.downloadReceipt = function() {
        alert('Receipt will be sent to your email. You can also download it from your confirmation email.');
    };
    
    // Go home
    window.goHome = function() {
        // Clear wizard state before redirecting
        localStorage.removeItem('wizardState');
        const redirectUrl = $('.wizard-container').data('redirect') || bizWizard.siteUrl;
        window.location.href = redirectUrl;
    };
    
})(jQuery);
