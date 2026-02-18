<?php
/**
 * Shared utility methods for meta tag generation.
 *
 * Centralizes common operations like description fallbacks, image
 * resolution, and meta tag output to eliminate duplication across
 * the Open Graph, Twitter Card, and meta output classes.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag_Helpers
 */
class MetaTag_Helpers {

	/**
	 * Post meta key prefix for all MetaTag fields.
	 *
	 * @var string
	 */
	const META_PREFIX = '_metatag_';

	/** Word limit for auto-generated descriptions. */
	const DESCRIPTION_WORD_LIMIT = 30;

	/**
	 * Get the SEO title for a post, falling back to the post title.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_post_title( $post_id ) {
		$custom = get_post_meta( $post_id, self::META_PREFIX . 'title', true );
		return $custom ? $custom : get_the_title( $post_id );
	}

	/**
	 * Get the SEO description for a post.
	 *
	 * Fallback chain: custom meta → excerpt → content (trimmed to 30 words).
	 *
	 * @param int          $post_id Post ID.
	 * @param WP_Post|null $post    Post object (avoids redundant DB query).
	 * @return string
	 */
	public static function get_post_description( $post_id, $post = null ) {
		$custom = get_post_meta( $post_id, self::META_PREFIX . 'description', true );
		if ( $custom ) {
			return $custom;
		}

		if ( ! $post ) {
			$post = get_post( $post_id );
		}

		if ( ! $post instanceof \WP_Post ) {
			return '';
		}

		if ( $post->post_excerpt ) {
			return wp_trim_words( $post->post_excerpt, self::DESCRIPTION_WORD_LIMIT, '' );
		}

		if ( $post->post_content ) {
			return wp_trim_words( wp_strip_all_tags( $post->post_content ), self::DESCRIPTION_WORD_LIMIT, '' );
		}

		return '';
	}

	/**
	 * Get the OG/social image for a post.
	 *
	 * Fallback chain: featured image → first content image → site default.
	 *
	 * @param int          $post_id Post ID.
	 * @param WP_Post|null $post    Post object (avoids redundant DB query).
	 * @return array|null Array with 'url', 'width', 'height' keys, or null.
	 */
	public static function get_post_image( $post_id, $post = null ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			if ( is_array( $image ) ) {
				return array(
					'url'    => $image[0],
					'width'  => $image[1],
					'height' => $image[2],
				);
			}
		}

		if ( ! $post ) {
			$post = get_post( $post_id );
		}

		if ( $post instanceof \WP_Post && $post->post_content ) {
			$first_image = self::get_first_content_image( $post->post_content );
			if ( $first_image ) {
				return array(
					'url'    => $first_image,
					'width'  => '',
					'height' => '',
				);
			}
		}

		$default = MetaTag::get_setting( 'default_og_image' );
		if ( $default ) {
			return array(
				'url'    => $default,
				'width'  => '',
				'height' => '',
			);
		}

		return null;
	}

	/**
	 * Extract the first image URL from HTML content.
	 *
	 * @param string $content HTML content.
	 * @return string|null Image URL or null if none found.
	 */
	public static function get_first_content_image( $content ) {
		if ( ! $content ) {
			return null;
		}

		if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Output a meta tag with the "name" attribute (e.g., Twitter, description).
	 *
	 * @param string $name    Meta tag name attribute.
	 * @param string $content Meta tag content value.
	 */
	public static function output_name_tag( $name, $content ) {
		$content = (string) $content;
		if ( '' === $content ) {
			return;
		}
		printf(
			'<meta name="%s" content="%s" />' . "\n",
			esc_attr( $name ),
			esc_attr( $content )
		);
	}

	/**
	 * Output a meta tag with the "property" attribute (e.g., Open Graph).
	 *
	 * @param string $property Meta tag property attribute.
	 * @param string $content  Meta tag content value.
	 */
	public static function output_property_tag( $property, $content ) {
		$content = (string) $content;
		if ( '' === $content ) {
			return;
		}
		printf(
			'<meta property="%s" content="%s" />' . "\n",
			esc_attr( $property ),
			esc_attr( $content )
		);
	}

	/**
	 * Get homepage title from settings, with fallback to site name.
	 *
	 * @return string
	 */
	public static function get_homepage_title() {
		$title = MetaTag::get_setting( 'homepage_title' );
		return $title ? $title : get_bloginfo( 'name' );
	}

	/**
	 * Get homepage description from settings, with fallback to tagline.
	 *
	 * @return string
	 */
	public static function get_homepage_description() {
		$desc = MetaTag::get_setting( 'homepage_description' );
		return $desc ? $desc : get_bloginfo( 'description' );
	}

	/**
	 * Get the current singular post object, if available.
	 *
	 * Centralizes the is_singular() + get_queried_object() + instanceof WP_Post
	 * pattern used across Open Graph, Twitter Card, and meta output classes.
	 *
	 * @return \WP_Post|null Post object or null if not a singular view.
	 */
	public static function get_current_post() {
		if ( is_singular() ) {
			$post = get_queried_object();
			return ( $post instanceof \WP_Post ) ? $post : null;
		}
		return null;
	}
}
