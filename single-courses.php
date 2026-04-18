<?php
/**
 * Controlador de la vista individual de Cursos
 */

$timber_post = Timber::get_post();

if (!$timber_post) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    exit;
}

$context = Timber::context();
$context['post'] = $timber_post;

// Metadatos de Carbon Fields
$post_id = $timber_post->ID;
$context['precio_normal'] = carbon_get_post_meta( $post_id, 'abc_precio_normal' );
$context['precio_oferta'] = carbon_get_post_meta( $post_id, 'abc_precio_oferta' );
$context['resoluciones'] = carbon_get_post_meta( $post_id, 'abc_resoluciones' );
$context['requisitos'] = carbon_get_post_meta( $post_id, 'abc_requisitos' );
$context['descargas'] = carbon_get_post_meta( $post_id, 'abc_descargas' );
$context['img_secundaria'] = carbon_get_post_meta( $post_id, 'abc_img_secundaria' );
$context['objetivos'] = carbon_get_post_meta( $post_id, 'abc_objetivos' );
$context['programa'] = carbon_get_post_meta( $post_id, 'abc_programa' );
$context['woo_id'] = carbon_get_post_meta( $post_id, 'abc_woo_id' );

Timber::render( 'templates/single-courses.twig', $context );