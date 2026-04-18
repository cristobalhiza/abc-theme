<?php

/**
 * Obtiene las reseñas de Google Places (API New v1) y las almacena en un transient por 2 días.
 * Filtra solo las reseñas de 5 estrellas.
 *
 * Requiere las constantes PLACES_API_KEY y PLACES_ID en wp-config.php.
 */
function get_google_reviews() {
    $transient_key = 'abc_google_reviews';
    $reviews = get_transient($transient_key);

    if ($reviews !== false) {
        return $reviews;
    }

    $api_key  = defined('PLACES_API_KEY') ? PLACES_API_KEY : '';
    $place_id = defined('PLACES_ID')      ? PLACES_ID      : '';

    if (empty($api_key) || empty($place_id)) {
        return [];
    }

    // ── Endpoint de la nueva API Places (v1) ──
    $url = 'https://places.googleapis.com/v1/places/' . $place_id . '?languageCode=es';

    $response = wp_remote_get($url, [
        'timeout' => 15,
        'headers' => [
            'Content-Type'     => 'application/json',
            'X-Goog-Api-Key'   => $api_key,
            'X-Goog-FieldMask' => 'reviews',
            'Referer'          => get_site_url(),
        ],
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body      = json_decode(wp_remote_retrieve_body($response), true);

    if ($http_code !== 200 || empty($body['reviews']) || !is_array($body['reviews'])) {
        return [];
    }

    // ── Normalizar y filtrar solo 5 estrellas ──
    $normalized = [];

    foreach ($body['reviews'] as $item) {
        $rating = isset($item['rating']) ? (int) $item['rating'] : 0;

        if ($rating === 5) {
            $normalized[] = [
                'author_name'               => $item['authorAttribution']['displayName'] ?? 'Alumno ABC',
                'profile_photo_url'         => $item['authorAttribution']['photoUri']     ?? 'https://ui-avatars.com/api/?name=ABC',
                'rating'                    => 5,
                'text'                      => $item['originalText']['text'] ?? $item['text']['text'] ?? '',
                'relative_time_description' => $item['relativePublishTimeDescription']    ?? 'Reciente',
            ];
        }
    }

    // Guardar en caché por 2 días (solo si hay resultados)
    if (!empty($normalized)) {
        set_transient($transient_key, $normalized, 2 * DAY_IN_SECONDS);
    }

    return $normalized;
}

// ── Endpoint REST público (lazy loading) ──
add_action('rest_api_init', function () {
    register_rest_route('abc/v1', '/reviews', [
        'methods'             => 'GET',
        'callback'            => function () {
            return new WP_REST_Response(get_google_reviews(), 200);
        },
        'permission_callback' => '__return_true',
    ]);
});
