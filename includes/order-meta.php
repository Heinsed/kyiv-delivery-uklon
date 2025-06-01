<?php
if (!defined('ABSPATH')) exit;


add_action('woocommerce_checkout_update_order_meta', function($order_id) {
    $chosen_shipping = WC()->session->get('chosen_shipping_methods')[0] ?? '';
    if ($chosen_shipping !== 'kyiv_custom_shipping') {
        return;
    }

    if (!empty($_POST['kyiv_address'])) {
        update_post_meta($order_id, 'kyiv_address', sanitize_text_field($_POST['kyiv_address']));
    }

    update_post_meta($order_id, 'kyiv_is_gift', isset($_POST['kyiv_is_gift']) ? 'yes' : 'no');

    if (!empty($_POST['gift_recipient_name'])) {
        update_post_meta($order_id, 'gift_recipient_name', sanitize_text_field($_POST['gift_recipient_name']));
    }
    if (!empty($_POST['gift_recipient_phone'])) {
        update_post_meta($order_id, 'gift_recipient_phone', sanitize_text_field($_POST['gift_recipient_phone']));
    }
    if (!empty($_POST['gift_card_text'])) {
        update_post_meta($order_id, 'gift_card_text', sanitize_textarea_field($_POST['gift_card_text']));
    }
});

add_action('woocommerce_admin_order_data_after_shipping_address', function($order){
    $shipping_method = $order->get_shipping_methods();
    $shipping_method_id = '';
    foreach ($shipping_method as $method) {
        $shipping_method_id = $method->get_method_id();
        break;
    }

    if ($shipping_method_id !== 'kyiv_custom_shipping') {
        return;
    }

    echo '<p><strong>Адреса доставки (Kyiv):</strong> ' . esc_html(get_post_meta($order->get_id(), 'kyiv_address', true)) . '</p>';
    echo '<p><strong>Це на подарунок:</strong> ' . (get_post_meta($order->get_id(), 'kyiv_is_gift', true) === 'yes' ? 'Так' : 'Ні') . '</p>';

    if (get_post_meta($order->get_id(), 'kyiv_is_gift', true) === 'yes') {
        echo '<p><strong>Ім\'я одержувача:</strong> ' . esc_html(get_post_meta($order->get_id(), 'gift_recipient_name', true)) . '</p>';
        echo '<p><strong>Телефон одержувача:</strong> ' . esc_html(get_post_meta($order->get_id(), 'gift_recipient_phone', true)) . '</p>';
        echo '<p><strong>Що написати на листівці:</strong> ' . nl2br(esc_html(get_post_meta($order->get_id(), 'gift_card_text', true))) . '</p>';
    }
});


