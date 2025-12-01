<?php

defined('ABSPATH') || exit;

add_action( 'customize_register', function( $customizer ) {
	//TODO: Add Customizer
	// $customizer->add_setting('testimonials');
	// $customizer->add_control('testimonials', [
	// 	'label'			=> 'نظرات منتخب کاربران',
	// 	'description'	=> 'شناسه دیدگاه ها را با | جدا کنید.',
	// 	'type'			=> 'text',
	// 	'section'		=> 'static_front_page',
	// 	'input_attrs'	=> [
	// 		'style'		=> 'direction:ltr',
	// 	],
	// ]);

	// $customizer->add_setting('hero_image', [
	// 	'default'			=> THEMEURL.'assets/media/herosection.webp',
	// 	'capability'		=> 'edit_theme_options',
	// 	'sanitize_callback' => function( $file, $setting ){
	// 		$mimes = [
	// 			'jpg|jpeg|jpe'	=> 'image/jpeg',
	// 			'gif'			=> 'image/gif',
	// 			'png'			=> 'image/png',
	// 		];

	// 		$file_ext = wp_check_filetype( $file, $mimes );
	// 		return ( $file_ext['ext'] ? $file : $setting->default );
	// 	},
	// ]);
});