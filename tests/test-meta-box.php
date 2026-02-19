<?php
/**
 * Tests for MetaTag_Meta_Box save behavior.
 *
 * @package MetaTag
 */

/**
 * Class Test_Meta_Box
 */
class Test_Meta_Box extends WP_UnitTestCase {

	/**
	 * Meta box instance.
	 *
	 * @var MetaTag_Meta_Box
	 */
	private $meta_box;

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		$this->meta_box = new MetaTag_Meta_Box();
	}

	/**
	 * Test get_field_values returns empty strings for new post.
	 */
	public function test_field_values_empty_for_new_post() {
		$post_id = self::factory()->post->create();
		$values  = $this->meta_box->get_field_values( $post_id );

		$this->assertEmpty( $values['title'] );
		$this->assertEmpty( $values['description'] );
		$this->assertEmpty( $values['keyword'] );
		$this->assertEmpty( $values['canonical'] );
		$this->assertEmpty( $values['noindex'] );
		$this->assertEmpty( $values['nofollow'] );
	}

	/**
	 * Test get_field_values returns stored meta.
	 */
	public function test_field_values_returns_stored_meta() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, '_metatag_title', 'Custom Title' );
		update_post_meta( $post_id, '_metatag_description', 'Custom Desc' );
		update_post_meta( $post_id, '_metatag_noindex', '1' );

		$values = $this->meta_box->get_field_values( $post_id );

		$this->assertEquals( 'Custom Title', $values['title'] );
		$this->assertEquals( 'Custom Desc', $values['description'] );
		$this->assertEquals( '1', $values['noindex'] );
	}

	/**
	 * Test save_meta_box requires nonce.
	 */
	public function test_save_requires_nonce() {
		$post_id = self::factory()->post->create();

		$_POST = array(
			'metatag_title' => 'Should not save',
		);

		$this->meta_box->save_meta_box( $post_id );

		$title = get_post_meta( $post_id, '_metatag_title', true );
		$this->assertEmpty( $title );
	}

	/**
	 * Test save_meta_box saves with valid nonce and capability.
	 */
	public function test_save_with_valid_nonce_and_capability() {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$post_id = self::factory()->post->create( array( 'post_author' => $user_id ) );

		$_POST = array(
			'metatag_nonce'       => wp_create_nonce( 'metatag_save_meta' ),
			'metatag_title'       => 'Saved Title',
			'metatag_description' => 'Saved Description',
			'metatag_keyword'     => 'seo keyword',
			'metatag_canonical'   => 'https://example.com/canonical',
			'metatag_noindex'     => '1',
		);

		$this->meta_box->save_meta_box( $post_id );

		$this->assertEquals( 'Saved Title', get_post_meta( $post_id, '_metatag_title', true ) );
		$this->assertEquals( 'Saved Description', get_post_meta( $post_id, '_metatag_description', true ) );
		$this->assertEquals( 'seo keyword', get_post_meta( $post_id, '_metatag_keyword', true ) );
		$this->assertEquals( 'https://example.com/canonical', get_post_meta( $post_id, '_metatag_canonical', true ) );
		$this->assertEquals( '1', get_post_meta( $post_id, '_metatag_noindex', true ) );
		$this->assertEquals( '0', get_post_meta( $post_id, '_metatag_nofollow', true ) );
	}

	/**
	 * Test save_meta_box sanitizes input.
	 */
	public function test_save_sanitizes_input() {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$post_id = self::factory()->post->create( array( 'post_author' => $user_id ) );

		$_POST = array(
			'metatag_nonce'     => wp_create_nonce( 'metatag_save_meta' ),
			'metatag_title'     => '<script>alert("xss")</script>Title',
			'metatag_canonical' => 'javascript:alert(1)',
		);

		$this->meta_box->save_meta_box( $post_id );

		$title = get_post_meta( $post_id, '_metatag_title', true );
		$this->assertStringNotContainsString( '<script>', $title );

		$canonical = get_post_meta( $post_id, '_metatag_canonical', true );
		$this->assertStringNotContainsString( 'javascript:', $canonical );
	}

	/**
	 * Test meta box registers for posts and pages.
	 */
	public function test_meta_box_registers_for_posts_and_pages() {
		global $wp_meta_boxes;

		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->meta_box->register_meta_box();

		$this->assertArrayHasKey( 'metatag-seo', $wp_meta_boxes['post']['normal']['high'] );
		$this->assertArrayHasKey( 'metatag-seo', $wp_meta_boxes['page']['normal']['high'] );
	}

	/**
	 * Test meta box post types are filterable.
	 */
	public function test_meta_box_post_types_filterable() {
		add_filter(
			'metatag_meta_box_post_types',
			function ( $types ) {
				$types[] = 'product';
				return $types;
			}
		);

		global $wp_meta_boxes;

		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		register_post_type( 'product' );
		$this->meta_box->register_meta_box();

		$this->assertArrayHasKey( 'metatag-seo', $wp_meta_boxes['product']['normal']['high'] );
	}

	/**
	 * Test save_meta_box skips during autosave.
	 *
	 * IMPORTANT: This test must be last because define() is permanent
	 * and DOING_AUTOSAVE would affect all subsequent save tests.
	 */
	public function test_save_skips_autosave() {
		$post_id = self::factory()->post->create();

		define( 'DOING_AUTOSAVE', true );

		$_POST = array(
			'metatag_nonce' => wp_create_nonce( 'metatag_save_meta' ),
			'metatag_title' => 'Autosave title',
		);

		$this->meta_box->save_meta_box( $post_id );

		$title = get_post_meta( $post_id, '_metatag_title', true );
		$this->assertEmpty( $title );
	}
}
