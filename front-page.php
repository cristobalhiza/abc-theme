<?php
$context = Timber::context();
$context['post'] = Timber::get_post();

$args = [
    'post_type'      => 'courses',
    'posts_per_page' => 3,
    'orderby'        => 'date', 
    'order'          => 'ASC',
];
$context['cursos'] = Timber::get_posts( $args );

Timber::render( 'templates/pages/front-page.twig', $context );