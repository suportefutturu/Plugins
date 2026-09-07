/**
 * Admin JavaScript
 * 
 * @package Website_Simulator_MVP
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Status change handler
        $(document).on('change', '.wsmvp-status-select', function() {
            const $select = $(this);
            const id = $select.data('id');
            const status = $select.val();

            $.ajax({
                url: wsmvpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wsmvp_update_status',
                    nonce: wsmvpAdmin.nonce,
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        // Show success indicator
                        $select.css('border-color', '#22c55e');
                        setTimeout(() => {
                            $select.css('border-color', '');
                        }, 2000);
                    } else {
                        alert(response.data?.message || wsmvpAdmin.strings.error);
                    }
                },
                error: function() {
                    alert(wsmvpAdmin.strings.error);
                }
            });
        });

        // Delete simulation handler
        $(document).on('click', '.wsmvp-delete-simulation', function() {
            if (!confirm(wsmvpAdmin.strings.confirmDelete)) {
                return;
            }

            const $btn = $(this);
            const id = $btn.data('id');

            $.ajax({
                url: wsmvpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wsmvp_delete_simulation',
                    nonce: wsmvpAdmin.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data?.message || wsmvpAdmin.strings.error);
                    }
                },
                error: function() {
                    alert(wsmvpAdmin.strings.error);
                }
            });
        });

        // View details handler
        $(document).on('click', '.wsmvp-view-details', function() {
            const id = $(this).data('id');
            
            // Get row data
            const $row = $(this).closest('tr');
            const cells = $row.find('td');
            
            const html = `
                <h2>${wsmvpAdmin.strings.viewDetails || 'Detalhes da Simulação'}</h2>
                <div class="wsmvp-detail-row">
                    <strong>ID:</strong> #${id}
                </div>
                <div class="wsmvp-detail-row">
                    <strong>Nome:</strong> ${cells.eq(2).text()}
                </div>
                <div class="wsmvp-detail-row">
                    <strong>E-mail:</strong> ${cells.eq(3).text()}
                </div>
                <div class="wsmvp-detail-row">
                    <strong>Empresa:</strong> ${cells.eq(4).text() || '-'}
                </div>
                <div class="wsmvp-detail-row">
                    <strong>Valor Estimado:</strong> R$ ${cells.eq(5).text()}
                </div>
                <div class="wsmvp-detail-row">
                    <strong>Categoria:</strong> ${cells.eq(6).text().trim()}
                </div>
                <div class="wsmvp-detail-row">
                    <strong>Status:</strong> ${cells.eq(7).find('select').val()}
                </div>
            `;

            $('#wsmvp-modal-body').html(html);
            $('#wsmvp-modal').show();
        });

        // Close modal
        $(document).on('click', '.wsmvp-modal-close, .wsmvp-modal', function(e) {
            if (e.target === this) {
                $('#wsmvp-modal').hide();
            }
        });

        // Settings form handler
        $('#wsmvp-settings-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $status = $form.find('.wsmvp-save-status');
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if (key !== 'type') {
                    data[key] = value;
                }
            });

            $.ajax({
                url: wsmvpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wsmvp_save_settings',
                    nonce: wsmvpAdmin.nonce,
                    type: 'general',
                    data: JSON.stringify(data)
                },
                beforeSend: function() {
                    $status.text('Salvando...');
                },
                success: function(response) {
                    if (response.success) {
                        $status.text(wsmvpAdmin.strings.success);
                        setTimeout(() => {
                            $status.text('');
                        }, 3000);
                    } else {
                        $status.text(response.data?.message || wsmvpAdmin.strings.error);
                        $status.css('color', '#dc2626');
                    }
                },
                error: function() {
                    $status.text(wsmvpAdmin.strings.error);
                    $status.css('color', '#dc2626');
                }
            });

            return false;
        });
    });

})(jQuery);
