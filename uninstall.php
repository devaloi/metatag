<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Cleans up all plugin data from the database:
 * - Global plugin settings
 * - Per-post meta fields
 *
 * @package MetaTag
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Remove plugin settings.
delete_option( 'metatag_settings' );

// Remove all per-post meta fields.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		$wpdb->esc_like( '_metatag_' ) . '%'
	)
);
