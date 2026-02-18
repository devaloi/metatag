<?php
/**
 * Plugin Name: MetaTag
 * Plugin URI:  https://github.com/devaloi/metatag
 * Description: A lightweight WordPress plugin that adds customizable SEO meta tags, Open Graph, Twitter Cards, and JSON-LD structured data to any post or page.
 * Version:     1.0.0
 * Author:      devaloi
 * Author URI:  https://github.com/devaloi
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: metatag
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

define( 'METATAG_VERSION', '1.0.0' );
define( 'METATAG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'METATAG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'METATAG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once METATAG_PLUGIN_DIR . 'includes/class-metatag.php';

/**
 * Initialize the plugin.
 *
 * @return MetaTag
 */
function metatag_init() {
	return MetaTag::get_instance();
}

register_activation_hook( __FILE__, array( 'MetaTag', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MetaTag', 'deactivate' ) );

metatag_init();
