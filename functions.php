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
	// Localization
	// load_theme_textdomain( txtdmn, THEMEDIR .'langs' );

	add_action( 'wp_before_admin_bar_render', function(){
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('wp-logo');
	}, 0 );

	global $content_width;
	if ( ! isset( $content_width ) ) $content_width = 1320;
	

	add_theme_support('title-tag');
	// add_theme_support( 'align-wide' );
	// add_theme_support( 'custom-units' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );

	register_nav_menus([
		'primary'		=> __('Menu'),
	]);


	//Widgets
	add_action('widgets_init', function() {
		register_sidebar([
			'id'			=> 'sidebar',
			'name'			=> __('Sidebar'),
			'before_widget'	=> '<section id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</section>',
			'before_title'	=> '<div role="heading" aria-level="4" class="text-lg mb-4 widget-title">',
			'after_title'	=> '</div>',
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
		if ( isset($widget_opt[$widget_num]['classes']) && !empty($widget_opt[$widget_num]['classes']) )
			$params[0]['before_widget'] = preg_replace( '/class="/', "class=\"{$widget_opt[$widget_num]['classes']} ", $params[0]['before_widget'], 1 );
		return $params;
	});

	//Enqueue scripts
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'index_rel_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 
	remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	

	//Disable Block Theme
	add_theme_support( 'disable-custom-gradients' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

	add_action( 'wp_enqueue_scripts', function() {
		if( ! is_admin() ) {
			wp_deregister_script( 'jquery' );
			wp_enqueue_script( 'jquery', THEMEURL.'assets/js/vendor/jquery.min.js', [], '3.7.1', false );
		}
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );

		wp_register_style( 'splide', THEMEURL.'assets/css/splide.min.css', [], '4.1.3');
		wp_register_script( 'splide', THEMEURL.'assets/js/vendor/splide.min.js', [], '4.1.3', true );
		if ( comments_open() ) wp_enqueue_script('comment-reply');

		wp_enqueue_style( 'stylesheet', THEMEURL.'assets/css/stylesheet.css' );
	}, 99 );

	add_action( 'wp_head', function(){
		//TODO:Preload Font
		// printf(
		// 	'<link rel="prefetch" as="font" href="%s" type="%s" crossorigin="anonymous">',
		// 	THEMEURL .'assets/media/FONTNAME.woff2',
		// 	'font/woff2',
		// );
		echo PHP_EOL;
	}, 5 );

	add_filter( 'emoji_svg_url', '__return_false' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );

	add_filter( 'body_class', function( $classes ) {
		$classes = [];
		if( is_404() ) $classes[] = 'error404';
		$classes[] = ( is_rtl() ) ? 'rtl' : 'ltr';
		if( is_admin_bar_showing() ) $classes[] = 'admin_bar';
		return $classes;
	}, 99 );

	add_filter('header_class', function( $classes ) {
		$classes = [];
		return $classes;
	});

	add_action( 'wp_body_open', function(){
		get_template_part('parts/icons');
	}, 99);

	add_action( 'wp_footer', function(){
		printf('<script id="themejs" src="%s" data-ajax="%s" defer></script>', THEMEURL. 'assets/js/script.min.js', admin_url('admin-ajax.php') );
	}, 99);

	//Archive and Loop
	add_filter( 'max_srcset_image_width', '__return_false' );
	add_filter( 'wp_calculate_image_srcset', '__return_false' );
	add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );


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

	//includes
	foreach (glob(THEMEDIR.'inc/*.php') as $file) require_once $file;

	//Arvand Panel Style
	add_action( 'admin_enqueue_scripts',function(){
		wp_enqueue_style( 'arvandpanel', THEMEURL . 'assets/css/arvand-admin.css', false, '1.0.0' );
	});
	add_action( 'login_enqueue_scripts', function(){
		wp_enqueue_style( 'arvandpanel', THEMEURL . 'assets/css/arvand-admin.css', false, '1.0.0' );
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
		$notice = '';

		if( isset( $_POST['cfsubmit'] ) ) {
			try {
				$name		= isset( $_POST['cfName'] ) ? sanitize_text_field( $_POST['cfName'] ) : '';
				$phone		= isset( $_POST['cfPhone'] ) ? sanitize_text_field( $_POST['cfPhone'] ) : '';
				$subject	= isset( $_POST['cfSubject'] ) ? sanitize_text_field( $_POST['cfSubject'] ) : '';
				$email		= isset( $_POST['cfEmail'] ) ? sanitize_email( $_POST['cfEmail'] ) : '';
				$message	= isset( $_POST['cfMessage'] ) ? sanitize_textarea_field( $_POST['cfMessage'] ) : '';
				if( ! wp_verify_nonce( $_POST['cfsubmit'], 'cfsubmit' ) || ( isset($_POST['cfSecux']) && ! empty( $_POST['cfSecux']) ) ) {
					throw new Exception( 'به دلایل امنیتی ارسال پیام امکان پذیر نیست!' );
				}
				if( empty($name) || empty($phone)|| empty($subject) || empty($subject) || empty($message) ){
					throw new Exception( 'فیلد های ضروری را تکمیل کنید!' );
				}

				if( ! empty($email) && ! is_email( $email ) ){
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
				$mailbody = str_replace('{{footer}}', sprintf('<p>در تاریخ <span dir="ltr">%s</span> با شناسه <span dir="ltr">%s</span></p>', current_time('Y-m-d H:i'), 'IP' ), $mailbody);
				$sent = wp_mail( $atts['to'], $subject, $mailbody, $headers );
				if( $sent ){
					$notice = sprintf('<p class="text-green-700" role="alert">%s</p>', 'پیام شما باموفقیت ارسال شد.');
				} else {
					throw new Exception( 'ارسال پیام، شکست خورد! اشکال از سمت سایت می‌باشد' );
				}
			} catch (Exception $e) {
				$notice = sprintf('<p class="text-red-600" role="alert">%s</p>', $e->getMessage() );
			}
		}

		ob_start();
		get_template_part( 'parts/contactus', 'form', ['notice' => $notice]);
		return ob_get_clean();
	});

	//Plugins Compatibility
	add_theme_support( 'rank-math-breadcrumbs' );

	//Remove WP ParsiDate Dashobard
	if( function_exists('wpp_add_our_dashboard_primary_widget') ){
		remove_action( 'admin_init', 'wpp_add_our_dashboard_primary_widget', 1 );
	}

	//Arvand Panel Style
	add_action( 'admin_enqueue_scripts',function(){
		wp_enqueue_style( 'arvandpanel', THEMEURL . 'assets/css/arvand-admin.css', false, '1.0.0' );
	});
	add_action( 'login_enqueue_scripts', function(){
		wp_enqueue_style( 'arvandpanel', THEMEURL . 'assets/css/arvand-admin.css', false, '1.0.0' );
	});
	add_filter( 'login_headerurl', function() {
		return home_url();
	});
	add_filter('admin_footer_text', function() {
		return 'توسعه داده شده توسط <a href="https://arvandec.com" target="_blank">آژانس دیجیتال مارکتینگ آروند</a> بر بستر <a href="https://wordpress.org" target="_blank">وردپرس</a>.';
	});

});

