<?php
/**
 * Tests for MetaTag_Twitter_Card.
 *
 * @package MetaTag
 */

/**
 * Class Test_Twitter_Card
 */
class Test_Twitter_Card extends WP_UnitTestCase {

	/**
	 * Twitter Card instance.
	 *
	 * @var MetaTag_Twitter_Card
	 */
	private $twitter;

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		$this->twitter = new MetaTag_Twitter_Card();
	}

	/**
	 * Test Twitter Card output contains card type.
	 */
	public function test_twitter_card_type_is_summary_large_image() {
		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->twitter->output_twitter_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'twitter:card', $output );
		$this->assertStringContainsString( 'summary_large_image', $output );
	}

	/**
	 * Test Twitter site handle is included when configured.
	 */
	public function test_twitter_site_handle_included() {
		update_option(
			'metatag_settings',
			array_merge( MetaTag::get_default_settings(), array( 'twitter_handle' => '@testuser' ) )
		);

		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->twitter->output_twitter_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'twitter:site', $output );
		$this->assertStringContainsString( '@testuser', $output );
	}

	/**
	 * Test Twitter handle without @ prefix gets formatted.
	 */
	public function test_twitter_handle_formatting() {
		update_option(
			'metatag_settings',
			array_merge( MetaTag::get_default_settings(), array( 'twitter_handle' => 'noatsign' ) )
		);

		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->twitter->output_twitter_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( '@noatsign', $output );
	}

	/**
	 * Test Twitter tags inherit title from OG.
	 */
	public function test_twitter_title_inherits_from_og() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Inherited Title' ) );
		update_post_meta( $post_id, '_metatag_title', 'Custom SEO Title' );

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->twitter->output_twitter_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'twitter:title', $output );
		$this->assertStringContainsString( 'Custom SEO Title', $output );
	}

	/**
	 * Test Twitter description inherits from OG.
	 */
	public function test_twitter_description_inherits_from_og() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'Content for the description field.',
			)
		);
		update_post_meta( $post_id, '_metatag_description', 'Custom description' );

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->twitter->output_twitter_tags();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'twitter:description', $output );
		$this->assertStringContainsString( 'Custom description', $output );
	}

	/**
	 * Test no twitter:site tag when handle is empty.
	 */
	public function test_no_twitter_site_when_empty() {
		update_option(
			'metatag_settings',
			array_merge( MetaTag::get_default_settings(), array( 'twitter_handle' => '' ) )
		);

		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->twitter->output_twitter_tags();
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'twitter:site', $output );
	}
}
