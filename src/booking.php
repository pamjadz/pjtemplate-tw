<?php
/**
 * Appointment Scheduler & Booking
 */

defined('ABSPATH') || exit;

class ArvandBookingSystem {
	protected static $_instance = null;

	public function __construct() {
		add_action('template_redirect', [$this, 'template_redirect']);

		add_action( 'rest_api_init', [$this, 'register_routes'] );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_available_slots( $user_id, $days_ahead = 16 ){
		// $shifts		= get_user_meta( $current_user->ID, 'appointments_available', true );
		// $booked		= get_user_meta( $current_user->ID, 'appointments_unavailable', true );
		$interval	= 30;
		$today		= new DateTimeImmutable( 'today', wp_timezone() );
		$results	= [];

		$shifts = [
			'2025-09-05' => [
				['start' => '12:00', 'end' => '14:00'],
				['start' => '18:00', 'end' => '21:00'],
			],
			'2025-09-08' => [
				['start' => '09:00', 'end' => '11:00'],
			],
		];

		$booked = [
			'2025-08-28' => ['18:30','12:00'],
			'2025-08-29' => ['09:30'],
		];

		for ( $i = 0; $i <= $days_ahead; $i++ ) {
			$dateObj  = $today->add( new DateInterval( "P{$i}D" ) );
			$dateKey  = $dateObj->format( 'Y-m-d' );
			$slots    = [];
			if ( isset( $shifts[ $dateKey ] ) ) {
				foreach ( $shifts[ $dateKey ] as $shift ) {
					$start = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateKey . ' ' . $shift['start'], wp_timezone() );
					$end   = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateKey . ' ' . $shift['end'], wp_timezone() );
					if ( ! $start || ! $end ) {
						continue;
					}

					$intervalObj = new DateInterval( "PT{$interval}M" );
					$cursor      = $start;

					while ( $cursor < $end ) {
						$time = $cursor->format( 'H:i' );
						if ( empty( $booked[ $dateKey ] ) || ! in_array( $time, $booked[ $dateKey ], true ) ) {
							$slots[] = $time;
						}
						$cursor = $cursor->add( $intervalObj );
					}
				}
			}
			$results[ $dateKey ] = $slots;
		}

		if( ! empty( $results ) ){
			foreach ( $results as $date => $slots ) {
				if ( ! empty( $slots ) ) {
					$results = array_slice( $results, array_search( $date, array_keys( $results ), true ), null, true );
					break;
				}
			}
		}

		return $results;
	}

	public function template_redirect(){
		if( isset( $_REQUEST['securitynonce'] ) && wp_verify_nonce( $_REQUEST['securitynonce'], 'arasta-book-appointment' )){
			$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
			$salon_id = isset( $_POST['salon_id'] ) ? absint( $_POST['salon_id'] ) : 0;
			$selected_date = isset($_POST['datetime_book']) ? sanitize_text_field( $_POST['datetime_book'] ) : '';
			if ( preg_match( '/^(?:\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])|\d{2}-\d{2})$/', $selected_date ) ) {
				var_dump( $selected_date );
			}
			PJNotice::add_notice( 'پرداخت با موفقیت انجام شد', 'success' );
			// var_dump($user_id, $salon_id);
		}
	}

	public function register_routes(){
		register_rest_route('arasta/v1', '/events/super', [
			'methods'				=> 'GET',
			'callback'				=> [$this, 'get_rest_events_super'],
			'permission_callback'	=> '__return_true', //TODO: make it safe
		]);
	}

	private function get_calendar_events($user_id, $date_start = null, $date_end = null ){
		$list = [
			['title' => 'Meeting', 'start' => '2025-08-30T10:30:00', 'end' => '2025-08-30T11:00:00','salon_id' => 123],
			['title' => 'Lunch', 'start' => '2025-08-29T12:00:00', 'salon_id' => 123],
			['title' => 'Meeting', 'start' => '2025-08-12T14:30:00', 'salon_id' => 123],
			['title' => 'Happy Hour', 'start' => '2025-08-12T17:30:00', 'salon_id' => 123],
			['title' => 'Dinner', 'start' => '2025-08-12T20:00:00', 'salon_id' => 123],
			['title' => 'Birthday Party', 'start' => '2025-08-13T07:00:00', 'salon_id' => 123],
		];

		return $list;
	}

	public function get_rest_events_super( WP_REST_Request $request ) {
		$date_start = sanitize_text_field( $request->get_param( 'start' ) );
		$date_end = sanitize_textarea_field( $request->get_param( 'end' ) );
		return $this->get_calendar_events('super', $date_start, $date_end);
	}
}

ArvandBookingSystem::instance();

function BookSys() {
	return ArvandBookingSystem::instance();
}