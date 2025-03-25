<?php
/**
 * Test suite for Webinar Auto-Draft plugin
 *
 * @package WebinarAutoDraft
 */

/**
 * Class Test_Webinar_Auto_Draft
 *
 * @group webinar-autodraft
 */
class Test_Webinar_Auto_Draft extends WP_UnitTestCase {
    /**
     * Test post type registration
     */
    public function test_post_type_exists() {
        $this->assertTrue(post_type_exists('webinar'));
    }

    /**
     * Test webinar status change
     */
    public function test_webinar_status_change() {
        // Create a test webinar post
        $webinar_id = $this->factory->post->create(array(
            'post_type' => 'webinar',
            'post_status' => 'publish',
            'post_title' => 'Test Webinar',
        ));

        // Add the autodraft tag
        wp_set_object_terms($webinar_id, 'autodraft', 'post_tag');

        // Set a past date
        update_field('webinar_date', date('Y-m-d', strtotime('-1 day')), $webinar_id);

        // Run the check function
        check_expired_webinars();

        // Get the updated post
        $updated_post = get_post($webinar_id);

        // Assert the post status is now draft
        $this->assertEquals('draft', $updated_post->post_status);
    }

    /**
     * Test webinar with future date
     */
    public function test_webinar_future_date() {
        // Create a test webinar post
        $webinar_id = $this->factory->post->create(array(
            'post_type' => 'webinar',
            'post_status' => 'publish',
            'post_title' => 'Future Webinar',
        ));

        // Add the autodraft tag
        wp_set_object_terms($webinar_id, 'autodraft', 'post_tag');

        // Set a future date
        update_field('webinar_date', date('Y-m-d', strtotime('+1 day')), $webinar_id);

        // Run the check function
        check_expired_webinars();

        // Get the updated post
        $updated_post = get_post($webinar_id);

        // Assert the post status is still publish
        $this->assertEquals('publish', $updated_post->post_status);
    }

    /**
     * Test webinar without date
     */
    public function test_webinar_no_date() {
        // Create a test webinar post
        $webinar_id = $this->factory->post->create(array(
            'post_type' => 'webinar',
            'post_status' => 'publish',
            'post_title' => 'No Date Webinar',
        ));

        // Add the autodraft tag
        wp_set_object_terms($webinar_id, 'autodraft', 'post_tag');

        // Run the check function
        check_expired_webinars();

        // Get the updated post
        $updated_post = get_post($webinar_id);

        // Assert the post status is still publish
        $this->assertEquals('publish', $updated_post->post_status);
    }

    /**
     * Test webinar without autodraft tag
     */
    public function test_webinar_no_tag() {
        // Create a test webinar post
        $webinar_id = $this->factory->post->create(array(
            'post_type' => 'webinar',
            'post_status' => 'publish',
            'post_title' => 'No Tag Webinar',
        ));

        // Set a past date
        update_field('webinar_date', date('Y-m-d', strtotime('-1 day')), $webinar_id);

        // Run the check function
        check_expired_webinars();

        // Get the updated post
        $updated_post = get_post($webinar_id);

        // Assert the post status is still publish
        $this->assertEquals('publish', $updated_post->post_status);
    }

    /**
     * Test settings registration
     */
    public function test_settings_registration() {
        // Create an instance of the settings class
        $settings = new WAD_Settings();

        // Assert the settings are registered
        $this->assertTrue(get_option('wad_check_frequency') !== false);
        $this->assertTrue(get_option('wad_batch_size') !== false);
        $this->assertTrue(get_option('wad_enable_logging') !== false);
        $this->assertTrue(get_option('wad_notification_emails') !== false);
    }

    /**
     * Test notification sending
     */
    public function test_notification_sending() {
        // Test data
        $email = 'test@example.com';
        $data = array(
            'reverted_count' => 2,
            'failed_count' => 1,
            'total_processed' => 3,
        );

        // Send notification
        $result = WAD_Notifications::send_notification($email, $data);

        // Assert the notification was sent
        $this->assertTrue($result);
    }
} 