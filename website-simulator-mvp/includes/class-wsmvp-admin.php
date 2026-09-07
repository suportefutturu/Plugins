<?php
/**
 * Admin handler for Website Simulator MVP
 * 
 * Handles admin menu, pages, and functionality
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WSMVP_Admin {
    
    private $settings;
    private $database;
    
    public function __construct() {
        $this->settings = new WSMVP_Settings();
        $this->database = new WSMVP_Database();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Website Simulator', 'website-simulator-mvp'),
            __('Website Simulator', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-desktop',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'wsmvp-dashboard',
            __('Dashboard', 'website-simulator-mvp'),
            __('Dashboard', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-dashboard',
            array($this, 'render_dashboard')
        );
        
        // Simulations submenu
        add_submenu_page(
            'wsmvp-dashboard',
            __('Simulations', 'website-simulator-mvp'),
            __('Simulations', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-simulations',
            array($this, 'render_simulations')
        );
        
        // Questions submenu
        add_submenu_page(
            'wsmvp-dashboard',
            __('Questions', 'website-simulator-mvp'),
            __('Questions', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-questions',
            array($this, 'render_questions')
        );
        
        // Pricing submenu
        add_submenu_page(
            'wsmvp-dashboard',
            __('Pricing Rules', 'website-simulator-mvp'),
            __('Pricing Rules', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-pricing',
            array($this, 'render_pricing')
        );
        
        // Settings submenu
        add_submenu_page(
            'wsmvp-dashboard',
            __('Settings', 'website-simulator-mvp'),
            __('Settings', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-settings',
            array($this, 'render_settings')
        );
    }
    
    /**
     * Handle admin actions
     */
    public function handle_actions() {
        // Export CSV
        if (isset($_GET['page']) && $_GET['page'] === 'wsmvp-simulations' 
            && isset($_GET['action']) && $_GET['action'] === 'export_csv') {
            check_admin_referer('wsmvp_export_csv');
            $this->database->export_csv();
        }
        
        // Update simulation status
        if (isset($_POST['wsmvp_update_status'])) {
            check_admin_referer('wsmvp_update_status');
            
            $id = intval($_POST['simulation_id']);
            $status = sanitize_text_field($_POST['status']);
            
            $this->database->update_simulation_status($id, $status);
            
            wp_redirect(add_query_arg(array(
                'page' => 'wsmvp-simulations',
                'updated' => '1'
            ), admin_url('admin.php')));
            exit;
        }
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        $stats = $this->database->get_statistics();
        $settings = $this->settings->get_settings();
        
        include WSMVP_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Render simulations list page
     */
    public function render_simulations() {
        $filters = array(
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'category' => isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '',
            'search' => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'page' => isset($_GET['paged']) ? intval($_GET['paged']) : 1,
            'per_page' => 20
        );
        
        $simulations = $this->database->get_simulations($filters);
        $total = count($simulations);
        
        include WSMVP_PLUGIN_DIR . 'admin/views/simulations.php';
    }
    
    /**
     * Render questions management page
     */
    public function render_questions() {
        $questions = $this->settings->get_questions();
        
        include WSMVP_PLUGIN_DIR . 'admin/views/questions.php';
    }
    
    /**
     * Render pricing rules page
     */
    public function render_pricing() {
        $pricing_rules = $this->settings->get_pricing_rules();
        
        // Group by category
        $grouped_rules = array();
        foreach ($pricing_rules as $rule) {
            $category = $rule['category'];
            if (!isset($grouped_rules[$category])) {
                $grouped_rules[$category] = array();
            }
            $grouped_rules[$category][] = $rule;
        }
        
        include WSMVP_PLUGIN_DIR . 'admin/views/pricing.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        $plugin_settings = $this->settings->get_settings();
        
        include WSMVP_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Get status label
     */
    public function get_status_label($status) {
        $labels = array(
            'new' => __('New', 'website-simulator-mvp'),
            'contacted' => __('Contacted', 'website-simulator-mvp'),
            'proposal_sent' => __('Proposal Sent', 'website-simulator-mvp'),
            'converted' => __('Converted', 'website-simulator-mvp'),
            'archived' => __('Archived', 'website-simulator-mvp')
        );
        
        return $labels[$status] ?? $status;
    }
    
    /**
     * Get status class for styling
     */
    public function get_status_class($status) {
        $classes = array(
            'new' => 'status-new',
            'contacted' => 'status-contacted',
            'proposal_sent' => 'status-proposal',
            'converted' => 'status-converted',
            'archived' => 'status-archived'
        );
        
        return $classes[$status] ?? '';
    }
}
