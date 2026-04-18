<?php
$context = Timber::context();
$context['post'] = Timber::get_post();

$args = [
    'post_type'      => 'courses',
    'posts_per_page' => 3,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
];
$context['cursos'] = Timber::get_posts( $args );

// Recuperamos las fotos de los alumnos desde las opciones globales del tema
$context['fotos_alumnos'] = carbon_get_theme_option( 'abc_fotos_alumnos' );

// Recuperamos las reseñas de Google Places (con caché de 2 días)
$context['google_reviews'] = get_google_reviews();

Timber::render( 'templates/pages/front-page.twig', $context );