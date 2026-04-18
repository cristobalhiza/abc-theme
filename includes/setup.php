<?php
use Carbon_Fields\Carbon_Fields;

add_action('after_setup_theme', function() {
    Carbon_Fields::boot();

    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    
    register_nav_menus([
        'primary' => 'Menú Principal'
    ]);
});

// Encolar scripts y estilos
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('abc-style', get_template_directory_uri() . '/style.css', array(), '1.14.0');
    
    wp_enqueue_script('abc-contact-form', get_template_directory_uri() . '/assets/js/contact-form.js', array(), '1.0.0', true);
    
    // Pasar variables al script JS
    wp_localize_script('abc-contact-form', 'abcContactConfig', array(
        'turnstileSiteKey' => defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : ''
    ));
});

add_filter('timber/context', function ($context) {
    $context['turnstile_site_key'] = defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '';
    return $context;
});