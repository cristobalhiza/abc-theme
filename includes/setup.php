<?php
use Carbon_Fields\Carbon_Fields;

add_action('after_setup_theme', function() {
    // Inicializar Carbon Fields (el autoloader ya se garantizó en functions.php)
    Carbon_Fields::boot();

    // Soporte nativo del tema
    add_theme_support('post-thumbnails');
    
    // Registro de menús
    register_nav_menus([
        'primary' => 'Menú Principal'
    ]);
});