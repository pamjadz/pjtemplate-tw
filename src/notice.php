<?php
class PJNotice {
	const SESSION_KEY = 'arasta_alert';

	public static function add_notice( string $message, string $type = 'success' ): void {
		if ( ! session_id() ) {
			session_start();
		}

		if ( ! isset( $_SESSION[ self::SESSION_KEY ] ) ) {
			$_SESSION[ self::SESSION_KEY ] = [];
		}

		$_SESSION[ self::SESSION_KEY ][] = [
			'message' => sanitize_text_field( $message ),
			'type'    => sanitize_key( $type ),
		];
	}

	public static function get_notices(): array {
		if ( ! session_id() ) {
			session_start();
		}
		$notices = $_SESSION[ self::SESSION_KEY ] ?? [];
		unset( $_SESSION[ self::SESSION_KEY ] );
		return $notices;
	}

	public static function print_notices(): void {
		$notices = self::get_notices();
		if ( empty( $notices ) ) {
			return;
		}
		echo '<div class="arvand-notice" role="alert">';
		foreach ( $notices as $notice ) {
			get_template_part( 'parts/notice', $notice['type'], ['message' => $notice['message'] ] );
		}
		echo '</div>';
	}
}
