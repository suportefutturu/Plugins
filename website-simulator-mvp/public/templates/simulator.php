<?php
/**
 * Simulator template
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = WSMVP_Settings::get_instance()->get_settings();
$questions = WSMVP_Settings::get_instance()->get_questions();

// Sort questions by order
usort($questions, function($a, $b) {
    return ($a['order'] ?? 0) - ($b['order'] ?? 0);
});

// Filter active questions
$active_questions = array_filter($questions, function($q) {
    return !empty($q['active']);
});

$total_steps = count($active_questions) + 1; // +1 for final step
?>

<div class="wsmvp-simulator" id="wsmvp-simulator">
    <div class="wsmvp-simulator-header">
        <?php if (!empty($settings['company_logo'])): ?>
            <img src="<?php echo esc_url($settings['company_logo']); ?>" alt="<?php echo esc_attr($settings['company_name']); ?>" class="wsmvp-logo">
        <?php else: ?>
            <h2 class="wsmvp-title"><?php echo esc_html($settings['company_name']); ?></h2>
        <?php endif; ?>
    </div>

    <div class="wsmvp-intro" id="wsmvp-intro">
        <h1><?php _e('Simulador de Website', 'website-simulator-mvp'); ?></h1>
        <p class="wsmvp-intro-text"><?php echo esc_html($settings['intro_text']); ?></p>
        <button type="button" class="wsmvp-btn wsmvp-btn-primary" id="wsmvp-start-btn">
            <?php _e('Começar Simulação', 'website-simulator-mvp'); ?>
        </button>
    </div>

    <form id="wsmvp-form" style="display: none;">
        <div class="wsmvp-progress">
            <div class="wsmvp-progress-bar">
                <div class="wsmvp-progress-fill" style="width: 0%;"></div>
            </div>
            <div class="wsmvp-progress-text">
                <span><?php _e('Etapa', 'website-simulator-mvp'); ?></span>
                <span class="wsmvp-current-step">1</span>
                <span><?php _e('de', 'website-simulator-mvp'); ?></span>
                <span class="wsmvp-total-steps"><?php echo $total_steps; ?></span>
            </div>
        </div>

        <div class="wsmvp-steps-container">
            <?php 
            $step_index = 0;
            foreach ($active_questions as $question): 
                $step_index++;
                $is_first = ($step_index === 1);
                $is_last = ($step_index === count($active_questions));
            ?>
            <div class="wsmvp-step" data-step="<?php echo $step_index; ?>" <?php if (!$is_first) echo 'style="display:none;"'; ?>>
                <h3 class="wsmvp-step-title"><?php echo esc_html($question['title']); ?></h3>
                <?php if (!empty($question['description'])): ?>
                    <p class="wsmvp-step-description"><?php echo esc_html($question['description']); ?></p>
                <?php endif; ?>

                <div class="wsmvp-field">
                    <?php
                    $field_name = $question['id'];
                    $field_type = $question['type'];
                    $is_required = !empty($question['required']);
                    $options = $question['options'] ?? array();

                    if ($field_type === 'select'):
                    ?>
                        <select name="<?php echo esc_attr($field_name); ?>" <?php if ($is_required) echo 'required'; ?>>
                            <option value=""><?php _e('Selecione...', 'website-simulator-mvp'); ?></option>
                            <?php foreach ($options as $option): ?>
                                <option value="<?php echo esc_attr($option['value']); ?>">
                                    <?php echo esc_html($option['text']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($field_type === 'radio'): ?>
                        <div class="wsmvp-options-grid">
                            <?php foreach ($options as $option): ?>
                                <label class="wsmvp-option-card">
                                    <input type="radio" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($option['value']); ?>" <?php if ($is_required) echo 'required'; ?>>
                                    <span class="wsmvp-option-text"><?php echo esc_html($option['text']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($field_type === 'checkbox'): ?>
                        <div class="wsmvp-options-list">
                            <?php foreach ($options as $option): ?>
                                <label class="wsmvp-option-item">
                                    <input type="checkbox" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($option['value']); ?>">
                                    <span class="wsmvp-option-text"><?php echo esc_html($option['text']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($field_type === 'textarea'): ?>
                        <textarea name="<?php echo esc_attr($field_name); ?>" rows="4" <?php if ($is_required) echo 'required'; ?>></textarea>

                    <?php else: ?>
                        <input type="<?php echo esc_attr($field_type); ?>" name="<?php echo esc_attr($field_name); ?>" <?php if ($is_required) echo 'required'; ?>>
                    <?php endif; ?>
                </div>

                <div class="wsmvp-step-actions">
                    <?php if (!$is_first): ?>
                        <button type="button" class="wsmvp-btn wsmvp-btn-secondary wsmvp-prev-btn">
                            <?php _e('Voltar', 'website-simulator-mvp'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($is_last): ?>
                        <button type="button" class="wsmvp-btn wsmvp-btn-primary wsmvp-next-btn">
                            <?php _e('Ver Resultado', 'website-simulator-mvp'); ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="wsmvp-btn wsmvp-btn-primary wsmvp-next-btn">
                            <?php _e('Próximo', 'website-simulator-mvp'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Final Step: Contact Form -->
            <div class="wsmvp-step" data-step="<?php echo $total_steps; ?>" style="display: none;">
                <h3 class="wsmvp-step-title"><?php _e('Quase lá! Vamos conversar?', 'website-simulator-mvp'); ?></h3>
                <p class="wsmvp-step-description"><?php _e('Preencha seus dados para receber a proposta completa.', 'website-simulator-mvp'); ?></p>

                <div class="wsmvp-preview-container" id="wsmvp-preview-container">
                    <div class="wsmvp-preview-loading">
                        <div class="wsmvp-spinner"></div>
                        <p><?php _e('Gerando prévia do seu site...', 'website-simulator-mvp'); ?></p>
                    </div>
                </div>

                <div class="wsmvp-result-summary" id="wsmvp-result-summary">
                    <h4><?php _e('Resumo da Estimativa', 'website-simulator-mvp'); ?></h4>
                    <div class="wsmvp-result-grid">
                        <div class="wsmvp-result-item">
                            <span class="wsmvp-result-label"><?php _e('Categoria:', 'website-simulator-mvp'); ?></span>
                            <span class="wsmvp-result-value" id="result-category">-</span>
                        </div>
                        <div class="wsmvp-result-item">
                            <span class="wsmvp-result-label"><?php _e('Investimento:', 'website-simulator-mvp'); ?></span>
                            <span class="wsmvp-result-value" id="result-price">-</span>
                        </div>
                        <div class="wsmvp-result-item">
                            <span class="wsmvp-result-label"><?php _e('Prazo estimado:', 'website-simulator-mvp'); ?></span>
                            <span class="wsmvp-result-value" id="result-deadline">-</span>
                        </div>
                    </div>
                </div>

                <div class="wsmvp-contact-form">
                    <div class="wsmvp-field">
                        <label for="wsmvp-name"><?php _e('Nome completo *', 'website-simulator-mvp'); ?></label>
                        <input type="text" id="wsmvp-name" name="name" required>
                    </div>

                    <div class="wsmvp-field">
                        <label for="wsmvp-email"><?php _e('E-mail *', 'website-simulator-mvp'); ?></label>
                        <input type="email" id="wsmvp-email" name="email" required>
                    </div>

                    <div class="wsmvp-field">
                        <label for="wsmvp-company"><?php _e('Empresa', 'website-simulator-mvp'); ?></label>
                        <input type="text" id="wsmvp-company" name="company">
                    </div>

                    <div class="wsmvp-field">
                        <label for="wsmvp-whatsapp"><?php _e('WhatsApp', 'website-simulator-mvp'); ?></label>
                        <input type="tel" id="wsmvp-whatsapp" name="whatsapp">
                    </div>

                    <div class="wsmvp-field">
                        <label for="wsmvp-notes"><?php _e('Observações adicionais', 'website-simulator-mvp'); ?></label>
                        <textarea id="wsmvp-notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="wsmvp-field wsmvp-consent">
                        <label>
                            <input type="checkbox" id="wsmvp-consent" name="consent" required>
                            <span><?php echo esc_html($settings['privacy_notice']); ?></span>
                        </label>
                    </div>

                    <div class="wsmvp-step-actions">
                        <button type="button" class="wsmvp-btn wsmvp-btn-secondary wsmvp-prev-btn">
                            <?php _e('Voltar', 'website-simulator-mvp'); ?>
                        </button>
                        <button type="submit" class="wsmvp-btn wsmvp-btn-primary wsmvp-submit-btn">
                            <?php echo esc_html($settings['contact_button_text']); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="wsmvp-success" id="wsmvp-success" style="display: none;">
        <div class="wsmvp-success-icon">✓</div>
        <h2><?php _e('Obrigado!', 'website-simulator-mvp'); ?></h2>
        <p><?php _e('Sua simulação foi enviada com sucesso. Em breve entraremos em contato.', 'website-simulator-mvp'); ?></p>
        <button type="button" class="wsmvp-btn wsmvp-btn-primary" onclick="location.reload()">
            <?php _e('Nova Simulação', 'website-simulator-mvp'); ?>
        </button>
    </div>

    <div class="wsmvp-error" id="wsmvp-error" style="display: none;">
        <p class="wsmvp-error-message"></p>
        <button type="button" class="wsmvp-btn wsmvp-btn-primary" onclick="document.getElementById('wsmvp-error').style.display='none'">
            <?php _e('Tentar Novamente', 'website-simulator-mvp'); ?>
        </button>
    </div>
</div>
