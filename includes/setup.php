<?php
use Carbon_Fields\Carbon_Fields;

add_action('after_setup_theme', function() {
    // Inicializar Carbon Fields (el autoloader ya se garantizó en functions.php)
    Carbon_Fields::boot();

    // Soporte nativo del tema
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    
    // Registro de menús
    register_nav_menus([
        'primary' => 'Menú Principal'
    ]);
});

// Agregar variables globales al contexto de Timber (disponibles en todos los templates .twig)
add_filter('timber/context', function ($context) {
    $context['turnstile_site_key'] = defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '';
    return $context;
});