<?php
/**
 * JSON-LD structured data generation.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag_JSON_LD
 *
 * Outputs JSON-LD structured data in the document head.
 */
class MetaTag_JSON_LD {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_json_ld' ), 4 );
	}

	/**
	 * Output JSON-LD structured data.
	 */
	public function output_json_ld() {
		if ( is_admin() ) {
			return;
		}

		$schema = array();

		if ( is_singular( 'post' ) ) {
			$schema = $this->get_article_schema();
		} elseif ( is_singular( 'page' ) ) {
			$schema = $this->get_webpage_schema();
		} elseif ( is_front_page() ) {
			$schema = $this->get_website_schema();
		}

		if ( empty( $schema ) ) {
			return;
		}

		echo "\n<!-- MetaTag JSON-LD -->\n";
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
		echo "<!-- /MetaTag JSON-LD -->\n";
	}

	/**
	 * Get Article schema for single posts.
	 *
	 * @return array Schema data.
	 */
	private function get_article_schema() {
		$post_id = get_queried_object_id();
		$post    = get_queried_object();
		$author  = get_userdata( $post->post_author );

		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Article',
			'headline'      => get_the_title( $post_id ),
			'url'           => get_permalink( $post_id ),
			'datePublished' => get_the_date( 'c', $post_id ),
			'dateModified'  => get_the_modified_date( 'c', $post_id ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => $author ? $author->display_name : '',
			),
			'publisher'     => $this->get_publisher(),
		);

		$description = get_post_meta( $post_id, '_metatag_description', true );
		if ( $description ) {
			$schema['description'] = $description;
		}

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			if ( $image ) {
				$schema['image'] = array(
					'@type'  => 'ImageObject',
					'url'    => $image[0],
					'width'  => $image[1],
					'height' => $image[2],
				);
			}
		}

		$breadcrumbs = $this->get_breadcrumb_list( $post_id, $post );
		if ( $breadcrumbs ) {
			$schema['mainEntityOfPage'] = array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $post_id ),
			);
		}

		return array( $schema, $breadcrumbs ? $breadcrumbs : null );
	}

	/**
	 * Get WebPage schema for pages.
	 *
	 * @return array Schema data.
	 */
	private function get_webpage_schema() {
		$post_id = get_queried_object_id();

		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'WebPage',
			'name'          => get_the_title( $post_id ),
			'url'           => get_permalink( $post_id ),
			'datePublished' => get_the_date( 'c', $post_id ),
			'dateModified'  => get_the_modified_date( 'c', $post_id ),
			'publisher'     => $this->get_publisher(),
		);

		$description = get_post_meta( $post_id, '_metatag_description', true );
		if ( $description ) {
			$schema['description'] = $description;
		}

		$breadcrumbs = $this->get_breadcrumb_list( $post_id );
		if ( $breadcrumbs ) {
			return array( $schema, $breadcrumbs );
		}

		return $schema;
	}

	/**
	 * Get WebSite schema for the front page.
	 *
	 * @return array Schema data.
	 */
	private function get_website_schema() {
		return array(
			'@context'    => 'https://schema.org',
			'@type'       => 'WebSite',
			'name'        => get_bloginfo( 'name' ),
			'url'         => home_url( '/' ),
			'description' => get_bloginfo( 'description' ),
			'publisher'   => $this->get_publisher(),
		);
	}

	/**
	 * Get the publisher schema fragment.
	 *
	 * @return array Publisher data.
	 */
	private function get_publisher() {
		return array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		);
	}

	/**
	 * Get BreadcrumbList schema.
	 *
	 * @param int          $post_id Post ID.
	 * @param WP_Post|null $post    Post object.
	 * @return array|null BreadcrumbList schema or null.
	 */
	private function get_breadcrumb_list( $post_id, $post = null ) {
		$items = array();
		$position = 1;

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => __( 'Home', 'metatag' ),
			'item'     => home_url( '/' ),
		);

		if ( $post && 'post' === $post->post_type ) {
			$categories = get_the_category( $post_id );
			if ( ! empty( $categories ) ) {
				$category = $categories[0];
				$items[]  = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => $category->name,
					'item'     => get_category_link( $category->term_id ),
				);
			}
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => get_the_title( $post_id ),
			'item'     => get_permalink( $post_id ),
		);

		if ( count( $items ) < 2 ) {
			return null;
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}
}
