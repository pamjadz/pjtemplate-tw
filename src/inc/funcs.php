<?php

defined('ABSPATH') || exit;

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

if( ! function_exists('get_user_ip') ) {
	function get_user_ip() {
		$server_keys = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR', 
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		];
		foreach ($server_keys as $key) {
			if (!empty($_SERVER[$key])) {
				$ip = $_SERVER[$key];
				$ip = explode(',', $ip)[0];
				$ip = trim($ip);
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
					return sanitize_text_field($ip);
				}
			}
		}
		return '127.0.0.1';
	}
}

if( ! function_exists('has_user_role') ) {
	function has_user_role(string|array $role, $user_id = null ){
		$user = $user_id ? get_user_by( 'ID', $user_id ) : wp_get_current_user();
		if( ! $user ) return false;
		return array_intersect( (array) $role, (array) $user->roles );
	}
}

if( ! function_exists('clsx') ) {
	function clsx($condition, $true_class, $false_class = ''){
		return $condition ? esc_attr( $true_class ) : esc_attr( $false_class );
	}
}

if( ! function_exists('wp_enqueue') ){
	function wp_enqueue( string $handle ){
		wp_enqueue_script($handle);
		wp_enqueue_style($handle);
	}
}

if( ! function_exists('filter_min_max_number') ){
	function filter_min_max_number( int $number, int $min, int|null $max = null ){
		$number = max( $min, $number );
		if( ! is_null( $max ) ) {
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
	function sanitize_natid_ir( $code = '' ) {
		$code = preg_replace('/[^0-9]/', '', sanitize_number_field($code));
		if( strlen($code) != 10 ) {
			return false;
		}
		if (preg_match('/^(\d)\1{9}$/', $code) ) {
			return false;
		}
		$sum = 0;
		for ($i = 0; $i < 9; $i++) {
			$sum += (int)$code[$i] * (10 - $i);
		}

		$remainder = $sum % 11;
		$controlDigit = (int)$code[9];

		if (($remainder < 2 && $controlDigit == $remainder) || 
			($remainder >= 2 && $controlDigit == (11 - $remainder))) {
			return $code;
		}

		return false;
	}
}

if( ! function_exists('natid_exists') ){
	function natid_exists( $natid = '' ){
		$meta	= 'natid';
		$natid	= sanitize_natid_ir( $natid );
		if( $natid ){
			global $wpdb;
			$exists = $wpdb->get_row( $wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", $meta, $natid) );
			if( $exists ){
				return absint( $exists->user_id );
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
			return $primary;
		}

		return false;
	}
}

if( ! function_exists('get_estimated_reading') ) {
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
		
		return ceil( $words / $wpm );
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

	function the_default_thumbnail( $size = 'thumbnail', $attr = [], $post_id = null ){
		echo get_default_thumbnail( $size, $post_id, $attr );
	}
}

if( ! function_exists('the_breadcrumbs') ){
	function the_breadcrumbs($custom_class = ''){
		$custom_class = 'breadcrumbs '.sanitize_text_field( $custom_class ); 
		$custom_class = trim( $custom_class );
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

if ( ! function_exists( 'the_pagination' ) ) {
	function the_pagination( array $args = [] ) {
		global $wp_query;
		$query = $args['query'] ?? $wp_query;
		$big   = $args['big'] ?? 999999999;
		$paged = $args['current'] ?? get_query_var( 'paged' );
		$paged = max( 1, absint( $paged ) );
		$base = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big, false ) ) );
		
		$defaults = [
			'type'			=> 'plain',
			'add_args'		=> [],
			'mid_size'		=> 1,
			'end_size'		=> 2,
			'base'			=> $base,
			'total'			=> $query->max_num_pages,
			'current'		=> $paged,
			'prev_text'		=> sprintf('<span class="sr-only">%s</span>%s', esc_html__( 'Previous page' ), is_rtl() ? '&rarr;' : '&larr;'),
			'next_text'		=> sprintf('<span class="sr-only">%s</span>%s', esc_html__( 'Next page' ), is_rtl() ? '&larr;' : '&rarr;'),
			'before_tag'	=> '<nav class="%s" role="navigation" aria-label="%s">',
			'after_tag'		=> '</nav>',
			'aria_label'	=> esc_html__( 'Page navigation' ),
			'class'			=> '',
			'echo'			=> true,
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['total'] <= 1 ) {
			return;
		}

		$before_tag = $args['before_tag'];
		$after_tag  = $args['after_tag'];
		$aria_label = $args['aria_label'];
		$nav_class  = trim( 'pagination '.$args['class'] );
		$nav_class	= array_filter( array_unique( explode(' ', $nav_class) ) );

		$excluded_args = [ 'before_tag', 'after_tag', 'aria_label', 'class', 'query', 'echo' ];
		$paginate_args = array_diff_key( $args, array_flip( $excluded_args ) );

		$pagination_html = paginate_links( $paginate_args );
		
		if ( empty( $pagination_html ) ) {
			return;
		}

		$before_tag = sprintf( $before_tag, esc_attr( implode(' ', $nav_class) ), esc_attr( $aria_label ) );

		$output = sprintf("%s\n\t%s\n%s", $before_tag, $pagination_html, $after_tag);

		if ( $args['echo'] ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $output;
		}
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

		if( ! empty($terms) ){
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
