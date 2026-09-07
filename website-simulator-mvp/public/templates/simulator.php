<?php
/**
 * Frontend Simulator Template
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

$frontend = new WSMVP_Frontend();
$config = $frontend->get_simulator_config();
$settings = $config['settings'];
$steps = $config['steps'];
$total_steps = $config['total_steps'];
?>

<div class="wsmvp-simulator" id="wsmvpSimulator">
    <?php if ($atts['show_title'] === 'true'): ?>
        <div class="wsmvp-header">
            <?php if (!empty($settings['logo_url'])): ?>
                <img src="<?php echo esc_url($settings['logo_url']); ?>" alt="<?php echo esc_attr($settings['company_name']); ?>" class="wsmvp-logo">
            <?php endif; ?>
            <h1><?php esc_html_e('Website Simulator', 'website-simulator-mvp'); ?></h1>
        </div>
    <?php endif; ?>
    
    <?php if ($atts['show_intro'] === 'true' && !empty($settings['intro_text'])): ?>
        <div class="wsmvp-intro">
            <p><?php echo esc_html($settings['intro_text']); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Progress Bar -->
    <div class="wsmvp-progress-container">
        <div class="wsmvp-progress-bar">
            <div class="wsmvp-progress-fill" style="width: 12.5%;"></div>
        </div>
        <div class="wsmvp-progress-steps">
            <?php for ($i = 1; $i <= $total_steps; $i++): ?>
                <div class="wsmvp-progress-step <?php echo $i === 1 ? 'active' : ''; ?>" data-step="<?php echo esc_attr($i); ?>">
                    <span class="step-number"><?php echo esc_html($i); ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    
    <!-- Form Container -->
    <form id="wsmvp-form" class="wsmvp-form">
        <?php wp_nonce_field('wsmvp_frontend_nonce', 'wsmvp_nonce'); ?>
        
        <!-- Question Steps -->
        <?php foreach ($steps as $step_number => $questions): ?>
            <div class="wsmvp-step <?php echo $step_number === 1 ? 'active' : ''; ?>" data-step="<?php echo esc_attr($step_number); ?>">
                <?php foreach ($questions as $question): ?>
                    <?php if ($question['type'] !== 'customization'): ?>
                        <div class="wsmvp-question" data-question-id="<?php echo esc_attr($question['id']); ?>">
                            <h3 class="wsmvp-question-title">
                                <?php echo esc_html($question['title']); ?>
                                <?php if ($question['required']): ?>
                                    <span class="wsmvp-required">*</span>
                                <?php endif; ?>
                            </h3>
                            
                            <?php if (!empty($question['description'])): ?>
                                <p class="wsmvp-question-description"><?php echo esc_html($question['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="wsmvp-question-field">
                                <?php
                                $field_name = $question['id'];
                                $field_value = '';
                                
                                switch ($question['type']) {
                                    case 'select':
                                        ?>
                                        <select name="<?php echo esc_attr($field_name); ?>" <?php echo $question['required'] ? 'required' : ''; ?>>
                                            <option value=""><?php esc_html_e('Select an option...', 'website-simulator-mvp'); ?></option>
                                            <?php foreach ($question['options'] as $option): ?>
                                                <option value="<?php echo esc_attr($option['value']); ?>"><?php echo esc_html($option['label']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php
                                        break;
                                        
                                    case 'radio':
                                        foreach ($question['options'] as $option): ?>
                                            <label class="wsmvp-radio-option">
                                                <input type="radio" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($option['value']); ?>" <?php echo $question['required'] ? 'required' : ''; ?>>
                                                <span class="wsmvp-option-text"><?php echo esc_html($option['label']); ?></span>
                                            </label>
                                        <?php endforeach;
                                        break;
                                        
                                    case 'checkbox':
                                        foreach ($question['options'] as $option): ?>
                                            <label class="wsmvp-checkbox-option">
                                                <input type="checkbox" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($option['value']); ?>">
                                                <span class="wsmvp-option-text"><?php echo esc_html($option['label']); ?></span>
                                            </label>
                                        <?php endforeach;
                                        break;
                                        
                                    case 'text':
                                        ?>
                                        <input type="text" name="<?php echo esc_attr($field_name); ?>" <?php echo $question['required'] ? 'required' : ''; ?>>
                                        <?php
                                        break;
                                        
                                    case 'email':
                                        ?>
                                        <input type="email" name="<?php echo esc_attr($field_name); ?>" <?php echo $question['required'] ? 'required' : ''; ?>>
                                        <?php
                                        break;
                                        
                                    case 'number':
                                        ?>
                                        <input type="number" name="<?php echo esc_attr($field_name); ?>" <?php echo $question['required'] ? 'required' : ''; ?>>
                                        <?php
                                        break;
                                        
                                    case 'textarea':
                                        ?>
                                        <textarea name="<?php echo esc_attr($field_name); ?>" rows="4" <?php echo $question['required'] ? 'required' : ''; ?>></textarea>
                                        <?php
                                        break;
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <!-- Contact Information Step (Final) -->
        <div class="wsmvp-step" data-step="contact">
            <h2><?php esc_html_e('Your Information', 'website-simulator-mvp'); ?></h2>
            <p><?php esc_html_e('Enter your contact details to receive your project estimate.', 'website-simulator-mvp'); ?></p>
            
            <div class="wsmvp-form-group">
                <label for="wsmvp-name"><?php esc_html_e('Full Name', 'website-simulator-mvp'); ?> <span class="required">*</span></label>
                <input type="text" id="wsmvp-name" name="name" required>
            </div>
            
            <div class="wsmvp-form-group">
                <label for="wsmvp-company"><?php esc_html_e('Company', 'website-simulator-mvp'); ?></label>
                <input type="text" id="wsmvp-company" name="company">
            </div>
            
            <div class="wsmvp-form-group">
                <label for="wsmvp-email"><?php esc_html_e('Email', 'website-simulator-mvp'); ?> <span class="required">*</span></label>
                <input type="email" id="wsmvp-email" name="email" required>
            </div>
            
            <div class="wsmvp-form-group">
                <label for="wsmvp-phone"><?php esc_html_e('WhatsApp/Phone', 'website-simulator-mvp'); ?></label>
                <input type="tel" id="wsmvp-phone" name="phone">
            </div>
            
            <div class="wsmvp-form-group">
                <label for="wsmvp-notes"><?php esc_html_e('Additional Notes', 'website-simulator-mvp'); ?></label>
                <textarea id="wsmvp-notes" name="notes" rows="4"></textarea>
            </div>
            
            <div class="wsmvp-form-group wsmvp-consent">
                <label class="wsmvp-checkbox-label">
                    <input type="checkbox" id="wsmvp-consent" name="consent" required>
                    <span><?php esc_html_e('I agree to the privacy policy and consent to having this website store my submitted information so they can respond to my inquiry.', 'website-simulator-mvp'); ?> <span class="required">*</span></span>
                </label>
            </div>
        </div>
        
        <!-- Result Step -->
        <div class="wsmvp-step" data-step="result">
            <div class="wsmvp-result-container">
                <h2><?php esc_html_e('Your Project Estimate', 'website-simulator-mvp'); ?></h2>
                
                <div class="wsmvp-result-summary">
                    <div class="wsmvp-result-category">
                        <span class="label"><?php esc_html_e('Project Category:', 'website-simulator-mvp'); ?></span>
                        <span class="value" id="result-category">-</span>
                    </div>
                    
                    <div class="wsmvp-result-price">
                        <span class="label"><?php esc_html_e('Estimated Investment:', 'website-simulator-mvp'); ?></span>
                        <div class="price-range">
                            <span id="result-price-min">-</span>
                            <span><?php esc_html_e('to', 'website-simulator-mvp'); ?></span>
                            <span id="result-price-max">-</span>
                        </div>
                    </div>
                    
                    <div class="wsmvp-result-deadline">
                        <span class="label"><?php esc_html_e('Estimated Deadline:', 'website-simulator-mvp'); ?></span>
                        <span class="value"><span id="result-deadline">-</span> <?php esc_html_e('days', 'website-simulator-mvp'); ?></span>
                    </div>
                </div>
                
                <div class="wsmvp-preview-toggle">
                    <button type="button" class="button" id="togglePreviewBtn">
                        <?php esc_html_e('View Site Preview', 'website-simulator-mvp'); ?>
                    </button>
                </div>
                
                <div class="wsmvp-site-preview" id="sitePreview">
                    <!-- Preview will be loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Success Message -->
        <div class="wsmvp-step" data-step="success">
            <div class="wsmvp-success-message">
                <div class="wsmvp-success-icon">✓</div>
                <h2><?php esc_html_e('Thank You!', 'website-simulator-mvp'); ?></h2>
                <p><?php esc_html_e('Your simulation has been submitted successfully. Our team will contact you soon.', 'website-simulator-mvp'); ?></p>
                
                <div class="wsmvp-next-actions">
                    <a href="#" class="button button-primary" id="downloadProposalBtn">
                        <?php esc_html_e('Download Proposal', 'website-simulator-mvp'); ?>
                    </a>
                    <?php
                    $whatsapp_number = $settings['whatsapp_number'] ?? '';
                    if (!empty($whatsapp_number)) {
                        $whatsapp_msg = urlencode(__('Hello! I just made a website simulation and would like to know more.', 'website-simulator-mvp'));
                        $whatsapp_link = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp_number) . '?text=' . $whatsapp_msg;
                    } else {
                        $whatsapp_link = '#';
                    }
                    ?>
                    <a href="<?php echo esc_url($whatsapp_link); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Contact via WhatsApp', 'website-simulator-mvp'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="wsmvp-navigation">
            <button type="button" class="button button-secondary" id="prevBtn" style="display: none;">
                <?php esc_html_e('Previous', 'website-simulator-mvp'); ?>
            </button>
            <button type="button" class="button button-primary" id="nextBtn">
                <?php esc_html_e('Next', 'website-simulator-mvp'); ?>
            </button>
            <button type="submit" class="button button-primary button-large" id="submitBtn" style="display: none;">
                <?php esc_html_e('Submit Request', 'website-simulator-mvp'); ?>
            </button>
        </div>
    </form>
    
    <!-- Loading Overlay -->
    <div class="wsmvp-loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="wsmvp-spinner"></div>
        <p><?php esc_html_e('Calculating...', 'website-simulator-mvp'); ?></p>
    </div>
</div>
