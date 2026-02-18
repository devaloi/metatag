<?php
/**
 * Tests for MetaTag_Open_Graph and shared helpers.
 *
 * @package MetaTag
 */

/**
 * Class Test_Open_Graph
 */
class Test_Open_Graph extends WP_UnitTestCase {

	/**
	 * Open Graph instance.
	 *
	 * @var MetaTag_Open_Graph
	 */
	private $og;

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		$this->og = new MetaTag_Open_Graph();
	}

	/**
	 * Test post title uses custom meta title when set.
	 */
	public function test_post_title_uses_custom_meta() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Original Title' ) );
		update_post_meta( $post_id, '_metatag_title', 'Custom OG Title' );

		$title = MetaTag_Helpers::get_post_title( $post_id );
		$this->assertEquals( 'Custom OG Title', $title );
	}

	/**
	 * Test post title falls back to post title.
	 */
	public function test_post_title_falls_back_to_post_title() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Original Title' ) );

		$title = MetaTag_Helpers::get_post_title( $post_id );
		$this->assertEquals( 'Original Title', $title );
	}

	/**
	 * Test post description uses custom meta description.
	 */
	public function test_post_description_uses_custom_meta() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Content here.',
				'post_excerpt' => 'Excerpt here.',
			)
		);
		$post = get_post( $post_id );
		update_post_meta( $post_id, '_metatag_description', 'Custom OG Description' );

		$desc = MetaTag_Helpers::get_post_description( $post_id, $post );
		$this->assertEquals( 'Custom OG Description', $desc );
	}

	/**
	 * Test post description falls back to excerpt.
	 */
	public function test_post_description_falls_back_to_excerpt() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Content here.',
				'post_excerpt' => 'The excerpt text.',
			)
		);
		$post = get_post( $post_id );

		$desc = MetaTag_Helpers::get_post_description( $post_id, $post );
		$this->assertStringContainsString( 'excerpt text', $desc );
	}

	/**
	 * Test post description falls back to content when no excerpt.
	 */
	public function test_post_description_falls_back_to_content() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'The actual post content for testing.',
				'post_excerpt' => '',
			)
		);
		$post = get_post( $post_id );

		$desc = MetaTag_Helpers::get_post_description( $post_id, $post );
		$this->assertStringContainsString( 'actual post content', $desc );
	}

	/**
	 * Test post image returns null when no image available.
	 */
	public function test_post_image_returns_null_when_no_image() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'No images here.' ) );
		$post    = get_post( $post_id );

		$image = MetaTag_Helpers::get_post_image( $post_id, $post );
		$this->assertNull( $image );
	}

	/**
	 * Test post image extracts first content image as fallback.
	 */
	public function test_post_image_falls_back_to_content_image() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Text <img src="https://example.com/photo.jpg" /> more text.',
			)
		);
		$post = get_post( $post_id );

		$image = MetaTag_Helpers::get_post_image( $post_id, $post );
		$this->assertNotNull( $image );
		$this->assertEquals( 'https://example.com/photo.jpg', $image['url'] );
	}

	/**
	 * Test first content image extraction with no images returns null.
	 */
	public function test_first_content_image_returns_null_for_empty() {
		$this->assertNull( MetaTag_Helpers::get_first_content_image( '' ) );
		$this->assertNull( MetaTag_Helpers::get_first_content_image( 'No images here' ) );
	}

	/**
	 * Test OG tags output contains required properties.
	 */
	public function test_og_tags_output_contains_required_properties() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'OG Test Post',
				'post_content' => 'Content for OG testing.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->og->output_og_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'og:title', $output );
		$this->assertStringContainsString( 'og:description', $output );
		$this->assertStringContainsString( 'og:url', $output );
		$this->assertStringContainsString( 'og:type', $output );
		$this->assertStringContainsString( 'og:site_name', $output );
	}

	/**
	 * Test OG type is article for posts.
	 */
	public function test_og_type_is_article_for_posts() {
		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->og->output_og_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'content="article"', $output );
	}

	/**
	 * Test OG type is website for pages.
	 */
	public function test_og_type_is_website_for_pages() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$this->go_to( get_permalink( $page_id ) );

		ob_start();
		$this->og->output_og_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'content="website"', $output );
	}

	/**
	 * Test post description returns empty for non-WP_Post objects.
	 */
	public function test_post_description_returns_empty_for_invalid_post() {
		$desc = MetaTag_Helpers::get_post_description( 0 );
		$this->assertEmpty( $desc );
	}
}
