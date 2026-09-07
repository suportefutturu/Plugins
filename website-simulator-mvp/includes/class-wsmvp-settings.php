<?php
/**
 * Settings handler class
 * 
 * Handles plugin settings, default questions and pricing rules
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WSMVP_Settings {

    private static $instance = null;
    private $settings_key = 'wsmvp_settings';
    private $questions_key = 'wsmvp_questions';
    private $pricing_key = 'wsmvp_pricing_rules';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set default options on activation
     */
    public static function set_defaults() {
        // Check if already set
        if (get_option('wsmvp_version')) {
            return;
        }

        // Default settings
        $defaults = array(
            'company_name' => get_bloginfo('name'),
            'company_logo' => '',
            'primary_color' => '#2563eb',
            'secondary_color' => '#1e40af',
            'button_color' => '#16a34a',
            'currency' => 'BRL',
            'currency_symbol' => 'R$',
            'min_project_value' => 500,
            'intro_text' => __('Descubra o site perfeito para o seu negócio! Responda algumas perguntas e receba uma estimativa personalizada.', 'website-simulator-mvp'),
            'contact_button_text' => __('Solicitar Orçamento', 'website-simulator-mvp'),
            'admin_email' => get_option('admin_email'),
            'whatsapp_number' => '',
            'default_deadline' => 30,
            'terms_text' => __('Ao enviar este formulário, você concorda com nossos termos de uso e política de privacidade.', 'website-simulator-mvp'),
            'privacy_notice' => __('Seus dados estão seguros. Não compartilhamos suas informações com terceiros.', 'website-simulator-mvp'),
            'enable_pdf' => true,
            'enable_email_capture' => true,
            'show_public_price' => true,
            'price_margin_min' => 0.9,
            'price_margin_max' => 1.2,
        );
        update_option('wsmvp_settings', $defaults);

        // Default questions
        $questions = self::get_default_questions();
        update_option('wsmvp_questions', $questions);

        // Default pricing rules
        $pricing = self::get_default_pricing_rules();
        update_option('wsmvp_pricing_rules', $pricing);

        // Set version
        update_option('wsmvp_version', WSMVP_VERSION);
    }

    /**
     * Get default questions
     */
    public static function get_default_questions() {
        return array(
            array(
                'id' => 'objective',
                'title' => __('Qual é o objetivo principal do seu site?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'select',
                'required' => true,
                'order' => 1,
                'active' => true,
                'options' => array(
                    array('value' => 'present_company', 'text' => __('Apresentar uma empresa', 'website-simulator-mvp'), 'value_add' => 0),
                    array('value' => 'generate_leads', 'text' => __('Gerar contatos', 'website-simulator-mvp'), 'value_add' => 200),
                    array('value' => 'sell_products', 'text' => __('Vender produtos', 'website-simulator-mvp'), 'value_add' => 1500),
                    array('value' => 'promote_services', 'text' => __('Divulgar serviços', 'website-simulator-mvp'), 'value_add' => 300),
                    array('value' => 'create_blog', 'text' => __('Criar um blog', 'website-simulator-mvp'), 'value_add' => 400),
                    array('value' => 'landing_page', 'text' => __('Criar uma landing page', 'website-simulator-mvp'), 'value_add' => -500),
                ),
            ),
            array(
                'id' => 'site_type',
                'title' => __('Que tipo de site você precisa?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'select',
                'required' => true,
                'order' => 2,
                'active' => true,
                'options' => array(
                    array('value' => 'institutional', 'text' => __('Site institucional', 'website-simulator-mvp'), 'base_value' => 1500),
                    array('value' => 'landing_page', 'text' => __('Landing page', 'website-simulator-mvp'), 'base_value' => 900),
                    array('value' => 'ecommerce', 'text' => __('Loja virtual', 'website-simulator-mvp'), 'base_value' => 3500),
                    array('value' => 'blog', 'text' => __('Blog', 'website-simulator-mvp'), 'base_value' => 1200),
                    array('value' => 'portfolio', 'text' => __('Portfólio', 'website-simulator-mvp'), 'base_value' => 1000),
                    array('value' => 'membership', 'text' => __('Área de membros', 'website-simulator-mvp'), 'base_value' => 2500),
                ),
            ),
            array(
                'id' => 'pages',
                'title' => __('Quais páginas deseja incluir?', 'website-simulator-mvp'),
                'description' => __('Selecione todas as páginas que você precisa.', 'website-simulator-mvp'),
                'type' => 'checkbox',
                'required' => true,
                'order' => 3,
                'active' => true,
                'options' => array(
                    array('value' => 'home', 'text' => __('Página inicial', 'website-simulator-mvp'), 'value_add' => 0),
                    array('value' => 'about', 'text' => __('Sobre', 'website-simulator-mvp'), 'value_add' => 150),
                    array('value' => 'services', 'text' => __('Serviços', 'website-simulator-mvp'), 'value_add' => 200),
                    array('value' => 'products', 'text' => __('Produtos', 'website-simulator-mvp'), 'value_add' => 250),
                    array('value' => 'blog', 'text' => __('Blog', 'website-simulator-mvp'), 'value_add' => 300),
                    array('value' => 'portfolio', 'text' => __('Portfólio', 'website-simulator-mvp'), 'value_add' => 200),
                    array('value' => 'testimonials', 'text' => __('Depoimentos', 'website-simulator-mvp'), 'value_add' => 150),
                    array('value' => 'faq', 'text' => __('FAQ', 'website-simulator-mvp'), 'value_add' => 100),
                    array('value' => 'contact', 'text' => __('Contato', 'website-simulator-mvp'), 'value_add' => 100),
                    array('value' => 'privacy', 'text' => __('Política de privacidade', 'website-simulator-mvp'), 'value_add' => 80),
                ),
            ),
            array(
                'id' => 'features',
                'title' => __('Quais funcionalidades deseja?', 'website-simulator-mvp'),
                'description' => __('Selecione os recursos que você precisa.', 'website-simulator-mvp'),
                'type' => 'checkbox',
                'required' => false,
                'order' => 4,
                'active' => true,
                'options' => array(
                    array('value' => 'contact_form', 'text' => __('Formulário de contato', 'website-simulator-mvp'), 'value_add' => 150),
                    array('value' => 'whatsapp_button', 'text' => __('Botão de WhatsApp', 'website-simulator-mvp'), 'value_add' => 100),
                    array('value' => 'instagram_integration', 'text' => __('Integração com Instagram', 'website-simulator-mvp'), 'value_add' => 200),
                    array('value' => 'google_maps', 'text' => __('Google Maps', 'website-simulator-mvp'), 'value_add' => 100),
                    array('value' => 'blog_module', 'text' => __('Blog', 'website-simulator-mvp'), 'value_add' => 400),
                    array('value' => 'newsletter', 'text' => __('Newsletter', 'website-simulator-mvp'), 'value_add' => 250),
                    array('value' => 'scheduling', 'text' => __('Agendamento', 'website-simulator-mvp'), 'value_add' => 500),
                    array('value' => 'membership_area', 'text' => __('Área de membros', 'website-simulator-mvp'), 'value_add' => 2000),
                    array('value' => 'online_payment', 'text' => __('Pagamento online', 'website-simulator-mvp'), 'value_add' => 800),
                    array('value' => 'crm_integration', 'text' => __('Integração com CRM', 'website-simulator-mvp'), 'value_add' => 600),
                    array('value' => 'seo_basic', 'text' => __('SEO básico', 'website-simulator-mvp'), 'value_add' => 300),
                    array('value' => 'mobile_optimization', 'text' => __('Otimização para celular', 'website-simulator-mvp'), 'value_add' => 200),
                ),
            ),
            array(
                'id' => 'visual_level',
                'title' => __('Qual nível de personalização visual deseja?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'radio',
                'required' => true,
                'order' => 5,
                'active' => true,
                'options' => array(
                    array('value' => 'basic', 'text' => __('Modelo básico', 'website-simulator-mvp'), 'value_add' => 0),
                    array('value' => 'colors_texts', 'text' => __('Personalização de cores e textos', 'website-simulator-mvp'), 'value_add' => 300),
                    array('value' => 'custom_design', 'text' => __('Design personalizado', 'website-simulator-mvp'), 'value_add' => 1200),
                    array('value' => 'fully_custom', 'text' => __('Design totalmente sob medida', 'website-simulator-mvp'), 'value_add' => 2500),
                ),
            ),
            array(
                'id' => 'content_status',
                'title' => __('Você já possui os conteúdos?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'radio',
                'required' => true,
                'order' => 6,
                'active' => true,
                'options' => array(
                    array('value' => 'have_all', 'text' => __('Sim, já tenho tudo', 'website-simulator-mvp'), 'value_add' => 0),
                    array('value' => 'have_partial', 'text' => __('Tenho parte do conteúdo', 'website-simulator-mvp'), 'value_add' => 300),
                    array('value' => 'need_texts', 'text' => __('Preciso de ajuda com textos', 'website-simulator-mvp'), 'value_add' => 600),
                    array('value' => 'need_everything', 'text' => __('Preciso de textos e imagens', 'website-simulator-mvp'), 'value_add' => 1200),
                ),
            ),
            array(
                'id' => 'deadline',
                'title' => __('Qual é o prazo desejado?', 'website-simulator-mvp'),
                'description' => '',
                'type' => 'radio',
                'required' => true,
                'order' => 7,
                'active' => true,
                'options' => array(
                    array('value' => 'no_urgency', 'text' => __('Sem urgência', 'website-simulator-mvp'), 'multiplier' => 1),
                    array('value' => '30_days', 'text' => __('Até 30 dias', 'website-simulator-mvp'), 'multiplier' => 1),
                    array('value' => '15_days', 'text' => __('Até 15 dias', 'website-simulator-mvp'), 'multiplier' => 1.15),
                    array('value' => 'priority', 'text' => __('Preciso de prioridade', 'website-simulator-mvp'), 'multiplier' => 1.25),
                ),
            ),
        );
    }

    /**
     * Get default pricing rules
     */
    public static function get_default_pricing_rules() {
        return array(
            'base_values' => array(
                'institutional' => 1500,
                'landing_page' => 900,
                'ecommerce' => 3500,
                'blog' => 1200,
                'portfolio' => 1000,
                'membership' => 2500,
            ),
            'page_values' => array(
                'home' => 0,
                'about' => 150,
                'services' => 200,
                'products' => 250,
                'blog' => 300,
                'portfolio' => 200,
                'testimonials' => 150,
                'faq' => 100,
                'contact' => 100,
                'privacy' => 80,
            ),
            'feature_values' => array(
                'contact_form' => 150,
                'whatsapp_button' => 100,
                'instagram_integration' => 200,
                'google_maps' => 100,
                'blog_module' => 400,
                'newsletter' => 250,
                'scheduling' => 500,
                'membership_area' => 2000,
                'online_payment' => 800,
                'crm_integration' => 600,
                'seo_basic' => 300,
                'mobile_optimization' => 200,
            ),
            'visual_multipliers' => array(
                'basic' => 1,
                'colors_texts' => 1.1,
                'custom_design' => 1.3,
                'fully_custom' => 1.5,
            ),
            'content_values' => array(
                'have_all' => 0,
                'have_partial' => 300,
                'need_texts' => 600,
                'need_everything' => 1200,
            ),
            'deadline_multipliers' => array(
                'no_urgency' => 1,
                '30_days' => 1,
                '15_days' => 1.15,
                'priority' => 1.25,
            ),
            'complexity_scores' => array(
                'basic' => array('min' => 0, 'max' => 1000),
                'intermediate' => array('min' => 1001, 'max' => 3000),
                'advanced' => array('min' => 3001, 'max' => 6000),
                'custom' => array('min' => 6001, 'max' => 999999),
            ),
        );
    }

    /**
     * Get settings
     */
    public function get_settings() {
        return get_option($this->settings_key, array());
    }

    /**
     * Update settings
     */
    public function update_settings($settings) {
        return update_option($this->settings_key, $settings);
    }

    /**
     * Get a specific setting
     */
    public function get_setting($key, $default = '') {
        $settings = $this->get_settings();
        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Get questions
     */
    public function get_questions() {
        return get_option($this->questions_key, self::get_default_questions());
    }

    /**
     * Update questions
     */
    public function update_questions($questions) {
        return update_option($this->questions_key, $questions);
    }

    /**
     * Get pricing rules
     */
    public function get_pricing_rules() {
        return get_option($this->pricing_key, self::get_default_pricing_rules());
    }

    /**
     * Update pricing rules
     */
    public function update_pricing_rules($rules) {
        return update_option($this->pricing_key, $rules);
    }
}
