<?php
/**
 * The template for displaying single salon page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 */

defined('ABSPATH') || exit;

wp_enqueue_script('fullcalendar');
get_header();

global $current_user;
$available_slots = BookSys()->get_available_slots( $current_user->ID );
enqueue_swiper();
?>
<form id="book-appointment-selector" method="post" action="#" class="mx-auto max-w-2xl p-5 rounded-t-4xl">
	<p id="book-appointment-title">رزرو نوبت از علی محمدی سالن تست</p>
	<input type="hidden" name="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>">
	<input type="hidden" name="salon_id" value="123">
	<?php wp_nonce_field( 'arasta-book-appointment', 'securitynonce' ); ?>
	<p class="text-sm text-muted mb-4">درنظر داشته باشید شروع تاریخ ها، از اولین روزی هست که امکان نوبت‌دهی وجود دارد</p>
	<div class="swiper" data-swiper='{"loop":false,"slidesPerView":5,"spaceBetween":10}'>
		<div class="swiper-wrapper flex">
			<?php
			foreach ( $available_slots as $dateStr => $times ) {
				$date_times = array_map(
					static fn( $time ) => $dateStr . '|' . $time,
					$times
				);
				$today    = wp_date( 'Y-m-d' );
				$tomorrow = wp_date( 'Y-m-d', strtotime( '+1 day' ) );
				if ( $dateStr === $today ) {
					$label = 'امروز';
				} elseif ( $dateStr === $tomorrow ) {
					$label = 'فردا';
				} else {
					$label = wp_date( 'l', strtotime( $dateStr ) );
				}
				$total_appointments = count( $times );
				$dateObj = new DateTimeImmutable( $dateStr, wp_timezone() );
				printf(
					'<button type="button" class="swiper-slide bg-card border border-gray-100 p-2 text-center rounded-2xl hover:bg-gray-50 [&.active]:border-primary-500 [&.active]:bg-primary-100" data-times="%s">
						<span class="text-sm text-muted block mb-1">%s</span> 
						<time datetime="%s" class="block">%s</time> 
						<span class="block text-xs mt-2">%s</span>
					</button>',
					esc_attr( wp_json_encode( $date_times ) ),
					esc_html( $label ),
					esc_attr( $dateObj->format( DATE_ATOM ) ),
					esc_html( wp_date( 'Y/m/d', $dateObj->getTimestamp() ) ),
					$total_appointments
						? sprintf( '<span class="text-green-500">%d نوبت</span>', $total_appointments )
						: '&mdash;'
				);
			}
			?>
		</div>
		<div class="appointments_available mt-4"></div>
	</div>
</form>


<?php get_footer(); ?>