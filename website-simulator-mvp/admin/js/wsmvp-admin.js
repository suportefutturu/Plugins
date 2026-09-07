/**
 * Admin JavaScript for Website Simulator MVP
 * 
 * @package WSMVP
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Color picker initialization
        if ($.fn.wpColorPicker) {
            $('.wsmvp-color-picker').wpColorPicker();
        }
        
        // Media uploader for logo
        $('#wsmvp_upload_logo').on('click', function(e) {
            e.preventDefault();
            
            const mediaUploader = wp.media({
                title: wsmvpAdminData.i18n.chooseLogo || 'Choose Logo',
                button: {
                    text: wsmvpAdminData.i18n.useAsLogo || 'Use as Logo'
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
        
        // Confirm delete actions
        $('.wsmvp-delete-action').on('click', function(e) {
            if (!confirm(wsmvpAdminData.i18n.confirmDelete || 'Are you sure?')) {
                e.preventDefault();
            }
        });
        
        // Save settings confirmation
        $('form#wsmvp-settings-form').on('submit', function() {
            // Could add validation here
        });
    });
    
})(jQuery);
