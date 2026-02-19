<?php
/**
 * Tests for MetaTag_JSON_LD.
 *
 * @package MetaTag
 */

/**
 * Class Test_JSON_LD
 */
class Test_JSON_LD extends WP_UnitTestCase {

	/**
	 * JSON-LD instance.
	 *
	 * @var MetaTag_JSON_LD
	 */
	private $json_ld;

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		$this->json_ld = new MetaTag_JSON_LD();
	}

	/**
	 * Test Article schema is output for posts.
	 */
	public function test_article_schema_for_posts() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'JSON-LD Test Post',
				'post_content' => 'Content here.',
				'post_type'    => 'post',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'application/ld+json', $output );
		$this->assertStringContainsString( '"@type"', $output );
		$this->assertStringContainsString( 'Article', $output );
		$this->assertStringContainsString( 'JSON-LD Test Post', $output );
	}

	/**
	 * Test WebPage schema is output for pages.
	 */
	public function test_webpage_schema_for_pages() {
		$page_id = self::factory()->post->create(
			array(
				'post_title' => 'JSON-LD Test Page',
				'post_type'  => 'page',
			)
		);

		$this->go_to( get_permalink( $page_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'application/ld+json', $output );
		$this->assertStringContainsString( 'WebPage', $output );
		$this->assertStringContainsString( 'JSON-LD Test Page', $output );
	}

	/**
	 * Test JSON-LD output is valid JSON.
	 */
	public function test_json_ld_output_is_valid_json() {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Valid JSON Test',
				'post_content' => 'Content.',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		preg_match( '/<script type="application\/ld\+json">\s*(.*?)\s*<\/script>/s', $output, $matches );
		$this->assertNotEmpty( $matches );

		$decoded = json_decode( $matches[1], true );
		$this->assertNotNull( $decoded, 'JSON-LD output should be valid JSON' );
	}

	/**
	 * Test Article schema includes author.
	 */
	public function test_article_schema_includes_author() {
		$user_id = self::factory()->user->create( array( 'display_name' => 'Test Author' ) );
		$post_id = self::factory()->post->create(
			array(
				'post_author' => $user_id,
				'post_title'  => 'Author Test',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Test Author', $output );
		$this->assertStringContainsString( 'Person', $output );
	}

	/**
	 * Test schema includes publisher.
	 */
	public function test_schema_includes_publisher() {
		$post_id = self::factory()->post->create();

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Organization', $output );
		$this->assertStringContainsString( 'publisher', $output );
	}

	/**
	 * Test BreadcrumbList is included for posts.
	 */
	public function test_breadcrumb_list_for_posts() {
		$post_id = self::factory()->post->create(
			array(
				'post_title' => 'Breadcrumb Test',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'BreadcrumbList', $output );
	}

	/**
	 * Test no JSON-LD on archive pages.
	 */
	public function test_no_json_ld_on_archive() {
		$cat_id = self::factory()->category->create( array( 'name' => 'Test Cat' ) );
		self::factory()->post->create_many( 3, array( 'post_category' => array( $cat_id ) ) );

		$this->go_to( get_category_link( $cat_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'application/ld+json', $output );
	}

	/**
	 * Test special characters in title are handled in JSON-LD.
	 */
	public function test_special_characters_in_json_ld() {
		$post_id = self::factory()->post->create(
			array(
				'post_title' => 'Post with "quotes" & <brackets>',
			)
		);

		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->json_ld->output_json_ld();
		$output = ob_get_clean();

		preg_match( '/<script type="application\/ld\+json">\s*(.*?)\s*<\/script>/s', $output, $matches );
		if ( ! empty( $matches ) ) {
			$decoded = json_decode( $matches[1], true );
			$this->assertNotNull( $decoded, 'JSON with special characters should still be valid' );
		}
	}
}
