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
$context['resolucion_texto'] = carbon_get_post_meta( $timber_post->ID, 'abc_resolucion_texto' );
$context['resolucion_pdf'] = carbon_get_post_meta( $timber_post->ID, 'abc_resolucion_pdf' );
$context['requisitos'] = carbon_get_post_meta( $timber_post->ID, 'abc_requisitos' );
$context['descargas'] = carbon_get_post_meta( $timber_post->ID, 'abc_descargas' );
$context['img_secundaria'] = carbon_get_post_meta( $timber_post->ID, 'abc_img_secundaria' );

Timber::render( 'templates/single-courses.twig', $context );