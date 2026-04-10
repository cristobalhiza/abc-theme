<?php
/**
 * Controlador de la vista individual de Cursos
 * Ubicación: single-cursos.php
 */

$context = Timber::context();
$timber_post = Timber::get_post();
$context['post'] = $timber_post;

// Extraemos los metadatos de Carbon Fields
$context['precio_normal'] = carbon_get_post_meta( $timber_post->ID, 'abc_precio_normal' );
$context['precio_oferta'] = carbon_get_post_meta( $timber_post->ID, 'abc_precio_oferta' );
$context['resoluciones'] = carbon_get_post_meta( $timber_post->ID, 'abc_resoluciones' );
$context['requisitos'] = carbon_get_post_meta( $timber_post->ID, 'abc_requisitos' );
$context['descargas'] = carbon_get_post_meta( $timber_post->ID, 'abc_descargas' );
$context['img_secundaria'] = carbon_get_post_meta( $timber_post->ID, 'abc_img_secundaria' );
$context['objetivos'] = carbon_get_post_meta( $timber_post->ID, 'abc_objetivos' );
$context['programa'] = carbon_get_post_meta( $timber_post->ID, 'abc_programa' );
$context['woo_id'] = carbon_get_post_meta( $timber_post->ID, 'abc_woo_id' );

Timber::render( 'templates/single-courses.twig', $context );