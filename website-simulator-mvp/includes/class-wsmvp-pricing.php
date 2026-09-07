<?php
/**
 * Pricing calculator for Website Simulator MVP
 * 
 * Calculates project estimates based on user selections
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WSMVP_Pricing {
    
    private $settings;
    
    public function __construct() {
        $this->settings = new WSMVP_Settings();
        
        // Register AJAX handlers
        add_action('wp_ajax_wsmvp_calculate_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_nopriv_wsmvp_calculate_price', array($this, 'ajax_calculate_price'));
    }
    
    /**
     * Calculate project price based on answers
     */
    public function calculate($answers) {
        $pricing_rules = $this->settings->get_pricing_rules();
        $settings = $this->settings->get_settings();
        
        $base_value = 0;
        $additional_value = 0;
        $complexity_score = 0;
        $percentage_multiplier = 0;
        $customization_value = 0;
        
        // Process each answer
        foreach ($answers as $question_id => $answer) {
            if (empty($answer)) {
                continue;
            }
            
            // Handle different question types
            $answer_values = is_array($answer) ? $answer : array($answer);
            
            foreach ($answer_values as $answer_value) {
                if (empty($answer_value)) {
                    continue;
                }
                
                // Find matching pricing rules
                foreach ($pricing_rules as $rule) {
                    if (!$rule['is_active']) {
                        continue;
                    }
                    
                    $match = false;
                    
                    // Match by rule key and option
                    if ($rule['rule_key'] === $question_id && $rule['option_key'] === $answer_value) {
                        $match = true;
                    }
                    
                    if (!$match) {
                        continue;
                    }
                    
                    $rule_value = floatval($rule['additional_value']);
                    $rule_complexity = intval($rule['complexity_score']);
                    
                    // Apply rule based on type
                    switch ($rule['rule_type']) {
                        case 'base':
                            $base_value = max($base_value, $rule_value);
                            $complexity_score += $rule_complexity;
                            break;
                            
                        case 'additional':
                            $additional_value += $rule_value;
                            $complexity_score += $rule_complexity;
                            break;
                            
                        case 'multiplier':
                            $customization_value += $rule_value;
                            $complexity_score += $rule_complexity;
                            break;
                            
                        case 'percentage':
                            $percentage_multiplier = max($percentage_multiplier, $rule_value);
                            $complexity_score += $rule_complexity;
                            break;
                    }
                }
            }
        }
        
        // Calculate subtotal
        $subtotal = $base_value + $additional_value + $customization_value;
        
        // Apply percentage multiplier (urgency, etc.)
        if ($percentage_multiplier > 0) {
            $subtotal += ($subtotal * $percentage_multiplier);
        }
        
        // Apply minimum value
        $min_value = floatval($settings['min_project_value']);
        $total = max($subtotal, $min_value);
        
        // Round if enabled
        if (!empty($settings['round_prices'])) {
            $total = round($total / 100) * 100;
        }
        
        // Determine project category
        $category = $this->determine_category($complexity_score, $total);
        
        // Calculate deadline estimate
        $deadline = $this->calculate_deadline($answers, $settings);
        
        // Calculate price range
        $margin_min = floatval($settings['price_margin_min']);
        $margin_max = floatval($settings['price_margin_max']);
        
        $price_min = $total * (1 - $margin_min);
        $price_max = $total * (1 + $margin_max);
        
        return array(
            'base_value' => round($base_value, 2),
            'additional_value' => round($additional_value, 2),
            'customization_value' => round($customization_value, 2),
            'subtotal' => round($subtotal, 2),
            'total' => round($total, 2),
            'price_min' => round($price_min, 2),
            'price_max' => round($price_max, 2),
            'complexity_score' => $complexity_score,
            'category' => $category,
            'deadline_days' => $deadline,
            'currency' => $settings['currency'],
            'currency_symbol' => $settings['currency_symbol']
        );
    }
    
    /**
     * Determine project category based on complexity and value
     */
    private function determine_category($complexity_score, $total_value) {
        // Simple categorization logic
        if ($complexity_score <= 5 || $total_value < 1500) {
            return 'basic';
        } elseif ($complexity_score <= 12 || $total_value < 4000) {
            return 'intermediate';
        } elseif ($complexity_score <= 20 || $total_value < 8000) {
            return 'advanced';
        } else {
            return 'custom';
        }
    }
    
    /**
     * Calculate estimated deadline
     */
    private function calculate_deadline($answers, $settings) {
        $base_deadline = intval($settings['default_deadline']);
        
        // Adjust based on site type
        if (isset($answers['site_type'])) {
            switch ($answers['site_type']) {
                case 'landing_page':
                    $base_deadline = min($base_deadline, 10);
                    break;
                case 'ecommerce':
                    $base_deadline = max($base_deadline, 45);
                    break;
                case 'membership':
                    $base_deadline = max($base_deadline, 40);
                    break;
            }
        }
        
        // Adjust based on content status
        if (isset($answers['content_status'])) {
            switch ($answers['content_status']) {
                case 'have_all':
                    $base_deadline = intval($base_deadline * 0.8);
                    break;
                case 'need_everything':
                    $base_deadline = intval($base_deadline * 1.3);
                    break;
            }
        }
        
        // Adjust based on urgency
        if (isset($answers['deadline'])) {
            switch ($answers['deadline']) {
                case 'priority':
                    $base_deadline = intval($base_deadline * 0.5);
                    break;
                case '15_days':
                    $base_deadline = min($base_deadline, 15);
                    break;
                case '30_days':
                    $base_deadline = min($base_deadline, 30);
                    break;
            }
        }
        
        return max(5, $base_deadline); // Minimum 5 days
    }
    
    /**
     * Format price for display
     */
    public function format_price($value, $currency_symbol = 'R$') {
        return $currency_symbol . ' ' . number_format($value, 2, ',', '.');
    }
    
    /**
     * Get category label
     */
    public function get_category_label($category) {
        $labels = array(
            'basic' => __('Basic Project', 'website-simulator-mvp'),
            'intermediate' => __('Intermediate Project', 'website-simulator-mvp'),
            'advanced' => __('Advanced Project', 'website-simulator-mvp'),
            'custom' => __('Custom Project', 'website-simulator-mvp')
        );
        
        return $labels[$category] ?? $category;
    }
    
    /**
     * AJAX handler for price calculation
     */
    public function ajax_calculate_price() {
        check_ajax_referer('wsmvp_frontend_nonce', 'nonce');
        
        $answers = isset($_POST['answers']) ? $_POST['answers'] : array();
        
        // Sanitize answers
        $sanitized_answers = array();
        foreach ($answers as $key => $value) {
            if (is_array($value)) {
                $sanitized_answers[$key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized_answers[$key] = sanitize_text_field($value);
            }
        }
        
        $result = $this->calculate($sanitized_answers);
        
        wp_send_json_success($result);
    }
}
