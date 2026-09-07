<?php
/**
 * Settings view
 * 
 * @package Website_Simulator_MVP
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = empty($settings) ? array() : $settings;
?>

<div class="wsmvp-admin-wrap">
    <h1><?php _e('Configurações do Plugin', 'website-simulator-mvp'); ?></h1>

    <form id="wsmvp-settings-form" method="post">
        <input type="hidden" name="type" value="general">
        
        <h2><?php _e('Informações da Empresa', 'website-simulator-mvp'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="company_name"><?php _e('Nome da Empresa', 'website-simulator-mvp'); ?></label></th>
                <td><input type="text" id="company_name" name="company_name" value="<?php echo esc_attr($settings['company_name'] ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="admin_email"><?php _e('E-mail para Receber Leads', 'website-simulator-mvp'); ?></label></th>
                <td><input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($settings['admin_email'] ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="whatsapp_number"><?php _e('Número de WhatsApp', 'website-simulator-mvp'); ?></label></th>
                <td><input type="text" id="whatsapp_number" name="whatsapp_number" value="<?php echo esc_attr($settings['whatsapp_number'] ?? ''); ?>" class="regular-text" placeholder="+55 11 99999-9999"></td>
            </tr>
        </table>

        <h2><?php _e('Aparência', 'website-simulator-mvp'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="primary_color"><?php _e('Cor Principal', 'website-simulator-mvp'); ?></label></th>
                <td><input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>"></td>
            </tr>
            <tr>
                <th><label for="secondary_color"><?php _e('Cor Secundária', 'website-simulator-mvp'); ?></label></th>
                <td><input type="color" id="secondary_color" name="secondary_color" value="<?php echo esc_attr($settings['secondary_color'] ?? '#1e40af'); ?>"></td>
            </tr>
            <tr>
                <th><label for="button_color"><?php _e('Cor dos Botões', 'website-simulator-mvp'); ?></label></th>
                <td><input type="color" id="button_color" name="button_color" value="<?php echo esc_attr($settings['button_color'] ?? '#16a34a'); ?>"></td>
            </tr>
        </table>

        <h2><?php _e('Moeda e Preços', 'website-simulator-mvp'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="currency_symbol"><?php _e('Símbolo da Moeda', 'website-simulator-mvp'); ?></label></th>
                <td><input type="text" id="currency_symbol" name="currency_symbol" value="<?php echo esc_attr($settings['currency_symbol'] ?? 'R$'); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="min_project_value"><?php _e('Valor Mínimo do Projeto (R$)', 'website-simulator-mvp'); ?></label></th>
                <td><input type="number" id="min_project_value" name="min_project_value" value="<?php echo esc_attr($settings['min_project_value'] ?? 500); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="price_margin_min"><?php _e('Margem Mínima', 'website-simulator-mvp'); ?></label></th>
                <td><input type="number" step="0.1" id="price_margin_min" name="price_margin_min" value="<?php echo esc_attr($settings['price_margin_min'] ?? 0.9); ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="price_margin_max"><?php _e('Margem Máxima', 'website-simulator-mvp'); ?></label></th>
                <td><input type="number" step="0.1" id="price_margin_max" name="price_margin_max" value="<?php echo esc_attr($settings['price_margin_max'] ?? 1.2); ?>" class="small-text"></td>
            </tr>
        </table>

        <h2><?php _e('Textos', 'website-simulator-mvp'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="intro_text"><?php _e('Texto de Apresentação', 'website-simulator-mvp'); ?></label></th>
                <td><textarea id="intro_text" name="intro_text" rows="3" class="large-text"><?php echo esc_textarea($settings['intro_text'] ?? ''); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="contact_button_text"><?php _e('Texto do Botão de Contato', 'website-simulator-mvp'); ?></label></th>
                <td><input type="text" id="contact_button_text" name="contact_button_text" value="<?php echo esc_attr($settings['contact_button_text'] ?? ''); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="privacy_notice"><?php _e('Aviso de Privacidade', 'website-simulator-mvp'); ?></label></th>
                <td><textarea id="privacy_notice" name="privacy_notice" rows="2" class="large-text"><?php echo esc_textarea($settings['privacy_notice'] ?? ''); ?></textarea></td>
            </tr>
        </table>

        <h2><?php _e('Opções', 'website-simulator-mvp'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php _e('Gerar PDF', 'website-simulator-mvp'); ?></th>
                <td>
                    <label><input type="checkbox" name="enable_pdf" value="1" <?php checked($settings['enable_pdf'] ?? true, true); ?>> <?php _e('Ativar geração de PDF', 'website-simulator-mvp'); ?></label>
                </td>
            </tr>
            <tr>
                <th><?php _e('Captura de E-mail', 'website-simulator-mvp'); ?></th>
                <td>
                    <label><input type="checkbox" name="enable_email_capture" value="1" <?php checked($settings['enable_email_capture'] ?? true, true); ?>> <?php _e('Enviar e-mail de confirmação ao cliente', 'website-simulator-mvp'); ?></label>
                </td>
            </tr>
            <tr>
                <th><?php _e('Exibir Preço Público', 'website-simulator-mvp'); ?></th>
                <td>
                    <label><input type="checkbox" name="show_public_price" value="1" <?php checked($settings['show_public_price'] ?? true, true); ?>> <?php _e('Mostrar estimativa de preço no frontend', 'website-simulator-mvp'); ?></label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php _e('Salvar Configurações', 'website-simulator-mvp'); ?></button>
            <span class="wsmvp-save-status"></span>
        </p>
    </form>
</div>
