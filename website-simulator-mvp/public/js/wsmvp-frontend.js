/**
 * Frontend JavaScript
 * 
 * @package Website_Simulator_MVP
 */

(function($) {
    'use strict';

    const WSMVP = {
        currentStep: 1,
        totalSteps: 0,
        responses: {},

        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.totalSteps = parseInt($('.wsmvp-total-steps').text());
        },

        cacheElements: function() {
            this.$intro = $('#wsmvp-intro');
            this.$form = $('#wsmvp-form');
            this.$steps = $('.wsmvp-step');
            this.$progressFill = $('.wsmvp-progress-fill');
            this.$currentStep = $('.wsmvp-current-step');
            this.$startBtn = $('#wsmvp-start-btn');
            this.$previewContainer = $('#wsmvp-preview-container');
            this.$resultSummary = $('#wsmvp-result-summary');
        },

        bindEvents: function() {
            this.$startBtn.on('click', () => this.startSimulation());
            
            $(document).on('click', '.wsmvp-next-btn', () => this.nextStep());
            $(document).on('click', '.wsmvp-prev-btn', () => this.prevStep());
            
            $(document).on('change', 'input, select, textarea', (e) => this.handleInputChange(e));
            
            this.$form.on('submit', (e) => this.handleSubmit(e));

            // Option card selection
            $(document).on('click', '.wsmvp-option-card', function() {
                $('.wsmvp-option-card').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input').prop('checked', true);
            });
        },

        startSimulation: function() {
            this.$intro.hide();
            this.$form.show();
            this.updateProgress();
        },

        nextStep: function() {
            if (!this.validateCurrentStep()) {
                return;
            }

            this.saveCurrentResponses();

            if (this.currentStep < this.totalSteps) {
                this.showStep(this.currentStep + 1);
                
                // If going to final step, load preview and calculate
                if (this.currentStep + 1 === this.totalSteps) {
                    this.loadPreview();
                    this.calculateEstimate();
                }
            }
        },

        prevStep: function() {
            if (this.currentStep > 1) {
                this.showStep(this.currentStep - 1);
            }
        },

        showStep: function(step) {
            this.$steps.hide();
            $(`.wsmvp-step[data-step="${step}"]`).show();
            this.currentStep = step;
            this.updateProgress();
        },

        updateProgress: function() {
            const progress = (this.currentStep / this.totalSteps) * 100;
            this.$progressFill.css('width', `${progress}%`);
            this.$currentStep.text(this.currentStep);
        },

        validateCurrentStep: function() {
            const $currentStepEl = $(`.wsmvp-step[data-step="${this.currentStep}"]`);
            const $requiredFields = $currentStepEl.find('[required]');
            let isValid = true;

            $requiredFields.each(function() {
                const $field = $(this);
                let value = $field.val();
                
                // Handle checkboxes
                if ($field.attr('type') === 'checkbox') {
                    const name = $field.attr('name');
                    const $checked = $(`input[name="${name}"]:checked`);
                    if ($checked.length === 0 && $field.closest('.wsmvp-field').find('[required]').length > 0) {
                        isValid = false;
                    }
                    return;
                }

                // Handle radios
                if ($field.attr('type') === 'radio') {
                    const name = $field.attr('name');
                    if ($(`input[name="${name}"]:checked`).length === 0) {
                        isValid = false;
                    }
                    return;
                }

                if (!value || value.trim() === '') {
                    isValid = false;
                    $field.addClass('error');
                } else {
                    $field.removeClass('error');
                }
            });

            if (!isValid) {
                alert(wsmvpConfig.strings.required);
            }

            return isValid;
        },

        saveCurrentResponses: function() {
            const $currentStepEl = $(`.wsmvp-step[data-step="${this.currentStep}"]`);
            
            // Save text inputs and selects
            $currentStepEl.find('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], select, textarea').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    WSMVP.responses[name] = $(this).val();
                }
            });

            // Save radio buttons
            $currentStepEl.find('input[type="radio"]:checked').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    WSMVP.responses[name] = $(this).val();
                }
            });

            // Save checkboxes
            $currentStepEl.find('input[type="checkbox"]:checked').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    if (!WSMVP.responses[name]) {
                        WSMVP.responses[name] = [];
                    }
                    WSMVP.responses[name].push($(this).val());
                }
            });
        },

        handleInputChange: function(e) {
            const $target = $(e.target);
            
            // Update option card selection
            if ($target.is('input[type="radio"]')) {
                $target.closest('.wsmvp-options-grid').find('.wsmvp-option-card').removeClass('selected');
                $target.closest('.wsmvp-option-card').addClass('selected');
            }
        },

        loadPreview: function() {
            this.$previewContainer.html(`
                <div class="wsmvp-preview-loading">
                    <div class="wsmvp-spinner"></div>
                    <p>${wsmvpConfig.strings.loading}</p>
                </div>
            `);

            $.ajax({
                url: wsmvpConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wsmvp_get_preview',
                    nonce: wsmvpConfig.nonce,
                    responses: JSON.stringify(this.responses)
                },
                success: (response) => {
                    if (response.success) {
                        this.$previewContainer.html(response.data.html);
                    }
                }
            });
        },

        calculateEstimate: function() {
            // Calculate client-side for immediate feedback
            const pricing = this.calculatePricing();
            
            $('#result-category').text(pricing.categoryLabel);
            $('#result-price').text(`${pricing.currencySymbol} ${pricing.minValue} - ${pricing.maxValue}`);
            $('#result-deadline').text(`${pricing.deadlineDays} dias`);
        },

        calculatePricing: function() {
            const r = this.responses;
            let total = 0;

            // Base value by site type
            const baseValues = {
                institutional: 1500,
                landing_page: 900,
                ecommerce: 3500,
                blog: 1200,
                portfolio: 1000,
                membership: 2500
            };
            total += baseValues[r.site_type] || 1500;

            // Add pages
            if (r.pages && Array.isArray(r.pages)) {
                const pageValues = {
                    home: 0, about: 150, services: 200, products: 250,
                    blog: 300, portfolio: 200, testimonials: 150,
                    faq: 100, contact: 100, privacy: 80
                };
                r.pages.forEach(page => {
                    total += pageValues[page] || 0;
                });
            }

            // Add features
            if (r.features && Array.isArray(r.features)) {
                const featureValues = {
                    contact_form: 150, whatsapp_button: 100, instagram_integration: 200,
                    google_maps: 100, blog_module: 400, newsletter: 250,
                    scheduling: 500, membership_area: 2000, online_payment: 800,
                    crm_integration: 600, seo_basic: 300, mobile_optimization: 200
                };
                r.features.forEach(feature => {
                    total += featureValues[feature] || 0;
                });
            }

            // Visual multiplier
            const visualMultipliers = { basic: 1, colors_texts: 1.1, custom_design: 1.3, fully_custom: 1.5 };
            total *= visualMultipliers[r.visual_level] || 1;

            // Content value
            const contentValues = { have_all: 0, have_partial: 300, need_texts: 600, need_everything: 1200 };
            total += contentValues[r.content_status] || 0;

            // Deadline multiplier
            const deadlineMultipliers = { no_urgency: 1, '30_days': 1, '15_days': 1.15, priority: 1.25 };
            total *= deadlineMultipliers[r.deadline] || 1;

            // Ensure minimum
            total = Math.max(total, 500);
            total = Math.round(total / 10) * 10;

            // Min/Max with margin
            const minValue = Math.round(total * 0.9);
            const maxValue = Math.round(total * 1.2);

            // Category
            let category = 'basic';
            if (total > 6000) category = 'custom';
            else if (total > 3000) category = 'advanced';
            else if (total > 1000) category = 'intermediate';

            const categoryLabels = {
                basic: 'Projeto Básico',
                intermediate: 'Projeto Intermediário',
                advanced: 'Projeto Avançado',
                custom: 'Projeto Sob Medida'
            };

            // Deadline days
            const baseDays = { landing_page: 7, institutional: 15, portfolio: 12, blog: 18, membership: 25, ecommerce: 30 };
            let deadlineDays = baseDays[r.site_type] || 20;
            deadlineDays = Math.round(deadlineDays * (deadlineMultipliers[r.deadline] || 1));

            return {
                total,
                minValue: minValue.toLocaleString('pt-BR'),
                maxValue: maxValue.toLocaleString('pt-BR'),
                categoryLabel: categoryLabels[category],
                deadlineDays,
                currencySymbol: 'R$'
            };
        },

        handleSubmit: function(e) {
            e.preventDefault();

            const consent = $('#wsmvp-consent').is(':checked');
            if (!consent) {
                alert(wsmvpConfig.strings.privacyRequired);
                return;
            }

            // Save final form data
            this.responses.name = $('#wsmvp-name').val();
            this.responses.email = $('#wsmvp-email').val();
            this.responses.company = $('#wsmvp-company').val();
            this.responses.whatsapp = $('#wsmvp-whatsapp').val();
            this.responses.notes = $('#wsmvp-notes').val();

            const $submitBtn = $('.wsmvp-submit-btn');
            $submitBtn.prop('disabled', true).text(wsmvpConfig.strings.loading);

            $.ajax({
                url: wsmvpConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wsmvp_submit_simulation',
                    nonce: wsmvpConfig.nonce,
                    name: this.responses.name,
                    email: this.responses.email,
                    company: this.responses.company,
                    whatsapp: this.responses.whatsapp,
                    notes: this.responses.notes,
                    consent: '1',
                    responses: JSON.stringify(this.responses)
                },
                success: (response) => {
                    if (response.success) {
                        this.$form.hide();
                        $('#wsmvp-success').show();
                    } else {
                        this.showError(response.data?.message || wsmvpConfig.strings.error);
                    }
                },
                error: () => {
                    this.showError(wsmvpConfig.strings.error);
                },
                complete: () => {
                    $submitBtn.prop('disabled', false).text(wsmvpConfig.strings.finish);
                }
            });
        },

        showError: function(message) {
            this.$form.hide();
            $('#wsmvp-error .wsmvp-error-message').text(message);
            $('#wsmvp-error').show();
        }
    };

    $(document).ready(() => {
        if ($('#wsmvp-simulator').length) {
            WSMVP.init();
        }
    });

})(jQuery);
