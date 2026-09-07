<?php
/**
 * Uninstall handler for Website Simulator MVP
 * 
 * Removes plugin data on uninstall (if configured)
 * 
 * @package WSMVP
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Only remove data if explicitly allowed in settings
if (get_option('wsmvp_remove_data_on_uninstall', false)) {
    global $wpdb;
    
    // Drop custom tables
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsmvp_simulations");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsmvp_questions");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsmvp_pricing_rules");
    
    // Remove all options
    delete_option('wsmvp_version');
    delete_option('wsmvp_db_version');
    delete_option('wsmvp_settings');
    delete_option('wsmvp_questions');
    delete_option('wsmvp_pricing_rules');
    delete_option('wsmvp_activated');
    delete_option('wsmvp_remove_data_on_uninstall');
    
    // Clear any scheduled events
    wp_clear_scheduled_hook('wsmvp_cleanup_old_leads');
}
