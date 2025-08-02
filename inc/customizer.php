<?php

defined('ABSPATH') || exit;

require_once THEMEDIR . '/inc/kirki/kirki.php';

add_filter( 'kirki_settings_page', '__return_false' );

Kirki::add_config( 'theme_config', [
	'capability'  => 'edit_theme_options',
	'option_type' => 'theme_mod',
]);

// Kirki::add_field( 'theme_config', [
// 	//options
// ]);