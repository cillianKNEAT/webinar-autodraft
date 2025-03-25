#!/bin/bash

WP_TESTS_DIR=$1
WP_CORE_DIR=$2

# Verify input parameters
if [ -z "$WP_TESTS_DIR" ] || [ -z "$WP_CORE_DIR" ]; then
    echo "Error: WP_TESTS_DIR and WP_CORE_DIR must be provided"
    exit 1
fi

# Ensure directories exist
if [ ! -d "$WP_TESTS_DIR" ]; then
    echo "Error: Test directory not found: $WP_TESTS_DIR"
    exit 1
fi

if [ ! -d "$WP_CORE_DIR" ]; then
    echo "Error: WordPress core directory not found: $WP_CORE_DIR"
    exit 1
fi

# Verify WordPress core files
if [ ! -f "$WP_CORE_DIR/wp-settings.php" ]; then
    echo "Error: WordPress core files not found in $WP_CORE_DIR"
    exit 1
fi

if [ ! -f "$WP_CORE_DIR/wp-includes/class-wp-phpmailer.php" ]; then
    echo "Error: WordPress includes files not found in $WP_CORE_DIR/wp-includes"
    exit 1
fi

# Create wp-tests-config.php
cat > "$WP_TESTS_DIR/wp-tests-config.php" << EOF
<?php
// Force known values to be used by WP tests.
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );

// Use the same database credentials as the install script.
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// Set the WordPress core directory.
define( 'ABSPATH', '${WP_CORE_DIR}/' );

// Set the WordPress tests directory.
define( 'WP_TESTS_DIR', '${WP_TESTS_DIR}/' );

// Set the WordPress test suite directory.
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills' );

// Set the WordPress test suite version.
define( 'WP_TESTS_VERSION', 'latest' );

// Set the WordPress test suite locale.
define( 'WP_TESTS_LOCALE', '' );

// Set the WordPress test suite timezone.
define( 'WP_TESTS_TIMEZONE', 'UTC' );

// Set the WordPress test suite debug mode.
define( 'WP_TESTS_DEBUG', false );

// Set the WordPress test suite multisite mode.
define( 'WP_TESTS_MULTISITE', false );

// Set the table prefix
\$table_prefix = 'wp_';

// Ensure WordPress core files are loaded
require_once ABSPATH . 'wp-settings.php';
EOF

# Verify the config file was created
if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
    echo "Error: Failed to create wp-tests-config.php"
    exit 1
fi

echo "Successfully created wp-tests-config.php" 