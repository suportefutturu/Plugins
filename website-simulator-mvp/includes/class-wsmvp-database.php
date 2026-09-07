<?php
/**
 * Database handler for Website Simulator MVP
 * 
 * Handles database table creation and migrations
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WSMVP_Database {
    
    private $db_version = '1.0.0';
    
    public function __construct() {
        add_action('wp_ajax_wsmvp_get_simulations', array($this, 'ajax_get_simulations'));
    }
    
    /**
     * Create custom database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Simulations table
        $table_simulations = $wpdb->prefix . 'wsmvp_simulations';
        $sql_simulations = "CREATE TABLE $table_simulations (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            name varchar(255) NOT NULL,
            company varchar(255) DEFAULT '',
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT '',
            answers longtext NOT NULL,
            estimated_value decimal(10,2) DEFAULT 0.00,
            project_category varchar(100) DEFAULT 'basic',
            status varchar(50) DEFAULT 'new',
            notes text DEFAULT '',
            consent tinyint(1) DEFAULT 0,
            ip_address varchar(45) DEFAULT '',
            user_agent varchar(500) DEFAULT '',
            PRIMARY KEY  (id),
            KEY email (email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Questions table (for custom questions)
        $table_questions = $wpdb->prefix . 'wsmvp_questions';
        $sql_questions = "CREATE TABLE $table_questions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text DEFAULT '',
            field_type varchar(50) NOT NULL DEFAULT 'select',
            required tinyint(1) DEFAULT 1,
            field_order int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            conditional_rules longtext DEFAULT NULL,
            options longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY field_order (field_order),
            KEY status (status)
        ) $charset_collate;";
        
        // Pricing rules table
        $table_pricing = $wpdb->prefix . 'wsmvp_pricing_rules';
        $sql_pricing = "CREATE TABLE $table_pricing (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            rule_key varchar(100) NOT NULL,
            rule_type varchar(50) NOT NULL,
            option_key varchar(100) NOT NULL,
            option_label varchar(255) NOT NULL,
            additional_value decimal(10,2) DEFAULT 0.00,
            complexity_score int(11) DEFAULT 0,
            category varchar(100) DEFAULT '',
            is_active tinyint(1) DEFAULT 1,
            display_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY rule_key (rule_key),
            KEY rule_type (rule_type),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_simulations);
        dbDelta($sql_questions);
        dbDelta($sql_pricing);
        
        // Update DB version
        update_option('wsmvp_db_version', $this->db_version);
    }
    
    /**
     * Save simulation to database
     */
    public function save_simulation($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wsmvp_simulations';
        
        $insert_data = array(
            'name' => sanitize_text_field($data['name']),
            'company' => sanitize_text_field($data['company'] ?? ''),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'answers' => wp_json_encode($data['answers']),
            'estimated_value' => floatval($data['estimated_value']),
            'project_category' => sanitize_text_field($data['project_category'] ?? 'basic'),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'consent' => !empty($data['consent']) ? 1 : 0,
            'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        );
        
        $format = array(
            '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s', '%s'
        );
        
        $result = $wpdb->insert($table, $insert_data, $format);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get simulation by ID
     */
    public function get_simulation($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wsmvp_simulations';
        
        $simulation = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
        );
        
        if ($simulation && $simulation->answers) {
            $simulation->answers = json_decode($simulation->answers, true);
        }
        
        return $simulation;
    }
    
    /**
     * Get all simulations with filters
     */
    public function get_simulations($args = array()) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wsmvp_simulations';
        
        $defaults = array(
            'status' => '',
            'category' => '',
            'search' => '',
            'date_from' => '',
            'date_to' => '',
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $values = array();
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = sanitize_text_field($args['status']);
        }
        
        if (!empty($args['category'])) {
            $where[] = 'project_category = %s';
            $values[] = sanitize_text_field($args['category']);
        }
        
        if (!empty($args['search'])) {
            $search_term = '%' . sanitize_text_field($args['search']) . '%';
            $where[] = '(name LIKE %s OR company LIKE %s OR email LIKE %s)';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'DATE(created_at) >= %s';
            $values[] = sanitize_text_field($args['date_from']);
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'DATE(created_at) <= %s';
            $values[] = sanitize_text_field($args['date_to']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        $sql = "SELECT * FROM $table WHERE $where_clause 
                ORDER BY {$args['orderby']} {$args['order']} 
                LIMIT %d OFFSET %d";
        
        $values[] = intval($args['per_page']);
        $values[] = intval($offset);
        
        $prepared_sql = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $values));
        
        $simulations = $wpdb->get_results($prepared_sql);
        
        // Decode answers
        foreach ($simulations as $simulation) {
            if ($simulation->answers) {
                $simulation->answers = json_decode($simulation->answers, true);
            }
        }
        
        return $simulations;
    }
    
    /**
     * Update simulation status
     */
    public function update_simulation_status($id, $status) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wsmvp_simulations';
        
        $valid_statuses = array('new', 'contacted', 'proposal_sent', 'converted', 'archived');
        
        if (!in_array($status, $valid_statuses)) {
            return false;
        }
        
        return $wpdb->update(
            $table,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Get simulation statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wsmvp_simulations';
        
        $stats = array();
        
        // Total simulations
        $stats['total_simulations'] = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        // Leads captured (with consent)
        $stats['total_leads'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE consent = 1");
        
        // Proposals generated (status = proposal_sent or converted)
        $stats['proposals_generated'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE status IN ('proposal_sent', 'converted')"
        );
        
        // Average estimated value
        $stats['average_value'] = $wpdb->get_var("SELECT AVG(estimated_value) FROM $table");
        
        // Status breakdown
        $stats['by_status'] = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM $table GROUP BY status",
            ARRAY_A
        );
        
        // Category breakdown
        $stats['by_category'] = $wpdb->get_results(
            "SELECT project_category, COUNT(*) as count FROM $table GROUP BY project_category",
            ARRAY_A
        );
        
        // Recent simulations
        $stats['recent'] = $wpdb->get_results(
            "SELECT id, name, email, estimated_value, status, created_at 
             FROM $table ORDER BY created_at DESC LIMIT 5",
            ARRAY_A
        );
        
        return $stats;
    }
    
    /**
     * Export simulations to CSV
     */
    public function export_csv() {
        $simulations = $this->get_simulations(array('per_page' => 1000));
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=wsmvp-simulations-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, array(
            __('ID', 'website-simulator-mvp'),
            __('Date', 'website-simulator-mvp'),
            __('Name', 'website-simulator-mvp'),
            __('Company', 'website-simulator-mvp'),
            __('Email', 'website-simulator-mvp'),
            __('Phone', 'website-simulator-mvp'),
            __('Estimated Value', 'website-simulator-mvp'),
            __('Category', 'website-simulator-mvp'),
            __('Status', 'website-simulator-mvp'),
            __('Consent', 'website-simulator-mvp')
        ));
        
        foreach ($simulations as $simulation) {
            fputcsv($output, array(
                $simulation->id,
                $simulation->created_at,
                $simulation->name,
                $simulation->company,
                $simulation->email,
                $simulation->phone,
                number_format($simulation->estimated_value, 2, ',', '.'),
                $simulation->project_category,
                $simulation->status,
                $simulation->consent ? __('Yes', 'website-simulator-mvp') : __('No', 'website-simulator-mvp')
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX handler for getting simulations
     */
    public function ajax_get_simulations() {
        check_ajax_referer('wsmvp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'website-simulator-mvp')));
        }
        
        $simulations = $this->get_simulations($_POST);
        wp_send_json_success($simulations);
    }
}
