<?php
/*
Plugin Name: Kyiv Custom Shipping Uklon
Description: Кастомний метод доставки по Києву з розрахунком вартості через Uklon по полю "Адреси" та додатковими полями подарунка.
Version: 1.0
Author: ValsyDev
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/checkout-fields.php';
require_once plugin_dir_path(__FILE__) . 'includes/order-meta.php';
require_once plugin_dir_path(__FILE__) . 'includes/scripts.php';
require_once plugin_dir_path(__FILE__) . 'includes/uklon-api.php';

add_action('woocommerce_shipping_init', function() {
    if (class_exists('WC_Shipping_Method')) {
        include_once plugin_dir_path(__FILE__) . 'includes/class-kyiv-shipping-method.php';
    }
});

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['kyiv_custom_shipping'] = 'WC_Shipping_Kyiv_Custom';
    return $methods;
});


require plugin_dir_path(__FILE__) . 'lib/plugin-update-checker/plugin-update-checker.php';

$update_checker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/YOUR_USERNAME/YOUR_REPO_NAME/',
    __FILE__,
    'your-plugin-slug'
);

// Указываем ветку (например, main)
$update_checker->setBranch('main');

// Указываем токен, если репозиторий приватный
$update_checker->setAuthentication('ghp_yourGithubTokenHere');
