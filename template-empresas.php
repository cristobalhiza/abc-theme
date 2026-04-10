<?php
/**
 * Template Name: Empresas
 * 
 * Plantilla para la página dedicada a empresas.
 */

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render( 'templates/pages/template-empresas.twig', $context );