//------Core Functions
if( ! function_exists('console_log') ){
	function console_log() {
		if( ! func_get_args() ) return;
		foreach( func_get_args() as $log ){
			if( class_exists('QM') && ! DOING_AJAX ){
				do_action( 'qm/debug', $log );
			} else {
				$log_msg = date('c').": ";
				ob_start();
				foreach( func_get_args() as $log ){
					print_r( $log );
					echo PHP_EOL;
				}
				$log_msg .= ob_get_clean();
				error_log( $log_msg );
			}
		}
	}
}

if( ! function_exists('the_breadcrumbs') ){
	function the_breadcrumbs($custom_class = ''){
		$custom_class = sanitize_text_field( $custom_class ); 
		if ( function_exists('yoast_breadcrumb') ) {
			yoast_breadcrumb('<nav id="breadcrumbs" class="'. esc_attr( $custom_class ) .'" aria-label="مسیر راهنما">', '</nav>');
		} elseif( function_exists('rank_math_the_breadcrumbs') ) {
			rank_math_the_breadcrumbs([
				'wrap_before'	=> '<nav id="breadcrumbs" class="'. esc_attr( $custom_class ) .'" aria-label="فهرست راهنما">',
				'wrap_after'	=> '</nav>',
			]);
		} elseif( function_exists('woocommerce_breadcrumb') ){
			woocommerce_breadcrumb([
				'wrap_before'	=> '<nav id="breadcrumbs" class="'. esc_attr( $custom_class ) .'" aria-label="فهرست راهنما">',
				'wrap_after'	=> '</nav>',
			]);
		} else {
			$output = '<nav id="breadcrumbs" class="'. esc_attr( $custom_class ) .'" aria-label="فهرست راهنما">';
			$output .= '</nav>';
			echo $output;
		}
	}
}

