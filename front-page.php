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

// Simulamos la respuesta estructurada de la API de Google Places
// Una vez que tengas tu API Key, reemplazaremos este array por la llamada wp_remote_get()
$context['google_reviews'] = [
    [
        'author_name' => 'María José Soto',
        'profile_photo_url' => 'https://ui-avatars.com/api/?name=Maria+Jose&background=random',
        'rating' => 5,
        'text' => 'Excelente experiencia. Los instructores tienen muchísima paciencia y los autos están impecables. Saqué mi licencia a la primera gracias a sus consejos en la ruta.',
        'relative_time_description' => 'hace 2 semanas'
    ],
    [
        'author_name' => 'Carlos Valenzuela',
        'profile_photo_url' => 'https://ui-avatars.com/api/?name=Carlos+Valenzuela&background=random',
        'rating' => 5,
        'text' => 'El laboratorio psicotécnico me ayudó mucho a perder los nervios antes del examen municipal. Totalmente recomendados para la licencia profesional.',
        'relative_time_description' => 'hace 1 mes'
    ],
    [
        'author_name' => 'Francisca Lagos',
        'profile_photo_url' => 'https://ui-avatars.com/api/?name=Francisca+Lagos&background=random',
        'rating' => 5,
        'text' => 'Muy profesionales desde el primer día de clases teóricas. La profesora explica todo súper claro y los horarios son muy flexibles.',
        'relative_time_description' => 'hace 3 meses'
    ]
];

Timber::render( 'templates/pages/front-page.twig', $context );