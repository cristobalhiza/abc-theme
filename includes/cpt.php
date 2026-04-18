<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Registro de Custom Post Types
 */
function abc_register_cursos_cpt()
{
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
        'rewrite'       => ['slug' => 'cursos'],
        'menu_icon'     => 'dashicons-car',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'show_in_rest'  => true,
    ];

    register_post_type('courses', $args);
}
add_action('init', 'abc_register_cursos_cpt');

/**
 * Registro de Campos Personalizados (Carbon Fields)
 */
function abc_attach_curso_meta()
{
    Container::make('post_meta', 'Detalles Comerciales y Técnicos')
        ->where('post_type', '=', 'courses')
        ->set_context('normal')
        ->set_priority('high')
        ->add_fields(array(
            Field::make('text', 'abc_precio_normal', 'Precio Normal ($)')
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('text', 'abc_precio_oferta', 'Precio Oferta ($)')
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('rich_text', 'abc_objetivos', 'Objetivos del Curso'),
            Field::make('complex', 'abc_resoluciones', 'Resoluciones y Certificaciones')
                ->set_layout('tabbed-horizontal')
                ->setup_labels(array(
                    'plural_name' => 'Documentos',
                    'singular_name' => 'Documento',
                ))
                ->add_fields(array(
                    Field::make('text', 'titulo', 'Nombre del Documento')
                        ->set_width(50),
                    Field::make('file', 'archivo', 'Archivo PDF')
                        ->set_value_type('url')
                        ->set_width(50),
                )),
            Field::make('rich_text', 'abc_requisitos', 'Requisitos del Curso'),
            Field::make('complex', 'abc_programa', 'Programa del Curso')
                ->set_layout('tabbed-vertical')
                ->setup_labels(array(
                    'plural_name' => 'Módulos',
                    'singular_name' => 'Módulo',
                ))
                ->set_collapsed(true)
                ->add_fields(array(
                    Field::make('text', 'etiqueta', 'Etiqueta')
                        ->set_width(30),
                    Field::make('text', 'contenido', 'Contenido / Detalle')
                        ->set_width(70),
                )),
            Field::make('complex', 'abc_descargas', 'Archivos de Descarga Adicionales')
                ->set_layout('tabbed-horizontal')
                ->add_fields(array(
                    Field::make('text', 'titulo', 'Título del Enlace')
                        ->set_width(50),
                    Field::make('file', 'archivo', 'Archivo PDF')->set_value_type('url')
                        ->set_width(50),
                )),
        ));

    Container::make('post_meta', 'Configuración Adicional')
        ->where('post_type', '=', 'courses')
        ->set_context('side')
        ->set_priority('low')
        ->add_fields(array(
            Field::make('image', 'abc_img_secundaria', 'Imagen Secundaria')
                ->set_value_type('url'),
            Field::make('text', 'abc_woo_id', 'ID WooCommerce')
                ->set_attribute('type', 'number'),
        ));

    Container::make('theme_options', 'Opciones de la Escuela')
        ->set_icon('dashicons-building')
        ->add_fields(array(
            Field::make('header_import', 'config_contacto_header', 'Configuración de Contacto'),
            Field::make('text', 'abc_email_destino', 'Email de Destino')
                ->set_help_text('A este correo llegarán las consultas del formulario.')
                ->set_attribute('placeholder', 'ejemplo@abcconduccion.cl')
                ->set_width(50),
            Field::make('text', 'abc_nombre_remitente', 'Nombre del Remitente')
                ->set_help_text('Nombre que aparecerá como emisor del correo.')
                ->set_attribute('placeholder', 'ABC Escuela de Conductores')
                ->set_width(50),
            
            Field::make('header_import', 'config_aprobados_header', 'Galería de Alumnos'),
            Field::make('media_gallery', 'abc_fotos_alumnos', 'Fotografías de Alumnos Aprobados')
                ->set_type(array('image'))
        ));
}
add_action('carbon_fields_register_fields', 'abc_attach_curso_meta');
