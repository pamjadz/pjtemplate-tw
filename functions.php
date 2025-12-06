<?php
/**
 * Core Functions
 *
 * @author 	Pouriya Amjadzadeh
 * @version 3.0.0
 * @package https://arvandec.com
 */

defined('ABSPATH') || exit;

//TODO: Renmate txtdmn
define( 'txtdmn', 'txtdmn' );
define( 'THEMEDIR', trailingslashit( get_template_directory() ) );
define( 'THEMEURL', trailingslashit( get_template_directory_uri() ) );

add_action( 'after_switch_theme', function(){
	update_option('thumbnail_size_w', 0);
	update_option('thumbnail_size_h', 0);
	update_option('thumbnail_crop', true);
	update_option('medium_size_w', 0);
	update_option('medium_size_h', 0);
	update_option('medium_crop', true);

	update_option('medium_large_size_w', 0);
	update_option('medium_large_size_h', 0);

	update_option('large_size_w', 0);
	update_option('large_size_h', 0);
	update_option('large_crop', true);

});

add_action( 'after_setup_theme', function(){
	global $content_width;
	$content_width = 1440;

	//Theme Supports
	add_theme_support('title-tag');
	// add_theme_support( 'align-wide' );
	// add_theme_support( 'custom-units' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );

	add_action( 'wp_before_admin_bar_render', function(){
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('wp-logo');
	}, 0 );

	//wp_head Cleanup
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'rsd_link');
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action('template_redirect', 'rest_output_link_header', 11);

	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');

	// remove_action('wp_head', 'rel_canonical');
	remove_action( 'wp_head', 'index_rel_link' );
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	add_filter('emoji_svg_url', '__return_false');
	add_filter('wp_img_tag_add_auto_sizes', '__return_false');

	//Disable Block Theme
	add_theme_support( 'disable-custom-gradients' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

	add_action( 'wp_enqueue_scripts', function() {
		if( ! is_admin() ) {
			wp_deregister_script( 'jquery' );
			wp_enqueue_script( 'jquery', THEMEURL.'assets/libs/jquery.min.js', [], '3.7.1', [
				'in_footer'	=> false,
			]);

			wp_dequeue_style('wp-block-library');
			wp_dequeue_style('wp-block-library-theme');
			wp_dequeue_style('global-styles');
		}

		wp_register_style( 'splide', THEMEURL.'assets/libs/splide/splide-core.min.css', [], '4.1.3');
		wp_register_script( 'splide', THEMEURL.'assets/libs/splide/splide.min.js', [], '4.1.3', [
			'strategy'	=> 'defer',
			'in_footer'	=> true,
		]);

		if ( comments_open() ) wp_enqueue_script('comment-reply');

		wp_enqueue_style( 'stylesheet', THEMEURL.'assets/stylesheet.css' );
	}, 99 );

	add_filter( 'body_class', function( $classes ) {
		$classes = [];
		//Classes HERE
		if( is_404() ) $classes[] = 'error404';
		$classes[] = ( is_rtl() ) ? 'rtl' : 'ltr';
		if( is_admin_bar_showing() ) $classes[] = 'admin_bar';
		return $classes;
	}, 99 );

	add_filter('header_class', function( $classes ) {
		return $classes;
	});

	add_action( 'wp_body_open', function(){
		get_template_part('parts/icons');
		echo PHP_EOL;
	}, 99);

	add_action( 'wp_footer', function() {
		printf('<script id="themejs" src="%s" data-ajax="%s" defer></script>', THEMEURL. 'assets/script.min.js', admin_url('admin-ajax.php') );
		echo PHP_EOL;
	}, 99);

	register_nav_menus([
		'primary'	=> __('Menu'),
	]);

	//Archive and Loop
	add_filter( 'max_srcset_image_width', '__return_false' );
	add_filter( 'wp_calculate_image_srcset', '__return_false' );
	add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

	//Widgets
	add_action('widgets_init', function() {
		register_sidebar([
			'id'			=> 'sidebar',
			'name'			=> __('Sidebar'),
			'before_widget'	=> '<section id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</section>',
			'before_title'	=> '<h4 class="widget-title">',
			'after_title'	=> '</h4>',
		]);
	});
	
	//Classic Widgets
	add_filter( 'use_widgets_block_editor', '__return_false' );
	add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );

	//Add Custom Class Widgets
	add_filter('widget_form_callback', function( $instance, $widget ) {
		printf('<p><label for="%1$s">CSS Classes</label><input id="%1$s" type="text" name="%2$s" dir="ltr" value="%3$s" class="widefat"></p>', $widget->get_field_id('classes'), $widget->get_field_name('classes'), ( isset($instance['classes']) ? $instance['classes'] : null ));
		return $instance;
	}, 10, 2);
	add_filter( 'widget_update_callback', function( $instance, $new_instance ) {
		$instance['classes'] = $new_instance['classes'];
		return $instance;
	}, 10, 2);
	add_filter( 'dynamic_sidebar_params', function( $params ) {
		global $wp_registered_widgets;
		$widget_id	= $params[0]['widget_id'];
		$widget_obj	= $wp_registered_widgets[$widget_id];
		$widget_opt	= get_option($widget_obj['callback'][0]->option_name);
		$widget_num    = $widget_obj['params'][0]['number'];    
		if ( isset($widget_opt[$widget_num]['classes']) && !empty($widget_opt[$widget_num]['classes']) ) {
			$params[0]['before_widget'] = preg_replace( '/class="/', "class=\"{$widget_opt[$widget_num]['classes']} ", $params[0]['before_widget'], 1 );
		}
		return $params;
	});

	//Newest Users Registered First
	add_action( 'pre_get_users', function($query){
		$query->query_vars['orderby'] = 'user_registered';
		$query->query_vars['order'] = 'DESC';
	});
	add_filter( 'manage_users_columns', function($columns){
		$columns['registration_date'] = 'تاریخ عضویت';
		return $columns;
	});
	add_filter( 'manage_users_custom_column', function($row_output, $column_id_attr, $user) {
		if( $column_id_attr == 'registration_date' ){
			return sprintf('<span dir="ltr">%s</span>', date_i18n( 'Y-m-d H:i', strtotime( get_the_author_meta( 'registered', $user ) ) ) );
		}
		return $row_output;
	}, 10, 3);
	add_filter( 'pre_get_avatar', function( $avatar, $id_or_email, $args ) {
		$user = false;
		if( $id_or_email ){
			if( is_numeric( $id_or_email ) ){
				$user = get_user_by('id' , $id_or_email);
			} elseif( is_object($id_or_email) ) {
				if( isset( $id_or_email->user_id ) ) {
					$user = get_user_by( 'id' , $id_or_email->user_id );
				} else {
					$user = false;
				}
			} elseif( is_email($id_or_email) ) {
				$user = get_user_by('email', $id_or_email );   
			}
		}

		$avatar_src = '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 24 24"><path fill="#fafafa" d="M0 0h24v24H0z"/><path fill="#4d1120" d="M12 5.5c-1.7 0-3 1.3-3 3s1.3 3.5 3 3.5 3-2 3-3.6-1.3-2.9-3-2.9m4.4 13.2c1-.7 1.3-2 .6-3a6 6 0 0 0-5-2.7 6 6 0 0 0-5 2.6c-.7 1-.4 2.4.6 3A8 8 0 0 0 12 20a8 8 0 0 0 4.4-1.3"/></svg>';
		$avatar_src = 'data:image/svg+xml;base64,'.base64_encode( $avatar_src );

		//TODO: if want localavatar
		// if( $user && get_user_meta( $user->ID, 'localavatar', true ) ){
		// 	$custom_avatar_id = absint( get_user_meta( $user->ID, 'localavatar', true ) );
		// 	if( $custom_avatar_id ) {
		// 		$custom_avatar_src = wp_get_attachment_image_url( $custom_avatar_id, 'full' );
		// 		if( $custom_avatar_src ) {
		// 			$avatar_src = $custom_avatar_src;
		// 		}
		// 	}
		// }

		$class = $args['class'] ?? '';
		$attrs = [
			'src'           => $avatar_src,
			'alt'           => $args['alt'] ?? '',
			'width'         => $args['width'] ?? '',
			'height'        => $args['height'] ?? '',
			'class'         => is_array( $class ) ? $class : [ $class ],
			'loading'       => $args['loading'] ?? 'lazy',
			'fetchpriority' => $args['fetchpriority'] ?? '',
			'decoding'      => $args['decoding'] ?? '',
		];

		$attrs['class'] = array_merge( ["avatar", "avatar-{$args['size']}"], $attrs['class']);
		$attrs['class'] = implode(' ', array_filter( $attrs['class'] ));

		if( isset( $args['extra_attr'] ) && is_array( $args['extra_attr'] ) ) {
			$extra_attrs = array_filter($args['extra_attr'], fn( $value ) => $value !== null);
			$attrs = array_merge( $attrs, $extra_attrs );
		}

		$html_attrs = implode(' ', array_filter(array_map(
			function($k, $v) {
				if( $v === null || $v === '' || $v === false ) {
					return '';
				}
				if( $v === 0 || $v === '0' ) {
					return $k . '="' . esc_attr($v) . '"';
				}
				return $k . '="' . esc_attr($v) . '"';
			},
			array_keys($attrs),
			$attrs
		)));

		return "<img {$html_attrs}>";
	} , 10, 3);

	//includes
	foreach (glob(THEMEDIR.'src/inc/*.php') as $file) require_once $file;

	//Arvand Panel Style
	add_action( 'admin_enqueue_scripts',function(){
		wp_enqueue_style( 'arvandpanel', THEMEURL . 'assets/arvand-admin.css', false, '1.0.0' );
	});
	add_action( 'login_enqueue_scripts', function(){
		wp_enqueue_style( 'arvandpanel', THEMEURL . 'assets/arvand-admin.css', false, '1.0.0' );
	});
	add_filter( 'login_headerurl', function() {
		return home_url();
	});
	add_filter('admin_footer_text', function() {
		return 'توسعه داده شده توسط <a href="https://arvandec.com" target="_blank">آژانس دیجیتال مارکتینگ آروند</a> بر بستر <a href="https://wordpress.org" target="_blank">وردپرس</a>.';
	});

	//Custom Contact Form
	add_shortcode( 'arvand_contact_form', function( $atts ) {
		$atts = shortcode_atts(['to' => get_option('admin_email')], $atts);
		ob_start();
		get_template_part( 'parts/contactus', 'form' );
		return ob_get_clean();
	});
	add_action('wp_ajax_arvand_contact_form', 'arvand_contact_form_cb');
	add_action('wp_ajax_nopriv_arvand_contact_form', 'arvand_contact_form_cb');
	function arvand_contact_form_cb() {
		$form_data = isset( $_POST['form_data'] ) ? json_decode( stripslashes( $_POST['form_data'] ), true) : [];
		$output = [ 'success' => false, 'message' => null ];
		try {
			$name		= sanitize_text_field( $form_data['arvcfName'] ?? '' );
			$phone		= sanitize_text_field( $form_data['arvcfPhone'] ?? '' );
			$email		= sanitize_email( $form_data['arvcfEmail'] ?? '' );
			$subject	= sanitize_text_field( $form_data['arvcfSubject'] ?? '' );
			$message	= sanitize_textarea_field( $form_data['arvcfMessage'] ?? '' );
			
			if( ! wp_verify_nonce( $form_data['arvand-contactform'], 'arvand-contactform' ) || ( isset($form_data['arvcfSecux']) && ! empty( $form_data['arvcfSecux']) ) ) {
				throw new Exception( 'به دلایل امنیتی ارسال پیام امکان پذیر نیست!' );
			}

			if( empty( $name ) || empty( $phone )|| empty( $subject ) || empty( $message ) ){
				throw new Exception( 'فیلد های ضروری را تکمیل کنید!' );
			}

			if( ! empty( $email ) && ! is_email( $email ) ) {
				throw new Exception( 'ایمیل وارد شده صحیح نمی‌باشد' );
			}

			$headers = ['Content-Type: text/html; charset=UTF-8'];
			if( $email ) {
				$headers[] = sprintf('Reply-To: %s <%s>', $name, $email);
			}

			ob_start();
			get_template_part( 'parts/contactus', 'email' );
			$mailbody = ob_get_clean();
			$mailbody = str_replace('{{title}}', $subject, $mailbody);
			$message = sprintf('<p>فرستنده: <strong>%s</strong></p><p>شماره تماس: <strong>%s</strong></p> %s', $name, $phone, wpautop($message) );
			$mailbody = str_replace('{{content}}', $message, $mailbody);
			$mailbody = str_replace('{{footer}}', sprintf('<p>در تاریخ <span dir="ltr">%s</span> با شناسه <span dir="ltr">%s</span></p>', current_time('Y-m-d H:i'), get_user_ip() ), $mailbody);
			$sent = wp_mail( $atts['to'], $subject, $mailbody, $headers );
			if( $sent ){
				$output['success'] = true;
				$output['message'] = 'پیام شما باموفقیت ارسال شد.';
			} else {
				throw new Exception( 'ارسال پیام، شکست خورد! اشکال از سمت سایت می‌باشد' );
			}
		} catch (Exception $e) {
			$output['message'] = $e->getMessage();
		}
		wp_send_json( $output );
	}

	//Plugins Compatibility
	add_theme_support( 'rank-math-breadcrumbs' );

	//Remove WP ParsiDate Dashobard
	if( function_exists('wpp_add_our_dashboard_primary_widget') ){
		remove_action( 'admin_init', 'wpp_add_our_dashboard_primary_widget', 1 );
	}

});

