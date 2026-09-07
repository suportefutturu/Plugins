<?php
/**
 * Simulations list view
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wsmvp-admin-wrap">
    <h1><?php _e('Simulações', 'website-simulator-mvp'); ?></h1>

    <div class="wsmvp-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="wsmvp-simulations">
            
            <select name="status">
                <option value=""><?php _e('Todos os Status', 'website-simulator-mvp'); ?></option>
                <option value="new" <?php selected($status_filter, 'new'); ?>><?php _e('Novo', 'website-simulator-mvp'); ?></option>
                <option value="contacted" <?php selected($status_filter, 'contacted'); ?>><?php _e('Em Contato', 'website-simulator-mvp'); ?></option>
                <option value="proposal_sent" <?php selected($status_filter, 'proposal_sent'); ?>><?php _e('Proposta Enviada', 'website-simulator-mvp'); ?></option>
                <option value="converted" <?php selected($status_filter, 'converted'); ?>><?php _e('Convertido', 'website-simulator-mvp'); ?></option>
                <option value="archived" <?php selected($status_filter, 'archived'); ?>><?php _e('Arquivado', 'website-simulator-mvp'); ?></option>
            </select>

            <input type="text" name="search" placeholder="<?php _e('Buscar por nome, e-mail ou empresa...', 'website-simulator-mvp'); ?>" value="<?php echo esc_attr($search); ?>">

            <button type="submit" class="button"><?php _e('Filtrar', 'website-simulator-mvp'); ?></button>
            <a href="<?php echo admin_url('admin-ajax.php?action=wsmvp_export_csv&_wpnonce=' . wp_create_nonce('wsmvp_nonce')); ?>" class="button button-secondary" target="_blank"><?php _e('Exportar CSV', 'website-simulator-mvp'); ?></a>
        </form>
    </div>

    <?php if (empty($simulations)): ?>
        <p class="wsmvp-empty-state"><?php _e('Nenhuma simulação encontrada.', 'website-simulator-mvp'); ?></p>
    <?php else: ?>
        <table class="wsmvp-table wsmvp-table-simulations">
            <thead>
                <tr>
                    <th><?php _e('ID', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('Data', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('Nome', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('E-mail', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('Empresa', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('Valor', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('Categoria', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('Status', 'website-simulator-mvp'); ?></th>
                    <th><?php _e('Ações', 'website-simulator-mvp'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($simulations as $sim): ?>
                <tr data-id="<?php echo $sim['id']; ?>">
                    <td>#<?php echo $sim['id']; ?></td>
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
                        <select class="wsmvp-status-select" data-id="<?php echo $sim['id']; ?>">
                            <option value="new" <?php selected($sim['status'], 'new'); ?>><?php _e('Novo', 'website-simulator-mvp'); ?></option>
                            <option value="contacted" <?php selected($sim['status'], 'contacted'); ?>><?php _e('Em Contato', 'website-simulator-mvp'); ?></option>
                            <option value="proposal_sent" <?php selected($sim['status'], 'proposal_sent'); ?>><?php _e('Proposta Enviada', 'website-simulator-mvp'); ?></option>
                            <option value="converted" <?php selected($sim['status'], 'converted'); ?>><?php _e('Convertido', 'website-simulator-mvp'); ?></option>
                            <option value="archived" <?php selected($sim['status'], 'archived'); ?>><?php _e('Arquivado', 'website-simulator-mvp'); ?></option>
                        </select>
                    </td>
                    <td>
                        <button class="wsmvp-btn-icon wsmvp-view-details" data-id="<?php echo $sim['id']; ?>" title="<?php _e('Ver Detalhes', 'website-simulator-mvp'); ?>">👁</button>
                        <button class="wsmvp-btn-icon wsmvp-delete-simulation" data-id="<?php echo $sim['id']; ?>" title="<?php _e('Excluir', 'website-simulator-mvp'); ?>">🗑</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="wsmvp-pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=wsmvp-simulations&paged=<?php echo $i; ?>&status=<?php echo esc_attr($status_filter); ?>&search=<?php echo esc_attr($search); ?>" 
                   class="button <?php echo ($i === $paged) ? 'button-primary' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Details Modal -->
<div id="wsmvp-modal" class="wsmvp-modal" style="display:none;">
    <div class="wsmvp-modal-content">
        <span class="wsmvp-modal-close">&times;</span>
        <div id="wsmvp-modal-body"></div>
    </div>
</div>
