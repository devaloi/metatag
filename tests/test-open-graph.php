<?php
/**
 * Tests for MetaTag_Open_Graph.
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
	 * Test OG title uses custom meta title when set.
	 */
	public function test_og_title_uses_custom_meta() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Original Title' ) );
		$post    = get_post( $post_id );
		update_post_meta( $post_id, '_metatag_title', 'Custom OG Title' );

		$title = $this->og->get_title( $post_id, $post );
		$this->assertEquals( 'Custom OG Title', $title );
	}

	/**
	 * Test OG title falls back to post title.
	 */
	public function test_og_title_falls_back_to_post_title() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Original Title' ) );
		$post    = get_post( $post_id );

		$title = $this->og->get_title( $post_id, $post );
		$this->assertEquals( 'Original Title', $title );
	}

	/**
	 * Test OG description uses custom meta description.
	 */
	public function test_og_description_uses_custom_meta() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Content here.',
				'post_excerpt' => 'Excerpt here.',
			)
		);
		$post = get_post( $post_id );
		update_post_meta( $post_id, '_metatag_description', 'Custom OG Description' );

		$desc = $this->og->get_description( $post_id, $post );
		$this->assertEquals( 'Custom OG Description', $desc );
	}

	/**
	 * Test OG description falls back to excerpt.
	 */
	public function test_og_description_falls_back_to_excerpt() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Content here.',
				'post_excerpt' => 'The excerpt text.',
			)
		);
		$post = get_post( $post_id );

		$desc = $this->og->get_description( $post_id, $post );
		$this->assertStringContainsString( 'excerpt text', $desc );
	}

	/**
	 * Test OG description falls back to content when no excerpt.
	 */
	public function test_og_description_falls_back_to_content() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'The actual post content for testing.',
				'post_excerpt' => '',
			)
		);
		$post = get_post( $post_id );

		$desc = $this->og->get_description( $post_id, $post );
		$this->assertStringContainsString( 'actual post content', $desc );
	}

	/**
	 * Test OG image returns null when no image available.
	 */
	public function test_og_image_returns_null_when_no_image() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'No images here.' ) );
		$post    = get_post( $post_id );

		$image = $this->og->get_image( $post_id, $post );
		$this->assertNull( $image );
	}

	/**
	 * Test OG image extracts first content image as fallback.
	 */
	public function test_og_image_falls_back_to_content_image() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Text <img src="https://example.com/photo.jpg" /> more text.',
			)
		);
		$post = get_post( $post_id );

		$image = $this->og->get_image( $post_id, $post );
		$this->assertNotNull( $image );
		$this->assertEquals( 'https://example.com/photo.jpg', $image['url'] );
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
}
