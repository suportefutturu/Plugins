<?php
/**
 * Pricing calculator class
 * 
 * Handles price estimation based on user responses
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WSMVP_Pricing {

    private static $instance = null;
    private $settings;
    private $pricing_rules;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->settings = WSMVP_Settings::get_instance()->get_settings();
        $this->pricing_rules = WSMVP_Settings::get_instance()->get_pricing_rules();
    }

    /**
     * Calculate estimated price based on responses
     */
    public function calculate($responses) {
        if (empty($responses)) {
            return array(
                'total' => 0,
                'min_value' => 0,
                'max_value' => 0,
                'category' => 'basic',
                'deadline_days' => 30,
                'complexity_score' => 0,
            );
        }

        $total = 0;
        $complexity_score = 0;

        // Get site type base value
        $site_type = isset($responses['site_type']) ? $responses['site_type'] : 'institutional';
        $base_value = isset($this->pricing_rules['base_values'][$site_type]) 
            ? $this->pricing_rules['base_values'][$site_type] 
            : 1500;
        $total += $base_value;
        $complexity_score += $base_value / 100;

        // Add objective value
        if (isset($responses['objective'])) {
            $objective_value = $this->get_option_value('objective', $responses['objective'], 'value_add');
            $total += $objective_value;
            $complexity_score += $objective_value / 100;
        }

        // Add pages values
        if (isset($responses['pages']) && is_array($responses['pages'])) {
            foreach ($responses['pages'] as $page) {
                $page_value = isset($this->pricing_rules['page_values'][$page]) 
                    ? $this->pricing_rules['page_values'][$page] 
                    : 0;
                $total += $page_value;
                $complexity_score += 1; // Each page adds 1 to complexity
            }
        }

        // Add features values
        if (isset($responses['features']) && is_array($responses['features'])) {
            foreach ($responses['features'] as $feature) {
                $feature_value = isset($this->pricing_rules['feature_values'][$feature]) 
                    ? $this->pricing_rules['feature_values'][$feature] 
                    : 0;
                $total += $feature_value;
                $complexity_score += $feature_value / 200;
            }
        }

        // Apply visual level multiplier
        $visual_level = isset($responses['visual_level']) ? $responses['visual_level'] : 'basic';
        $visual_multiplier = isset($this->pricing_rules['visual_multipliers'][$visual_level]) 
            ? $this->pricing_rules['visual_multipliers'][$visual_level] 
            : 1;
        $total *= $visual_multiplier;
        $complexity_score *= $visual_multiplier;

        // Add content creation value
        $content_status = isset($responses['content_status']) ? $responses['content_status'] : 'have_all';
        $content_value = isset($this->pricing_rules['content_values'][$content_status]) 
            ? $this->pricing_rules['content_values'][$content_status] 
            : 0;
        $total += $content_value;
        $complexity_score += $content_value / 100;

        // Apply deadline multiplier
        $deadline = isset($responses['deadline']) ? $responses['deadline'] : '30_days';
        $deadline_multiplier = isset($this->pricing_rules['deadline_multipliers'][$deadline]) 
            ? $this->pricing_rules['deadline_multipliers'][$deadline] 
            : 1;
        $total *= $deadline_multiplier;

        // Ensure minimum project value
        $min_value = isset($this->settings['min_project_value']) ? $this->settings['min_project_value'] : 500;
        if ($total < $min_value) {
            $total = $min_value;
        }

        // Round to nearest 10
        $total = round($total / 10) * 10;

        // Calculate min and max values with margin
        $margin_min = isset($this->settings['price_margin_min']) ? $this->settings['price_margin_min'] : 0.9;
        $margin_max = isset($this->settings['price_margin_max']) ? $this->settings['price_margin_max'] : 1.2;
        $min_value_calc = round($total * $margin_min);
        $max_value_calc = round($total * $margin_max);

        // Determine project category
        $category = $this->get_category($complexity_score);

        // Calculate deadline days
        $deadline_days = $this->get_deadline_days($deadline, $site_type);

        return array(
            'total' => $total,
            'min_value' => $min_value_calc,
            'max_value' => $max_value_calc,
            'category' => $category,
            'deadline_days' => $deadline_days,
            'complexity_score' => round($complexity_score),
            'breakdown' => array(
                'base_value' => $base_value,
                'pages_value' => $this->calculate_pages_value($responses),
                'features_value' => $this->calculate_features_value($responses),
                'content_value' => $content_value,
                'visual_multiplier' => $visual_multiplier,
                'deadline_multiplier' => $deadline_multiplier,
            ),
        );
    }

    /**
     * Get option value from questions
     */
    private function get_option_value($question_id, $option_value, $value_key = 'value_add') {
        $questions = WSMVP_Settings::get_instance()->get_questions();
        
        foreach ($questions as $question) {
            if ($question['id'] === $question_id) {
                foreach ($question['options'] as $option) {
                    if ($option['value'] === $option_value) {
                        return isset($option[$value_key]) ? $option[$value_key] : 0;
                    }
                }
            }
        }
        
        return 0;
    }

    /**
     * Calculate pages total value
     */
    private function calculate_pages_value($responses) {
        $total = 0;
        if (isset($responses['pages']) && is_array($responses['pages'])) {
            foreach ($responses['pages'] as $page) {
                $total += isset($this->pricing_rules['page_values'][$page]) 
                    ? $this->pricing_rules['page_values'][$page] 
                    : 0;
            }
        }
        return $total;
    }

    /**
     * Calculate features total value
     */
    private function calculate_features_value($responses) {
        $total = 0;
        if (isset($responses['features']) && is_array($responses['features'])) {
            foreach ($responses['features'] as $feature) {
                $total += isset($this->pricing_rules['feature_values'][$feature]) 
                    ? $this->pricing_rules['feature_values'][$feature] 
                    : 0;
            }
        }
        return $total;
    }

    /**
     * Get project category based on complexity score
     */
    private function get_category($score) {
        $scores = $this->pricing_rules['complexity_scores'];
        
        if ($score <= $scores['basic']['max']) {
            return 'basic';
        } elseif ($score <= $scores['intermediate']['max']) {
            return 'intermediate';
        } elseif ($score <= $scores['advanced']['max']) {
            return 'advanced';
        } else {
            return 'custom';
        }
    }

    /**
     * Get estimated deadline in days
     */
    private function get_deadline_days($deadline, $site_type) {
        $base_days = array(
            'landing_page' => 7,
            'institutional' => 15,
            'portfolio' => 12,
            'blog' => 18,
            'membership' => 25,
            'ecommerce' => 30,
        );
        
        $base = isset($base_days[$site_type]) ? $base_days[$site_type] : 20;
        
        $multipliers = array(
            'no_urgency' => 1.2,
            '30_days' => 1,
            '15_days' => 0.7,
            'priority' => 0.5,
        );
        
        $multiplier = isset($multipliers[$deadline]) ? $multipliers[$deadline] : 1;
        
        return round($base * $multiplier);
    }

    /**
     * Format price for display
     */
    public function format_price($value) {
        $currency_symbol = isset($this->settings['currency_symbol']) ? $this->settings['currency_symbol'] : 'R$';
        $currency = isset($this->settings['currency']) ? $this->settings['currency'] : 'BRL';
        
        if ($currency === 'BRL') {
            return $currency_symbol . ' ' . number_format($value, 2, ',', '.');
        }
        
        return $currency_symbol . ' ' . number_format($value, 2);
    }

    /**
     * Get category label
     */
    public function get_category_label($category) {
        $labels = array(
            'basic' => __('Projeto Básico', 'website-simulator-mvp'),
            'intermediate' => __('Projeto Intermediário', 'website-simulator-mvp'),
            'advanced' => __('Projeto Avançado', 'website-simulator-mvp'),
            'custom' => __('Projeto Sob Medida', 'website-simulator-mvp'),
        );
        
        return isset($labels[$category]) ? $labels[$category] : $category;
    }

    /**
     * Get site type label
     */
    public function get_site_type_label($type) {
        $labels = array(
            'institutional' => __('Site Institucional', 'website-simulator-mvp'),
            'landing_page' => __('Landing Page', 'website-simulator-mvp'),
            'ecommerce' => __('Loja Virtual', 'website-simulator-mvp'),
            'blog' => __('Blog', 'website-simulator-mvp'),
            'portfolio' => __('Portfólio', 'website-simulator-mvp'),
            'membership' => __('Área de Membros', 'website-simulator-mvp'),
        );
        
        return isset($labels[$type]) ? $labels[$type] : $type;
    }

    /**
     * Get objective label
     */
    public function get_objective_label($objective) {
        $labels = array(
            'present_company' => __('Apresentar uma empresa', 'website-simulator-mvp'),
            'generate_leads' => __('Gerar contatos', 'website-simulator-mvp'),
            'sell_products' => __('Vender produtos', 'website-simulator-mvp'),
            'promote_services' => __('Divulgar serviços', 'website-simulator-mvp'),
            'create_blog' => __('Criar um blog', 'website-simulator-mvp'),
            'landing_page' => __('Criar uma landing page', 'website-simulator-mvp'),
        );
        
        return isset($labels[$objective]) ? $labels[$objective] : $objective;
    }
}
