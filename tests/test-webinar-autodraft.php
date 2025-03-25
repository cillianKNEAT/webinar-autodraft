<?php
/**
 * Test suite for Webinar Auto-Draft plugin
 *
 * @package WebinarAutoDraft
 * @since 1.0
 */

require_once __DIR__ . '/mocks/class-mock-acf.php';

/**
 * Class Test_Webinar_Auto_Draft
 *
 * @group webinar-autodraft
 */
class Test_Webinar_Auto_Draft extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Clear mock ACF fields.
		Mock_ACF::clear_fields();

		// Ensure post type is registered.
		if ( ! post_type_exists( 'webinar' ) ) {
			register_webinar_post_type();
		}

		// Create the autodraft tag if it doesn't exist.
		if ( ! term_exists( 'autodraft', 'post_tag' ) ) {
			wp_insert_term( 'autodraft', 'post_tag' );
		}
	}

	/**
	 * Clean up after each test
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// Clean up any created posts.
		$posts = get_posts(
			array(
				'post_type'      => 'webinar',
				'posts_per_page' => -1,
				'post_status'    => 'any',
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

		// Clear mock ACF fields.
		Mock_ACF::clear_fields();

		parent::tearDown();
	}

	/**
	 * Test post type registration
	 *
	 * @return void
	 */
	public function test_post_type_exists() {
		$this->assertTrue( post_type_exists( 'webinar' ) );
	}

	/**
	 * Test webinar status change
	 *
	 * @return void
	 */
	public function test_webinar_status_change() {
		// Create a test webinar post.
		$webinar_id = $this->factory->post->create(
			array(
				'post_type'   => 'webinar',
				'post_status' => 'publish',
				'post_title'  => 'Test Webinar',
			)
		);

		// Add the autodraft tag.
		wp_set_object_terms( $webinar_id, 'autodraft', 'post_tag' );

		// Set a past date.
		update_field( 'webinar_date', gmdate( 'Y-m-d', strtotime( '-1 day' ) ), $webinar_id );

		// Run the check function.
		check_expired_webinars();

		// Get the updated post.
		$updated_post = get_post( $webinar_id );

		// Assert the post status is now draft.
		$this->assertEquals( 'draft', $updated_post->post_status );
	}

	/**
	 * Test webinar with future date
	 *
	 * @return void
	 */
	public function test_webinar_future_date() {
		// Create a test webinar post.
		$webinar_id = $this->factory->post->create(
			array(
				'post_type'   => 'webinar',
				'post_status' => 'publish',
				'post_title'  => 'Future Webinar',
			)
		);

		// Add the autodraft tag.
		wp_set_object_terms( $webinar_id, 'autodraft', 'post_tag' );

		// Set a future date.
		update_field( 'webinar_date', gmdate( 'Y-m-d', strtotime( '+1 day' ) ), $webinar_id );

		// Run the check function.
		check_expired_webinars();

		// Get the updated post.
		$updated_post = get_post( $webinar_id );

		// Assert the post status is still publish.
		$this->assertEquals( 'publish', $updated_post->post_status );
	}

	/**
	 * Test webinar with no date
	 *
	 * @return void
	 */
	public function test_webinar_no_date() {
		// Create a test webinar post.
		$webinar_id = $this->factory->post->create(
			array(
				'post_type'   => 'webinar',
				'post_status' => 'publish',
				'post_title'  => 'No Date Webinar',
			)
		);

		// Add the autodraft tag.
		wp_set_object_terms( $webinar_id, 'autodraft', 'post_tag' );

		// Run the check function.
		check_expired_webinars();

		// Get the updated post.
		$updated_post = get_post( $webinar_id );

		// Assert the post status is still publish.
		$this->assertEquals( 'publish', $updated_post->post_status );
	}

	/**
	 * Test webinar with no tag
	 *
	 * @return void
	 */
	public function test_webinar_no_tag() {
		// Create a test webinar post.
		$webinar_id = $this->factory->post->create(
			array(
				'post_type'   => 'webinar',
				'post_status' => 'publish',
				'post_title'  => 'No Tag Webinar',
			)
		);

		// Set a past date.
		update_field( 'webinar_date', gmdate( 'Y-m-d', strtotime( '-1 day' ) ), $webinar_id );

		// Run the check function.
		check_expired_webinars();

		// Get the updated post.
		$updated_post = get_post( $webinar_id );

		// Assert the post status is still publish.
		$this->assertEquals( 'publish', $updated_post->post_status );
	}

	/**
	 * Test settings registration
	 *
	 * @return void
	 */
	public function test_settings_registration() {
		// Create an instance of the settings class.
		$settings = new WAD_Settings();

		// Assert the settings are registered.
		$this->assertTrue( get_option( 'wad_check_frequency' ) !== false );
		$this->assertTrue( get_option( 'wad_batch_size' ) !== false );
		$this->assertTrue( get_option( 'wad_enable_logging' ) !== false );
		$this->assertTrue( get_option( 'wad_notification_emails' ) !== false );
	}

	/**
	 * Test notification sending
	 *
	 * @return void
	 */
	public function test_notification_sending() {
		// Test data.
		$email = 'test@example.com';
		$data  = array(
			'reverted_count'  => 2,
			'failed_count'    => 1,
			'total_processed' => 3,
		);

		// Send notification.
		$result = WAD_Notifications::send_notification( $email, $data );

		// Assert the notification was sent.
		$this->assertTrue( $result );
	}
}
