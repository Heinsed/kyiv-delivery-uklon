<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    if (is_checkout()) {
        wp_enqueue_script('kyiv-custom-shipping', plugin_dir_url(__FILE__) . '../assets/js/kyiv-uklon-delivery.js', ['jquery'], '1.0', true);
        wp_localize_script('kyiv-custom-shipping', 'kyivShippingData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce_get_fields' => wp_create_nonce('get_kyiv_shipping_fields_nonce'),
            'nonce_save_address' => wp_create_nonce('save_kyiv_address_nonce'),
            'nonce_clear_session' => wp_create_nonce('clear_kyiv_address_nonce'),
        ]);
        wp_enqueue_style('kyiv-custom-shipping-style', plugin_dir_url(__FILE__) . '../assets/css/kyiv-uklon-delivery.css', [], '1.0');
    }
});
