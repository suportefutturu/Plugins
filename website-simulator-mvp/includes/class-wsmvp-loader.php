<?php
/**
 * Loader for Website Simulator MVP
 * 
 * Registers and initializes hooks, filters, and assets
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WSMVP_Loader {
    
    public function __construct() {
        $this->register_hooks();
        $this->register_assets();
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Shortcode registration
        add_shortcode('website_simulator', array($this, 'render_shortcode'));
        
        // Gutenberg block registration
        add_action('init', array($this, 'register_block'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers for frontend
        add_action('wp_ajax_wsmvp_submit_simulation', array($this, 'ajax_submit_simulation'));
        add_action('wp_ajax_nopriv_wsmvp_submit_simulation', array($this, 'ajax_submit_simulation'));
    }
    
    /**
     * Register script and style handles
     */
    private function register_assets() {
        // Frontend CSS
        wp_register_style(
            'wsmvp-frontend',
            WSMVP_PLUGIN_URL . 'public/css/wsmvp-frontend.css',
            array(),
            WSMVP_VERSION
        );
        
        // Frontend JS
        wp_register_script(
            'wsmvp-frontend',
            WSMVP_PLUGIN_URL . 'public/js/wsmvp-frontend.js',
            array('jquery'),
            WSMVP_VERSION,
            true
        );
        
        // Admin CSS
        wp_register_style(
            'wsmvp-admin',
            WSMVP_PLUGIN_URL . 'admin/css/wsmvp-admin.css',
            array('wp-color-picker'),
            WSMVP_VERSION
        );
        
        // Admin JS
        wp_register_script(
            'wsmvp-admin',
            WSMVP_PLUGIN_URL . 'admin/js/wsmvp-admin.js',
            array('jquery', 'wp-color-picker'),
            WSMVP_VERSION,
            true
        );
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_title' => 'true',
            'show_intro' => 'true'
        ), $atts);
        
        ob_start();
        include WSMVP_PLUGIN_DIR . 'public/templates/simulator.php';
        return ob_get_clean();
    }
    
    /**
     * Register Gutenberg block
     */
    public function register_block() {
        if (function_exists('register_block_type')) {
            register_block_type('wsmvp/simulator', array(
                'render_callback' => array($this, 'render_shortcode')
            ));
        }
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        global $post;
        
        // Check if simulator is on this page
        $has_shortcode = false;
        if (is_a($post, 'WP_Post')) {
            $has_shortcode = has_shortcode($post->post_content, 'website_simulator');
        }
        
        if (!$has_shortcode && !isset($_GET['wsmvp_preview'])) {
            return;
        }
        
        wp_enqueue_style('wsmvp-frontend');
        wp_enqueue_script('wsmvp-frontend');
        
        // Localize script with data
        $settings = new WSMVP_Settings();
        $plugin_settings = $settings->get_settings();
        $questions = $settings->get_questions();
        
        wp_localize_script('wsmvp-frontend', 'wsmvpData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wsmvp_frontend_nonce'),
            'settings' => $plugin_settings,
            'questions' => $questions,
            'i18n' => array(
                'next' => __('Next', 'website-simulator-mvp'),
                'previous' => __('Previous', 'website-simulator-mvp'),
                'submit' => __('Submit', 'website-simulator-mvp'),
                'required' => __('This field is required', 'website-simulator-mvp'),
                'invalidEmail' => __('Please enter a valid email', 'website-simulator-mvp'),
                'consentRequired' => __('You must accept the privacy policy', 'website-simulator-mvp'),
                'error' => __('An error occurred. Please try again.', 'website-simulator-mvp'),
                'success' => __('Simulation submitted successfully!', 'website-simulator-mvp')
            )
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wsmvp') === false && strpos($hook, 'website-simulator') === false) {
            return;
        }
        
        wp_enqueue_style('wsmvp-admin');
        wp_enqueue_script('wsmvp-admin');
        
        wp_localize_script('wsmvp-admin', 'wsmvpAdminData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wsmvp_admin_nonce'),
            'i18n' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'website-simulator-mvp'),
                'saved' => __('Settings saved successfully', 'website-simulator-mvp'),
                'error' => __('An error occurred', 'website-simulator-mvp')
            )
        ));
    }
    
    /**
     * AJAX handler for submitting simulation
     */
    public function ajax_submit_simulation() {
        check_ajax_referer('wsmvp_frontend_nonce', 'nonce');
        
        // Validate required fields
        $required_fields = array('name', 'email', 'answers', 'consent');
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array(
                    'message' => sprintf(__('Field %s is required', 'website-simulator-mvp'), $field)
                ));
            }
        }
        
        // Validate email
        if (!is_email($_POST['email'])) {
            wp_send_json_error(array(
                'message' => __('Please provide a valid email address', 'website-simulator-mvp')
            ));
        }
        
        // Validate consent
        if (empty($_POST['consent'])) {
            wp_send_json_error(array(
                'message' => __('You must accept the privacy policy', 'website-simulator-mvp')
            ));
        }
        
        // Prepare data
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'company' => sanitize_text_field($_POST['company'] ?? ''),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'answers' => $_POST['answers'],
            'estimated_value' => floatval($_POST['estimated_value'] ?? 0),
            'project_category' => sanitize_text_field($_POST['project_category'] ?? 'basic'),
            'notes' => sanitize_textarea_field($_POST['notes'] ?? ''),
            'consent' => true
        );
        
        // Save simulation
        $database = new WSMVP_Database();
        $simulation_id = $database->save_simulation($data);
        
        if ($simulation_id) {
            // Send notification emails
            $this->send_notification_emails($data, $simulation_id);
            
            wp_send_json_success(array(
                'id' => $simulation_id,
                'message' => __('Simulation submitted successfully!', 'website-simulator-mvp')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to save simulation. Please try again.', 'website-simulator-mvp')
            ));
        }
    }
    
    /**
     * Send notification emails
     */
    private function send_notification_emails($data, $simulation_id) {
        $settings = new WSMVP_Settings();
        $plugin_settings = $settings->get_settings();
        
        // Email to admin
        $to_admin = $plugin_settings['lead_email'];
        $subject_admin = sprintf(
            __('New Website Simulation - %s', 'website-simulator-mvp'),
            $data['name']
        );
        
        $message_admin = sprintf(
            __("A new website simulation was submitted:\n\nName: %s\nCompany: %s\nEmail: %s\nPhone: %s\nEstimated Value: %s\nCategory: %s\n\nView details in the admin panel.", 'website-simulator-mvp'),
            $data['name'],
            $data['company'],
            $data['email'],
            $data['phone'],
            number_format($data['estimated_value'], 2, ',', '.'),
            $data['project_category']
        );
        
        wp_mail($to_admin, $subject_admin, $message_admin);
        
        // Confirmation email to client (if enabled)
        if (!empty($plugin_settings['enable_email_capture'])) {
            $to_client = $data['email'];
            $subject_client = __('Thank you for your interest!', 'website-simulator-mvp');
            
            $message_client = sprintf(
                __("Hello %s,\n\nThank you for using our website simulator. We have received your project estimate request.\n\nOur team will contact you soon to discuss your project.\n\nBest regards,\n%s", 'website-simulator-mvp'),
                $data['name'],
                $plugin_settings['company_name']
            );
            
            wp_mail($to_client, $subject_client, $message_client);
        }
    }
}
