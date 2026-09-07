<?php
/**
 * Admin Dashboard View
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wsmvp-dashboard">
    <h1><?php echo esc_html__('Website Simulator Dashboard', 'website-simulator-mvp'); ?></h1>
    
    <div class="wsmvp-stats-grid">
        <div class="wsmvp-stat-card">
            <div class="stat-icon dashicons dashicons-forms"></div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['total_simulations'] ?? 0); ?></h3>
                <p><?php esc_html_e('Total Simulations', 'website-simulator-mvp'); ?></p>
            </div>
        </div>
        
        <div class="wsmvp-stat-card">
            <div class="stat-icon dashicons dashicons-email-alt"></div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['total_leads'] ?? 0); ?></h3>
                <p><?php esc_html_e('Leads Captured', 'website-simulator-mvp'); ?></p>
            </div>
        </div>
        
        <div class="wsmvp-stat-card">
            <div class="stat-icon dashicons dashicons-media-document"></div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['proposals_generated'] ?? 0); ?></h3>
                <p><?php esc_html_e('Proposals Generated', 'website-simulator-mvp'); ?></p>
            </div>
        </div>
        
        <div class="wsmvp-stat-card">
            <div class="stat-icon dashicons dashicons-chart-line"></div>
            <div class="stat-content">
                <h3><?php echo esc_html($settings['currency_symbol'] . ' ' . number_format($stats['average_value'] ?? 0, 2, ',', '.')); ?></h3>
                <p><?php esc_html_e('Average Value', 'website-simulator-mvp'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="wsmvp-dashboard-grid">
        <div class="wsmvp-dashboard-panel">
            <h2><?php esc_html_e('Recent Simulations', 'website-simulator-mvp'); ?></h2>
            
            <?php if (!empty($stats['recent'])): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Name', 'website-simulator-mvp'); ?></th>
                            <th><?php esc_html_e('Email', 'website-simulator-mvp'); ?></th>
                            <th><?php esc_html_e('Value', 'website-simulator-mvp'); ?></th>
                            <th><?php esc_html_e('Status', 'website-simulator-mvp'); ?></th>
                            <th><?php esc_html_e('Date', 'website-simulator-mvp'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent'] as $simulation): ?>
                            <tr>
                                <td><?php echo esc_html($simulation['name']); ?></td>
                                <td><?php echo esc_html($simulation['email']); ?></td>
                                <td><?php echo esc_html($settings['currency_symbol'] . ' ' . number_format($simulation['estimated_value'], 2, ',', '.')); ?></td>
                                <td><span class="wsmvp-status-badge status-<?php echo esc_attr($simulation['status']); ?>"><?php echo esc_html(ucfirst($simulation['status'])); ?></span></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($simulation['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="wsmvp-view-all">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wsmvp-simulations')); ?>" class="button button-secondary">
                        <?php esc_html_e('View All Simulations', 'website-simulator-mvp'); ?>
                    </a>
                </p>
            <?php else: ?>
                <p><?php esc_html_e('No simulations yet. Share your simulator shortcode to start collecting leads!', 'website-simulator-mvp'); ?></p>
                <div class="wsmvp-shortcode-box">
                    <p><strong><?php esc_html_e('Shortcode:', 'website-simulator-mvp'); ?></strong></p>
                    <code>[website_simulator]</code>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="wsmvp-dashboard-panel">
            <h2><?php esc_html_e('Simulations by Status', 'website-simulator-mvp'); ?></h2>
            
            <?php if (!empty($stats['by_status'])): ?>
                <ul class="wsmvp-status-list">
                    <?php foreach ($stats['by_status'] as $status_item): ?>
                        <li>
                            <span class="status-label status-<?php echo esc_attr($status_item['status']); ?>">
                                <?php echo esc_html(ucfirst($status_item['status'])); ?>
                            </span>
                            <span class="status-count"><?php echo esc_html($status_item['count']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php esc_html_e('No data available yet.', 'website-simulator-mvp'); ?></p>
            <?php endif; ?>
            
            <h2><?php esc_html_e('Simulations by Category', 'website-simulator-mvp'); ?></h2>
            
            <?php if (!empty($stats['by_category'])): ?>
                <ul class="wsmvp-category-list">
                    <?php foreach ($stats['by_category'] as $category_item): ?>
                        <li>
                            <span class="category-label">
                                <?php echo esc_html(ucfirst($category_item['project_category'])); ?>
                            </span>
                            <span class="category-count"><?php echo esc_html($category_item['count']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php esc_html_e('No data available yet.', 'website-simulator-mvp'); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="wsmvp-dashboard-panel">
            <h2><?php esc_html_e('Quick Actions', 'website-simulator-mvp'); ?></h2>
            
            <div class="wsmvp-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wsmvp-simulations')); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-forms"></span>
                    <?php esc_html_e('View Simulations', 'website-simulator-mvp'); ?>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=wsmvp-settings')); ?>" class="button button-secondary button-large">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e('Configure Settings', 'website-simulator-mvp'); ?>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=wsmvp-pricing')); ?>" class="button button-secondary button-large">
                    <span class="dashicons dashicons-cart"></span>
                    <?php esc_html_e('Manage Pricing', 'website-simulator-mvp'); ?>
                </a>
            </div>
            
            <h2><?php esc_html_e('Plugin Status', 'website-simulator-mvp'); ?></h2>
            
            <div class="wsmvp-plugin-status">
                <p>
                    <strong><?php esc_html_e('Version:', 'website-simulator-mvp'); ?></strong> 
                    <?php echo esc_html(WSMVP_VERSION); ?>
                </p>
                <p>
                    <strong><?php esc_html_e('Database Version:', 'website-simulator-mvp'); ?></strong> 
                    <?php echo esc_html(get_option('wsmvp_db_version', 'N/A')); ?>
                </p>
                <p>
                    <strong><?php esc_html_e('Status:', 'website-simulator-mvp'); ?></strong> 
                    <span class="wsmvp-status-indicator active"></span>
                    <?php esc_html_e('Active', 'website-simulator-mvp'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.wsmvp-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.wsmvp-stat-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 40px;
    color: #2271b1;
}

.stat-content h3 {
    margin: 0;
    font-size: 28px;
    color: #1d2327;
}

.stat-content p {
    margin: 5px 0 0;
    color: #646970;
}

.wsmvp-dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.wsmvp-dashboard-panel {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
}

.wsmvp-dashboard-panel h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #c3c4c7;
}

.wsmvp-status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-new { background: #dbeafe; color: #1e40af; }
.status-contacted { background: #fef3c7; color: #92400e; }
.status-proposal { background: #ddd6fe; color: #6b21a8; }
.status-converted { background: #d1fae5; color: #065f46; }
.status-archived { background: #f3f4f6; color: #374151; }

.wsmvp-shortcode-box {
    background: #f0f0f1;
    padding: 15px;
    border-radius: 4px;
    margin-top: 15px;
}

.wsmvp-shortcode-box code {
    display: block;
    margin-top: 10px;
    font-size: 14px;
    padding: 10px;
    background: #fff;
    border: 1px solid #c3c4c7;
}

.wsmvp-status-list,
.wsmvp-category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.wsmvp-status-list li,
.wsmvp-category-list li {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f1;
}

.wsmvp-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.wsmvp-quick-actions .button {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}

.wsmvp-plugin-status p {
    margin: 10px 0;
}

.wsmvp-status-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #4ade80;
    margin-right: 5px;
}
</style>
