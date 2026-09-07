<?php
/**
 * Frontend handler class
 * 
 * Handles simulator rendering, AJAX actions and form submission
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WSMVP_Frontend {

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
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_wsmvp_submit_simulation', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_wsmvp_submit_simulation', array($this, 'handle_submission'));
        add_action('wp_ajax_wsmvp_get_preview', array($this, 'get_preview'));
        add_action('wp_ajax_nopriv_wsmvp_get_preview', array($this, 'get_preview'));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'wsmvp-frontend',
            WSMVP_PLUGIN_URL . 'public/css/wsmvp-frontend.css',
            array(),
            WSMVP_VERSION
        );

        wp_enqueue_script(
            'wsmvp-frontend',
            WSMVP_PLUGIN_URL . 'public/js/wsmvp-frontend.js',
            array('jquery'),
            WSMVP_VERSION,
            true
        );

        wp_localize_script('wsmvp-frontend', 'wsmvpConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wsmvp_nonce'),
            'strings' => array(
                'step' => __('Etapa', 'website-simulator-mvp'),
                'of' => __('de', 'website-simulator-mvp'),
                'next' => __('Próximo', 'website-simulator-mvp'),
                'previous' => __('Voltar', 'website-simulator-mvp'),
                'finish' => __('Ver Resultado', 'website-simulator-mvp'),
                'required' => __('Este campo é obrigatório', 'website-simulator-mvp'),
                'error' => __('Ocorreu um erro. Tente novamente.', 'website-simulator-mvp'),
                'loading' => __('Enviando...', 'website-simulator-mvp'),
                'success' => __('Simulação enviada com sucesso!', 'website-simulator-mvp'),
                'privacyRequired' => __('Você deve aceitar os termos de privacidade', 'website-simulator-mvp'),
            ),
        ));
    }

    /**
     * Render simulator shortcode
     */
    public static function render_simulator($atts) {
        $atts = shortcode_atts(array(), $atts, 'website_simulator');
        
        ob_start();
        include WSMVP_PLUGIN_DIR . 'public/templates/simulator.php';
        return ob_get_clean();
    }

    /**
     * Handle form submission
     */
    public function handle_submission() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wsmvp_nonce')) {
            wp_send_json_error(array('message' => __('Erro de segurança', 'website-simulator-mvp')));
        }

        // Sanitize inputs
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $company = isset($_POST['company']) ? sanitize_text_field($_POST['company']) : '';
        $whatsapp = isset($_POST['whatsapp']) ? sanitize_text_field($_POST['whatsapp']) : '';
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        $consent = isset($_POST['consent']) && $_POST['consent'] === '1';
        $responses = isset($_POST['responses']) ? json_decode(stripslashes($_POST['responses']), true) : array();

        // Validate required fields
        if (empty($name)) {
            wp_send_json_error(array('message' => __('Por favor, informe seu nome', 'website-simulator-mvp')));
        }

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => __('Por favor, informe um e-mail válido', 'website-simulator-mvp')));
        }

        if (!$consent) {
            wp_send_json_error(array('message' => __('Você deve aceitar os termos de privacidade', 'website-simulator-mvp')));
        }

        // Calculate price
        $pricing = WSMVP_Pricing::get_instance()->calculate($responses);

        // Save simulation
        $db = WSMVP_Database::get_instance();
        $simulation_id = $db->save_simulation(array(
            'name' => $name,
            'company' => $company,
            'email' => $email,
            'whatsapp' => $whatsapp,
            'responses' => $responses,
            'estimated_value' => $pricing['total'],
            'project_category' => $pricing['category'],
            'notes' => $notes,
            'consent' => $consent,
        ));

        if (!$simulation_id) {
            wp_send_json_error(array('message' => __('Erro ao salvar simulação', 'website-simulator-mvp')));
        }

        // Send notification email to admin
        $this->send_admin_notification($simulation_id, $pricing);

        // Send confirmation email to client
        $this->send_client_confirmation($simulation_id, $pricing);

        // Return success
        wp_send_json_success(array(
            'simulation_id' => $simulation_id,
            'pricing' => $pricing,
        ));
    }

    /**
     * Get preview HTML
     */
    public function get_preview() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wsmvp_nonce')) {
            wp_send_json_error(array('message' => __('Erro de segurança', 'website-simulator-mvp')));
        }

        $responses = isset($_POST['responses']) ? json_decode(stripslashes($_POST['responses']), true) : array();
        
        ob_start();
        $this->render_preview($responses);
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }

    /**
     * Render preview based on responses
     */
    private function render_preview($responses) {
        $settings = $this->settings;
        $primary_color = isset($responses['primary_color']) ? $responses['primary_color'] : ($settings['primary_color'] ?? '#2563eb');
        $secondary_color = isset($responses['secondary_color']) ? $responses['secondary_color'] : ($settings['secondary_color'] ?? '#1e40af');
        $company_name = isset($responses['company_name']) ? $responses['company_name'] : ($settings['company_name'] ?? 'Sua Empresa');
        $site_type = isset($responses['site_type']) ? $responses['site_type'] : 'institutional';
        $pages = isset($responses['pages']) ? $responses['pages'] : array();
        $features = isset($responses['features']) ? $responses['features'] : array();
        $visual_style = isset($responses['visual_style']) ? $responses['visual_style'] : 'modern';
        
        $has_whatsapp = in_array('whatsapp_button', $features);
        $has_ecommerce = $site_type === 'ecommerce';
        $has_blog = in_array('blog', $pages) || in_array('blog_module', $features);
        $has_testimonials = in_array('testimonials', $pages);
        $has_contact = in_array('contact', $pages) || in_array('contact_form', $features);
        $has_maps = in_array('google_maps', $features);
        ?>
        <div class="wsmvp-preview" style="--wsmvp-primary: <?php echo esc_attr($primary_color); ?>; --wsmvp-secondary: <?php echo esc_attr($secondary_color); ?>;">
            <div class="wsmvp-preview-header">
                <div class="wsmvp-preview-logo"><?php echo esc_html($company_name); ?></div>
                <nav class="wsmvp-preview-nav">
                    <?php if (in_array('home', $pages)): ?>
                        <a href="#"><?php _e('Início', 'website-simulator-mvp'); ?></a>
                    <?php endif; ?>
                    <?php if (in_array('about', $pages)): ?>
                        <a href="#"><?php _e('Sobre', 'website-simulator-mvp'); ?></a>
                    <?php endif; ?>
                    <?php if (in_array('services', $pages)): ?>
                        <a href="#"><?php _e('Serviços', 'website-simulator-mvp'); ?></a>
                    <?php endif; ?>
                    <?php if (in_array('portfolio', $pages)): ?>
                        <a href="#"><?php _e('Portfólio', 'website-simulator-mvp'); ?></a>
                    <?php endif; ?>
                    <?php if (in_array('contact', $pages)): ?>
                        <a href="#"><?php _e('Contato', 'website-simulator-mvp'); ?></a>
                    <?php endif; ?>
                </nav>
            </div>

            <div class="wsmvp-preview-hero">
                <h1><?php echo esc_html(sprintf(__('Bem-vindo à %s', 'website-simulator-mvp'), $company_name)); ?></h1>
                <p><?php _e('Soluções profissionais para o seu negócio', 'website-simulator-mvp'); ?></p>
                <button class="wsmvp-preview-cta"><?php _e('Saiba Mais', 'website-simulator-mvp'); ?></button>
            </div>

            <?php if (in_array('services', $pages)): ?>
            <div class="wsmvp-preview-section">
                <h2><?php _e('Nossos Serviços', 'website-simulator-mvp'); ?></h2>
                <div class="wsmvp-preview-grid">
                    <div class="wsmvp-preview-card">
                        <h3><?php _e('Consultoria', 'website-simulator-mvp'); ?></h3>
                        <p><?php _e('Soluções personalizadas para sua empresa', 'website-simulator-mvp'); ?></p>
                    </div>
                    <div class="wsmvp-preview-card">
                        <h3><?php _e('Desenvolvimento', 'website-simulator-mvp'); ?></h3>
                        <p><?php _e('Tecnologia de ponta para seu negócio', 'website-simulator-mvp'); ?></p>
                    </div>
                    <div class="wsmvp-preview-card">
                        <h3><?php _e('Suporte', 'website-simulator-mvp'); ?></h3>
                        <p><?php _e('Atendimento dedicado 24/7', 'website-simulator-mvp'); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($has_ecommerce): ?>
            <div class="wsmvp-preview-section">
                <h2><?php _e('Produtos em Destaque', 'website-simulator-mvp'); ?></h2>
                <div class="wsmvp-preview-grid">
                    <div class="wsmvp-preview-product">
                        <div class="wsmvp-preview-product-img"></div>
                        <h3><?php _e('Produto 1', 'website-simulator-mvp'); ?></h3>
                        <p class="wsmvp-preview-price"><?php _e('R$ 99,90', 'website-simulator-mvp'); ?></p>
                    </div>
                    <div class="wsmvp-preview-product">
                        <div class="wsmvp-preview-product-img"></div>
                        <h3><?php _e('Produto 2', 'website-simulator-mvp'); ?></h3>
                        <p class="wsmvp-preview-price"><?php _e('R$ 149,90', 'website-simulator-mvp'); ?></p>
                    </div>
                    <div class="wsmvp-preview-product">
                        <div class="wsmvp-preview-product-img"></div>
                        <h3><?php _e('Produto 3', 'website-simulator-mvp'); ?></h3>
                        <p class="wsmvp-preview-price"><?php _e('R$ 199,90', 'website-simulator-mvp'); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($has_testimonials): ?>
            <div class="wsmvp-preview-section">
                <h2><?php _e('Depoimentos', 'website-simulator-mvp'); ?></h2>
                <div class="wsmvp-preview-testimonial">
                    <p>"<?php _e('Excelente serviço! Recomendo a todos.', 'website-simulator-mvp'); ?>"</p>
                    <cite><?php _e('- João Silva', 'website-simulator-mvp'); ?></cite>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($has_blog): ?>
            <div class="wsmvp-preview-section">
                <h2><?php _e('Últimas do Blog', 'website-simulator-mvp'); ?></h2>
                <div class="wsmvp-preview-grid">
                    <article class="wsmvp-preview-article">
                        <h3><?php _e('Como escolher o melhor serviço', 'website-simulator-mvp'); ?></h3>
                        <p><?php _e('Dicas essenciais para tomar a decisão certa...', 'website-simulator-mvp'); ?></p>
                    </article>
                    <article class="wsmvp-preview-article">
                        <h3><?php _e('Tendências para 2025', 'website-simulator-mvp'); ?></h3>
                        <p><?php _e('O que esperar do mercado nos próximos anos...', 'website-simulator-mvp'); ?></p>
                    </article>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($has_contact): ?>
            <div class="wsmvp-preview-section">
                <h2><?php _e('Entre em Contato', 'website-simulator-mvp'); ?></h2>
                <form class="wsmvp-preview-form">
                    <input type="text" placeholder="<?php _e('Seu nome', 'website-simulator-mvp'); ?>" disabled>
                    <input type="email" placeholder="<?php _e('Seu e-mail', 'website-simulator-mvp'); ?>" disabled>
                    <textarea placeholder="<?php _e('Sua mensagem', 'website-simulator-mvp'); ?>" disabled></textarea>
                    <button type="button" disabled><?php _e('Enviar', 'website-simulator-mvp'); ?></button>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($has_maps): ?>
            <div class="wsmvp-preview-section">
                <h2><?php _e('Localização', 'website-simulator-mvp'); ?></h2>
                <div class="wsmvp-preview-map"></div>
            </div>
            <?php endif; ?>

            <div class="wsmvp-preview-footer">
                <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($company_name); ?>. <?php _e('Todos os direitos reservados.', 'website-simulator-mvp'); ?></p>
            </div>

            <?php if ($has_whatsapp): ?>
            <div class="wsmvp-preview-whatsapp">
                <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/></svg>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Send admin notification email
     */
    private function send_admin_notification($simulation_id, $pricing) {
        $admin_email = isset($this->settings['admin_email']) ? $this->settings['admin_email'] : get_option('admin_email');
        
        if (empty($admin_email)) {
            return;
        }

        $simulation = WSMVP_Database::get_instance()->get_simulation($simulation_id);
        
        $subject = sprintf(__('Nova simulação de website - %s', 'website-simulator-mvp'), $simulation['name']);
        
        $message = sprintf(__("Nova simulação recebida!\n\nCliente: %s\nEmpresa: %s\nEmail: %s\nWhatsApp: %s\n\nValor estimado: %s\nCategoria: %s\nPrazo: %d dias\n\nVeja mais detalhes no painel administrativo.", 'website-simulator-mvp'),
            $simulation['name'],
            $simulation['company'],
            $simulation['email'],
            $simulation['whatsapp'],
            number_format($pricing['total'], 2, ',', '.'),
            WSMVP_Pricing::get_instance()->get_category_label($pricing['category']),
            $pricing['deadline_days']
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Send client confirmation email
     */
    private function send_client_confirmation($simulation_id, $pricing) {
        if (!isset($this->settings['enable_email_capture']) || !$this->settings['enable_email_capture']) {
            return;
        }

        $simulation = WSMVP_Database::get_instance()->get_simulation($simulation_id);
        
        $subject = __('Sua simulação de website', 'website-simulator-mvp');
        
        $message = sprintf(__("Olá %s,\n\nObrigado por usar nosso simulador de websites!\n\nCom base nas suas escolhas, preparamos uma estimativa para o seu projeto:\n\nTipo de projeto: %s\nInvestimento estimado: %s a %s\nPrazo aproximado: %d dias úteis\n\nEm breve entraremos em contato para discutir os detalhes do seu projeto.\n\nAtenciosamente,\n%s", 'website-simulator-mvp'),
            $simulation['name'],
            WSMVP_Pricing::get_instance()->get_category_label($pricing['category']),
            WSMVP_Pricing::get_instance()->format_price($pricing['min_value']),
            WSMVP_Pricing::get_instance()->format_price($pricing['max_value']),
            $pricing['deadline_days'],
            $this->settings['company_name'] ?? get_bloginfo('name')
        );

        wp_mail($simulation['email'], $subject, $message);
    }
}
