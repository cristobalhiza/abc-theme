<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$composer_autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $composer_autoload ) ) {
    require_once $composer_autoload;
} else {
    wp_die('Error fatal: Falta el directorio vendor en el tema. Ejecuta composer install.');
}

Timber\Timber::init();

require_once __DIR__ . '/includes/timber.php';
require_once __DIR__ . '/includes/setup.php';
require_once __DIR__ . '/includes/cpt.php';