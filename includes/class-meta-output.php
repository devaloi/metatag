<?php
/**
 * Meta tag output in wp_head.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag_Meta_Output
 *
 * Renders SEO meta tags in the document head.
 */
class MetaTag_Meta_Output {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_meta_tags' ), 1 );
		add_filter( 'document_title_parts', array( $this, 'filter_title' ) );
	}

	/**
	 * Output meta tags in the document head.
	 */
	public function output_meta_tags() {
		if ( is_admin() ) {
			return;
		}

		$description = $this->get_description();
		$canonical   = $this->get_canonical();
		$robots      = $this->get_robots();

		echo "\n<!-- MetaTag SEO -->\n";

		if ( $description ) {
			printf(
				'<meta name="description" content="%s" />' . "\n",
				esc_attr( $description )
			);
		}

		if ( $canonical ) {
			printf(
				'<link rel="canonical" href="%s" />' . "\n",
				esc_url( $canonical )
			);
		}

		if ( $robots ) {
			printf(
				'<meta name="robots" content="%s" />' . "\n",
				esc_attr( $robots )
			);
		}

		echo "<!-- /MetaTag SEO -->\n";
	}

	/**
	 * Filter the document title parts.
	 *
	 * @param array $title_parts Title parts array.
	 * @return array Modified title parts.
	 */
	public function filter_title( $title_parts ) {
		if ( is_admin() ) {
			return $title_parts;
		}

		if ( is_singular() ) {
			$post_id   = get_queried_object_id();
			$seo_title = get_post_meta( $post_id, '_metatag_title', true );

			if ( $seo_title ) {
				$title_parts['title'] = $seo_title;
			}
		}

		if ( is_front_page() ) {
			$homepage_title = MetaTag::get_setting( 'homepage_title' );
			if ( $homepage_title ) {
				$title_parts['title'] = $homepage_title;
			}
		}

		$separator = MetaTag::get_setting( 'title_separator', '|' );
		if ( isset( $title_parts['site'] ) && $separator ) {
			$title_parts['site'] = $separator . ' ' . $title_parts['site'];
			unset( $title_parts['tagline'] );
		}

		return $title_parts;
	}

	/**
	 * Get the meta description for the current page.
	 *
	 * Fallback chain: custom → excerpt → auto-generated from content.
	 *
	 * @return string
	 */
	public function get_description() {
		if ( is_front_page() ) {
			$homepage_desc = MetaTag::get_setting( 'homepage_description' );
			if ( $homepage_desc ) {
				return $homepage_desc;
			}
		}

		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			$post    = get_queried_object();

			$custom = get_post_meta( $post_id, '_metatag_description', true );
			if ( $custom ) {
				return $custom;
			}

			if ( $post && $post->post_excerpt ) {
				return wp_trim_words( $post->post_excerpt, 30, '' );
			}

			if ( $post && $post->post_content ) {
				return wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '' );
			}
		}

		$default = MetaTag::get_setting( 'default_description' );
		if ( $default ) {
			return $default;
		}

		return '';
	}

	/**
	 * Get the canonical URL for the current page.
	 *
	 * @return string
	 */
	public function get_canonical() {
		if ( ! is_singular() ) {
			return '';
		}

		$post_id  = get_queried_object_id();
		$custom   = get_post_meta( $post_id, '_metatag_canonical', true );

		if ( $custom ) {
			return $custom;
		}

		return get_permalink( $post_id );
	}

	/**
	 * Get the robots meta content for the current page.
	 *
	 * @return string
	 */
	public function get_robots() {
		if ( ! is_singular() ) {
			return '';
		}

		$post_id  = get_queried_object_id();
		$noindex  = get_post_meta( $post_id, '_metatag_noindex', true );
		$nofollow = get_post_meta( $post_id, '_metatag_nofollow', true );

		$directives = array();
		if ( '1' === $noindex ) {
			$directives[] = 'noindex';
		}
		if ( '1' === $nofollow ) {
			$directives[] = 'nofollow';
		}

		return implode( ', ', $directives );
	}
}
