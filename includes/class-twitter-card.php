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

		$this->output_tag( 'twitter:card', 'summary_large_image' );

		$twitter_handle = MetaTag::get_setting( 'twitter_handle' );
		if ( $twitter_handle ) {
			$handle = $this->format_handle( $twitter_handle );
			$this->output_tag( 'twitter:site', $handle );
		}

		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			$post    = get_queried_object();

			$og = new MetaTag_Open_Graph();

			$this->output_tag( 'twitter:title', $og->get_title( $post_id, $post ) );
			$this->output_tag( 'twitter:description', $og->get_description( $post_id, $post ) );

			$image_data = $og->get_image( $post_id, $post );
			if ( $image_data ) {
				$this->output_tag( 'twitter:image', $image_data['url'] );
			}
		} elseif ( is_front_page() ) {
			$title = MetaTag::get_setting( 'homepage_title' );
			$this->output_tag( 'twitter:title', $title ? $title : get_bloginfo( 'name' ) );

			$desc = MetaTag::get_setting( 'homepage_description' );
			$this->output_tag( 'twitter:description', $desc ? $desc : get_bloginfo( 'description' ) );

			$image = MetaTag::get_setting( 'default_og_image' );
			if ( $image ) {
				$this->output_tag( 'twitter:image', $image );
			}
		}

		echo "<!-- /MetaTag Twitter Card -->\n";
	}

	/**
	 * Format a Twitter handle to include the @ prefix.
	 *
	 * @param string $handle Twitter handle.
	 * @return string Formatted handle.
	 */
	private function format_handle( $handle ) {
		$handle = trim( $handle );
		if ( $handle && '@' !== $handle[0] ) {
			$handle = '@' . $handle;
		}
		return $handle;
	}

	/**
	 * Output a single Twitter meta tag.
	 *
	 * @param string $name    Meta tag name.
	 * @param string $content Tag content.
	 */
	private function output_tag( $name, $content ) {
		if ( '' === $content && '0' !== $content ) {
			return;
		}
		printf(
			'<meta name="%s" content="%s" />' . "\n",
			esc_attr( $name ),
			esc_attr( $content )
		);
	}
}
