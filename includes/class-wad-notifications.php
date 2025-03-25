<?php
/**
 * Notifications class for Webinar Auto-Draft
 *
 * @package WebinarAutoDraft
 */

if (!defined('ABSPATH')) {
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
    public static function send_notification($email, $data) {
        if (!is_email($email)) {
            return false;
        }

        $subject = sprintf(
            /* translators: %s: Site name */
            __('[%s] Webinar Status Update', 'webinar-autodraft'),
            get_bloginfo('name')
        );

        $message = self::get_notification_message($data);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        );

        return wp_mail($email, $subject, $message, $headers);
    }

    /**
     * Get the notification message content
     *
     * @param array $data The notification data.
     * @return string The formatted message.
     */
    private static function get_notification_message($data) {
        $message = '<html><body>';
        $message .= '<h2>' . esc_html__('Webinar Status Update', 'webinar-autodraft') . '</h2>';
        $message .= '<p>' . sprintf(
            /* translators: %s: Current date */
            esc_html__('This is an automated message from your website. The following webinar status changes were processed on %s:', 'webinar-autodraft'),
            date_i18n(get_option('date_format') . ' ' . get_option('time_format'))
        ) . '</p>';
        
        $message .= '<ul>';
        $message .= '<li>' . sprintf(
            /* translators: %d: Number of webinars */
            esc_html__('Total webinars processed: %d', 'webinar-autodraft'),
            $data['total_processed']
        ) . '</li>';
        
        if ($data['reverted_count'] > 0) {
            $message .= '<li>' . sprintf(
                /* translators: %d: Number of webinars */
                esc_html__('Successfully reverted to draft: %d', 'webinar-autodraft'),
                $data['reverted_count']
            ) . '</li>';
        }
        
        if ($data['failed_count'] > 0) {
            $message .= '<li>' . sprintf(
                /* translators: %d: Number of webinars */
                esc_html__('Failed to revert: %d', 'webinar-autodraft'),
                $data['failed_count']
            ) . '</li>';
        }
        
        $message .= '</ul>';
        $message .= '<p>' . esc_html__('You can view and manage your webinars in the WordPress admin panel.', 'webinar-autodraft') . '</p>';
        $message .= '</body></html>';

        return $message;
    }
} 