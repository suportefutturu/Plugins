<?php
/**
 * Pricing rules view
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wsmvp-admin-wrap">
    <h1><?php _e('Regras de Preço', 'website-simulator-mvp'); ?></h1>
    
    <p class="description"><?php _e('Os valores abaixo são configurados via código. Para personalizar, edite o arquivo class-wsmvp-settings.php.', 'website-simulator-mvp'); ?></p>

    <div class="wsmvp-pricing-sections">
        <div class="wsmvp-pricing-section">
            <h2><?php _e('Valores Base por Tipo de Site', 'website-simulator-mvp'); ?></h2>
            <table class="wsmvp-table">
                <thead>
                    <tr>
                        <th><?php _e('Tipo', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Valor (R$)', 'website-simulator-mvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing_rules['base_values'] as $type => $value): ?>
                    <tr>
                        <td><?php echo esc_html(ucfirst($type)); ?></td>
                        <td>R$ <?php echo number_format($value, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wsmvp-pricing-section">
            <h2><?php _e('Valores por Página', 'website-simulator-mvp'); ?></h2>
            <table class="wsmvp-table">
                <thead>
                    <tr>
                        <th><?php _e('Página', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Valor Adicional (R$)', 'website-simulator-mvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing_rules['page_values'] as $page => $value): ?>
                    <tr>
                        <td><?php echo esc_html(ucfirst($page)); ?></td>
                        <td>R$ <?php echo number_format($value, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wsmvp-pricing-section">
            <h2><?php _e('Valores por Funcionalidade', 'website-simulator-mvp'); ?></h2>
            <table class="wsmvp-table">
                <thead>
                    <tr>
                        <th><?php _e('Funcionalidade', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Valor Adicional (R$)', 'website-simulator-mvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing_rules['feature_values'] as $feature => $value): ?>
                    <tr>
                        <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $feature))); ?></td>
                        <td>R$ <?php echo number_format($value, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wsmvp-pricing-section">
            <h2><?php _e('Multiplicadores de Personalização Visual', 'website-simulator-mvp'); ?></h2>
            <table class="wsmvp-table">
                <thead>
                    <tr>
                        <th><?php _e('Nível', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Multiplicador', 'website-simulator-mvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing_rules['visual_multipliers'] as $level => $mult): ?>
                    <tr>
                        <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $level))); ?></td>
                        <td>x<?php echo number_format($mult, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wsmvp-pricing-section">
            <h2><?php _e('Categorias de Projeto', 'website-simulator-mvp'); ?></h2>
            <table class="wsmvp-table">
                <thead>
                    <tr>
                        <th><?php _e('Categoria', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Pontuação Mínima', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Pontuação Máxima', 'website-simulator-mvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing_rules['complexity_scores'] as $cat => $range): ?>
                    <tr>
                        <td><?php echo esc_html(WSMVP_Pricing::get_instance()->get_category_label($cat)); ?></td>
                        <td><?php echo number_format($range['min'], 0); ?></td>
                        <td><?php echo number_format($range['max'], 0); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
