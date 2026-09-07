<?php
/**
 * Uninstall handler
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if user has permission
if (!current_user_can('activate_plugins')) {
    return;
}

// Check if we should delete data
$delete_data = get_option('wsmvp_delete_data_on_uninstall', false);

if ($delete_data) {
    global $wpdb;
    
    // Drop tables
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsmvp_simulations");
    
    // Delete all options
    delete_option('wsmvp_version');
    delete_option('wsmvp_db_version');
    delete_option('wsmvp_settings');
    delete_option('wsmvp_questions');
    delete_option('wsmvp_pricing_rules');
    delete_option('wsmvp_delete_data_on_uninstall');
    
    // Clear any transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wsmvp_%'");
}
