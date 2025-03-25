<?php
/**
 * Plugin Name: Webinar Auto-Draft
 * Description: Automatically reverts manually tagged webinar posts to draft status when their date has passed
 * Version: 1.0
 * Author: Cillian Bracken Conway
 * Requires at least: 5.0
 * Requires PHP: 7.2
 *
 * @package WebinarAutoDraft
 */

// Make sure this file is not accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WAD_VERSION', '1.0' );
define( 'WAD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WAD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files.
require_once WAD_PLUGIN_DIR . 'includes/class-wad-settings.php';
require_once WAD_PLUGIN_DIR . 'includes/class-wad-notifications.php';

// Initialize settings.
$wad_settings      = new WAD_Settings();
$wad_notifications = new WAD_Notifications();

/**
 * Register the webinar post type
 *
 * @since 1.0
 * @return void
 */
function register_webinar_post_type() {
	register_post_type(
		'webinar',
		array(
			'public'      => true,
			'has_archive' => true,
			'supports'    => array( 'title', 'editor', 'thumbnail' ),
			'labels'      => array(
				'name'               => __( 'Webinars', 'webinar-autodraft' ),
				'singular_name'      => __( 'Webinar', 'webinar-autodraft' ),
				'add_new'            => __( 'Add New', 'webinar-autodraft' ),
				'add_new_item'       => __( 'Add New Webinar', 'webinar-autodraft' ),
				'edit_item'          => __( 'Edit Webinar', 'webinar-autodraft' ),
				'new_item'           => __( 'New Webinar', 'webinar-autodraft' ),
				'view_item'          => __( 'View Webinar', 'webinar-autodraft' ),
				'search_items'       => __( 'Search Webinars', 'webinar-autodraft' ),
				'not_found'          => __( 'No webinars found', 'webinar-autodraft' ),
				'not_found_in_trash' => __( 'No webinars found in Trash', 'webinar-autodraft' ),
			),
		)
	);
}
add_action( 'init', 'register_webinar_post_type' );

// No auto-tagging function needed as tags will be added manually.

/**
 * Schedule cron job to check webinar dates based on settings
 *
 * @since 1.0
 * @return void
 */
function schedule_webinar_check() {
	if ( ! wp_next_scheduled( 'check_expired_webinars' ) ) {
		// Get schedule from settings.
		$schedule = get_option( 'wad_check_frequency', 'quarter_day' );
		wp_schedule_event( time(), $schedule, 'check_expired_webinars' );
	}
}

/**
 * Add custom schedules for webinar checks
 *
 * @since 1.0
 * @param array $schedules Array of existing schedules.
 * @return array Modified schedules array.
 */
function add_webinar_schedules( $schedules ) {
	$schedules['quarter_day'] = array(
		'interval' => 6 * HOUR_IN_SECONDS,
		'display'  => __( 'Every 6 hours (4 times per day)', 'webinar-autodraft' ),
	);
	$schedules['twice_daily'] = array(
		'interval' => 12 * HOUR_IN_SECONDS,
		'display'  => __( 'Twice daily', 'webinar-autodraft' ),
	);
	$schedules['daily']       = array(
		'interval' => DAY_IN_SECONDS,
		'display'  => __( 'Once daily', 'webinar-autodraft' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'add_webinar_schedules' );
add_action( 'wp', 'schedule_webinar_check' );

/**
 * Function to check for expired webinars and revert them to draft
 *
 * @since 1.0
 * @return void
 */
function check_expired_webinars() {
	// Get settings.
	$batch_size          = get_option( 'wad_batch_size', 50 );
	$enable_logging      = get_option( 'wad_enable_logging', true );
	$notification_emails = get_option( 'wad_notification_emails', array( get_option( 'admin_email' ) ) );

	// Get current UTC date.
	$current_date = gmdate( 'Ymd' );

	// Get all published webinar posts with the "autodraft" tag.
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Using simpler tag parameter for better performance
	$args = array(
		'post_type'      => 'webinar',
		'post_status'    => 'publish',
		'posts_per_page' => $batch_size,
		'tag'            => 'autodraft',
	);

	$webinars = get_posts( $args );

	if ( empty( $webinars ) ) {
		return;
	}

	$reverted_count = 0;
	$failed_count   = 0;

	// Loop through each webinar post.
	foreach ( $webinars as $webinar ) {
		// Get the webinar_date ACF field value.
		$webinar_date = get_field( 'webinar_date', $webinar->ID );

		// Skip if no date is set.
		if ( empty( $webinar_date ) ) {
			if ( $enable_logging ) {
				error_log(
					sprintf(
						'Webinar ID %d skipped: No date set.',
						$webinar->ID
					)
				);
			}
			continue;
		}

		// Convert the webinar date to Ymd format for comparison.
		$formatted_webinar_date = gmdate( 'Ymd', strtotime( $webinar_date ) );

		// If webinar date is in the past, revert to draft.
		if ( $formatted_webinar_date < $current_date ) {
			// Update post status to draft.
			$update_result = wp_update_post(
				array(
					'ID'          => $webinar->ID,
					'post_status' => 'draft',
				)
			);

			if ( is_wp_error( $update_result ) ) {
				$failed_count++;
				if ( $enable_logging ) {
					error_log(
						sprintf(
							'Failed to revert webinar ID %d to draft: %s.',
							$webinar->ID,
							$update_result->get_error_message()
						)
					);
				}
			} else {
				$reverted_count++;
				if ( $enable_logging ) {
					error_log(
						sprintf(
							'Successfully reverted webinar ID %d to draft status.',
							$webinar->ID
						)
					);
				}
			}
		}
	}

	// Send notification if any webinars were processed.
	if ( $reverted_count > 0 || $failed_count > 0 ) {
		$notification_data = array(
			'reverted_count'  => $reverted_count,
			'failed_count'    => $failed_count,
			'total_processed' => count( $webinars ),
		);

		foreach ( $notification_emails as $email ) {
			WAD_Notifications::send_notification( $email, $notification_data );
		}
	}
}
add_action( 'check_expired_webinars', 'check_expired_webinars' );

/**
 * Clean up on plugin deactivation
 *
 * @since 1.0
 * @return void
 */
register_deactivation_hook(
	__FILE__,
	function() {
		// Remove the scheduled event.
		wp_clear_scheduled_hook( 'check_expired_webinars' );

		// Clean up options.
		delete_option( 'wad_check_frequency' );
		delete_option( 'wad_batch_size' );
		delete_option( 'wad_enable_logging' );
		delete_option( 'wad_notification_emails' );
	}
);

// No activation hook for tagging needed as tags will be added manually.
