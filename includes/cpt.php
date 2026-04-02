<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

function abc_register_cursos_cpt() {
    $labels = [
        'name'          => 'Cursos',
        'singular_name' => 'Curso',
        'menu_name'     => 'Cursos ABC',
        'all_items'     => 'Todos los Cursos',
        'add_new_item'  => 'Añadir Nuevo Curso',
        'edit_item'     => 'Editar Curso',
    ];

    $args = [
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => true,
        'menu_icon'     => 'dashicons-car',
        'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ],
        'show_in_rest'  => true,
    ];

    register_post_type( 'courses', $args );
}
add_action( 'init', 'abc_register_cursos_cpt' );

add_action( 'carbon_fields_register_fields', 'abc_attach_curso_meta' );
function abc_attach_curso_meta() {
    
    Container::make( 'post_meta', 'Detalles Comerciales y Técnicos' )
        ->where( 'post_type', '=', 'courses' )
        ->set_context( 'normal' )
        ->set_priority( 'high' ) 
        ->add_fields( array(
            Field::make( 'text', 'abc_precio_normal', 'Precio Normal ($)' )
                ->set_attribute( 'type', 'number' )
                ->set_width( 50 ),
            Field::make( 'text', 'abc_precio_oferta', 'Precio Oferta ($)' )
                ->set_attribute( 'type', 'number' )
                ->set_width( 50 ),
            Field::make( 'text', 'abc_resolucion_texto', 'Nombre del documento' )
                ->set_help_text( 'Ej: Resolución 3462/2020...' )
                ->set_width( 50 ),
            Field::make( 'file', 'abc_resolucion_pdf', 'PDF de la Resolución' )
                ->set_value_type( 'url' )
                ->set_width( 25 ),
            Field::make( 'file', 'abc_pdf_requisitos', 'PDF de Requisitos' )
                ->set_value_type( 'url' )
                ->set_width( 25 ),
            Field::make( 'complex', 'abc_descargas', 'Archivos de Descarga Adicionales' )
                ->set_layout( 'tabbed-horizontal' )
                ->add_fields( array(
                    Field::make( 'text', 'titulo', 'Título del Enlace' )
                        ->set_width( 50 ),
                    Field::make( 'file', 'archivo', 'Archivo PDF' )->set_value_type( 'url' )
                        ->set_width( 50 ),
                ) ),
        ) );

    Container::make( 'post_meta', 'Configuración Adicional' )
        ->where( 'post_type', '=', 'courses' )
        ->set_context( 'side' )
        ->set_priority( 'low' )
        ->add_fields( array(
            Field::make( 'image', 'abc_img_secundaria', 'Imagen Secundaria' )
                ->set_value_type( 'url' )
                ->set_help_text( 'Imagen de apoyo para la vista individual.' ),
            Field::make( 'text', 'abc_woo_id', 'ID WooCommerce' )
                ->set_attribute( 'type', 'number' )
                ->set_help_text( 'ID del producto para pasarela de pago.' ),
        ) );

    // Panel global: Opciones de la Escuela
    Container::make( 'theme_options', 'Opciones de la Escuela' )
        ->set_icon( 'dashicons-building' )
        ->add_fields( array(
            Field::make( 'media_gallery', 'abc_fotos_alumnos', 'Fotografías de Alumnos Aprobados' )
                ->set_type( array( 'image' ) )
                ->set_help_text( 'Sube aquí las fotos de los alumnos con los vehículos. Aparecerán en el carrusel de la página de inicio.' )
        ) );
}