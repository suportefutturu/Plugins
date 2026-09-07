<?php
/**
 * Settings handler for Website Simulator MVP
 * 
 * Manages plugin settings and default values
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WSMVP_Settings {
    
    private $option_name = 'wsmvp_settings';
    private $questions_option = 'wsmvp_questions';
    private $pricing_option = 'wsmvp_pricing_rules';
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Set default options on activation
     */
    public function set_defaults() {
        $defaults = $this->get_default_settings();
        
        if (false === get_option($this->option_name)) {
            add_option($this->option_name, $defaults);
        }
        
        // Set default questions
        if (false === get_option($this->questions_option)) {
            add_option($this->questions_option, $this->get_default_questions());
        }
        
        // Set default pricing rules
        if (false === get_option($this->pricing_option)) {
            add_option($this->pricing_option, $this->get_default_pricing_rules());
        }
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wsmvp_settings_group', $this->option_name, array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
    }
    
    /**
     * Get current settings
     */
    public function get_settings() {
        return get_option($this->option_name, $this->get_default_settings());
    }
    
    /**
     * Update settings
     */
    public function update_settings($settings) {
        return update_option($this->option_name, $settings);
    }
    
    /**
     * Get questions
     */
    public function get_questions() {
        return get_option($this->questions_option, $this->get_default_questions());
    }
    
    /**
     * Update questions
     */
    public function update_questions($questions) {
        return update_option($this->questions_option, $questions);
    }
    
    /**
     * Get pricing rules
     */
    public function get_pricing_rules() {
        return get_option($this->pricing_option, $this->get_default_pricing_rules());
    }
    
    /**
     * Update pricing rules
     */
    public function update_pricing_rules($rules) {
        return update_option($this->pricing_option, $rules);
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['company_name'] = sanitize_text_field($input['company_name'] ?? '');
        $sanitized['logo_url'] = esc_url_raw($input['logo_url'] ?? '');
        $sanitized['primary_color'] = sanitize_hex_color($input['primary_color'] ?? '#3b82f6');
        $sanitized['secondary_color'] = sanitize_hex_color($input['secondary_color'] ?? '#1e40af');
        $sanitized['currency'] = sanitize_text_field($input['currency'] ?? 'BRL');
        $sanitized['currency_symbol'] = sanitize_text_field($input['currency_symbol'] ?? 'R$');
        $sanitized['min_project_value'] = floatval($input['min_project_value'] ?? 500);
        $sanitized['intro_text'] = sanitize_textarea_field($input['intro_text'] ?? '');
        $sanitized['contact_button_text'] = sanitize_text_field($input['contact_button_text'] ?? __('Get Quote', 'website-simulator-mvp'));
        $sanitized['lead_email'] = sanitize_email($input['lead_email'] ?? get_option('admin_email'));
        $sanitized['whatsapp_number'] = sanitize_text_field($input['whatsapp_number'] ?? '');
        $sanitized['default_deadline'] = intval($input['default_deadline'] ?? 30);
        $sanitized['terms_of_use'] = wp_kses_post($input['terms_of_use'] ?? '');
        $sanitized['privacy_notice'] = wp_kses_post($input['privacy_notice'] ?? '');
        $sanitized['enable_pdf'] = !empty($input['enable_pdf']);
        $sanitized['enable_email_capture'] = !empty($input['enable_email_capture']);
        $sanitized['show_public_price'] = !empty($input['show_public_price']);
        $sanitized['price_margin_min'] = floatval($input['price_margin_min'] ?? 0.1);
        $sanitized['price_margin_max'] = floatval($input['price_margin_max'] ?? 0.3);
        $sanitized['round_prices'] = !empty($input['round_prices']);
        
        return $sanitized;
    }
    
    /**
     * Get default settings
     */
    private function get_default_settings() {
        return array(
            'company_name' => get_bloginfo('name'),
            'logo_url' => '',
            'primary_color' => '#3b82f6',
            'secondary_color' => '#1e40af',
            'currency' => 'BRL',
            'currency_symbol' => 'R$',
            'min_project_value' => 500,
            'intro_text' => __('Discover the perfect solution for your online presence. Answer a few questions and get an instant estimate for your website project.', 'website-simulator-mvp'),
            'contact_button_text' => __('Get Quote', 'website-simulator-mvp'),
            'lead_email' => get_option('admin_email'),
            'whatsapp_number' => '',
            'default_deadline' => 30,
            'terms_of_use' => '',
            'privacy_notice' => '',
            'enable_pdf' => true,
            'enable_email_capture' => true,
            'show_public_price' => true,
            'price_margin_min' => 0.1,
            'price_margin_max' => 0.3,
            'round_prices' => true,
            'remove_data_on_uninstall' => false
        );
    }
    
    /**
     * Get default questions
     */
    private function get_default_questions() {
        return array(
            array(
                'id' => 'objective',
                'title' => __('What is the main objective of your website?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'select',
                'required' => true,
                'order' => 1,
                'step' => 1,
                'options' => array(
                    array('value' => 'present_company', 'label' => __('Present a company', 'website-simulator-mvp')),
                    array('value' => 'generate_leads', 'label' => __('Generate contacts', 'website-simulator-mvp')),
                    array('value' => 'sell_products', 'label' => __('Sell products', 'website-simulator-mvp')),
                    array('value' => 'promote_services', 'label' => __('Promote services', 'website-simulator-mvp')),
                    array('value' => 'create_blog', 'label' => __('Create a blog', 'website-simulator-mvp')),
                    array('value' => 'landing_page', 'label' => __('Create a landing page', 'website-simulator-mvp'))
                )
            ),
            array(
                'id' => 'site_type',
                'title' => __('What type of website do you need?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'select',
                'required' => true,
                'order' => 2,
                'step' => 2,
                'options' => array(
                    array('value' => 'institutional', 'label' => __('Institutional website', 'website-simulator-mvp')),
                    array('value' => 'landing_page', 'label' => __('Landing page', 'website-simulator-mvp')),
                    array('value' => 'ecommerce', 'label' => __('Online store', 'website-simulator-mvp')),
                    array('value' => 'blog', 'label' => __('Blog', 'website-simulator-mvp')),
                    array('value' => 'portfolio', 'label' => __('Portfolio', 'website-simulator-mvp')),
                    array('value' => 'membership', 'label' => __('Membership area', 'website-simulator-mvp'))
                )
            ),
            array(
                'id' => 'pages',
                'title' => __('Which pages do you want to include?', 'website-simulator-mvp'),
                'description' => __('Select all that apply', 'website-simulator-mvp'),
                'type' => 'checkbox',
                'required' => true,
                'order' => 3,
                'step' => 3,
                'options' => array(
                    array('value' => 'home', 'label' => __('Home page', 'website-simulator-mvp')),
                    array('value' => 'about', 'label' => __('About', 'website-simulator-mvp')),
                    array('value' => 'services', 'label' => __('Services', 'website-simulator-mvp')),
                    array('value' => 'products', 'label' => __('Products', 'website-simulator-mvp')),
                    array('value' => 'blog', 'label' => __('Blog', 'website-simulator-mvp')),
                    array('value' => 'portfolio', 'label' => __('Portfolio', 'website-simulator-mvp')),
                    array('value' => 'testimonials', 'label' => __('Testimonials', 'website-simulator-mvp')),
                    array('value' => 'faq', 'label' => __('FAQ', 'website-simulator-mvp')),
                    array('value' => 'contact', 'label' => __('Contact', 'website-simulator-mvp')),
                    array('value' => 'privacy', 'label' => __('Privacy policy', 'website-simulator-mvp'))
                )
            ),
            array(
                'id' => 'features',
                'title' => __('Which features do you want?', 'website-simulator-mvp'),
                'description' => __('Select all that apply', 'website-simulator-mvp'),
                'type' => 'checkbox',
                'required' => false,
                'order' => 4,
                'step' => 4,
                'options' => array(
                    array('value' => 'contact_form', 'label' => __('Contact form', 'website-simulator-mvp')),
                    array('value' => 'whatsapp_button', 'label' => __('WhatsApp button', 'website-simulator-mvp')),
                    array('value' => 'instagram_integration', 'label' => __('Instagram integration', 'website-simulator-mvp')),
                    array('value' => 'google_maps', 'label' => __('Google Maps', 'website-simulator-mvp')),
                    array('value' => 'blog_module', 'label' => __('Blog', 'website-simulator-mvp')),
                    array('value' => 'newsletter', 'label' => __('Newsletter', 'website-simulator-mvp')),
                    array('value' => 'scheduling', 'label' => __('Scheduling', 'website-simulator-mvp')),
                    array('value' => 'membership_area', 'label' => __('Membership area', 'website-simulator-mvp')),
                    array('value' => 'online_payment', 'label' => __('Online payment', 'website-simulator-mvp')),
                    array('value' => 'crm_integration', 'label' => __('CRM integration', 'website-simulator-mvp')),
                    array('value' => 'basic_seo', 'label' => __('Basic SEO', 'website-simulator-mvp')),
                    array('value' => 'mobile_optimization', 'label' => __('Mobile optimization', 'website-simulator-mvp'))
                )
            ),
            array(
                'id' => 'visual_customization',
                'title' => __('What level of visual customization do you want?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'radio',
                'required' => true,
                'order' => 5,
                'step' => 5,
                'options' => array(
                    array('value' => 'basic_template', 'label' => __('Basic template', 'website-simulator-mvp')),
                    array('value' => 'colors_texts', 'label' => __('Colors and texts customization', 'website-simulator-mvp')),
                    array('value' => 'custom_design', 'label' => __('Custom design', 'website-simulator-mvp')),
                    array('value' => 'fully_custom', 'label' => __('Fully custom design', 'website-simulator-mvp'))
                )
            ),
            array(
                'id' => 'content_status',
                'title' => __('Do you already have the content?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'radio',
                'required' => true,
                'order' => 6,
                'step' => 6,
                'options' => array(
                    array('value' => 'have_all', 'label' => __('Yes, I have everything', 'website-simulator-mvp')),
                    array('value' => 'have_partial', 'label' => __('I have part of the content', 'website-simulator-mvp')),
                    array('value' => 'need_texts', 'label' => __('I need help with texts', 'website-simulator-mvp')),
                    array('value' => 'need_everything', 'label' => __('I need texts and images', 'website-simulator-mvp'))
                )
            ),
            array(
                'id' => 'deadline',
                'title' => __('What is your desired deadline?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'radio',
                'required' => true,
                'order' => 7,
                'step' => 6,
                'options' => array(
                    array('value' => 'no_urgency', 'label' => __('No urgency', 'website-simulator-mvp')),
                    array('value' => '30_days', 'label' => __('Up to 30 days', 'website-simulator-mvp')),
                    array('value' => '15_days', 'label' => __('Up to 15 days', 'website-simulator-mvp')),
                    array('value' => 'priority', 'label' => __('I need priority', 'website-simulator-mvp'))
                )
            ),
            array(
                'id' => 'branding',
                'title' => __('Visual Customization', 'website-simulator-mvp'),
                'description' => __('Customize the look of your future website', 'website-simulator-mvp'),
                'type' => 'customization',
                'required' => false,
                'order' => 8,
                'step' => 7,
                'options' => array()
            )
        );
    }
    
    /**
     * Get default pricing rules
     */
    private function get_default_pricing_rules() {
        return array(
            // Site types - base values
            array(
                'rule_key' => 'site_type',
                'rule_type' => 'base',
                'option_key' => 'institutional',
                'option_label' => __('Institutional website', 'website-simulator-mvp'),
                'additional_value' => 1500.00,
                'complexity_score' => 3,
                'category' => 'site_type',
                'is_active' => 1,
                'display_order' => 1
            ),
            array(
                'rule_key' => 'site_type',
                'rule_type' => 'base',
                'option_key' => 'landing_page',
                'option_label' => __('Landing page', 'website-simulator-mvp'),
                'additional_value' => 900.00,
                'complexity_score' => 2,
                'category' => 'site_type',
                'is_active' => 1,
                'display_order' => 2
            ),
            array(
                'rule_key' => 'site_type',
                'rule_type' => 'base',
                'option_key' => 'ecommerce',
                'option_label' => __('Online store', 'website-simulator-mvp'),
                'additional_value' => 3500.00,
                'complexity_score' => 5,
                'category' => 'site_type',
                'is_active' => 1,
                'display_order' => 3
            ),
            array(
                'rule_key' => 'site_type',
                'rule_type' => 'base',
                'option_key' => 'blog',
                'option_label' => __('Blog', 'website-simulator-mvp'),
                'additional_value' => 1200.00,
                'complexity_score' => 2,
                'category' => 'site_type',
                'is_active' => 1,
                'display_order' => 4
            ),
            array(
                'rule_key' => 'site_type',
                'rule_type' => 'base',
                'option_key' => 'portfolio',
                'option_label' => __('Portfolio', 'website-simulator-mvp'),
                'additional_value' => 1000.00,
                'complexity_score' => 2,
                'category' => 'site_type',
                'is_active' => 1,
                'display_order' => 5
            ),
            array(
                'rule_key' => 'site_type',
                'rule_type' => 'base',
                'option_key' => 'membership',
                'option_label' => __('Membership area', 'website-simulator-mvp'),
                'additional_value' => 2500.00,
                'complexity_score' => 4,
                'category' => 'site_type',
                'is_active' => 1,
                'display_order' => 6
            ),
            
            // Pages - additional values per page
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'home',
                'option_label' => __('Home page', 'website-simulator-mvp'),
                'additional_value' => 0.00,
                'complexity_score' => 0,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 1
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'about',
                'option_label' => __('About', 'website-simulator-mvp'),
                'additional_value' => 150.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 2
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'services',
                'option_label' => __('Services', 'website-simulator-mvp'),
                'additional_value' => 200.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 3
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'products',
                'option_label' => __('Products', 'website-simulator-mvp'),
                'additional_value' => 300.00,
                'complexity_score' => 2,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 4
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'blog',
                'option_label' => __('Blog', 'website-simulator-mvp'),
                'additional_value' => 250.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 5
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'portfolio',
                'option_label' => __('Portfolio', 'website-simulator-mvp'),
                'additional_value' => 200.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 6
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'testimonials',
                'option_label' => __('Testimonials', 'website-simulator-mvp'),
                'additional_value' => 100.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 7
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'faq',
                'option_label' => __('FAQ', 'website-simulator-mvp'),
                'additional_value' => 100.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 8
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'contact',
                'option_label' => __('Contact', 'website-simulator-mvp'),
                'additional_value' => 100.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 9
            ),
            array(
                'rule_key' => 'page',
                'rule_type' => 'additional',
                'option_key' => 'privacy',
                'option_label' => __('Privacy policy', 'website-simulator-mvp'),
                'additional_value' => 100.00,
                'complexity_score' => 1,
                'category' => 'pages',
                'is_active' => 1,
                'display_order' => 10
            ),
            
            // Features - additional values
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'contact_form',
                'option_label' => __('Contact form', 'website-simulator-mvp'),
                'additional_value' => 150.00,
                'complexity_score' => 1,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 1
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'whatsapp_button',
                'option_label' => __('WhatsApp button', 'website-simulator-mvp'),
                'additional_value' => 50.00,
                'complexity_score' => 1,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 2
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'instagram_integration',
                'option_label' => __('Instagram integration', 'website-simulator-mvp'),
                'additional_value' => 200.00,
                'complexity_score' => 2,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 3
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'google_maps',
                'option_label' => __('Google Maps', 'website-simulator-mvp'),
                'additional_value' => 100.00,
                'complexity_score' => 1,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 4
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'newsletter',
                'option_label' => __('Newsletter', 'website-simulator-mvp'),
                'additional_value' => 200.00,
                'complexity_score' => 2,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 5
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'scheduling',
                'option_label' => __('Scheduling', 'website-simulator-mvp'),
                'additional_value' => 400.00,
                'complexity_score' => 3,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 6
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'membership_area',
                'option_label' => __('Membership area', 'website-simulator-mvp'),
                'additional_value' => 2000.00,
                'complexity_score' => 4,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 7
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'online_payment',
                'option_label' => __('Online payment', 'website-simulator-mvp'),
                'additional_value' => 800.00,
                'complexity_score' => 3,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 8
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'crm_integration',
                'option_label' => __('CRM integration', 'website-simulator-mvp'),
                'additional_value' => 600.00,
                'complexity_score' => 3,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 9
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'basic_seo',
                'option_label' => __('Basic SEO', 'website-simulator-mvp'),
                'additional_value' => 300.00,
                'complexity_score' => 2,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 10
            ),
            array(
                'rule_key' => 'feature',
                'rule_type' => 'additional',
                'option_key' => 'mobile_optimization',
                'option_label' => __('Mobile optimization', 'website-simulator-mvp'),
                'additional_value' => 200.00,
                'complexity_score' => 2,
                'category' => 'features',
                'is_active' => 1,
                'display_order' => 11
            ),
            
            // Visual customization levels
            array(
                'rule_key' => 'visual_customization',
                'rule_type' => 'multiplier',
                'option_key' => 'basic_template',
                'option_label' => __('Basic template', 'website-simulator-mvp'),
                'additional_value' => 0.00,
                'complexity_score' => 1,
                'category' => 'customization',
                'is_active' => 1,
                'display_order' => 1
            ),
            array(
                'rule_key' => 'visual_customization',
                'rule_type' => 'multiplier',
                'option_key' => 'colors_texts',
                'option_label' => __('Colors and texts customization', 'website-simulator-mvp'),
                'additional_value' => 500.00,
                'complexity_score' => 2,
                'category' => 'customization',
                'is_active' => 1,
                'display_order' => 2
            ),
            array(
                'rule_key' => 'visual_customization',
                'rule_type' => 'multiplier',
                'option_key' => 'custom_design',
                'option_label' => __('Custom design', 'website-simulator-mvp'),
                'additional_value' => 1200.00,
                'complexity_score' => 3,
                'category' => 'customization',
                'is_active' => 1,
                'display_order' => 3
            ),
            array(
                'rule_key' => 'visual_customization',
                'rule_type' => 'multiplier',
                'option_key' => 'fully_custom',
                'option_label' => __('Fully custom design', 'website-simulator-mvp'),
                'additional_value' => 2500.00,
                'complexity_score' => 5,
                'category' => 'customization',
                'is_active' => 1,
                'display_order' => 4
            ),
            
            // Content status
            array(
                'rule_key' => 'content_status',
                'rule_type' => 'additional',
                'option_key' => 'have_all',
                'option_label' => __('Yes, I have everything', 'website-simulator-mvp'),
                'additional_value' => 0.00,
                'complexity_score' => 0,
                'category' => 'content',
                'is_active' => 1,
                'display_order' => 1
            ),
            array(
                'rule_key' => 'content_status',
                'rule_type' => 'additional',
                'option_key' => 'have_partial',
                'option_label' => __('I have part of the content', 'website-simulator-mvp'),
                'additional_value' => 300.00,
                'complexity_score' => 1,
                'category' => 'content',
                'is_active' => 1,
                'display_order' => 2
            ),
            array(
                'rule_key' => 'content_status',
                'rule_type' => 'additional',
                'option_key' => 'need_texts',
                'option_label' => __('I need help with texts', 'website-simulator-mvp'),
                'additional_value' => 600.00,
                'complexity_score' => 2,
                'category' => 'content',
                'is_active' => 1,
                'display_order' => 3
            ),
            array(
                'rule_key' => 'content_status',
                'rule_type' => 'additional',
                'option_key' => 'need_everything',
                'option_label' => __('I need texts and images', 'website-simulator-mvp'),
                'additional_value' => 1200.00,
                'complexity_score' => 3,
                'category' => 'content',
                'is_active' => 1,
                'display_order' => 4
            ),
            
            // Deadline urgency
            array(
                'rule_key' => 'deadline',
                'rule_type' => 'percentage',
                'option_key' => 'no_urgency',
                'option_label' => __('No urgency', 'website-simulator-mvp'),
                'additional_value' => 0.00,
                'complexity_score' => 0,
                'category' => 'deadline',
                'is_active' => 1,
                'display_order' => 1
            ),
            array(
                'rule_key' => 'deadline',
                'rule_type' => 'percentage',
                'option_key' => '30_days',
                'option_label' => __('Up to 30 days', 'website-simulator-mvp'),
                'additional_value' => 0.00,
                'complexity_score' => 1,
                'category' => 'deadline',
                'is_active' => 1,
                'display_order' => 2
            ),
            array(
                'rule_key' => 'deadline',
                'rule_type' => 'percentage',
                'option_key' => '15_days',
                'option_label' => __('Up to 15 days', 'website-simulator-mvp'),
                'additional_value' => 0.15,
                'complexity_score' => 2,
                'category' => 'deadline',
                'is_active' => 1,
                'display_order' => 3
            ),
            array(
                'rule_key' => 'deadline',
                'rule_type' => 'percentage',
                'option_key' => 'priority',
                'option_label' => __('I need priority', 'website-simulator-mvp'),
                'additional_value' => 0.25,
                'complexity_score' => 3,
                'category' => 'deadline',
                'is_active' => 1,
                'display_order' => 4
            )
        );
    }
}
