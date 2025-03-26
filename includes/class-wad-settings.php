<?php
/**
 * Settings class for Webinar Auto-Draft
 *
 * @package WebinarAutoDraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WAD_Settings
 *
 * Handles the admin settings page and options for the Webinar Auto-Draft plugin.
 */
class WAD_Settings {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add the settings page to the WordPress admin menu
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Webinar Auto-Draft Settings', 'webinar-autodraft' ),
			__( 'Webinar Auto-Draft', 'webinar-autodraft' ),
			'manage_options',
			'webinar-autodraft',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register the plugin settings
	 */
	public function register_settings() {
		// Add debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Webinar Auto-Draft] Registering settings' );
		}

		register_setting( 'wad_settings', 'wad_check_frequency', array(
			'sanitize_callback' => array( $this, 'sanitize_check_frequency' ),
			'default' => 'quarter_day',
			'type' => 'string'
		) );
		register_setting( 'wad_settings', 'wad_batch_size', array(
			'sanitize_callback' => array( $this, 'sanitize_batch_size' ),
			'default' => 50,
			'type' => 'integer'
		) );
		register_setting( 'wad_settings', 'wad_enable_logging', array(
			'sanitize_callback' => array( $this, 'sanitize_enable_logging' ),
			'default' => true,
			'type' => 'boolean'
		) );
		register_setting( 'wad_settings', 'wad_notification_emails', array(
			'sanitize_callback' => array( $this, 'sanitize_notification_emails' ),
			'default' => array( get_option( 'admin_email' ) ),
			'type' => 'array'
		) );

		add_settings_section(
			'wad_main_section',
			__( 'Main Settings', 'webinar-autodraft' ),
			array( $this, 'render_section_description' ),
			'webinar-autodraft'
		);

		add_settings_field(
			'wad_check_frequency',
			__( 'Check Frequency', 'webinar-autodraft' ),
			array( $this, 'render_check_frequency_field' ),
			'webinar-autodraft',
			'wad_main_section'
		);

		add_settings_field(
			'wad_batch_size',
			__( 'Batch Size', 'webinar-autodraft' ),
			array( $this, 'render_batch_size_field' ),
			'webinar-autodraft',
			'wad_main_section'
		);

		add_settings_field(
			'wad_enable_logging',
			__( 'Enable Logging', 'webinar-autodraft' ),
			array( $this, 'render_logging_field' ),
			'webinar-autodraft',
			'wad_main_section'
		);

		add_settings_field(
			'wad_notification_emails',
			__( 'Notification Emails', 'webinar-autodraft' ),
			array( $this, 'render_notification_emails_field' ),
			'webinar-autodraft',
			'wad_main_section'
		);
	}

	/**
	 * Render the settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Add debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Webinar Auto-Draft] Rendering settings page' );
		}

		// Check if settings were saved
		if ( isset( $_GET['settings-updated'] ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[Webinar Auto-Draft] Settings updated successfully' );
			}
			add_settings_error(
				'wad_messages',
				'wad_message',
				__( 'Settings Saved', 'webinar-autodraft' ),
				'updated'
			);
		}

		// Show settings errors
		settings_errors( 'wad_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php" id="wad-settings-form">
				<?php
				settings_fields( 'wad_settings' );
				do_settings_sections( 'webinar-autodraft' );
				submit_button( __( 'Save Settings', 'webinar-autodraft' ), 'primary', 'submit', true );
				?>
			</form>
		</div>
		<style>
		#wad-settings-form {
			max-width: 800px;
		}
		#wad-settings-form .submit {
			margin-top: 20px;
		}
		</style>
		<?php
	}

	/**
	 * Render the section description
	 */
	public function render_section_description() {
		echo '<p>' . esc_html__( 'Configure how the Webinar Auto-Draft plugin works.', 'webinar-autodraft' ) . '</p>';
	}

	/**
	 * Render the check frequency field
	 */
	public function render_check_frequency_field() {
		$frequency = get_option( 'wad_check_frequency', 'quarter_day' );
		$schedules = wp_get_schedules();
		?>
		<select name="wad_check_frequency">
			<?php foreach ( $schedules as $key => $schedule ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $frequency, $key ); ?>>
					<?php echo esc_html( $schedule['display'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'How often should the plugin check for expired webinars?', 'webinar-autodraft' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the batch size field
	 */
	public function render_batch_size_field() {
		$batch_size = get_option( 'wad_batch_size', 50 );
		?>
		<input type="number" name="wad_batch_size" value="<?php echo esc_attr( $batch_size ); ?>" min="1" max="1000">
		<p class="description">
			<?php esc_html_e( 'Number of webinars to process in each batch (1-1000).', 'webinar-autodraft' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the logging field
	 */
	public function render_logging_field() {
		$enable_logging = get_option( 'wad_enable_logging', true );
		?>
		<label>
			<input type="checkbox" name="wad_enable_logging" value="1" <?php checked( $enable_logging ); ?>>
			<?php esc_html_e( 'Enable logging of webinar status changes', 'webinar-autodraft' ); ?>
		</label>
		<?php
	}

	/**
	 * Render the notification emails field
	 */
	public function render_notification_emails_field() {
		$emails = get_option( 'wad_notification_emails', array( get_option( 'admin_email' ) ) );
		?>
		<textarea name="wad_notification_emails" rows="3" cols="50"><?php echo esc_textarea( implode( "\n", $emails ) ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Enter one email address per line. These addresses will receive notifications about webinar status changes.', 'webinar-autodraft' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize check frequency
	 */
	public function sanitize_check_frequency( $value ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Webinar Auto-Draft] Sanitizing check frequency: ' . $value );
		}
		$schedules = wp_get_schedules();
		return isset( $schedules[ $value ] ) ? $value : 'quarter_day';
	}

	/**
	 * Sanitize batch size
	 */
	public function sanitize_batch_size( $value ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Webinar Auto-Draft] Sanitizing batch size: ' . $value );
		}
		$value = absint( $value );
		return min( max( $value, 1 ), 1000 );
	}

	/**
	 * Sanitize enable logging
	 */
	public function sanitize_enable_logging( $value ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Webinar Auto-Draft] Sanitizing enable logging: ' . ( $value ? 'true' : 'false' ) );
		}
		return (bool) $value;
	}

	/**
	 * Sanitize notification emails
	 */
	public function sanitize_notification_emails( $value ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Webinar Auto-Draft] Sanitizing notification emails' );
		}
		$emails = array_map( 'sanitize_email', explode( "\n", $value ) );
		$emails = array_filter( $emails, 'is_email' );
		return empty( $emails ) ? array( get_option( 'admin_email' ) ) : $emails;
	}
}
