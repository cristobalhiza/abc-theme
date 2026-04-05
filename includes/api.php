<?php
/**
 * Controladores de API REST y configuración de correo
 * Ubicación: includes/api.php
 */

// =======================================================================
// 1. ENDPOINT REST PARA EL FORMULARIO DE CONTACTO
// =======================================================================

add_action( 'rest_api_init', function () {
    register_rest_route( 'abc/v1', '/contact', array(
        'methods'             => 'POST',
        'callback'            => 'abc_handle_contact_submit',
        'permission_callback' => '__return_true' // Permite el acceso público sin autenticación
    ) );
} );

function abc_handle_contact_submit( WP_REST_Request $request ) {
    $params = $request->get_json_params();

    $name   = sanitize_text_field( $params['name'] ?? '' );
    $email  = sanitize_email( $params['email'] ?? '' );
    $phone  = sanitize_text_field( $params['phone'] ?? '' );
    $course_param = $params['course'] ?? '';
    if ( is_array( $course_param ) ) {
        $course_clean = array_map( 'sanitize_text_field', $course_param );
        $course = implode( ', ', $course_clean );
    } else {
        $course = sanitize_text_field( $course_param );
    }

    if ( empty( $name ) || empty( $email ) || ! is_email( $email ) || empty( $phone ) ) {
        return new WP_REST_Response( array( 'message' => 'Por favor, completa todos los campos con datos válidos.' ), 400 );
    }

    $to      = 'cristobalhiza@gmail.com';
    $subject = 'Nueva Contacto Web: ' . $course . $name;
    
    $body = "
        <h2>Nuevo prospecto desde el sitio web</h2>
        <p><strong>Nombre:</strong> {$name}</p>
        <p><strong>Teléfono:</strong> {$phone}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Curso de interés:</strong> {$course}</p>
    ";

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>'
    );

    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( $sent ) {
        return new WP_REST_Response( array( 'message' => 'Mensaje enviado exitosamente.' ), 200 );
    } else {
        return new WP_REST_Response( array( 'message' => 'Ocurrió un error en el servidor de correo.' ), 500 );
    }
}

// =======================================================================
// 2. INTERCEPCIÓN DE SMTP PARA USAR RESEND GLOBALMENTE
// =======================================================================

add_action( 'phpmailer_init', 'abc_setup_resend_smtp' );
function abc_setup_resend_smtp( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.resend.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;
    $phpmailer->SMTPSecure = 'ssl';
    
    // Credenciales de Resend
    $phpmailer->Username   = 'resend';
    $phpmailer->Password   = 're_7DVdwvct_un1JTuUJsMoeqZFs1T6HYY3J'; 
    
    $phpmailer->From       = 'contacto@notificaciones.abcconduccion.cl'; 
    $phpmailer->FromName   = 'ABC Escuela de Conductores';
}