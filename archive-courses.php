<?php
/**
 * Controlador para el archivo de Cursos
 */

$context = Timber::context();

$args = [
    'post_type'      => 'courses',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
];

$context['posts'] = Timber::get_posts($args);
$context['title'] = 'Nuestros Cursos';

Timber::render('templates/index.twig', $context);
