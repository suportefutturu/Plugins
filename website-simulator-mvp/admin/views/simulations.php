<?php
/**
 * Simulations List View
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

$admin = new WSMVP_Admin();
?>

<div class="wrap wsmvp-simulations">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Simulations', 'website-simulator-mvp'); ?>
    </h1>
    
    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wsmvp-simulations&action=export_csv'), 'wsmvp_export_csv')); ?>" class="page-title-action">
        <?php esc_html_e('Export CSV', 'website-simulator-mvp'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Simulation updated successfully.', 'website-simulator-mvp'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <form method="get" action="" class="wsmvp-filters">
        <input type="hidden" name="page" value="wsmvp-simulations">
        
        <div class="wsmvp-filter-row">
            <input type="text" name="search" placeholder="<?php esc_attr_e('Search by name, email or company...', 'website-simulator-mvp'); ?>" value="<?php echo esc_attr($filters['search']); ?>">
            
            <select name="status">
                <option value=""><?php esc_html_e('All Statuses', 'website-simulator-mvp'); ?></option>
                <option value="new" <?php selected($filters['status'], 'new'); ?>><?php esc_html_e('New', 'website-simulator-mvp'); ?></option>
                <option value="contacted" <?php selected($filters['status'], 'contacted'); ?>><?php esc_html_e('Contacted', 'website-simulator-mvp'); ?></option>
                <option value="proposal_sent" <?php selected($filters['status'], 'proposal_sent'); ?>><?php esc_html_e('Proposal Sent', 'website-simulator-mvp'); ?></option>
                <option value="converted" <?php selected($filters['status'], 'converted'); ?>><?php esc_html_e('Converted', 'website-simulator-mvp'); ?></option>
                <option value="archived" <?php selected($filters['status'], 'archived'); ?>><?php esc_html_e('Archived', 'website-simulator-mvp'); ?></option>
            </select>
            
            <select name="category">
                <option value=""><?php esc_html_e('All Categories', 'website-simulator-mvp'); ?></option>
                <option value="basic" <?php selected($filters['category'], 'basic'); ?>><?php esc_html_e('Basic', 'website-simulator-mvp'); ?></option>
                <option value="intermediate" <?php selected($filters['category'], 'intermediate'); ?>><?php esc_html_e('Intermediate', 'website-simulator-mvp'); ?></option>
                <option value="advanced" <?php selected($filters['category'], 'advanced'); ?>><?php esc_html_e('Advanced', 'website-simulator-mvp'); ?></option>
                <option value="custom" <?php selected($filters['category'], 'custom'); ?>><?php esc_html_e('Custom', 'website-simulator-mvp'); ?></option>
            </select>
            
            <input type="date" name="date_from" value="<?php echo esc_attr($filters['date_from']); ?>" placeholder="<?php esc_attr_e('From', 'website-simulator-mvp'); ?>">
            <input type="date" name="date_to" value="<?php echo esc_attr($filters['date_to']); ?>" placeholder="<?php esc_attr_e('To', 'website-simulator-mvp'); ?>">
            
            <button type="submit" class="button"><?php esc_html_e('Filter', 'website-simulator-mvp'); ?></button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wsmvp-simulations')); ?>" class="button"><?php esc_html_e('Clear', 'website-simulator-mvp'); ?></a>
        </div>
    </form>
    
    <!-- Simulations Table -->
    <table class="wp-list-table widefat fixed striped wsmvp-simulations-table">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Date', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Name', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Email', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Company', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Value', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Category', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Status', 'website-simulator-mvp'); ?></th>
                <th><?php esc_html_e('Actions', 'website-simulator-mvp'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($simulations)): ?>
                <?php foreach ($simulations as $simulation): ?>
                    <tr id="simulation-<?php echo esc_attr($simulation->id); ?>">
                        <td><?php echo esc_html($simulation->id); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($simulation->created_at))); ?></td>
                        <td>
                            <strong><?php echo esc_html($simulation->name); ?></strong>
                        </td>
                        <td>
                            <a href="mailto:<?php echo esc_attr($simulation->email); ?>">
                                <?php echo esc_html($simulation->email); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($simulation->company); ?></td>
                        <td>
                            <?php
                            $settings = get_option('wsmvp_settings');
                            echo esc_html(($settings['currency_symbol'] ?? 'R$') . ' ' . number_format($simulation->estimated_value, 2, ',', '.'));
                            ?>
                        </td>
                        <td>
                            <span class="wsmvp-category-badge category-<?php echo esc_attr($simulation->project_category); ?>">
                                <?php echo esc_html(ucfirst($simulation->project_category)); ?>
                            </span>
                        </td>
                        <td>
                            <span class="wsmvp-status-badge status-<?php echo esc_attr($simulation->status); ?>">
                                <?php echo esc_html($admin->get_status_label($simulation->status)); ?>
                            </span>
                        </td>
                        <td>
                            <div class="wsmvp-actions">
                                <button type="button" class="button button-small wsmvp-view-details" data-id="<?php echo esc_attr($simulation->id); ?>">
                                    <?php esc_html_e('View', 'website-simulator-mvp'); ?>
                                </button>
                                
                                <select class="wsmvp-status-select" data-id="<?php echo esc_attr($simulation->id); ?>">
                                    <option value="new" <?php selected($simulation->status, 'new'); ?>><?php esc_html_e('New', 'website-simulator-mvp'); ?></option>
                                    <option value="contacted" <?php selected($simulation->status, 'contacted'); ?>><?php esc_html_e('Contacted', 'website-simulator-mvp'); ?></option>
                                    <option value="proposal_sent" <?php selected($simulation->status, 'proposal_sent'); ?>><?php esc_html_e('Proposal Sent', 'website-simulator-mvp'); ?></option>
                                    <option value="converted" <?php selected($simulation->status, 'converted'); ?>><?php esc_html_e('Converted', 'website-simulator-mvp'); ?></option>
                                    <option value="archived" <?php selected($simulation->status, 'archived'); ?>><?php esc_html_e('Archived', 'website-simulator-mvp'); ?></option>
                                </select>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <?php esc_html_e('No simulations found.', 'website-simulator-mvp'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Simulation Detail Modal -->
    <div id="wsmvp-detail-modal" class="wsmvp-modal" style="display: none;">
        <div class="wsmvp-modal-content">
            <span class="wsmvp-modal-close">&times;</span>
            <h2><?php esc_html_e('Simulation Details', 'website-simulator-mvp'); ?></h2>
            <div id="wsmvp-modal-body"></div>
        </div>
    </div>
</div>

<style>
.wsmvp-filters {
    background: #fff;
    padding: 15px;
    margin: 20px 0;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
}

.wsmvp-filter-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.wsmvp-filter-row input[type="text"],
.wsmvp-filter-row input[type="date"],
.wsmvp-filter-row select {
    min-width: 150px;
}

.wsmvp-category-badge,
.wsmvp-status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.category-basic { background: #dbeafe; color: #1e40af; }
.category-intermediate { background: #fef3c7; color: #92400e; }
.category-advanced { background: #ddd6fe; color: #6b21a8; }
.category-custom { background: #fecaca; color: #991b1b; }

.wsmvp-actions {
    display: flex;
    gap: 5px;
    align-items: center;
}

.wsmvp-status-select {
    padding: 4px 8px;
    font-size: 12px;
}

/* Modal Styles */
.wsmvp-modal {
    position: fixed;
    z-index: 999999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.wsmvp-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 30px;
    border: 1px solid #888;
    width: 90%;
    max-width: 700px;
    border-radius: 8px;
    position: relative;
}

