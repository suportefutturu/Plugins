<?php
/**
 * Database handler class
 * 
 * Handles database table creation and simulation storage
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WSMVP_Database {

    private static $instance = null;
    private $table_name;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wsmvp_simulations';
    }

    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wsmvp_simulations';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            name varchar(255) NOT NULL,
            company varchar(255) DEFAULT '',
            email varchar(255) NOT NULL,
            whatsapp varchar(50) DEFAULT '',
            responses longtext NOT NULL,
            estimated_value decimal(10,2) DEFAULT 0.00,
            project_category varchar(50) DEFAULT 'basic',
            status varchar(50) DEFAULT 'new',
            notes text DEFAULT '',
            consent tinyint(1) DEFAULT 0,
            ip_address varchar(45) DEFAULT '',
            user_agent varchar(255) DEFAULT '',
            PRIMARY KEY (id),
            KEY email (email),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Update version
        update_option('wsmvp_db_version', WSMVP_VERSION);
    }

    /**
     * Save simulation
     */
    public function save_simulation($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'company' => sanitize_text_field($data['company']),
                'email' => sanitize_email($data['email']),
                'whatsapp' => sanitize_text_field($data['whatsapp']),
                'responses' => wp_json_encode($data['responses']),
                'estimated_value' => floatval($data['estimated_value']),
                'project_category' => sanitize_text_field($data['project_category']),
                'notes' => sanitize_textarea_field($data['notes']),
                'consent' => !empty($data['consent']) ? 1 : 0,
                'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
                'user_agent' => sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)),
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%f',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
            )
        );
        
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
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($result && isset($result['responses'])) {
            $result['responses'] = json_decode($result['responses'], true);
        }
        
        return $result;
    }

    /**
     * Get all simulations with filters
     */
    public function get_simulations($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => '',
            'search' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $params = array();
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $params[] = $args['status'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(name LIKE %s OR email LIKE %s OR company LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE {$where_clause} 
                  ORDER BY {$args['orderby']} {$args['order']} 
                  LIMIT %d OFFSET %d";
        
        $params[] = $args['limit'];
        $params[] = $args['offset'];
        
        $prepared = $wpdb->prepare($query, $params);
        $results = $wpdb->get_results($prepared, ARRAY_A);
        
        if ($results) {
            foreach ($results as &$row) {
                if (isset($row['responses'])) {
                    $row['responses'] = json_decode($row['responses'], true);
                }
            }
        }
        
        return $results;
    }

    /**
     * Update simulation status
     */
    public function update_status($id, $status) {
        global $wpdb;
        
        $valid_statuses = array('new', 'contacted', 'proposal_sent', 'converted', 'archived');
        
        if (!in_array($status, $valid_statuses)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * Get simulation count
     */
    public function get_count($status = '') {
        global $wpdb;
        
        if (!empty($status)) {
            $where = $wpdb->prepare('WHERE status = %s', $status);
        } else {
            $where = '';
        }
        
        $query = "SELECT COUNT(*) FROM {$this->table_name} {$where}";
        return (int) $wpdb->get_var($query);
    }

    /**
     * Get total estimated value
     */
    public function get_total_value() {
        global $wpdb;
        
        $query = "SELECT SUM(estimated_value) FROM {$this->table_name}";
        return (float) $wpdb->get_var($query);
    }

    /**
     * Get average estimated value
     */
    public function get_average_value() {
        global $wpdb;
        
        $query = "SELECT AVG(estimated_value) FROM {$this->table_name}";
        return (float) $wpdb->get_var($query);
    }

    /**
     * Delete simulation
     */
    public function delete_simulation($id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }

    /**
     * Export simulations to CSV
     */
    public function export_csv() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=wsmvp-simulations-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, array(
            __('ID', 'website-simulator-mvp'),
            __('Data', 'website-simulator-mvp'),
            __('Nome', 'website-simulator-mvp'),
            __('Empresa', 'website-simulator-mvp'),
            __('Email', 'website-simulator-mvp'),
            __('WhatsApp', 'website-simulator-mvp'),
            __('Valor Estimado', 'website-simulator-mvp'),
            __('Categoria', 'website-simulator-mvp'),
            __('Status', 'website-simulator-mvp'),
        ));
        
        $simulations = $this->get_simulations(array('limit' => 1000));
        
        foreach ($simulations as $sim) {
            fputcsv($output, array(
                $sim['id'],
                $sim['created_at'],
                $sim['name'],
                $sim['company'],
                $sim['email'],
                $sim['whatsapp'],
                number_format($sim['estimated_value'], 2, ',', '.'),
                $sim['project_category'],
                $sim['status'],
            ));
        }
        
        fclose($output);
        exit;
    }
}
