<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 */

defined('ABSPATH') || exit;

// get_template_part('parts/offcanvas', 'mmenu');

if( isset( $args['only_meta'] ) && TRUE === $args['only_meta'] ){
	wp_footer();
	echo '</body></html>';
	return;
}
?>

<footer id="siteFoot">
	
</footer>

<?php wp_footer(); ?>

</body>
</html>