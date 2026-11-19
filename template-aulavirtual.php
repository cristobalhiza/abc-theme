<?php
/**
 * Template Name: Aula Virtual
 *
 * Aviso temporal mientras el aula virtual (Moodle) está en mantenimiento.
 */

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('templates/pages/template-aulavirtual.twig', $context);
