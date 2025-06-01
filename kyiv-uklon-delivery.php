<?php
/*
Plugin Name: Kyiv Custom Shipping Uklon
Description: Кастомний метод доставки по Києву з розрахунком вартості через Uklon по полю "Адреси" та додатковими полями подарунка.
Version: 1.1.2
Tested up to: 6.7
Requires at least: 5.2
Requires PHP: 7.1
WC tested up to: 9.4
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



require_once plugin_dir_path(__FILE__) . 'lib/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/Heinsed/kyiv-delivery-uklon',
    __FILE__,
    'kyiv-uklon-delivery'
);

$myUpdateChecker->getVcsApi()->enableReleaseAssets();

$myUpdateChecker->setBranch('master');


