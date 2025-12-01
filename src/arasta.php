<?php

class ArastaSystem{
	const menuslug = 'arasta';
	public function __construct(){
		add_action( 'init', [$this, 'initfn'], 0 );
		add_action( 'admin_menu', [$this, 'register_menu'], 9 );
	}

	public function initfn(){
		register_post_type('beauty', [
			'label'					=> 'سالن‌ها',
			'labels'				=> [
				'name'			=> 'سالن‌ها',
				'singular_name'	=> 'سالن',
			],
			'show_ui'				=> true,
			'supports'				=> ['title', 'editor'],
			'hierarchical'			=> false,
			'has_archive'			=> true,
			'show_in_menu'			=> self::menuslug,
			'show_in_admin_bar'		=> true,
			'show_in_nav_menus'		=> false,
			'publicly_queryable'	=> true,
			'capability_type'		=> 'page',
			'rewrite'				=> [
				'feeds'				=> false,
				'with_front'		=> false,
			]
		]);
		register_taxonomy('beauty_services', ['beauty'], [
			'labels'	=> [
				'name'				=> 'خدمات زیبایی',
				'singular_name'		=> 'خدمت',
			],
			'hierarchical'			=> true,
			'public'				=> true,
			'show_ui'				=> true,
			'show_admin_column'		=> true,
			'show_in_nav_menus'		=> true,
			'show_tagcloud'			=> true,
		]);
	}
	
	public function register_menu(){
		add_menu_page('پلتفرم آراستا', 'آراستا', 'manage_options', self::menuslug, [$this, 'display_certificates_callback'], 'dashicons-awards', 2);
		add_submenu_page(self::menuslug, 'تقویم رزرو', 'تقویم رزرو', 'manage_options', 'calendar', [$this, 'display_calendar']);
		add_submenu_page(self::menuslug, null, 'خدمات زیبایی', 'manage_options', 'edit-tags.php?taxonomy=beauty_services&post_type=beauty', false);
	}

	public function display_certificates_callback(){
		echo 'HI';
	}

	public function display_calendar(){
		add_thickbox();
		include_once THEMEDIR.'parts/admin/calendar.php';
	}
}

new ArastaSystem();