if( ! function_exists('filter_min_max_number') ){
	function filter_min_max_number( int $number, int $min, int $max = null ){
		$number = max( $min, $number );
		if( is_null( $max ) ) {
			$number = min( $max, $number );
		}
		return $number;
	}
}

if( ! function_exists('sanitize_number_field') ){
	function sanitize_number_field( $number = '' ){
		$number = sanitize_text_field( $number );
		if( ! $number ) return false;
		$newNumbers = range(0, 9);
		$char_arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
		$number = str_replace($char_arabic, $newNumbers, $number);
		$char_persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
		$number = str_replace($char_persian, $newNumbers, $number);
		return $number;
	}
}

if( ! function_exists('sanitize_phone_ir') ){
	function sanitize_phone_ir( $number = '' ){
		$number = sanitize_number_field( $number );
		if( ! $number ) return false;
		$number = '0'.preg_replace( '/^(?:98|\+98|0098|0)?9/i', '9', $number );
		return preg_match( "/^09[0-9]{9}$/", $number ) ? $number : false;
	}
}

if( ! function_exists('sanitize_natid_ir') ){
	function sanitize_natid_ir( $value = '' ) {
		$value = sanitize_number_field( $value );
		if( ! preg_match('/^[0-9]{10}$/', $value ) ) return false;

		for( $i = 0; $i < 1; $i++ ){
			if(preg_match('/^'.$i.'{10}$/',$value)) return false;
		}

		$sum = 0;
		for( $i = 0;  $i < 9; $i++ ){
			$sum += (10-$i) * intval( substr($value, $i,1) );
			$ret = $sum % 11;
			$parity = intval( substr($value, 9,1) );
			if( ( $ret < 2 && $ret == $parity ) || ( $ret >= 2 && $ret == (11-$parity) ) ){
				return $value;
			}
		}
		return false;
	}
}

