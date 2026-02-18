<?php
/**
 * Main plugin class.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag
 *
 * Singleton class that bootstraps the plugin.
 */
class MetaTag {

	/**
	 * Singleton instance.
	 *
	 * @var MetaTag|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return MetaTag
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Loads dependencies and registers hooks.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required class files.
	 */
	private function load_dependencies() {
		$includes = METATAG_PLUGIN_DIR . 'includes/';

		require_once $includes . 'class-helpers.php';
		require_once $includes . 'class-meta-box.php';
		require_once $includes . 'class-meta-output.php';
		require_once $includes . 'class-open-graph.php';
		require_once $includes . 'class-twitter-card.php';
		require_once $includes . 'class-json-ld.php';
		require_once $includes . 'class-admin.php';
	}

	/**
	 * Register WordPress hooks.
	 *
	 * wp_head priorities:
	 *   1 — Meta description, canonical, robots (before other SEO plugins)
	 *   2 — Open Graph tags
	 *   3 — Twitter Card tags
	 *   4 — JSON-LD structured data
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		new MetaTag_Meta_Box();
		new MetaTag_Meta_Output();
		new MetaTag_Open_Graph();
		new MetaTag_Twitter_Card();
		new MetaTag_JSON_LD();

		if ( is_admin() && ! wp_doing_ajax() ) {
			new MetaTag_Admin();
		}
	}

	/**
	 * Load the plugin text domain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'metatag', false, dirname( METATAG_PLUGIN_BASENAME ) . '/languages' );
	}

	/**
	 * Plugin activation callback.
	 */
	public static function activate() {
		if ( ! get_option( 'metatag_settings' ) ) {
			update_option( 'metatag_settings', self::get_default_settings() );
		}
	}

	/**
	 * Plugin deactivation callback.
	 */
	public static function deactivate() {
		// Intentionally left empty. Settings are preserved on deactivation.
	}

	/**
	 * Get default plugin settings.
	 *
	 * @return array
	 */
	public static function get_default_settings() {
		return array(
			'title_separator'      => '|',
			'title_format'         => '%title% | %sitename%',
			'default_description'  => '',
			'twitter_handle'       => '',
			'facebook_url'         => '',
			'homepage_title'       => '',
			'homepage_description' => '',
			'default_og_image'     => '',
		);
	}

	/**
	 * Get a specific plugin setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if setting not found.
	 * @return mixed
	 */
	public static function get_setting( $key, $default = '' ) {
		$settings = get_option( 'metatag_settings', self::get_default_settings() );
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}
