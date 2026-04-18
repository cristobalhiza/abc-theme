<?php
// =======================================================================
// 1. ENDPOINT REST PARA EL FORMULARIO DE CONTACTO
// =======================================================================

add_action('rest_api_init', function () {
    register_rest_route('abc/v1', '/contact', array(
        'methods'             => 'POST',
        'callback'            => 'abc_handle_contact_submit',
        'permission_callback' => '__return_true'
    ));
});

function abc_handle_contact_submit(WP_REST_Request $request)
{
    $params = $request->get_json_params();

    // ---------------------------------------------------------------
    // Verificación de Cloudflare Turnstile
    // ---------------------------------------------------------------
    $turnstile_token = sanitize_text_field($params['turnstileToken'] ?? '');

    if (empty($turnstile_token)) {
        return new WP_REST_Response(array('message' => 'Falta la verificación de seguridad. Intenta nuevamente.'), 400);
    }

    if (defined('TURNSTILE_SECRET_KEY') && TURNSTILE_SECRET_KEY) {
        $verify_response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
            'body' => array(
                'secret'   => TURNSTILE_SECRET_KEY,
                'response' => $turnstile_token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ),
        ));

        if (is_wp_error($verify_response)) {
            return new WP_REST_Response(array('message' => 'Error al verificar la seguridad. Intenta nuevamente.'), 500);
        }

        $verify_body = json_decode(wp_remote_retrieve_body($verify_response), true);

        if (empty($verify_body['success'])) {
            return new WP_REST_Response(array('message' => 'Verificación de seguridad fallida. Intenta nuevamente.'), 403);
        }
    }

    // ---------------------------------------------------------------
    // Procesamiento y envío con wp_remote_post a la API de Resend
    // ---------------------------------------------------------------
    $name   = sanitize_text_field($params['name'] ?? '');
    $email  = sanitize_email($params['email'] ?? '');
    $phone  = sanitize_text_field($params['phone'] ?? '');
    $is_company = !empty($params['isCompany']);
    $course = is_array($params['course'] ?? '') ? implode(', ', array_map('sanitize_text_field', $params['course'])) : sanitize_text_field($params['course'] ?? '');

    if (empty($name) || empty($email) || !is_email($email) || empty($phone)) {
        return new WP_REST_Response(array('message' => 'Completa todos los campos.'), 400);
    }

    $to = carbon_get_theme_option('abc_email_destino') ?: 'cristobalhiza@gmail.com';
    $type_label = $is_company ? 'Empresa' : 'Persona';
    $subject = "Nuevo Contacto Web: $course - $name ($type_label)";
    $from_name = carbon_get_theme_option('abc_nombre_remitente') ?: 'ABC Escuela de Conductores';

    $html_content = "
        <h2>Nuevo prospecto desde el sitio web</h2>
        <p><strong>Nombre:</strong> {$name}</p>
        <p><strong>Teléfono:</strong> {$phone}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Curso de interés:</strong> {$course}</p>
        <p><strong>Tipo:</strong> {$type_label}</p>
    ";

    // Construcción de la petición HTTP nativa
    $response = wp_remote_post('https://api.resend.com/emails', array(
        'method'      => 'POST',
        'timeout'     => 15,
        'headers'     => array(
            'Authorization' => 'Bearer ' . RESEND_API_KEY,
            'Content-Type'  => 'application/json',
        ),
        'body'        => wp_json_encode(array(
            'from'     => "$from_name <contacto@notificaciones.abcconduccion.cl>",
            'to'       => [$to],
            'subject'  => $subject,
            'reply_to' => "$name <$email>",
            'html'     => $html_content,
        ))
    ));

    if (is_wp_error($response)) {
        error_log('Error crítico wp_remote_post (Resend): ' . $response->get_error_message());
        return new WP_REST_Response(array('message' => 'Error de conexión con el servidor de correo.'), 500);
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if ($response_code === 200) {
        return new WP_REST_Response(array('message' => 'Mensaje enviado exitosamente.'), 200);
    } else {
        // Registramos el error exacto de la API de Resend para debug
        error_log("Error Resend API (HTTP $response_code): " . print_r($response_body, true));
        return new WP_REST_Response(array('message' => 'El servicio de correo rechazó el envío. Intenta más tarde.'), 500);
    }
}