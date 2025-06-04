<?php
if (!defined('ABSPATH')) exit;

function get_coords_by_address_google($address, $api_key, $city = 'Kyiv', $country = 'UA') {
    $encoded_address = urlencode($address);
    $components = 'locality:' . urlencode($city) . '|country:' . urlencode($country);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$encoded_address}&components={$components}&key={$api_key}";

    $response = wp_remote_get($url, ['timeout' => 10]);
    if (is_wp_error($response)) return false;

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($body['status'] !== 'OK' || empty($body['results'])) return false;

    $location = $body['results'][0]['geometry']['location'];
    return ['lat' => $location['lat'], 'lon' => $location['lng']];
}

function get_uklon_delivery_cost($client_address, $settings) {
    $client_coords = get_coords_by_address_google($client_address, $settings['google_maps_api_key'],$settings['google_address_city'], $settings['google_address_country']);
    if (!$client_coords) return false;

    $auth_url = 'https://deliverygateway.staging.uklon.com.ua/api/v1/auth';
    $estimate_url = 'https://deliverygateway.staging.uklon.com.ua/api/v1/fares/estimate';

    $auth_payload = [
        'app_uid'       => $settings['uklon_app_uid'],
        'client_id'     => $settings['uklon_client_id'],
        'client_secret' => $settings['uklon_client_secret'],
    ];

    $auth_response = wp_remote_post($auth_url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($auth_payload),
        'timeout' => 15,
    ]);

    if (is_wp_error($auth_response)) return false;

    $auth_body = json_decode(wp_remote_retrieve_body($auth_response), true);
    if (empty($auth_body['access_token'])) return false;

    $token = $auth_body['access_token'];


    $pickup = [
        'latitude'  => floatval($settings['pickup_lat']),
        'longitude' => floatval($settings['pickup_lng']),
        'address'   => $settings['pickup_address'],
    ];

    $dropoff = [
        'latitude' => $client_coords['lat'],
        'longitude' => $client_coords['lon'],
        'address' => $client_address,
    ];

    $estimate_payload = [
        'city' => 1,
        'pickup_point' => $pickup,
        'dropoff_points' => [$dropoff],
        'products' => [
            'car' => [
                'door' => false,
                'confirmation_code' => true,
                'surprise' => false,
                'buyout' => false,
                'age_verification' => false,
                'deferred' => ['arrival' => 0],
            ],
        ],
        'conditions' => ['max_weight_grams' => null],
        'max_search_time_seconds' => 0,
    ];

    $estimate_response = wp_remote_post($estimate_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ],
        'body'    => json_encode($estimate_payload),
        'timeout' => 20,
    ]);

    if (is_wp_error($estimate_response)) return false;

    $estimate_body = json_decode(wp_remote_retrieve_body($estimate_response), true);
    return floatval($estimate_body['estimated_products']['car']['estimation']['cost']['recommended'] ?? 0);
}


function get_address_suggestions_google($address, $settings) {
    $api_key = $settings['google_places_api_key'] ?? '';
    $country = $settings['google_address_country'] ?? 'UA';
    $city    = $settings['google_address_city'] ?? '';

    if (!$api_key || !$address || !$city) return [];

    $encoded_address = urlencode($address);
    $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?"
        . "input={$encoded_address}"
        . "&types=address"
        . "&components=country:{$country}"
        . "&language=uk"
        . "&key={$api_key}";

    $response = wp_remote_get($url, ['timeout' => 10]);
    if (is_wp_error($response)) return [];

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($body['status'] !== 'OK' || empty($body['predictions'])) return [];

    $suggestions = [];

    foreach ($body['predictions'] as $prediction) {
        $types = $prediction['types'] ?? [];

        if (!in_array('route', $types) && !in_array('street_address', $types) && !in_array('premise', $types)) {
            continue;
        }

        $secondary_text = $prediction['structured_formatting']['secondary_text'] ?? '';
        if (stripos($secondary_text, $city) === false) continue;

        $main_text = $prediction['structured_formatting']['main_text'] ?? '';
        if ($main_text) {
            $suggestions[] = $main_text;
        }
    }

    return array_unique($suggestions);
}