.wsmvp-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.wsmvp-modal-close:hover {
    color: #000;
}

.wsmvp-detail-row {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f1;
}

.wsmvp-detail-label {
    font-weight: 600;
    width: 150px;
    color: #646970;
}

.wsmvp-detail-value {
    flex: 1;
    color: #1d2327;
}
</style>

<script>
jQuery(document).ready(function($) {
    // View details modal
    $('.wsmvp-view-details').on('click', function() {
        const simulationId = $(this).data('id');
        
        $.ajax({
            url: wsmvpAdminData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wsmvp_get_simulation_details',
                nonce: wsmvpAdminData.nonce,
                id: simulationId
            },
            success: function(response) {
                if (response.success) {
                    $('#wsmvp-modal-body').html(response.data.html);
                    $('#wsmvp-detail-modal').fadeIn();
                }
            }
        });
    });
    
    // Close modal
    $('.wsmvp-modal-close').on('click', function() {
        $('#wsmvp-detail-modal').fadeOut();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).is('#wsmvp-detail-modal')) {
            $('#wsmvp-detail-modal').fadeOut();
        }
    });
    
    // Update status
    $('.wsmvp-status-select').on('change', function() {
        const $select = $(this);
        const simulationId = $select.data('id');
        const newStatus = $select.val();
        
        $.ajax({
            url: wsmvpAdminData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wsmvp_update_simulation_status',
                nonce: wsmvpAdminData.nonce,
                id: simulationId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
