<?php
if (!defined('ABSPATH')) exit;

class WC_Shipping_Kyiv_Custom extends WC_Shipping_Method {
    public function __construct() {
        $this->id = 'kyiv_custom_shipping';
        $this->method_title = 'Доставка по Києву (Uklon)';
        $this->method_description = 'Кастомний метод доставки по Киеву с розрахунком вартості через Uklon.';
        $this->enabled = 'yes';
        $this->title = 'По Києву';

        $this->init();
        $this->init_form_fields();
        $this->init_settings();

        $this->google_maps_api_key = $this->get_option('google_maps_api_key');
        $this->google_places_api_key = $this->get_option('google_places_api_key');
        $this->google_address_city = $this->get_option('google_address_city');
        $this->google_address_country = $this->get_option('google_address_country');
        $this->uklon_app_uid = $this->get_option('uklon_app_uid');
        $this->uklon_client_id = $this->get_option('uklon_client_id');
        $this->uklon_client_secret = $this->get_option('uklon_client_secret');
        $this->pickup_address = $this->get_option('pickup_address');
        $this->pickup_lat = $this->get_option('pickup_lat');
        $this->pickup_lng = $this->get_option('pickup_lng');

        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init() {
        $this->enabled = $this->get_option('enabled', 'yes');
        $this->title = $this->get_option('title', $this->title);
    }

    public function calculate_shipping($package = []) {
        $address = WC()->session->get('kyiv_address');

        if (!$address) {
            $this->add_rate([
                'id'    => $this->id,
                'label' => $this->title,
                'cost'  => 0,
            ]);
            return;
        }

        $settings = [
            'google_maps_api_key' => $this->google_maps_api_key,
            'google_address_city' => $this->google_address_city,
            'google_address_country' => $this->google_address_country,
            'uklon_app_uid'       => $this->uklon_app_uid,
            'uklon_client_id'     => $this->uklon_client_id,
            'uklon_client_secret' => $this->uklon_client_secret,
            'pickup_address'      => $this->pickup_address,
            'pickup_lat'          => $this->pickup_lat,
            'pickup_lng'          => $this->pickup_lng,
        ];

        $cost = get_uklon_delivery_cost($address, $settings);

        if ($cost === false) {
            $this->add_rate([
                'id'    => $this->id,
                'label' => 'По Києву (Помилка)',
                'cost'  => 0,
            ]);
        } else {
            $this->add_rate([
                'id'    => $this->id,
                'label' => 'По Києву',
                'cost'  => $cost,
            ]);
        }
    }


    public function init_form_fields() {
        $this->form_fields = [
            'google_maps_api_key' => [
                'title'       => 'Google Geocoding API Key',
                'type'        => 'text',
                'description' => 'Введіть ваш API ключ для Google Geocoding API.',
                'default'     => '',
            ],
            'google_places_api_key' => [
                'title'       => 'Google Places API Key',
                'type'        => 'text',
                'description' => 'Введіть ваш API ключ для Google Places API.',
                'default'     => '',
            ],
            'google_address_city' => [
                'title'       => 'Місто (Google Geocode)',
                'type'        => 'text',
                'description' => 'Назва міста для обмеження пошуку адрес (наприклад, Київ)',
                'default'     => 'Kyiv',
            ],

            'google_address_country' => [
                'title'       => 'Країна (Google Geocode)',
                'type'        => 'text',
                'description' => 'Код країни у форматі ISO 3166-1 Alpha-2 (наприклад, UA)',
                'default'     => 'UA',
            ],
            'uklon_app_uid' => [
                'title'       => 'Uklon App UID',
                'type'        => 'text',
                'default'     => '',
            ],
            'uklon_client_id' => [
                'title'       => 'Uklon Client ID',
                'type'        => 'text',
                'default'     => '',
            ],
            'uklon_client_secret' => [
                'title'       => 'Uklon Client Secret',
                'type'        => 'password',
                'default'     => '',
            ],
            'pickup_address' => [
                'title'       => 'Адреса відправлення',
                'type'        => 'text',
                'default'     => 'вул. Володимирська, 24, Київ',
            ],
            'pickup_lat' => [
                'title'       => 'Широта відправлення (Latitude)',
                'type'        => 'text',
                'default'     => '50.4524989',
            ],
            'pickup_lng' => [
                'title'       => 'Довгота відправлення (Longitude)',
                'type'        => 'text',
                'default'     => '30.5083998',
            ],
        ];
    }

}



add_action('woocommerce_before_checkout_form', function() {
    WC()->session->__unset('kyiv_address');
    WC()->session->__unset('shipping_for_package_0');
    WC()->cart->set_session();
    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();
});
