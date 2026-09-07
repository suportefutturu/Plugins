<?php
/**
 * Admin handler class
 * 
 * Handles admin menu, pages and AJAX actions
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WSMVP_Admin {

    private static $instance = null;
    private $settings;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->settings = WSMVP_Settings::get_instance()->get_settings();
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_wsmvp_update_status', array($this, 'update_status'));
        add_action('wp_ajax_wsmvp_delete_simulation', array($this, 'delete_simulation'));
        add_action('wp_ajax_wsmvp_export_csv', array($this, 'export_csv'));
        add_action('wp_ajax_wsmvp_save_settings', array($this, 'save_settings'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Website Simulator', 'website-simulator-mvp'),
            __('Website Simulator', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-analytics',
            30
        );

        add_submenu_page(
            'wsmvp-dashboard',
            __('Dashboard', 'website-simulator-mvp'),
            __('Dashboard', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-dashboard',
            array($this, 'render_dashboard')
        );

        add_submenu_page(
            'wsmvp-dashboard',
            __('Simulações', 'website-simulator-mvp'),
            __('Simulações', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-simulations',
            array($this, 'render_simulations')
        );

        add_submenu_page(
            'wsmvp-dashboard',
            __('Perguntas', 'website-simulator-mvp'),
            __('Perguntas', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-questions',
            array($this, 'render_questions')
        );

        add_submenu_page(
            'wsmvp-dashboard',
            __('Preços', 'website-simulator-mvp'),
            __('Preços', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-pricing',
            array($this, 'render_pricing')
        );

        add_submenu_page(
            'wsmvp-dashboard',
            __('Configurações', 'website-simulator-mvp'),
            __('Configurações', 'website-simulator-mvp'),
            'manage_options',
            'wsmvp-settings',
            array($this, 'render_settings')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'wsmvp-') === false) {
            return;
        }

        wp_enqueue_style(
            'wsmvp-admin',
            WSMVP_PLUGIN_URL . 'admin/css/wsmvp-admin.css',
            array(),
            WSMVP_VERSION
        );

        wp_enqueue_script(
            'wsmvp-admin',
            WSMVP_PLUGIN_URL . 'admin/js/wsmvp-admin.js',
            array('jquery'),
            WSMVP_VERSION,
            true
        );

        wp_localize_script('wsmvp-admin', 'wsmvpAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wsmvp_nonce'),
            'strings' => array(
                'confirmDelete' => __('Tem certeza que deseja excluir?', 'website-simulator-mvp'),
                'error' => __('Ocorreu um erro', 'website-simulator-mvp'),
                'success' => __('Salvo com sucesso', 'website-simulator-mvp'),
            ),
        ));
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        $db = WSMVP_Database::get_instance();
        
        $stats = array(
            'total_simulations' => $db->get_count(),
            'new_leads' => $db->get_count('new'),
            'contacted' => $db->get_count('contacted'),
            'proposal_sent' => $db->get_count('proposal_sent'),
            'converted' => $db->get_count('converted'),
            'archived' => $db->get_count('archived'),
            'total_value' => $db->get_total_value(),
            'average_value' => $db->get_average_value(),
        );

        $recent_simulations = $db->get_simulations(array('limit' => 5));

        include WSMVP_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render simulations page
     */
    public function render_simulations() {
        $db = WSMVP_Database::get_instance();
        
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;

        $simulations = $db->get_simulations(array(
            'status' => $status_filter,
            'search' => $search,
            'limit' => $per_page,
            'offset' => ($paged - 1) * $per_page,
        ));

        $total = $db->get_count($status_filter);
        $total_pages = ceil($total / $per_page);

        include WSMVP_PLUGIN_DIR . 'admin/views/simulations.php';
    }

    /**
     * Render questions page
     */
    public function render_questions() {
        $settings = WSMVP_Settings::get_instance();
        $questions = $settings->get_questions();
        include WSMVP_PLUGIN_DIR . 'admin/views/questions.php';
    }

    /**
     * Render pricing page
     */
    public function render_pricing() {
        $settings = WSMVP_Settings::get_instance();
        $pricing_rules = $settings->get_pricing_rules();
        include WSMVP_PLUGIN_DIR . 'admin/views/pricing.php';
    }

    /**
     * Render settings page
     */
    public function render_settings() {
        $settings = WSMVP_Settings::get_instance()->get_settings();
        include WSMVP_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Update simulation status
     */
    public function update_status() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão', 'website-simulator-mvp')));
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wsmvp_nonce')) {
            wp_send_json_error(array('message' => __('Erro de segurança', 'website-simulator-mvp')));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (!$id || empty($status)) {
            wp_send_json_error(array('message' => __('Dados inválidos', 'website-simulator-mvp')));
        }

        $db = WSMVP_Database::get_instance();
        $result = $db->update_status($id, $status);

        if ($result) {
            wp_send_json_success(array('message' => __('Status atualizado', 'website-simulator-mvp')));
        } else {
            wp_send_json_error(array('message' => __('Erro ao atualizar', 'website-simulator-mvp')));
        }
    }

    /**
     * Delete simulation
     */
    public function delete_simulation() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão', 'website-simulator-mvp')));
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wsmvp_nonce')) {
            wp_send_json_error(array('message' => __('Erro de segurança', 'website-simulator-mvp')));
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (!$id) {
            wp_send_json_error(array('message' => __('ID inválido', 'website-simulator-mvp')));
        }

        $db = WSMVP_Database::get_instance();
        $result = $db->delete_simulation($id);

        if ($result) {
            wp_send_json_success(array('message' => __('Excluído com sucesso', 'website-simulator-mvp')));
        } else {
            wp_send_json_error(array('message' => __('Erro ao excluir', 'website-simulator-mvp')));
        }
    }

    /**
     * Export CSV
     */
    public function export_csv() {
        if (!current_user_can('manage_options')) {
            die(__('Sem permissão', 'website-simulator-mvp'));
        }

        $db = WSMVP_Database::get_instance();
        $db->export_csv();
    }

    /**
     * Save settings
     */
    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Sem permissão', 'website-simulator-mvp')));
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wsmvp_nonce')) {
            wp_send_json_error(array('message' => __('Erro de segurança', 'website-simulator-mvp')));
        }

        $settings_type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();

        $settings_instance = WSMVP_Settings::get_instance();

        switch ($settings_type) {
            case 'general':
                $settings_instance->update_settings($data);
                break;
            case 'questions':
                $settings_instance->update_questions($data);
                break;
            case 'pricing':
                $settings_instance->update_pricing_rules($data);
                break;
            default:
                wp_send_json_error(array('message' => __('Tipo inválido', 'website-simulator-mvp')));
        }

        wp_send_json_success(array('message' => __('Salvo com sucesso', 'website-simulator-mvp')));
    }
}
