<?php
/**
 * Tests for MetaTag_Meta_Output.
 *
 * @package MetaTag
 */

/**
 * Class Test_Meta_Output
 */
class Test_Meta_Output extends WP_UnitTestCase {

	/**
	 * Meta output instance.
	 *
	 * @var MetaTag_Meta_Output
	 */
	private $meta_output;

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		$this->meta_output = new MetaTag_Meta_Output();
	}

	/**
	 * Test that custom description is returned when set.
	 */
	public function test_custom_description_is_used() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Test Post',
				'post_content' => 'This is the post content that should not be used.',
				'post_excerpt' => 'This is the excerpt.',
			)
		);
		update_post_meta( $post_id, '_metatag_description', 'Custom SEO description' );

		$this->go_to( get_permalink( $post_id ) );

		$description = $this->meta_output->get_description();
		$this->assertEquals( 'Custom SEO description', $description );
	}

	/**
	 * Test fallback to excerpt when no custom description.
	 */
	public function test_description_falls_back_to_excerpt() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Test Post',
				'post_content' => 'Some content here.',
				'post_excerpt' => 'The post excerpt.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$description = $this->meta_output->get_description();
		$this->assertStringContainsString( 'The post excerpt', $description );
	}

	/**
	 * Test fallback to content when no excerpt and no custom description.
	 */
	public function test_description_falls_back_to_content() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Test Post',
				'post_content' => 'This is the full post content for testing purposes.',
				'post_excerpt' => '',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$description = $this->meta_output->get_description();
		$this->assertStringContainsString( 'full post content', $description );
	}

	/**
	 * Test empty description when post has no content.
	 */
	public function test_empty_description_with_no_content() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Empty Post',
				'post_content' => '',
				'post_excerpt' => '',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		$description = $this->meta_output->get_description();
		$this->assertEmpty( $description );
	}

	/**
	 * Test custom canonical URL.
	 */
	public function test_custom_canonical_url() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, '_metatag_canonical', 'https://example.com/canonical' );

		$this->go_to( get_permalink( $post_id ) );

		$canonical = $this->meta_output->get_canonical();
		$this->assertEquals( 'https://example.com/canonical', $canonical );
	}

	/**
	 * Test default canonical URL is the permalink.
	 */
	public function test_default_canonical_is_permalink() {
		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );

		$canonical = $this->meta_output->get_canonical();
		$this->assertEquals( get_permalink( $post_id ), $canonical );
	}

	/**
	 * Test robots meta with noindex.
	 */
	public function test_robots_noindex() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, '_metatag_noindex', '1' );

		$this->go_to( get_permalink( $post_id ) );

		$robots = $this->meta_output->get_robots();
		$this->assertStringContainsString( 'noindex', $robots );
	}

	/**
	 * Test robots meta with both noindex and nofollow.
	 */
	public function test_robots_noindex_nofollow() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, '_metatag_noindex', '1' );
		update_post_meta( $post_id, '_metatag_nofollow', '1' );

		$this->go_to( get_permalink( $post_id ) );

		$robots = $this->meta_output->get_robots();
		$this->assertStringContainsString( 'noindex', $robots );
		$this->assertStringContainsString( 'nofollow', $robots );
	}

	/**
	 * Test empty robots when no directives set.
	 */
	public function test_robots_empty_when_no_directives() {
		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );

		$robots = $this->meta_output->get_robots();
		$this->assertEmpty( $robots );
	}

	/**
	 * Test that meta tags are output in wp_head.
	 */
	public function test_meta_tags_output_in_head() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Test content for description.',
			)
		);
		update_post_meta( $post_id, '_metatag_description', 'Test description' );

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->meta_output->output_meta_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'MetaTag SEO', $output );
		$this->assertStringContainsString( 'name="description"', $output );
		$this->assertStringContainsString( 'Test description', $output );
		$this->assertStringContainsString( 'rel="canonical"', $output );
	}

	/**
	 * Test special characters are escaped in description.
	 */
	public function test_special_characters_escaped() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, '_metatag_description', 'Title with "quotes" & <special> chars' );

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->meta_output->output_meta_tags();
		$output = ob_get_clean();

		$this->assertStringNotContainsString( '<special>', $output );
		$this->assertStringContainsString( '&amp;', $output );
	}
}
