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
		register_setting( 'wad_settings', 'wad_check_frequency' );
		register_setting( 'wad_settings', 'wad_batch_size' );
		register_setting( 'wad_settings', 'wad_enable_logging' );
		register_setting( 'wad_settings', 'wad_notification_emails' );

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
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wad_settings' );
				do_settings_sections( 'webinar-autodraft' );
				submit_button();
				?>
			</form>
		</div>
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
}
