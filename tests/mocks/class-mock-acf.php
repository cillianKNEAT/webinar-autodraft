<?php
/**
 * Mock ACF class for testing
 *
 * @package WebinarAutoDraft
 */

/**
 * Class Mock_ACF
 */
class Mock_ACF {
    /**
     * Store field values
     *
     * @var array
     */
    private static $fields = array();

    /**
     * Get field value
     *
     * @param string $field_name Field name.
     * @param int    $post_id Post ID.
     * @return mixed Field value.
     */
    public static function get_field($field_name, $post_id) {
        return isset(self::$fields[$post_id][$field_name]) ? self::$fields[$post_id][$field_name] : null;
    }

    /**
     * Update field value
     *
     * @param string $field_name Field name.
     * @param mixed  $value Field value.
     * @param int    $post_id Post ID.
     * @return bool Whether the field was updated.
     */
    public static function update_field($field_name, $value, $post_id) {
        if (!isset(self::$fields[$post_id])) {
            self::$fields[$post_id] = array();
        }
        self::$fields[$post_id][$field_name] = $value;
        return true;
    }

    /**
     * Clear all field values
     */
    public static function clear_fields() {
        self::$fields = array();
    }
}

// Mock ACF functions
if (!function_exists('get_field')) {
    function get_field($field_name, $post_id) {
        return Mock_ACF::get_field($field_name, $post_id);
    }
}

if (!function_exists('update_field')) {
    function update_field($field_name, $value, $post_id) {
        return Mock_ACF::update_field($field_name, $value, $post_id);
    }
} 