if( ! function_exists('get_primary_term') ){
	function get_primary_term( $post_id = null, $taxonomy = 'category' ) {
		$primary = null;
		if( is_null( $post_id ) || empty( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}
		if( class_exists('WPSEO_Primary_Term') ){
			$primary = new WPSEO_Primary_Term( $taxonomy, $post_id );
			$primary = $primary->get_primary_term();
			$primary = get_term( $primary );
		} elseif( $rankterm = get_post_meta( $post_id, 'rank_math_primary_category', true ) ){
			$primary = get_term( $rankterm );
		} else {
			$primary = get_the_terms( $post_id, $taxonomy );
			foreach( $primary as $temp ){
				if ( 0 === $temp->parent ) {
					$primary = $temp;
					break;
				}
			}
			if( is_array( $primary ) ){
				$primary = reset( $primary );
			}
		}

		if( $primary && ! is_wp_error( $primary ) ) {
			$term = new stdClass();
			$term->link = esc_url( get_term_link( $primary ) );
			$term->name = $primary->name;
			$term->term_id = $primary->term_id;
			return $term;
		}

		return false;
	}
}

if( !function_exists('get_estimated_reading') ) {
	function get_estimated_reading( $post_id = null, $wpm = 180 ) {
		if( ! $post_id ) $post_id = get_the_ID();
		
		$content = get_post_field( 'post_content', $post_id );
		$content = strip_shortcodes( $content );
		$content = wp_strip_all_tags( $content );

		$images = substr_count( strtolower( $content ), '<img ' );
		$words = count( preg_split( '/\s+/', $content ) );

		if( $images > 0 ) {
			$imgtime = 0;
			for ( $i = 1; $i <= $images; $i++ ) {
				if ( $i >= 10 ) {
					$imgtime += 3 * intval( $wpm / 60 );
				} else {
					$imgtime += ( 12 - ( $i - 1 ) ) * intval( $wpm / 60 );
				}
			}
			$words += $imgtime;
		}

		if( 0 == $words ) return 0;
		
		return ceil($words / $wpm);
	}
}

if( ! function_exists('placeholder_image') ){
	function placeholder_image( $size = [], $bgcolor = null ){
		if( is_numeric( $size ) ) {
			$size = [$size, $size];
		} elseif( empty( $size ) ) {
			$size = [1, 1];
		}
		if( ! $bgcolor ){
			$bgcolor = 'rgb(234, 239, 253)';
		}

		$svg = sprintf( '<svg viewBox="0 0 %1$s %2$s" width="%1$s" height="%2$s" xmlns="http://www.w3.org/2000/svg"><rect width="100%%" height="100%%" fill="%3$s"/></svg>', $size[0], $size[1], $bgcolor );
		return 'data:image/svg+xml;base64,'.base64_encode( $svg );
	}
}

if( ! function_exists('wp_get_media_image_sizes') ){
	function wp_get_media_image_sizes( $size = '' ) {
		$wp_additional_image_sizes = wp_get_additional_image_sizes();
		$sizes = [];
		$get_intermediate_image_sizes = get_intermediate_image_sizes();
		foreach( $get_intermediate_image_sizes as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			} elseif ( isset( $wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = [
					'width'		=> $wp_additional_image_sizes[ $_size ]['width'],
					'height'	=> $wp_additional_image_sizes[ $_size ]['height'],
				];
			}
		}
		if( $size ) return ( isset( $sizes[ $size ] ) ? $sizes[ $size ] : false );
		return $sizes;
	}
}

if( ! function_exists('the_default_thumbnail') ){
	function get_default_thumbnail( $size = 'thumbnail', $post_id = null, $attr = [] ){
		if( ! $post_id ) $post_id = get_the_ID();
		if( has_post_thumbnail( $post_id ) ){
			return get_the_post_thumbnail( $post_id, $size, $attr );
		} else {
			$attr = wp_parse_args( $attr, [
				'src'		=> null,
				'alt'		=> null,
				'width'		=> 1,
				'height'	=> 1,
				'loading'	=> 'lazy',
				'decoding'	=> 'sync',
				'color'		=> null,
			]);
		
			if( is_numeric( $size ) ){
				$attr['width'] = $size;
				$attr['height'] = $size;
			} elseif( is_string( $size ) ){
				$size = wp_get_media_image_sizes( $size );
				if( $size ){
					reset( $size );
					$attr['width'] = $size['width'];
					$attr['height'] = $size['height'];
				}
			} elseif( is_array( $size ) ){
				$attr['width'] = $size[0];
				$attr['height'] = isset( $size[1] ) ? $size[1] : $size[0];
			}

			$attr['src'] = placeholder_image([$attr['width'], $attr['height']], $attr['color']);
			$attr['alt'] = get_the_title( $post_id );
			unset( $attr['color'] );

			if( isset( $attr['splide'] ) ){
				unset( $attr['splide'] );
			}
			
			$attr = array_map( 'esc_attr', $attr );
			$html = '<img';
			foreach ( $attr as $name => $value ) {
				$html .= " $name=" . '"' . $value . '"';
			}
			$html .= ' />';

			return $html;
		}
	}
	function the_default_thumbnail( $size = 'thumbnail', $attr = [] ){
		echo get_default_thumbnail( $size, null, $attr );
	}
}

if( ! function_exists('get_related_posts') ) {
	function get_related_posts( $numbers = 5, $post_id = null, $terms = ['category', 'post_tag'], $args = [] ){
		if( !$post_id ) $post_id = get_the_ID();

		$args = wp_parse_args( $args, [
			'post_type'			=> get_post_type($post_id),
			'post__not_in'		=> [ $post_id ],
			'posts_per_page'	=> $numbers,
		]);

		if( !empty($terms) ){
			$args['tax_query'] = ['relation' => 'OR'];
			foreach( $terms as $term ) {
				$post_terms = get_the_terms( $post_id, $term );
				$list = [];
				if( $post_terms ){
					foreach( $post_terms as $value) $list[] = $value->term_id;
					unset($post_terms);
					$args['tax_query'][] = [
						'taxonomy'	=> $term,
						'terms'		=> $list
					];
				}
			}
		}

		return new WP_Query( $args );
	}
}

if( ! function_exists('the_pagination') ){
	function the_pagination( $args = [], $query = null ){
		if( ! $query ) {
			global $wp_query;
			$query = $wp_query;
		}

		$default = [
			'args'			=> null,
			'mid_size'		=> 1,
			'end_size'		=> 2,
			'total'			=> $query->max_num_pages,
			'paged'			=> get_query_var('paged'),
			'prev_text'		=> is_rtl() ? '<svg width="16" height="16"><use xlink:href="#icon-arrow-start"></use></svg>' : '<svg width="16" height="16"><use xlink:href="#icon-arrow-end"></use></svg>',
			'next_text'		=> is_rtl() ? '<svg width="16" height="16"><use xlink:href="#icon-arrow-end"></use></svg>' : '<svg width="16" height="16"><use xlink:href="#icon-arrow-start"></use></svg>',
			'before_tag'	=> '<nav class="pagination">',
			'after_tag'		=> '</nav>',
		];

		$args = wp_parse_args( $args, $default );

		if( $args['total'] > 1 ){
			$big = 999999999;
			printf(
				"%s\n\t%s\n%s", 
				$args['before_tag'],
				paginate_links([
					'total'			=> intval( $args['total'] ),
					'format'		=> '?paged=%#%',
					'current'		=> max( 1, $args['paged']),
					'mid_size'		=> absint($args['mid_size']),
					'end_size'		=> absint($args['end_size']),
					'type'			=> 'plain',
					'prev_text'		=> $args['prev_text'],
					'next_text'		=> $args['next_text'],
					'add_args'		=> $args['args'],
					'add_fragment'	=> '',
					'base'			=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				]),
				$args['after_tag']
			);
		}
	}
}

//------Theme Functions
function enqueue_splidejs(){
	wp_enqueue_style( 'splide' );
	wp_enqueue_script( 'splide' );
}

function header_class(){
	$classes = array_unique( apply_filters( 'header_class', [] ) );
	if( !empty( $classes ) ){
		printf(' class="%s"', esc_attr( implode(' ', $classes) ) );
	}
}

function the_logo( $size = [] ){
	if( has_custom_logo() ){
		the_custom_logo();
	} else {
		$width = isset( $size[0] ) ? absint( $size[0] ) : 98;
		$height = isset( $size[1] ) ? absint( $size[1] ) : 32;
		$logo = sprintf('<img src="%s" title="%s" class="custom-logo" width="%s" height="%s" />', THEMEURL.'assets/media/logo.svg', get_bloginfo('name'), esc_attr( $width ), esc_attr( $height ) );
		$aria_current = is_front_page() && ! is_paged() ? ' aria-current="page"' : '';
		printf('<a href="%1$s" class="custom-logo-link" rel="home"%2$s>%3$s</a>', esc_url( home_url( '/' ) ), $aria_current, $logo);
	}
}
