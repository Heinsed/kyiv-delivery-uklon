<?php
if (!defined('ABSPATH')) exit;

add_action('woocommerce_after_order_notes', function ($checkout) {
    ?>
    <div id="kiev-custom-delivery-fields-selector"></div>

    <?php
});


add_action('woocommerce_checkout_process', function() {
    $chosen_methods = WC()->session->get('chosen_shipping_methods');
    $kyiv_address = isset($_POST['kyiv_address']) ? trim($_POST['kyiv_address']) : '';

    if (is_array($chosen_methods)) {
        foreach ($chosen_methods as $method) {
            if (strpos($method, 'kyiv_custom_shipping') !== false) {
                if (empty($kyiv_address)) {
                    wc_add_notice(__('Будь ласка, вкажіть адресу доставки по Києву.'), 'error');
                }
                break;
            }
        }
    }
});
