<?php
/**
 * Template Name: Nuestra Escuela
 * 
 * Plantilla para la página de la escuela (historia, misión, visión)
 */

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render( 'templates/pages/template-escuela.twig', $context );
