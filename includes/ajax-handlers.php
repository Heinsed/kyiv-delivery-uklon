<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_get_kyiv_shipping_fields', 'get_kyiv_shipping_fields_callback');
add_action('wp_ajax_nopriv_get_kyiv_shipping_fields', 'get_kyiv_shipping_fields_callback');
function get_kyiv_shipping_fields_callback() {
    check_ajax_referer('get_kyiv_shipping_fields_nonce', 'security');

    $checkout = WC()->checkout();

    ob_start();

    echo '<div class="kyiv-address-wrapper">';

    woocommerce_form_field('kyiv_address', [
        'type' => 'text',
        'label' => 'Адреса доставки',
        'required' => true,
        'class' => ['form-row-wide'],
    ], $checkout->get_value('kyiv_address'));

    echo '<ul id="suggestions-list"></ul>';
    echo '<p class="uklon-description">Розрахунок вартості доставки базується на тарифах Uklon Delivery.</p>';
    echo '</div>';

    woocommerce_form_field('kyiv_is_gift', [
        'type' => 'checkbox',
        'label' => 'Це на подарунок',
        'required' => false,
    ], $checkout->get_value('kyiv_is_gift'));

    echo '<div id="gift_fields" style="display:none;">';

    woocommerce_form_field('gift_recipient_name', [
        'type' => 'text',
        'label' => 'Ім\'я одержувача',
        'required' => false,
    ], $checkout->get_value('gift_recipient_name'));

    woocommerce_form_field('gift_recipient_phone', [
        'type' => 'text',
        'label' => 'Телефон одержувача',
        'required' => false,
    ], $checkout->get_value('gift_recipient_phone'));

    woocommerce_form_field('gift_card_text', [
        'type' => 'textarea',
        'label' => 'Що написати на листівці',
        'required' => false,
    ], $checkout->get_value('gift_card_text'));

    echo '</div>';

    $fields_html = ob_get_clean();

    wp_send_json_success($fields_html);
}


add_action('wp_ajax_save_kyiv_address_session', 'save_kyiv_address_session');
add_action('wp_ajax_nopriv_save_kyiv_address_session', 'save_kyiv_address_session');
function save_kyiv_address_session() {
    check_ajax_referer('save_kyiv_address_nonce', 'security');

    if (isset($_POST['kyiv_address'])) {
        WC()->session->set('kyiv_address', sanitize_text_field($_POST['kyiv_address']));

        WC()->session->__unset('shipping_for_package_0');
        WC()->cart->set_session();
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();

        wp_send_json_success();
    }

    wp_send_json_error();
}

add_action('wp_ajax_clear_kyiv_address_session', 'clear_kyiv_address_session');
add_action('wp_ajax_nopriv_clear_kyiv_address_session', 'clear_kyiv_address_session');



add_action('wp_ajax_get_address_suggestions', 'ajax_get_address_suggestions');
add_action('wp_ajax_nopriv_get_address_suggestions', 'ajax_get_address_suggestions');

function ajax_get_address_suggestions() {
    check_ajax_referer('save_kyiv_address_nonce', 'security');

    $address = sanitize_text_field($_POST['address'] ?? '');
    if (!$address) wp_send_json_error(['message' => 'Empty address']);

    $shipping_methods = WC()->shipping()->get_shipping_methods();
    $kyiv_method = $shipping_methods['kyiv_custom_shipping'] ?? null;

    if (!$kyiv_method || !is_object($kyiv_method)) {
        wp_send_json_error(['message' => 'Shipping method not found']);
    }

    $settings = [
        'google_maps_api_key'    => $kyiv_method->google_maps_api_key,
        'google_places_api_key'  => $kyiv_method->google_places_api_key,
        'google_address_country' => $kyiv_method->google_address_country,
        'google_address_city' => $kyiv_method->google_address_city,
    ];

    $suggestions = get_address_suggestions_google($address, $settings);
    wp_send_json_success(['suggestions' => $suggestions]);
}


function clear_kyiv_address_session() {
    check_ajax_referer('clear_kyiv_address_nonce', 'security');

    WC()->session->__unset('kyiv_address');
    WC()->session->__unset('shipping_for_package_0');
    WC()->cart->set_session();
    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();

    wp_send_json_success(['cleared' => true]);
}
