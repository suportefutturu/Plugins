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

/**
 * Main plugin class
 */
final class Website_Simulator_MVP {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('Website_Simulator_MVP_Uninstall', 'uninstall'));

        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'load_classes'));
        add_action('init', array($this, 'register_shortcodes'));
    }

    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'website-simulator-mvp',
            false,
            dirname(WSMVP_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Load plugin classes
     */
    public function load_classes() {
        // Database handler
        require_once WSMVP_PLUGIN_DIR . 'includes/class-wsmvp-database.php';
        
        // Settings handler
        require_once WSMVP_PLUGIN_DIR . 'includes/class-wsmvp-settings.php';
        
        // Pricing calculator
        require_once WSMVP_PLUGIN_DIR . 'includes/class-wsmvp-pricing.php';
        
        // Admin handler
        require_once WSMVP_PLUGIN_DIR . 'includes/class-wsmvp-admin.php';
        
        // Frontend handler
        require_once WSMVP_PLUGIN_DIR . 'includes/class-wsmvp-frontend.php';

        // Initialize database
        WSMVP_Database::get_instance();

        // Initialize admin
        if (is_admin()) {
            WSMVP_Admin::get_instance();
        }

        // Initialize frontend
        WSMVP_Frontend::get_instance();
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('website_simulator', array('WSMVP_Frontend', 'render_simulator'));
    }

    /**
     * Activation hook
     */
    public function activate() {
        // Create database tables
        WSMVP_Database::create_tables();
        
        // Set default options
        WSMVP_Settings::set_defaults();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivation hook
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

/**
 * Uninstall handler class
 */
class Website_Simulator_MVP_Uninstall {
    public static function uninstall() {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Check if user wants to delete data
        $delete_data = get_option('wsmvp_delete_data_on_uninstall', false);
        
        if ($delete_data) {
            // Drop tables
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wsmvp_simulations");
            
            // Delete options
            delete_option('wsmvp_version');
            delete_option('wsmvp_settings');
            delete_option('wsmvp_questions');
            delete_option('wsmvp_pricing_rules');
            delete_option('wsmvp_delete_data_on_uninstall');
        }
    }
}

// Initialize plugin
function wsmvp_init() {
    return Website_Simulator_MVP::get_instance();
}
wsmvp_init();