//------Theme Functions
function enqueue_splidejs(){
	wp_enqueue_style('splide');
	wp_enqueue_script('splide');
}

function header_class( string|array $custom_class = '' ){
	$classes = array_unique( apply_filters( 'header_class', (array) $custom_class ) );
	if( ! empty( $classes ) ){
		printf(' class="%s"', esc_attr( implode(' ', $classes) ) );
	}
}

function the_logo( $size, bool $link = true ){
	if( has_custom_logo() ){
		the_custom_logo();
	} else {
		if( is_numeric( $isze ) ){
			$width = $size;
			$height = $size * 0.125;
		} elseif( is_array( $size ) ){
			$width = $size[0];
			$height = $size[1];
		}

		$logo = sprintf('<img src="%s" title="%s" class="custom-logo" width="%s" height="%s" />', THEMEURL.'assets/media/logo.svg', get_bloginfo( 'name', 'display' ), esc_attr( $width ), esc_attr( $height ) );
		if( $link ){
			$aria_current = is_front_page() && ! is_paged() ? ' aria-current="page"' : '';
			printf('<a href="%1$s" class="custom-logo-link" rel="home"%2$s>%3$s</a>', esc_url( home_url( '/' ) ), $aria_current, $logo);
		} else {
			echo $logo;
		}
		
	}
}

