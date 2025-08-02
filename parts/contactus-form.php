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
		<p><input type="text" name="cfName" value="<?php echo isset( $_POST['cfName'] ) ? esc_attr( wp_unslash( $_POST['cfName'] ) ): ''; ?>" class="form-control border-gray-100 focus:border-primary" placeholder="نام و نام‌خانوادگی" aria-required="true" required></p>
		<p><input type="tel" name="cfPhone" value="<?php echo isset( $_POST['cfPhone'] ) ? esc_attr( wp_unslash( $_POST['cfPhone'] ) ): ''; ?>" class="form-control border-gray-100 focus:border-primary" placeholder="شماره همراه" aria-required="true" required></p>
		<p><input type="text" name="cfSubject" value="<?php echo isset( $_POST['cfSubject'] ) ? esc_attr( wp_unslash( $_POST['cfSubject'] ) ): ''; ?>" class="form-control border-gray-100 focus:border-primary" placeholder="موضوع پیام" aria-required="true" required></p>
		<p><input type="email" name="cfEmail" value="<?php echo isset( $_POST['cfEmail'] ) ? esc_attr( wp_unslash( $_POST['cfEmail'] ) ): ''; ?>" class="form-control border-gray-100 focus:border-primary" placeholder="پست الکترونیک (دلخواه)"></p>
	</div>
	<input type="text" name="cfSecux" value="" style="display:none">
	<textarea name="cfMessage" class="form-control border-gray-100 focus:border-primary" placeholder="متن پیام" required><?php echo isset( $_POST['cfMessage'] ) ? esc_textarea( wp_unslash( $_POST['cfMessage'] ) ) : ''; ?></textarea>
	<div class="flex items-center mt-4 gap-4">
		<div class="flex-auto"><?php echo $notice; ?></div>
		<button type="submit" name="cfsubmit" class="btn bg-primary text-white hover:bg-primary-darken" value="<?php echo wp_create_nonce('cfsubmit'); ?>">ارسال پیام</button>
	</div>
</form>