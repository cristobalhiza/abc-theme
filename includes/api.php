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
    // Procesamiento del formulario
    // ---------------------------------------------------------------
    $name   = sanitize_text_field($params['name'] ?? '');
    $email  = sanitize_email($params['email'] ?? '');
    $phone  = sanitize_text_field($params['phone'] ?? '');
    $is_company = !empty($params['isCompany']);
    $course_param = $params['course'] ?? '';
    if (is_array($course_param)) {
        $course_clean = array_map('sanitize_text_field', $course_param);
        $course = implode(', ', $course_clean);
    } else {
        $course = sanitize_text_field($course_param);
    }

    if (empty($name) || empty($email) || ! is_email($email) || empty($phone)) {
        return new WP_REST_Response(array('message' => 'Por favor, completa todos los campos con datos válidos.'), 400);
    }

    $to = carbon_get_theme_option('abc_email_destino');
    if (empty($to)) {
        $to = 'cristobalhiza@gmail.com';
    }

    $type_label = $is_company ? 'Empresa' : 'Persona';
    $subject = 'Nuevo Contacto Web: ' . $course . ' - ' . $name . ' (' . $type_label . ')';

    $body = "
        <h2>Nuevo prospecto desde el sitio web</h2>
        <p><strong>Nombre:</strong> {$name}</p>
        <p><strong>Teléfono:</strong> {$phone}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Curso de interés:</strong> {$course}</p>
        <p><strong>Tipo:</strong> {$type_label}</p>
    ";

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>'
    );

    global $abc_mail_error;
    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        return new WP_REST_Response(array('message' => 'Mensaje enviado exitosamente.'), 200);
    } else {
        $error_msg = $abc_mail_error ? $abc_mail_error : 'Error desconocido de conexión SMTP.';
        return new WP_REST_Response(array('message' => 'Resend dice: ' . $error_msg), 500);
    }
}

// =======================================================================
// 2. INTERCEPCIÓN DE SMTP PARA USAR RESEND GLOBALMENTE
// =======================================================================

add_action('phpmailer_init', 'abc_setup_resend_smtp');
function abc_setup_resend_smtp($phpmailer)
{
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.resend.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;
    $phpmailer->SMTPSecure = 'ssl';

    // Credenciales de Resend
    $phpmailer->Username   = 'resend';
    $phpmailer->Password   = RESEND_API_KEY;

    $from_name = carbon_get_theme_option('abc_nombre_remitente');
    if (empty($from_name)) {
        $from_name = 'ABC Escuela de Conductores';
    }

    $phpmailer->setFrom('contacto@notificaciones.abcconduccion.cl', $from_name);
    $phpmailer->SMTPDebug = 0;
}

// Capturador de errores globales de correo
global $abc_mail_error;
add_action('wp_mail_failed', function ($wp_error) {
    global $abc_mail_error;
    $abc_mail_error = $wp_error->get_error_message();
});
