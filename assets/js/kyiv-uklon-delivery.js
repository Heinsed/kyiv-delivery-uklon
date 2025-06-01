jQuery(function ($) {



    function loadKyivFields() {
        $.ajax({
            url: kyivShippingData.ajax_url,
            method: 'POST',
            data: {
                action: 'get_kyiv_shipping_fields',
                security: kyivShippingData.nonce_get_fields
            },
            success: function (response) {
                if(response.success) {
                    $('#kiev-custom-delivery-fields-selector').html(response.data);
                    const customFieldsContainer = $('#custom_delivery_fields');
                    const formTemplate = $('#kiev-custom-delivery-fields-selector').html();
                    $('#kiev-custom-delivery-fields-selector').remove();

                    if (customFieldsContainer.length && formTemplate) {
                        customFieldsContainer.after('<div id="kiev-custom-delivery-fields-selector">' + formTemplate + '</div>');
                    }
                }
            }
        });
    }

    function toggleKyivFields() {
        const selectedMethod = $('input[name^="shipping_method"]:checked').val();
        if (selectedMethod === 'kyiv_custom_shipping') {
            if ($('#kiev-custom-delivery-fields-selector').is(':empty')) {
                loadKyivFields();
            }
        } else {
            removeKyivFields();
        }
    }


    function removeKyivFields() {
        $('#kiev-custom-delivery-fields-selector').empty();
    }

    toggleKyivFields();

    function toggleGiftFields() {
        if ($('input[name="kyiv_is_gift"]').is(':checked')) {
            $('#gift_fields').show();
        } else {
            $('#gift_fields').hide();
        }
    }

    toggleGiftFields();

    function clearKyivShippingSession(callback) {
        $.ajax({
            url: kyivShippingData.ajax_url,
            method: 'POST',
            data: {
                action: 'clear_kyiv_address_session',
                security: kyivShippingData.nonce_clear_session
            },
            success: function (response) {
                $('input[name="kyiv_address"]').val('');
                $('input[name="kyiv_is_gift"]').prop('checked', false);
                $('input[name="gift_recipient_name"]').val('');
                $('input[name="gift_recipient_phone"]').val('');
                $('textarea[name="gift_card_text"]').val('');

                if (typeof callback === 'function') callback();
            },
        });
    }

    $('form.checkout').on('change', 'input[name^="shipping_method"]', function () {
        toggleKyivFields();
        clearKyivShippingSession(function() {
            $('body').trigger('update_checkout');
        });
    });


    $('form.checkout').on('change', 'input[name="kyiv_is_gift"]', function () {
        toggleGiftFields();
    });

    let addressTimeout;
    $('form.checkout').on('input', 'input[name="kyiv_address"]', function () {
        clearTimeout(addressTimeout);
        addressTimeout = setTimeout(function () {
            let val = $('input[name="kyiv_address"]').val();

            $.ajax({
                url: kyivShippingData.ajax_url,
                method: 'POST',
                data: {
                    action: 'save_kyiv_address_session',
                    kyiv_address: val,
                    security: kyivShippingData.nonce_save_address
                },

                success: function (response, textStatus, jqXHR) {

                    if (response.success) {
                        $('body').trigger('update_checkout');
                    }
                },

            });
        }, 800);
    });
});
