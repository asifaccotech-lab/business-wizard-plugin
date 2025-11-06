/**
 * Admin JavaScript for Business Wizard
 * Path: admin/js/admin-script.js
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Update submission status
        $('#submission-status').on('change', function() {
            const submissionId = $(this).data('id');
            const newStatus = $(this).val();
            
            if (confirm('Are you sure you want to change the status?')) {
                $.ajax({
                    url: bizWizardAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_submission_status',
                        nonce: bizWizardAdmin.nonce,
                        submission_id: submissionId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Status updated successfully');
                            location.reload();
                        } else {
                            alert('Error updating status: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Error communicating with server');
                    }
                });
            }
        });
        
        // Dismiss notices
        $('.notice.is-dismissible').on('click', '.notice-dismiss', function() {
            $(this).closest('.notice').fadeOut();
        });
        
        // Confirm delete actions
        $('.button-danger, .button-link-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Auto-save settings notification
        let settingsChanged = false;
        $('.form-table input, .form-table select, .form-table textarea').on('change', function() {
            settingsChanged = true;
        });
        
        $(window).on('beforeunload', function() {
            if (settingsChanged) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
        $('form').on('submit', function() {
            settingsChanged = false;
        });
        
        // Copy shortcode to clipboard
        $('.biz-copy-shortcode').on('click', function(e) {
            e.preventDefault();
            const shortcode = $(this).data('shortcode');
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(shortcode).select();
            document.execCommand('copy');
            $temp.remove();
            
            $(this).text('Copied!');
            setTimeout(() => {
                $(this).text('Copy');
            }, 2000);
        });
        
        // Filter submissions
        $('#business_type, #status').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Enhanced table row hover
        $('.wp-list-table tbody tr').hover(
            function() {
                $(this).css('background-color', '#f9f9f9');
            },
            function() {
                $(this).css('background-color', '');
            }
        );
        
        // Add loading state to buttons
        $('.button-primary').on('click', function() {
            const $btn = $(this);
            if (!$btn.hasClass('disabled')) {
                $btn.addClass('disabled').css('opacity', '0.6');
                setTimeout(() => {
                    $btn.removeClass('disabled').css('opacity', '1');
                }, 3000);
            }
        });
        
        // Statistics animation on dashboard
        $('.biz-stat-content h3').each(function() {
            const $this = $(this);
            const target = parseInt($this.text().replace(/,/g, ''));
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(function() {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                $this.text(Math.floor(current).toLocaleString());
            }, 20);
        });
        
    });
    
})(jQuery);
