<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WebinarAutoDraft
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo esc_html( "Could not find $_tests_dir/includes/functions.php\n" );
	echo "Current directory: " . getcwd() . "\n";
	echo "Environment variables:\n";
	print_r( getenv() );
	exit( 1 );
}

// Load PHPUnit Polyfills.
require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/webinar-autodraft.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Ensure the plugin is loaded.
if ( ! function_exists( 'register_webinar_post_type' ) ) {
	echo "Plugin not loaded properly\n";
	exit( 1 );
}

// Run WordPress init hook to register post types.
do_action( 'init' );
