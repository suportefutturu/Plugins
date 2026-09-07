<?php
/**
 * Settings View
 * 
 * @package WSMVP
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('wsmvp_settings', array());
?>

<div class="wrap wsmvp-settings">
    <h1><?php esc_html_e('Website Simulator Settings', 'website-simulator-mvp'); ?></h1>
    
    <form method="post" action="options.php" enctype="multipart/form-data">
        <?php settings_fields('wsmvp_settings_group'); ?>
        <?php do_settings_sections('wsmvp_settings_group'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Company Name', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="text" name="wsmvp_settings[company_name]" value="<?php echo esc_attr($settings['company_name'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Logo URL', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="text" name="wsmvp_settings[logo_url]" id="wsmvp_logo_url" value="<?php echo esc_url($settings['logo_url'] ?? ''); ?>" class="regular-text">
                    <button type="button" class="button" id="wsmvp_upload_logo"><?php esc_html_e('Upload Logo', 'website-simulator-mvp'); ?></button>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Primary Color', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="text" name="wsmvp_settings[primary_color]" class="wsmvp-color-picker" value="<?php echo esc_attr($settings['primary_color'] ?? '#3b82f6'); ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Secondary Color', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="text" name="wsmvp_settings[secondary_color]" class="wsmvp-color-picker" value="<?php echo esc_attr($settings['secondary_color'] ?? '#1e40af'); ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Currency Symbol', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="text" name="wsmvp_settings[currency_symbol]" value="<?php echo esc_attr($settings['currency_symbol'] ?? 'R$'); ?>" class="small-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Minimum Project Value', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="number" name="wsmvp_settings[min_project_value]" value="<?php echo esc_attr($settings['min_project_value'] ?? 500); ?>" class="small-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Intro Text', 'website-simulator-mvp'); ?></th>
                <td>
                    <textarea name="wsmvp_settings[intro_text]" rows="3" class="large-text"><?php echo esc_textarea($settings['intro_text'] ?? ''); ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Lead Email', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="email" name="wsmvp_settings[lead_email]" value="<?php echo esc_attr($settings['lead_email'] ?? get_option('admin_email')); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('WhatsApp Number', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="text" name="wsmvp_settings[whatsapp_number]" value="<?php echo esc_attr($settings['whatsapp_number'] ?? ''); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Include country code (e.g., +5511999999999)', 'website-simulator-mvp'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Default Deadline (days)', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="number" name="wsmvp_settings[default_deadline]" value="<?php echo esc_attr($settings['default_deadline'] ?? 30); ?>" class="small-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Price Margin Min (%)', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="number" name="wsmvp_settings[price_margin_min]" value="<?php echo esc_attr($settings['price_margin_min'] ?? 0.1); ?>" step="0.01" class="small-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Price Margin Max (%)', 'website-simulator-mvp'); ?></th>
                <td>
                    <input type="number" name="wsmvp_settings[price_margin_max]" value="<?php echo esc_attr($settings['price_margin_max'] ?? 0.3); ?>" step="0.01" class="small-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Terms of Use', 'website-simulator-mvp'); ?></th>
                <td>
                    <textarea name="wsmvp_settings[terms_of_use]" rows="5" class="large-text"><?php echo esc_textarea($settings['terms_of_use'] ?? ''); ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Privacy Notice', 'website-simulator-mvp'); ?></th>
                <td>
                    <textarea name="wsmvp_settings[privacy_notice]" rows="5" class="large-text"><?php echo esc_textarea($settings['privacy_notice'] ?? ''); ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e('Options', 'website-simulator-mvp'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wsmvp_settings[enable_pdf]" value="1" <?php checked(!empty($settings['enable_pdf'])); ?>>
                        <?php esc_html_e('Enable PDF generation', 'website-simulator-mvp'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="wsmvp_settings[enable_email_capture]" value="1" <?php checked(!empty($settings['enable_email_capture'])); ?>>
                        <?php esc_html_e('Send confirmation email to clients', 'website-simulator-mvp'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="wsmvp_settings[show_public_price]" value="1" <?php checked(!empty($settings['show_public_price'])); ?>>
                        <?php esc_html_e('Show price estimate to users', 'website-simulator-mvp'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="wsmvp_settings[round_prices]" value="1" <?php checked(!empty($settings['round_prices'])); ?>>
                        <?php esc_html_e('Round prices to nearest 100', 'website-simulator-mvp'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="wsmvp_settings[remove_data_on_uninstall]" value="1" <?php checked(!empty($settings['remove_data_on_uninstall'])); ?>>
                        <?php esc_html_e('Remove all data on uninstall', 'website-simulator-mvp'); ?>
                    </label>
                    <p class="description" style="color: #d63638;"><?php esc_html_e('Warning: This will delete all simulations and settings!', 'website-simulator-mvp'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Color picker
    $('.wsmvp-color-picker').wpColorPicker();
    
    // Media uploader for logo
    $('#wsmvp_upload_logo').on('click', function(e) {
        e.preventDefault();
        
        const mediaUploader = wp.media({
            title: '<?php esc_html_e('Choose Logo', 'website-simulator-mvp'); ?>',
            button: {
                text: '<?php esc_html_e('Use as Logo', 'website-simulator-mvp'); ?>'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#wsmvp_logo_url').val(attachment.url);
        });
        
        mediaUploader.open();
    });
});
</script>

<style>
.wsmvp-settings .form-table th {
    width: 200px;
    font-weight: 600;
}

.wsmvp-settings .wp-picker-container {
    margin-top: 10px;
}
</style>
