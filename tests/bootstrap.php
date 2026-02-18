<?php
/**
 * PHPUnit bootstrap for MetaTag tests.
 *
 * @package MetaTag
 */

$metatag_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $metatag_tests_dir ) {
	$metatag_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $metatag_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $metatag_tests_dir/includes/functions.php\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

require_once $metatag_tests_dir . '/includes/functions.php';

/**
 * Load the plugin for tests.
 */
function metatag_manually_load_plugin() {
	require dirname( __DIR__ ) . '/metatag.php';
}
tests_add_filter( 'muplugins_loaded', 'metatag_manually_load_plugin' );

require $metatag_tests_dir . '/includes/bootstrap.php';
