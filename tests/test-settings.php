<?php
/**
 * Tests for MetaTag_Admin settings and MetaTag core.
 *
 * @package MetaTag
 */

/**
 * Class Test_Settings
 */
class Test_Settings extends WP_UnitTestCase {

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		delete_option( 'metatag_settings' );
	}

	/**
	 * Test default settings are returned when option is missing.
	 */
	public function test_default_settings_returned_when_missing() {
		$title_sep = MetaTag::get_setting( 'title_separator' );
		$this->assertEquals( '|', $title_sep );
	}

	/**
	 * Test get_setting returns stored value.
	 */
	public function test_get_setting_returns_stored_value() {
		update_option(
			'metatag_settings',
			array_merge(
				MetaTag::get_default_settings(),
				array( 'twitter_handle' => '@testsite' )
			)
		);

		$this->assertEquals( '@testsite', MetaTag::get_setting( 'twitter_handle' ) );
	}

	/**
	 * Test get_setting returns default for missing key.
	 */
	public function test_get_setting_returns_default_for_missing_key() {
		$result = MetaTag::get_setting( 'nonexistent_key', 'fallback' );
		$this->assertEquals( 'fallback', $result );
	}

	/**
	 * Test activation sets default settings only when absent.
	 */
	public function test_activation_sets_defaults_when_absent() {
		MetaTag::activate();

		$settings = get_option( 'metatag_settings' );
		$this->assertIsArray( $settings );
		$this->assertEquals( '|', $settings['title_separator'] );
	}

	/**
	 * Test activation preserves existing settings.
	 */
	public function test_activation_preserves_existing_settings() {
		$custom = array_merge(
			MetaTag::get_default_settings(),
			array( 'twitter_handle' => '@existing' )
		);
		update_option( 'metatag_settings', $custom );

		MetaTag::activate();

		$settings = get_option( 'metatag_settings' );
		$this->assertEquals( '@existing', $settings['twitter_handle'] );
	}

	/**
	 * Test settings sanitization strips HTML from text fields.
	 */
	public function test_sanitize_settings_strips_html() {
		$admin = new MetaTag_Admin();

		$dirty = array(
			'title_separator'      => '<script>|</script>',
			'title_format'         => '%title% | %sitename%',
			'default_description'  => 'A <b>bold</b> description',
			'twitter_handle'       => '@user<script>alert(1)</script>',
			'facebook_url'         => 'https://facebook.com/page',
			'homepage_title'       => '<em>Home</em>',
			'homepage_description' => 'Homepage <script>desc</script>',
			'default_og_image'     => 'https://example.com/img.jpg',
		);

		$clean = $admin->sanitize_settings( $dirty );

		$this->assertStringNotContainsString( '<script>', $clean['title_separator'] );
		$this->assertStringNotContainsString( '<b>', $clean['default_description'] );
		$this->assertStringNotContainsString( '<script>', $clean['twitter_handle'] );
		$this->assertStringNotContainsString( '<em>', $clean['homepage_title'] );
		$this->assertStringNotContainsString( '<script>', $clean['homepage_description'] );
	}

	/**
	 * Test settings sanitization handles empty input.
	 */
	public function test_sanitize_settings_handles_empty_input() {
		$admin = new MetaTag_Admin();
		$clean = $admin->sanitize_settings( array() );

		$this->assertEquals( '|', $clean['title_separator'] );
		$this->assertEquals( '', $clean['twitter_handle'] );
		$this->assertEmpty( $clean['facebook_url'] );
	}

	/**
	 * Test Facebook URL is sanitized to valid URL.
	 */
	public function test_facebook_url_sanitized() {
		$admin = new MetaTag_Admin();
		$clean = $admin->sanitize_settings(
			array(
				'facebook_url' => 'javascript:alert(1)',
			)
		);

		$this->assertEmpty( $clean['facebook_url'] );
	}

	/**
	 * Test default settings contain all expected keys.
	 */
	public function test_default_settings_contain_all_keys() {
		$defaults = MetaTag::get_default_settings();
		$expected = array(
			'title_separator',
			'title_format',
			'default_description',
			'twitter_handle',
			'facebook_url',
			'homepage_title',
			'homepage_description',
			'default_og_image',
		);

		foreach ( $expected as $key ) {
			$this->assertArrayHasKey( $key, $defaults, "Missing default key: {$key}" );
		}
	}
}
