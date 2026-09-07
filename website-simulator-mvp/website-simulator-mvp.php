<?php
/**
 * Plugin Name: Website Simulator MVP
 * Plugin URI: https://example.com/website-simulator-mvp
 * Description: Simulador de criação de websites com questionário inteligente, prévia visual, estimativa comercial e captura de leads.
 * Version: 1.0.0
 * Author: Your Agency
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: website-simulator-mvp
 * Domain Path: /languages
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WSMVP_VERSION', '1.0.0');
define('WSMVP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WSMVP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WSMVP_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check PHP version
if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>' . 
            esc_html__('Website Simulator MVP requires PHP 8.1 or higher.', 'website-simulator-mvp') . 
            '</p></div>';
    });
    return;
}

// Autoloader for plugin classes
spl_autoload_register(function($class) {
    $prefix = 'WSMVP_';
    $base_dir = WSMVP_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Main plugin class
class Website_Simulator_MVP {
    
    private static $instance = null;
    private $loader;
    private $admin;
    private $frontend;
    private $settings;
    private $pricing;
    private $database;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_components();
        $this->load_textdomain();
        $this->register_hooks();
    }
    
    private function init_components() {
        $this->database = new WSMVP_Database();
        $this->loader = new WSMVP_Loader();
        $this->settings = new WSMVP_Settings();
        $this->pricing = new WSMVP_Pricing();
        $this->admin = new WSMVP_Admin();
        $this->frontend = new WSMVP_Frontend();
    }
    
    private function load_textdomain() {
        load_plugin_textdomain(
            'website-simulator-mvp',
            false,
            dirname(WSMVP_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    private function register_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('Website_Simulator_MVP', 'uninstall'));
    }
    
    public function activate() {
        // Create database tables
        $this->database->create_tables();
        
        // Set default options
        $this->settings->set_defaults();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation timestamp
        update_option('wsmvp_activated', time());
    }
    
    public function deactivate() {
        // Clear any scheduled events
        wp_clear_scheduled_hook('wsmvp_cleanup_old_leads');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public static function uninstall() {
        // Only remove data if explicitly allowed
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
        }
    }
}

// Initialize plugin
function wsmvp_init() {
    return Website_Simulator_MVP::get_instance();
}

// Start the plugin
wsmvp_init();
