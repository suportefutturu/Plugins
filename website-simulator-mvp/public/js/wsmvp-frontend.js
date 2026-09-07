/**
 * Frontend JavaScript for Website Simulator MVP
 * 
 * Handles the multi-step form, price calculation, and submission
 * 
 * @package WSMVP
 */

(function($) {
    'use strict';
    
    class WebsiteSimulator {
        constructor() {
            this.currentStep = 1;
            this.totalSteps = Object.keys(wsmvpData.steps).length;
            this.answers = {};
            this.calculationResult = null;
            
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.updateProgress();
        }
        
        bindEvents() {
            // Navigation buttons
            $('#nextBtn').on('click', () => this.nextStep());
            $('#prevBtn').on('click', () => this.prevStep());
            
            // Form submission
            $('#wsmvp-form').on('submit', (e) => this.handleSubmit(e));
            
            // Option selection styling
            $('.wsmvp-radio-option, .wsmvp-checkbox-option').on('click', function() {
                if ($(this).find('input[type="radio"]').length) {
                    $(this).siblings().removeClass('selected');
                    $(this).addClass('selected');
                } else if ($(this).find('input[type="checkbox"]').length) {
                    $(this).toggleClass('selected');
                }
            });
            
            // Preview toggle
            $('#togglePreviewBtn').on('click', () => this.togglePreview());
            
            // Real-time price calculation on answer change
            $('.wsmvp-question-field input, .wsmvp-question-field select, .wsmvp-question-field textarea').on('change', () => {
                this.collectAnswers();
                this.calculatePrice();
            });
        }
        
        collectAnswers() {
            this.answers = {};
            
            $('.wsmvp-step.active').each((_, step) => {
                const $step = $(step);
                
                // Get select values
                $step.find('select').each((_, select) => {
                    const name = $(select).attr('name');
                    const value = $(select).val();
                    if (value && name) {
                        this.answers[name] = value;
                    }
                });
                
                // Get radio values
                $step.find('input[type="radio"]:checked').each((_, radio) => {
                    const name = $(radio).attr('name');
                    const value = $(radio).val();
                    if (value && name) {
                        this.answers[name] = value;
                    }
                });
                
                // Get checkbox values
                $step.find('input[type="checkbox"]:checked').each((_, checkbox) => {
                    const name = $(checkbox).attr('name').replace('[]', '');
                    if (!this.answers[name]) {
                        this.answers[name] = [];
                    }
                    this.answers[name].push($(checkbox).val());
                });
                
                // Get text inputs
                $step.find('input[type="text"], input[type="email"], input[type="number"], input[type="tel"], textarea').each((_, input) => {
                    const name = $(input).attr('name');
                    const value = $(input).val();
                    if (value && name) {
                        this.answers[name] = value;
                    }
                });
            });
        }
        
        calculatePrice() {
            if (Object.keys(this.answers).length === 0) {
                return;
            }
            
            $.ajax({
                url: wsmvpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wsmvp_calculate_price',
                    nonce: wsmvpData.nonce,
                    answers: this.answers
                },
                success: (response) => {
                    if (response.success) {
                        this.calculationResult = response.data;
                        this.updateResultDisplay();
                    }
                }
            });
        }
        
        updateResultDisplay() {
            if (!this.calculationResult) return;
            
            const result = this.calculationResult;
            
            $('#result-category').text(this.getCategoryLabel(result.category));
            $('#result-price-min').text(this.formatPrice(result.price_min, result.currency_symbol));
            $('#result-price-max').text(this.formatPrice(result.price_max, result.currency_symbol));
            $('#result-deadline').text(result.deadline_days);
        }
        
        getCategoryLabel(category) {
            const labels = {
                'basic': wsmvpData.i18n.basicProject || 'Basic Project',
                'intermediate': wsmvpData.i18n.intermediateProject || 'Intermediate Project',
                'advanced': wsmvpData.i18n.advancedProject || 'Advanced Project',
                'custom': wsmvpData.i18n.customProject || 'Custom Project'
            };
            return labels[category] || category;
        }
        
        formatPrice(value, symbol) {
            return symbol + ' ' + value.toFixed(2).replace('.', ',');
        }
        
        nextStep() {
            if (!this.validateCurrentStep()) {
                return;
            }
            
            const $currentStep = $(`.wsmvp-step[data-step="${this.currentStep}"]`);
            $currentStep.removeClass('active');
            
            // Check if we're moving to contact step or result step
            if (this.currentStep >= this.totalSteps) {
                // Show contact step first
                if ($(`.wsmvp-step[data-step="contact"]`).length && !$('.wsmvp-step[data-step="contact"]').hasClass('active')) {
                    this.showStep('contact');
                    return;
                }
                
                // Calculate final price and show results
                this.calculatePrice();
                this.showStep('result');
                return;
            }
            
            this.currentStep++;
            this.showStep(this.currentStep);
            this.updateProgress();
        }
        
        prevStep() {
            const $currentStep = $(`.wsmvp-step[data-step="${this.currentStep}"]`);
            $currentStep.removeClass('active');
            
            if (this.currentStep <= 1) {
                return;
            }
            
            // Handle going back from special steps
            if (this.currentStep === 'contact') {
                this.currentStep = this.totalSteps;
            } else if (this.currentStep === 'result' || this.currentStep === 'success') {
                this.currentStep = 'contact';
                if ($('.wsmvp-step[data-step="contact"]').length) {
                    this.showStep('contact');
                    this.updateProgress();
                    return;
                }
            }
            
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateProgress();
        }
        
        showStep(step) {
            $(`.wsmvp-step[data-step="${step}"]`).addClass('active');
            this.updateNavigation(step);
        }
        
        updateNavigation(step) {
            const $prevBtn = $('#prevBtn');
            const $nextBtn = $('#nextBtn');
            const $submitBtn = $('#submitBtn');
            
            // Hide all buttons first
            $prevBtn.hide();
            $nextBtn.hide();
            $submitBtn.hide();
            
            if (step === 'result') {
                $submitBtn.show();
            } else if (step === 'success') {
                // No navigation on success
            } else if (step === 'contact') {
                $prevBtn.show();
                $submitBtn.show();
            } else {
                $prevBtn.show();
                $nextBtn.show();
                
                if (step <= 1) {
                    $prevBtn.hide();
                }
            }
        }
        
        updateProgress() {
            const progress = (this.currentStep / (this.totalSteps + 2)) * 100; // +2 for contact and result steps
            $('.wsmvp-progress-fill').css('width', progress + '%');
            
            $('.wsmvp-progress-step').each((index, step) => {
                const stepNum = parseInt($(step).data('step'));
                $(step).removeClass('active completed');
                
                if (stepNum < this.currentStep) {
                    $(step).addClass('completed');
                } else if (stepNum === this.currentStep) {
                    $(step).addClass('active');
                }
            });
        }
        
        validateCurrentStep() {
            const $currentStep = $(`.wsmvp-step[data-step="${this.currentStep}"]`);
            let isValid = true;
            
            // Clear previous errors
            $currentStep.find('.wsmvp-error').removeClass('wsmvp-error');
            $currentStep.find('.wsmvp-error-message').remove();
            
            // Validate required fields
            $currentStep.find('[required]').each((_, field) => {
                const $field = $(field);
                let value = $field.val();
                
                // Handle checkboxes
                if ($field.attr('type') === 'checkbox') {
                    const name = $field.attr('name').replace('[]', '');
                    const checked = $(`input[name="${$field.attr('name')}"]:checked`).length;
                    if (checked === 0) {
                        isValid = false;
                        this.showError($field.closest('.wsmvp-question'), wsmvpData.i18n.required);
                    }
                    return;
                }
                
                if (!value || value.trim() === '') {
                    isValid = false;
                    this.showError($field, wsmvpData.i18n.required);
                }
                
                // Validate email
                if ($field.attr('type') === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        this.showError($field, wsmvpData.i18n.invalidEmail);
                    }
                }
            });
            
            return isValid;
        }
        
        showError($element, message) {
            $element.addClass('wsmvp-error');
            
            if (!$element.next('.wsmvp-error-message').length) {
                $element.after(`<div class="wsmvp-error-message">${message}</div>`);
            }
        }
        
        handleSubmit(e) {
            e.preventDefault();
            
            if (!this.validateCurrentStep()) {
                return;
            }
            
            // Collect all answers
            this.collectAnswers();
            
            // Get contact info
            const contactData = {
                name: $('#wsmvp-name').val(),
                company: $('#wsmvp-company').val(),
                email: $('#wsmvp-email').val(),
                phone: $('#wsmvp-phone').val(),
                notes: $('#wsmvp-notes').val(),
                consent: $('#wsmvp-consent').is(':checked')
            };
            
            // Validate consent
            if (!contactData.consent) {
                this.showError($('#wsmvp-consent'), wsmvpData.i18n.consentRequired);
                return;
            }
            
            // Show loading
            $('#loadingOverlay').fadeIn();
            
            // Submit simulation
            $.ajax({
                url: wsmvpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wsmvp_submit_simulation',
                    nonce: wsmvpData.nonce,
                    ...contactData,
                    answers: this.answers,
                    estimated_value: this.calculationResult ? this.calculationResult.total : 0,
                    project_category: this.calculationResult ? this.calculationResult.category : 'basic'
                },
                success: (response) => {
                    $('#loadingOverlay').fadeOut();
                    
                    if (response.success) {
                        this.showSuccessStep();
                    } else {
                        alert(response.data.message || wsmvpData.i18n.error);
                    }
                },
                error: () => {
                    $('#loadingOverlay').fadeOut();
                    alert(wsmvpData.i18n.error);
                }
            });
        }
        
        showSuccessStep() {
            $('.wsmvp-step').removeClass('active');
            $('.wsmvp-step[data-step="success"]').addClass('active');
            $('.wsmvp-navigation').hide();
        }
        
        togglePreview() {
            const $preview = $('#sitePreview');
            $preview.toggleClass('active');
            
            if ($preview.hasClass('active')) {
                this.loadPreview();
            }
        }
        
        loadPreview() {
            // Generate preview based on answers
            const previewHtml = this.generatePreview();
            $('#sitePreview').html(previewHtml);
        }
        
        generatePreview() {
            const settings = wsmvpData.settings;
            const answers = this.answers;
            
            let html = '<div class="wsmvp-preview-container">';
            
            // Header
            html += '<div class="preview-header" style="background: ' + (settings.primary_color || '#3b82f6') + '; color: white; padding: 20px;">';
            html += '<div class="preview-logo">' + (settings.company_name || 'Company Name') + '</div>';
            html += '<nav class="preview-nav">Home | About | Services | Contact</nav>';
            html += '</div>';
            
            // Hero section
            html += '<div class="preview-hero" style="padding: 60px 20px; text-align: center; background: #f5f5f5;">';
            html += '<h2 style="font-size: 32px; margin-bottom: 20px;">Welcome to Your New Website</h2>';
            html += '<p style="font-size: 18px; color: #666;">Professional solutions for your business</p>';
            html += '<button style="margin-top: 20px; padding: 12px 30px; background: ' + (settings.primary_color || '#3b82f6') + '; color: white; border: none; border-radius: 4px; cursor: pointer;">Get Started</button>';
            html += '</div>';
            
            // Site type specific content
            if (answers.site_type === 'ecommerce') {
                html += '<div class="preview-products" style="padding: 40px 20px;">';
                html += '<h3 style="text-align: center; margin-bottom: 30px;">Featured Products</h3>';
                html += '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">';
                for (let i = 0; i < 3; i++) {
                    html += '<div style="border: 1px solid #ddd; padding: 20px; text-align: center;">';
                    html += '<div style="height: 150px; background: #eee; margin-bottom: 15px;"></div>';
                    html += '<p>Product ' + (i + 1) + '</p>';
                    html += '<p style="color: ' + (settings.primary_color || '#3b82f6') + '; font-weight: bold;">$99.99</p>';
                    html += '</div>';
                }
                html += '</div></div>';
            }
            
            // Features
            if (answers.features && answers.features.length > 0) {
                html += '<div class="preview-features" style="padding: 40px 20px; background: #f9f9f9;">';
                html += '<h3 style="text-align: center; margin-bottom: 30px;">Features</h3>';
                html += '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">';
                answers.features.forEach(feature => {
                    html += '<div style="padding: 20px; background: white; border-radius: 8px;">';
                    html += '<div style="width: 40px; height: 40px; background: ' + (settings.primary_color || '#3b82f6') + '; border-radius: 50%; margin-bottom: 15px;"></div>';
                    html += '<p style="font-weight: 600;">' + feature.replace(/_/g, ' ').toUpperCase() + '</p>';
                    html += '</div>';
                });
                html += '</div></div>';
            }
            
            // WhatsApp button
            if (answers.features && answers.features.includes('whatsapp_button')) {
                html += '<div style="position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; background: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 30px;">📱</div>';
            }
            
            // Footer
            html += '<footer style="background: #333; color: white; padding: 30px 20px; text-align: center;">';
            html += '<p>&copy; ' + new Date().getFullYear() + ' ' + (settings.company_name || 'Company Name') + '. All rights reserved.</p>';
            html += '</footer>';
            
            html += '</div>';
            
            return html;
        }
    }
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('#wsmvpSimulator').length) {
            new WebsiteSimulator();
        }
    });
    
})(jQuery);
