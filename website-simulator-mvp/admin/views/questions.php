<?php
/**
 * Questions Management View
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

$questions = get_option('wsmvp_questions', array());
?>

<div class="wrap wsmvp-questions">
    <h1><?php esc_html_e('Questionnaire Questions', 'website-simulator-mvp'); ?></h1>
    
    <p class="description">
        <?php esc_html_e('Manage the questions that appear in the website simulator. Questions are displayed in order.', 'website-simulator-mvp'); ?>
    </p>
    
    <div class="wsmvp-questions-list">
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $index => $question): ?>
                <div class="wsmvp-question-card" data-index="<?php echo esc_attr($index); ?>">
                    <div class="wsmvp-question-header">
                        <span class="wsmvp-question-order"><?php echo esc_html($question['order'] ?? $index + 1); ?>.</span>
                        <div class="wsmvp-question-info">
                            <h3><?php echo esc_html($question['title']); ?></h3>
                            <span class="wsmvp-question-type-badge type-<?php echo esc_attr($question['type']); ?>">
                                <?php echo esc_html(ucfirst($question['type'])); ?>
                            </span>
                            <?php if ($question['required']): ?>
                                <span class="wsmvp-required-badge"><?php esc_html_e('Required', 'website-simulator-mvp'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="wsmvp-question-actions">
                            <button type="button" class="button wsmvp-edit-question">
                                <?php esc_html_e('Edit', 'website-simulator-mvp'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <?php if (!empty($question['options'])): ?>
                        <div class="wsmvp-question-options">
                            <strong><?php esc_html_e('Options:', 'website-simulator-mvp'); ?></strong>
                            <ul>
                                <?php foreach ($question['options'] as $option): ?>
                                    <li><?php echo esc_html($option['label']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?php esc_html_e('No questions configured. Default questions will be used.', 'website-simulator-mvp'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="wsmvp-add-question-section">
        <h2><?php esc_html_e('Add Custom Question', 'website-simulator-mvp'); ?></h2>
        <p class="description">
            <?php esc_html_e('Note: For this MVP version, questions are managed via code. Custom question creation will be available in future versions.', 'website-simulator-mvp'); ?>
        </p>
        
        <div class="wsmvp-notice">
            <p>
                <strong><?php esc_html_e('To modify questions:', 'website-simulator-mvp'); ?></strong><br>
                <?php esc_html_e('Edit the get_default_questions() method in includes/class-wsmvp-settings.php', 'website-simulator-mvp'); ?>
            </p>
        </div>
    </div>
</div>

<style>
.wsmvp-questions-list {
    margin-top: 20px;
}

.wsmvp-question-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
}

.wsmvp-question-header {
    display: flex;
    align-items: center;
    gap: 15px;
}

.wsmvp-question-order {
    font-size: 20px;
    font-weight: bold;
    color: #2271b1;
    min-width: 30px;
}

.wsmvp-question-info {
    flex: 1;
}

.wsmvp-question-info h3 {
    margin: 0 0 10px;
    font-size: 16px;
}

.wsmvp-question-type-badge,
.wsmvp-required-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    margin-right: 5px;
}

.type-select { background: #dbeafe; color: #1e40af; }
.type-radio { background: #fef3c7; color: #92400e; }
.type-checkbox { background: #d1fae5; color: #065f46; }
.type-text { background: #f3f4f6; color: #374151; }

.wsmvp-required-badge {
    background: #fecaca;
    color: #991b1b;
}

.wsmvp-question-options {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f1;
}

.wsmvp-question-options ul {
    margin: 10px 0 0;
    padding-left: 20px;
}

.wsmvp-question-options li {
    margin-bottom: 5px;
}

.wsmvp-add-question-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.wsmvp-notice {
    background: #eff6ff;
    border-left: 4px solid #2271b1;
    padding: 15px;
    margin-top: 15px;
}
</style>
