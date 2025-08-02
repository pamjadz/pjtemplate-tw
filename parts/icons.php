<?php
/**
 * Theme SVG Icons
 *
 * This template is placed after the body and shows the website icons.
 *
 * @package IconIoir
 * @version 7.8.0
 * @url https://iconoir.com/
 */

defined( 'ABSPATH' ) || exit;

ob_start(); ?>

<!--SYMBOLS HERE-->

<?php
$svgsymbols = ob_get_clean();
preg_match_all('/<symbol([^>]*)>(.*?)<\/symbol>/is', $svgsymbols, $symbols, PREG_SET_ORDER);
$output = '';
foreach( $symbols as $symbol ){
	$attrs = $symbol[1];
	$content = preg_replace('></path>','/>', $symbol[2]);
	preg_match('/data-if="([^"]+)"/', $attrs, $if_match);
	$if_value = $if_match[1] ?? '';
	if( $if_value ){
		$attrs = preg_replace('/\s*data-if="[^"]+"/', '', $attrs);
		$should_render = false;
		$conditions = preg_split('/\s+/', $if_value);
		foreach ( $conditions as $condition ) {
			$and_parts = explode('__', $condition);
			$and_ok = true;
			foreach( $and_parts as $part ) {
				if( ! is_callable( $part ) || ! call_user_func( $part ) ) {
					$and_ok = false;
					break;
				}
			}
			if( $and_ok ) {
				$should_render = true;
				break;
			}
		}
		if( $should_render ) {
			$output .= "<symbol$attrs>$content</symbol>\n";
		}
	} else {
		$output .= "<symbol$attrs>$content</symbol>\n";
	}
}
if( ! empty( $output ) ){
	printf(
		'%2$s<svg xmlns="http://www.w3.org/2000/svg" class="hidden">%1$s</svg>%2$s',
		preg_replace(
			[
				'/\>[^\S ]+/s',
				'/[^\S ]+\</s',
				'/(\s)+/s',
				'/<!--(.|\s)*?-->/'
			],
			[
				'>',
				'<',
				'\\1',
				''
			],
			$output
		),
		PHP_EOL
	);
	echo PHP_EOL;
}

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */