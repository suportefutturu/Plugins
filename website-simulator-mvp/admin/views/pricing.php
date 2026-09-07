<?php
/**
 * Pricing Rules View
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wsmvp-pricing">
    <h1><?php esc_html_e('Pricing Rules', 'website-simulator-mvp'); ?></h1>
    
    <p class="description">
        <?php esc_html_e('Configure pricing values for different project options. These values are used to calculate the final estimate.', 'website-simulator-mvp'); ?>
    </p>
    
    <?php foreach ($grouped_rules as $category => $rules): ?>
        <div class="wsmvp-pricing-section">
            <h2><?php echo esc_html(ucfirst($category)); ?></h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Option', 'website-simulator-mvp'); ?></th>
                        <th><?php esc_html_e('Type', 'website-simulator-mvp'); ?></th>
                        <th><?php esc_html_e('Value', 'website-simulator-mvp'); ?></th>
                        <th><?php esc_html_e('Complexity', 'website-simulator-mvp'); ?></th>
                        <th><?php esc_html_e('Status', 'website-simulator-mvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($rule['option_label']); ?></strong>
                                <br>
                                <small><?php echo esc_html($rule['option_key']); ?></small>
                            </td>
                            <td>
                                <span class="wsmvp-rule-type type-<?php echo esc_attr($rule['rule_type']); ?>">
                                    <?php echo esc_html(ucfirst($rule['rule_type'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $settings = get_option('wsmvp_settings');
                                $currency_symbol = $settings['currency_symbol'] ?? 'R$';
                                
                                if ($rule['rule_type'] === 'percentage') {
                                    echo esc_html((floatval($rule['additional_value']) * 100) . '%');
                                } else {
                                    echo esc_html($currency_symbol . ' ' . number_format($rule['additional_value'], 2, ',', '.'));
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $complexity_stars = '';
                                for ($i = 0; $i < 5; $i++) {
                                    if ($i < $rule['complexity_score']) {
                                        $complexity_stars .= '★';
                                    } else {
                                        $complexity_stars .= '☆';
                                    }
                                }
                                echo '<span style="color: #fbbf24;">' . $complexity_stars . '</span>';
                                ?>
                            </td>
                            <td>
                                <?php if ($rule['is_active']): ?>
                                    <span class="wsmvp-status-active"><?php esc_html_e('Active', 'website-simulator-mvp'); ?></span>
                                <?php else: ?>
                                    <span class="wsmvp-status-inactive"><?php esc_html_e('Inactive', 'website-simulator-mvp'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
    
    <div class="wsmvp-edit-notice">
        <h3><?php esc_html_e('How to Edit Prices', 'website-simulator-mvp'); ?></h3>
        <p>
            <?php esc_html_e('For this MVP version, pricing rules are defined in code. To modify prices:', 'website-simulator-mvp'); ?>
        </p>
        <ol>
            <li><?php esc_html_e('Open the file:', 'website-simulator-mvp'); ?> <code>includes/class-wsmvp-settings.php</code></li>
            <li><?php esc_html_e('Find the method:', 'website-simulator-mvp'); ?> <code>get_default_pricing_rules()</code></li>
            <li><?php esc_html_e('Modify the', 'website-simulator-mvp'); ?> <code>additional_value</code> <?php esc_html_e('for each option', 'website-simulator-mvp'); ?></li>
        </ol>
        
        <div class="wsmvp-code-example">
            <pre><code>array(
    'rule_key' => 'site_type',
    'option_key' => 'ecommerce',
    'option_label' => __('Online store', 'website-simulator-mvp'),
    'additional_value' => 3500.00, // Change this value
    'complexity_score' => 5,
)</code></pre>
        </div>
    </div>
</div>

<style>
.wsmvp-pricing-section {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.wsmvp-pricing-section h2 {
    margin-top: 0;
    padding-bottom: 15px;
    border-bottom: 2px solid #2271b1;
}

.wsmvp-rule-type {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.type-base { background: #dbeafe; color: #1e40af; }
.type-additional { background: #d1fae5; color: #065f46; }
.type-multiplier { background: #fef3c7; color: #92400e; }
.type-percentage { background: #ddd6fe; color: #6b21a8; }

.wsmvp-status-active {
    display: inline-block;
    padding: 4px 8px;
    background: #4ade80;
    color: #065f46;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.wsmvp-status-inactive {
    display: inline-block;
    padding: 4px 8px;
    background: #f3f4f6;
    color: #374151;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.wsmvp-edit-notice {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.wsmvp-code-example {
    background: #1e1e1e;
    border-radius: 6px;
    padding: 15px;
    overflow-x: auto;
    margin-top: 15px;
}

.wsmvp-code-example pre {
    margin: 0;
    color: #d4d4d4;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    line-height: 1.5;
}
</style>
