<?php
/**
 * Frontend handler for Website Simulator MVP
 * 
 * Handles frontend display and interactions
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WSMVP_Frontend {
    
    private $settings;
    
    public function __construct() {
        $this->settings = new WSMVP_Settings();
        
        // Add custom endpoints
        add_action('init', array($this, 'add_endpoints'));
        add_action('template_redirect', array($this, 'handle_preview_endpoint'));
        
        // Filter content
        add_filter('body_class', array($this, 'add_body_class'));
    }
    
    /**
     * Add custom rewrite endpoints
     */
    public function add_endpoints() {
        add_rewrite_endpoint('wsmvp-preview', EP_PERMALINK | EP_PAGES);
        add_rewrite_endpoint('wsmvp-proposal', EP_PERMALINK | EP_PAGES);
    }
    
    /**
     * Handle preview endpoint
     */
    public function handle_preview_endpoint() {
        global $wp_query;
        
        if (isset($wp_query->query_vars['wsmvp-preview'])) {
            // Load preview template
            include WSMVP_PLUGIN_DIR . 'public/templates/preview.php';
            exit;
        }
        
        if (isset($wp_query->query_vars['wsmvp-proposal'])) {
            // Load proposal template
            $simulation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($simulation_id) {
                include WSMVP_PLUGIN_DIR . 'public/templates/proposal.php';
                exit;
            }
        }
    }
    
    /**
     * Add body class when simulator is present
     */
    public function add_body_class($classes) {
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'website_simulator')) {
            $classes[] = 'wsmvp-simulator-page';
        }
        
        return $classes;
    }
    
    /**
     * Get simulator configuration for frontend
     */
    public function get_simulator_config() {
        $plugin_settings = $this->settings->get_settings();
        $questions = $this->settings->get_questions();
        
        // Sort questions by order
        usort($questions, function($a, $b) {
            return $a['order'] - $b['order'];
        });
        
        // Group questions by step
        $steps = array();
        foreach ($questions as $question) {
            $step_number = $question['step'] ?? 1;
            if (!isset($steps[$step_number])) {
                $steps[$step_number] = array();
            }
            $steps[$step_number][] = $question;
        }
        
        return array(
            'settings' => $plugin_settings,
            'questions' => $questions,
            'steps' => $steps,
            'total_steps' => count($steps)
        );
    }
    
    /**
     * Generate preview HTML based on answers
     */
    public function generate_preview($answers) {
        ob_start();
        include WSMVP_PLUGIN_DIR . 'public/templates/preview-content.php';
        return ob_get_clean();
    }
}
