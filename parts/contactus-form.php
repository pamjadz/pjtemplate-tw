<?php 
/**
 * The template for displaying comment item
 *
 * @see 	https://developer.wordpress.org/reference/classes/wp_comment/
 * @author 	Pouriya Amjadzadeh
 * @version 3.0.0
 */

defined('ABSPATH') || exit;
?>

<form action="#" method="post">
	<div class="grid grid-cols-2 gap-4 mb-4">
		<p><input type="text" name="arvcfName" value="<?php echo esc_attr( wp_unslash( $_POST['arvcfName'] ?? '' ) ); ?>" class="form-control border-gray-100 focus:border-primary" placeholder="نام و نام‌خانوادگی" aria-required="true" required></p>
		<p><input type="tel" name="arvcfPhone" value="<?php echo esc_attr( wp_unslash( $_POST['arvcfPhone'] ?? '' ) ); ?>" class="form-control border-gray-100 focus:border-primary" placeholder="شماره همراه" aria-required="true" required></p>
		<p><input type="text" name="arvcfSubject" value="<?php echo esc_attr( wp_unslash( $_POST['arvcfSubject'] ?? '' ) ); ?>" class="form-control border-gray-100 focus:border-primary" placeholder="موضوع پیام" aria-required="true" required></p>
		<p><input type="email" name="arvcfEmail" value="<?php echo esc_attr( wp_unslash( $_POST['arvcfEmail'] ?? '' ) ); ?>" class="form-control border-gray-100 focus:border-primary" placeholder="پست الکترونیک (دلخواه)"></p>
	</div>
	<input type="text" name="arvcfSecux" value="" style="display:none">
	<textarea name="arvcfMessage" class="form-control border-gray-100 focus:border-primary" placeholder="متن پیام" required><?php echo isset( $_POST['arvcfMessage'] ) ? esc_textarea( wp_unslash( $_POST['arvcfMessage'] ) ) : ''; ?></textarea>
	<div class="flex items-center mt-4 gap-4">
		<button type="submit" class="btn bg-primary text-white hover:bg-primary-darken" value="<?php echo wp_create_nonce('cfsubmit'); ?>">ارسال پیام</button>
		<?php wp_nonce_field( 'arvand-contactform', 'arvand-contactform'); ?>
	</div>
</form>