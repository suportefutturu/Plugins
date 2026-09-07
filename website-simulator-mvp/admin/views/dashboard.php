<?php
/**
 * Dashboard view
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wsmvp-admin-wrap">
    <h1><?php _e('Website Simulator - Dashboard', 'website-simulator-mvp'); ?></h1>

    <div class="wsmvp-stats-grid">
        <div class="wsmvp-stat-card">
            <div class="wsmvp-stat-value"><?php echo $stats['total_simulations']; ?></div>
            <div class="wsmvp-stat-label"><?php _e('Total de Simulações', 'website-simulator-mvp'); ?></div>
        </div>

        <div class="wsmvp-stat-card wsmvp-stat-new">
            <div class="wsmvp-stat-value"><?php echo $stats['new_leads']; ?></div>
            <div class="wsmvp-stat-label"><?php _e('Novos Leads', 'website-simulator-mvp'); ?></div>
        </div>

        <div class="wsmvp-stat-card wsmvp-stat-converted">
            <div class="wsmvp-stat-value"><?php echo $stats['converted']; ?></div>
            <div class="wsmvp-stat-label"><?php _e('Convertidos', 'website-simulator-mvp'); ?></div>
        </div>

        <div class="wsmvp-stat-card">
            <div class="wsmvp-stat-value"><?php echo number_format($stats['average_value'], 2, ',', '.'); ?></div>
            <div class="wsmvp-stat-label"><?php _e('Valor Médio (R$)', 'website-simulator-mvp'); ?></div>
        </div>
    </div>

    <div class="wsmvp-dashboard-section">
        <h2><?php _e('Últimas Simulações', 'website-simulator-mvp'); ?></h2>
        
        <?php if (empty($recent_simulations)): ?>
            <p class="wsmvp-empty-state"><?php _e('Nenhuma simulação encontrada.', 'website-simulator-mvp'); ?></p>
        <?php else: ?>
            <table class="wsmvp-table">
                <thead>
                    <tr>
                        <th><?php _e('Data', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Nome', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('E-mail', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Empresa', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Valor', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Categoria', 'website-simulator-mvp'); ?></th>
                        <th><?php _e('Status', 'website-simulator-mvp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_simulations as $sim): ?>
                    <tr>
                        <td><?php echo date_i18n('d/m/Y H:i', strtotime($sim['created_at'])); ?></td>
                        <td><?php echo esc_html($sim['name']); ?></td>
                        <td><?php echo esc_html($sim['email']); ?></td>
                        <td><?php echo esc_html($sim['company']); ?></td>
                        <td>R$ <?php echo number_format($sim['estimated_value'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="wsmvp-badge wsmvp-badge-<?php echo esc_attr($sim['project_category']); ?>">
                                <?php echo esc_html(WSMVP_Pricing::get_instance()->get_category_label($sim['project_category'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="wsmvp-badge wsmvp-badge-status-<?php echo esc_attr($sim['status']); ?>">
                                <?php echo esc_html(ucfirst($sim['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="wsmvp-view-all">
                <a href="<?php echo admin_url('admin.php?page=wsmvp-simulations'); ?>" class="button button-primary">
                    <?php _e('Ver Todas as Simulações', 'website-simulator-mvp'); ?>
                </a>
            </p>
        <?php endif; ?>
    </div>

    <div class="wsmvp-dashboard-section">
        <h2><?php _e('Como Usar', 'website-simulator-mvp'); ?></h2>
        <ol>
            <li><?php _e('Crie uma página no WordPress com o shortcode:', 'website-simulator-mvp'); ?> <code>[website_simulator]</code></li>
            <li><?php _e('Compartilhe o link da página com seus clientes', 'website-simulator-mvp'); ?></li>
            <li><?php _e('Acompanhe as simulações neste dashboard', 'website-simulator-mvp'); ?></li>
            <li><?php _e('Entre em contato com os leads qualificados', 'website-simulator-mvp'); ?></li>
        </ol>
    </div>
</div>
