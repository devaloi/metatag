<?php
/**
 * Open Graph tag generation.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag_Open_Graph
 *
 * Outputs Open Graph meta tags in the document head.
 */
class MetaTag_Open_Graph {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_og_tags' ), 2 );
	}

	/**
	 * Output Open Graph tags.
	 */
	public function output_og_tags() {
		if ( is_admin() ) {
			return;
		}

		echo "\n<!-- MetaTag Open Graph -->\n";

		$this->output_tag( 'og:site_name', get_bloginfo( 'name' ) );
		$this->output_tag( 'og:locale', get_locale() );

		if ( is_singular() ) {
			$this->output_singular_tags();
		} elseif ( is_front_page() ) {
			$this->output_frontpage_tags();
		}

		echo "<!-- /MetaTag Open Graph -->\n";
	}

	/**
	 * Output OG tags for singular posts and pages.
	 */
	private function output_singular_tags() {
		$post_id = get_queried_object_id();
		$post    = get_queried_object();

		$type = ( 'post' === $post->post_type ) ? 'article' : 'website';
		$this->output_tag( 'og:type', $type );
		$this->output_tag( 'og:url', get_permalink( $post_id ) );
		$this->output_tag( 'og:title', $this->get_title( $post_id, $post ) );
		$this->output_tag( 'og:description', $this->get_description( $post_id, $post ) );

		$image_data = $this->get_image( $post_id, $post );
		if ( $image_data ) {
			$this->output_tag( 'og:image', $image_data['url'] );
			if ( ! empty( $image_data['width'] ) ) {
				$this->output_tag( 'og:image:width', $image_data['width'] );
			}
			if ( ! empty( $image_data['height'] ) ) {
				$this->output_tag( 'og:image:height', $image_data['height'] );
			}
		}

		if ( 'article' === $type ) {
			$this->output_tag( 'article:published_time', get_the_date( 'c', $post_id ) );
			$this->output_tag( 'article:modified_time', get_the_modified_date( 'c', $post_id ) );
		}
	}

	/**
	 * Output OG tags for the front page.
	 */
	private function output_frontpage_tags() {
		$this->output_tag( 'og:type', 'website' );
		$this->output_tag( 'og:url', home_url( '/' ) );

		$title = MetaTag::get_setting( 'homepage_title' );
		$this->output_tag( 'og:title', $title ? $title : get_bloginfo( 'name' ) );

		$desc = MetaTag::get_setting( 'homepage_description' );
		$this->output_tag( 'og:description', $desc ? $desc : get_bloginfo( 'description' ) );

		$image = MetaTag::get_setting( 'default_og_image' );
		if ( $image ) {
			$this->output_tag( 'og:image', $image );
		}
	}

	/**
	 * Get the OG title for a post.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return string
	 */
	public function get_title( $post_id, $post ) {
		$custom = get_post_meta( $post_id, '_metatag_title', true );
		return $custom ? $custom : get_the_title( $post_id );
	}

	/**
	 * Get the OG description for a post.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return string
	 */
	public function get_description( $post_id, $post ) {
		$custom = get_post_meta( $post_id, '_metatag_description', true );
		if ( $custom ) {
			return $custom;
		}

		if ( $post->post_excerpt ) {
			return wp_trim_words( $post->post_excerpt, 30, '' );
		}

		if ( $post->post_content ) {
			return wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '' );
		}

		return '';
	}

	/**
	 * Get the OG image for a post.
	 *
	 * Fallback chain: featured image → first content image → site default.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return array|null Array with 'url', 'width', 'height' keys or null.
	 */
	public function get_image( $post_id, $post ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			if ( $image ) {
				return array(
					'url'    => $image[0],
					'width'  => $image[1],
					'height' => $image[2],
				);
			}
		}

		$first_image = $this->get_first_content_image( $post->post_content );
		if ( $first_image ) {
			return array(
				'url'    => $first_image,
				'width'  => '',
				'height' => '',
			);
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
	 * Extract the first image URL from post content.
	 *
	 * @param string $content Post content.
	 * @return string|null Image URL or null.
	 */
	private function get_first_content_image( $content ) {
		if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches ) ) {
			return $matches[1];
		}
		return null;
	}

	/**
	 * Output a single OG meta tag.
	 *
	 * @param string $property OG property name.
	 * @param string $content  Tag content.
	 */
	private function output_tag( $property, $content ) {
		if ( '' === $content && '0' !== $content ) {
			return;
		}
		printf(
			'<meta property="%s" content="%s" />' . "\n",
			esc_attr( $property ),
			esc_attr( $content )
		);
	}
}
