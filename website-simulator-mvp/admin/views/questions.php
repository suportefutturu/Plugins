<?php
/**
 * Questions management view
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wsmvp-admin-wrap">
    <h1><?php _e('Perguntas do Questionário', 'website-simulator-mvp'); ?></h1>
    
    <p class="description"><?php _e('As perguntas abaixo são configuradas via código. Para personalizar, edite o arquivo class-wsmvp-settings.php.', 'website-simulator-mvp'); ?></p>

    <div class="wsmvp-questions-list">
        <?php foreach ($questions as $index => $question): ?>
        <div class="wsmvp-question-card">
            <div class="wsmvp-question-header">
                <h3><?php echo esc_html($question['title']); ?></h3>
                <span class="wsmvp-badge wsmvp-badge-<?php echo esc_attr($question['type']); ?>">
                    <?php echo esc_html(ucfirst($question['type'])); ?>
                </span>
            </div>
            
            <?php if (!empty($question['description'])): ?>
                <p class="wsmvp-question-desc"><?php echo esc_html($question['description']); ?></p>
            <?php endif; ?>
            
            <div class="wsmvp-question-meta">
                <span><?php _e('Obrigatória:', 'website-simulator-mvp'); ?> <?php echo !empty($question['required']) ? '✓' : '✗'; ?></span>
                <span><?php _e('Ativa:', 'website-simulator-mvp'); ?> <?php echo !empty($question['active']) ? '✓' : '✗'; ?></span>
                <span><?php _e('Ordem:', 'website-simulator-mvp'); ?> <?php echo esc_html($question['order']); ?></span>
            </div>
            
            <div class="wsmvp-question-options">
                <strong><?php _e('Opções:', 'website-simulator-mvp'); ?></strong>
                <ul>
                    <?php foreach ($question['options'] as $option): ?>
                        <li>
                            <?php echo esc_html($option['text']); ?>
                            <?php if (isset($option['base_value'])): ?>
                                <span class="wsmvp-option-value">(R$ <?php echo number_format($option['base_value'], 0, ',', '.'); ?>)</span>
                            <?php elseif (isset($option['value_add'])): ?>
                                <span class="wsmvp-option-value">(+R$ <?php echo number_format($option['value_add'], 0, ',', '.'); ?>)</span>
                            <?php elseif (isset($option['multiplier'])): ?>
                                <span class="wsmvp-option-value">(x<?php echo number_format($option['multiplier'], 2); ?>)</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
