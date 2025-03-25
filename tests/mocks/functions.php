<?php
/**
 * Mock ACF functions for testing
 *
 * @package WebinarAutoDraft
 */

require_once __DIR__ . '/class-mock-acf.php';

if ( ! function_exists( 'get_field' ) ) {
	/**
	 * Mock get_field function
	 *
	 * @param string $field_name Field name.
	 * @param int    $post_id    Post ID.
	 * @return mixed Field value.
	 */
	function get_field( $field_name, $post_id ) {
		return Mock_ACF::get_field( $field_name, $post_id );
	}
}

if ( ! function_exists( 'update_field' ) ) {
	/**
	 * Mock update_field function
	 *
	 * @param string $field_name Field name.
	 * @param mixed  $value      Field value.
	 * @param int    $post_id    Post ID.
	 * @return bool Whether the field was updated.
	 */
	function update_field( $field_name, $value, $post_id ) {
		return Mock_ACF::update_field( $field_name, $value, $post_id );
	}
}
