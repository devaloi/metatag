<?php
/**
 * Twitter Card tag generation.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag_Twitter_Card
 *
 * Outputs Twitter Card meta tags in the document head.
 * Reuses shared helpers for title, description, and image resolution
 * to stay consistent with Open Graph values.
 */
class MetaTag_Twitter_Card {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_twitter_tags' ), 3 );
	}

	/**
	 * Output Twitter Card tags.
	 */
	public function output_twitter_tags() {
		if ( is_admin() ) {
			return;
		}

		echo "\n<!-- MetaTag Twitter Card -->\n";

		MetaTag_Helpers::output_name_tag( 'twitter:card', 'summary_large_image' );

		$twitter_handle = MetaTag::get_setting( 'twitter_handle' );
		if ( $twitter_handle ) {
			MetaTag_Helpers::output_name_tag( 'twitter:site', self::format_handle( $twitter_handle ) );
		}

		if ( is_singular() ) {
			$post = MetaTag_Helpers::get_current_post();

			if ( $post ) {
				$post_id = $post->ID;
				MetaTag_Helpers::output_name_tag( 'twitter:title', MetaTag_Helpers::get_post_title( $post_id ) );
				MetaTag_Helpers::output_name_tag( 'twitter:description', MetaTag_Helpers::get_post_description( $post_id, $post ) );

				$image_data = MetaTag_Helpers::get_post_image( $post_id, $post );
				if ( $image_data ) {
					MetaTag_Helpers::output_name_tag( 'twitter:image', $image_data['url'] );
				}
			}
		} elseif ( is_front_page() ) {
			MetaTag_Helpers::output_name_tag( 'twitter:title', MetaTag_Helpers::get_homepage_title() );
			MetaTag_Helpers::output_name_tag( 'twitter:description', MetaTag_Helpers::get_homepage_description() );

			$image = MetaTag::get_setting( 'default_og_image' );
			if ( $image ) {
				MetaTag_Helpers::output_name_tag( 'twitter:image', $image );
			}
		}

		echo "<!-- /MetaTag Twitter Card -->\n";
	}

	/**
	 * Format a Twitter handle to include the @ prefix.
	 *
	 * @param string $handle Raw Twitter handle input.
	 * @return string Handle with @ prefix.
	 */
	private static function format_handle( $handle ) {
		$handle = trim( $handle );
		if ( '' === $handle ) {
			return '';
		}
		if ( '@' !== $handle[0] ) {
			$handle = '@' . $handle;
		}
		return $handle;
	}
}
