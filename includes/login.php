<?php
/**
 * Personalización del Login y Seguridad con Cloudflare Turnstile
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. Personalización Estética del Login
 */
add_action('login_enqueue_scripts', function() {
    $logo_url = get_template_directory_uri() . '/assets/logos/ABC-logo-nobg.webp';
    ?>
    <style type="text/css">
        body.login {
            background-color: #f8fafc;
        }
        #login h1 a, .login h1 a {
            background-image: url('<?php echo $logo_url; ?>');
            height: 100px;
            width: 100%;
            background-size: contain;
            background-repeat: no-repeat;
            padding-bottom: 10px;
        }
        #loginform {
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            padding: 30px;
        }
        .login #login_error, .login .message, .login .success {
            border-radius: 8px;
            border-left-width: 4px;
        }
        .wp-core-ui .button-primary {
            background: #0683C0 !important;
            border-color: #0683C0 !important;
            border-radius: 8px !important;
            padding: 5px 20px !important;
            height: auto !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease !important;
        }
        .wp-core-ui .button-primary:hover {
            background: #056a9b !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 131, 192, 0.3);
        }
        input[type=text]:focus, input[type=password]:focus, input[type=checkbox]:focus {
            border-color: #0683C0 !important;
            box-shadow: 0 0 0 1px #0683C0 !important;
        }
    </style>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php
});

// Cambiar URL del logo
add_filter('login_headerurl', function() {
    return home_url();
});

// Cambiar title del logo
add_filter('login_headertext', function() {
    return 'ABC Academia de Conducción';
});

/**
 * 2. Integración de Cloudflare Turnstile
 */

// Mostrar el widget en el formulario de login
add_action('login_form', function() {
    $site_key = defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '';
    if (!$site_key) return;
    ?>
    <div class="cf-turnstile" 
         data-sitekey="<?php echo esc_attr($site_key); ?>" 
         data-theme="light"
         style="margin-bottom: 20px; display: flex; justify-content: center;"></div>
    <?php
});

// Validación del lado del servidor
add_filter('authenticate', function($user, $username, $password) {
    // Solo validar si es una petición POST e intenta loguearse
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($username)) {
        return $user;
    }

    $turnstile_response = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';
    $secret_key = defined('TURNSTILE_SECRET_KEY') ? TURNSTILE_SECRET_KEY : '';

    if (empty($turnstile_response)) {
        return new WP_Error('turnstile_empty', '<strong>ERROR</strong>: Por favor, completa la verificación de seguridad.');
    }

    if (!$secret_key) {
        return $user; // No bloquear si la clave no está configurada
    }

    $verify_response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
        'body' => array(
            'secret'   => $secret_key,
            'response' => $turnstile_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        )
    ));

    if (is_wp_error($verify_response)) {
        return new WP_Error('turnstile_error', 'No se pudo conectar con el servicio de verificación.');
    }

    $response_body = wp_remote_retrieve_body($verify_response);
    $result = json_decode($response_body);

    if (!$result || !$result->success) {
        return new WP_Error('turnstile_failed', 'Fallo en la validación de seguridad. Inténtalo de nuevo.');
    }

    return $user;
}, 30, 3);
