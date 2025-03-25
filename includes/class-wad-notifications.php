<?php
/**
 * Notifications class for Webinar Auto-Draft
 *
 * @package WebinarAutoDraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WAD_Notifications
 *
 * Handles email notifications for the Webinar Auto-Draft plugin.
 */
class WAD_Notifications {
	/**
	 * Send notification email about webinar status changes
	 *
	 * @param string $email The recipient email address.
	 * @param array  $data  The notification data.
	 * @return bool Whether the email was sent successfully.
	 */
	public static function send_notification( $email, $data ) {
		if ( ! is_email( $email ) ) {
			return false;
		}

		$subject = sprintf(
			/* translators: %s: Site name */
			__( '[%s] Webinar Status Update', 'webinar-autodraft' ),
			get_bloginfo( 'name' )
		);

		$message = self::get_notification_message( $data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		return wp_mail( $email, $subject, $message, $headers );
	}

	/**
	 * Get the notification message content
	 *
	 * @param array $data The notification data.
	 * @return string The formatted message.
	 */
	private static function get_notification_message( $data ) {
		$message  = '<html><body>';
		$message .= '<h2>' . esc_html__( 'Webinar Status Update', 'webinar-autodraft' ) . '</h2>';
		$message .= '<p>' . sprintf(
			/* translators: %s: Current date */
			esc_html__( 'This is an automated message from your website. The following webinar status changes were processed on %s:', 'webinar-autodraft' ),
			date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
		) . '</p>';

		$message .= '<ul>';
		$message .= '<li>' . sprintf(
			/* translators: %d: Number of webinars */
			esc_html__( 'Total webinars processed: %d', 'webinar-autodraft' ),
			$data['total_processed']
		) . '</li>';

		if ( $data['reverted_count'] > 0 ) {
			$message .= '<li>' . sprintf(
				/* translators: %d: Number of webinars */
				esc_html__( 'Successfully reverted to draft: %d', 'webinar-autodraft' ),
				$data['reverted_count']
			) . '</li>';
		}

		if ( $data['failed_count'] > 0 ) {
			$message .= '<li>' . sprintf(
				/* translators: %d: Number of webinars */
				esc_html__( 'Failed to revert: %d', 'webinar-autodraft' ),
				$data['failed_count']
			) . '</li>';
		}

		$message .= '</ul>';
		$message .= '<p>' . esc_html__( 'You can view and manage your webinars in the WordPress admin panel.', 'webinar-autodraft' ) . '</p>';
		$message .= '</body></html>';

		return $message;
	}

	/**
	 * Log a message with appropriate formatting and level
	 *
	 * @since 1.0
	 * @param string $message The message to log.
	 * @param string $level   The log level (info, error, success).
	 * @return void
	 */
	public static function log_message( $message, $level = 'info' ) {
		// Format the message with timestamp and level.
		$formatted_message = sprintf(
			'[%s] [%s] %s',
			gmdate( 'Y-m-d H:i:s' ),
			strtoupper( $level ),
			$message
		);

		// Log to WordPress debug log if enabled.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $formatted_message );
		}

		// Store in plugin's log file if enabled.
		$log_file = WAD_PLUGIN_DIR . 'logs/webinar-autodraft.log';
		if ( file_exists( dirname( $log_file ) ) ) {
			file_put_contents( $log_file, $formatted_message . PHP_EOL, FILE_APPEND );
		}
	}
}
