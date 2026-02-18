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

		MetaTag_Helpers::output_property_tag( 'og:site_name', get_bloginfo( 'name' ) );
		MetaTag_Helpers::output_property_tag( 'og:locale', get_locale() );

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

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$type = ( 'post' === $post->post_type ) ? 'article' : 'website';
		MetaTag_Helpers::output_property_tag( 'og:type', $type );
		MetaTag_Helpers::output_property_tag( 'og:url', get_permalink( $post_id ) );
		MetaTag_Helpers::output_property_tag( 'og:title', MetaTag_Helpers::get_post_title( $post_id ) );
		MetaTag_Helpers::output_property_tag( 'og:description', MetaTag_Helpers::get_post_description( $post_id, $post ) );

		$image_data = MetaTag_Helpers::get_post_image( $post_id, $post );
		if ( $image_data ) {
			MetaTag_Helpers::output_property_tag( 'og:image', $image_data['url'] );
			if ( ! empty( $image_data['width'] ) ) {
				MetaTag_Helpers::output_property_tag( 'og:image:width', $image_data['width'] );
			}
			if ( ! empty( $image_data['height'] ) ) {
				MetaTag_Helpers::output_property_tag( 'og:image:height', $image_data['height'] );
			}
		}

		if ( 'article' === $type ) {
			MetaTag_Helpers::output_property_tag( 'article:published_time', get_the_date( 'c', $post_id ) );
			MetaTag_Helpers::output_property_tag( 'article:modified_time', get_the_modified_date( 'c', $post_id ) );
		}
	}

	/**
	 * Output OG tags for the front page.
	 */
	private function output_frontpage_tags() {
		MetaTag_Helpers::output_property_tag( 'og:type', 'website' );
		MetaTag_Helpers::output_property_tag( 'og:url', home_url( '/' ) );
		MetaTag_Helpers::output_property_tag( 'og:title', MetaTag_Helpers::get_homepage_title() );
		MetaTag_Helpers::output_property_tag( 'og:description', MetaTag_Helpers::get_homepage_description() );

		$image = MetaTag::get_setting( 'default_og_image' );
		if ( $image ) {
			MetaTag_Helpers::output_property_tag( 'og:image', $image );
		}
	}
}
