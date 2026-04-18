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

add_filter('timber/context', function ($context) {
    $context['turnstile_site_key'] = defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '';
    return $context;
});