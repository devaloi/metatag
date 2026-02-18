<?php
/**
 * Admin settings page.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag_Admin
 *
 * Registers and renders the plugin settings page.
 */
class MetaTag_Admin {

	/**
	 * Option name for plugin settings.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'metatag_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add the settings page under the Settings menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'MetaTag SEO Settings', 'metatag' ),
			__( 'MetaTag SEO', 'metatag' ),
			'manage_options',
			'metatag-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 */
	public function register_settings() {
		register_setting(
			'metatag_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => MetaTag::get_default_settings(),
			)
		);

		// General section.
		add_settings_section(
			'metatag_general',
			__( 'General Settings', 'metatag' ),
			array( $this, 'render_general_section' ),
			'metatag-settings'
		);

		add_settings_field(
			'title_separator',
			__( 'Title Separator', 'metatag' ),
			array( $this, 'render_separator_field' ),
			'metatag-settings',
			'metatag_general'
		);

		add_settings_field(
			'title_format',
			__( 'Title Format', 'metatag' ),
			array( $this, 'render_title_format_field' ),
			'metatag-settings',
			'metatag_general'
		);

		add_settings_field(
			'default_description',
			__( 'Default Description', 'metatag' ),
			array( $this, 'render_default_description_field' ),
			'metatag-settings',
			'metatag_general'
		);

		// Social section.
		add_settings_section(
			'metatag_social',
			__( 'Social Profiles', 'metatag' ),
			array( $this, 'render_social_section' ),
			'metatag-settings'
		);

		add_settings_field(
			'twitter_handle',
			__( 'Twitter Handle', 'metatag' ),
			array( $this, 'render_twitter_handle_field' ),
			'metatag-settings',
			'metatag_social'
		);

		add_settings_field(
			'facebook_url',
			__( 'Facebook Page URL', 'metatag' ),
			array( $this, 'render_facebook_url_field' ),
			'metatag-settings',
			'metatag_social'
		);

		add_settings_field(
			'default_og_image',
			__( 'Default OG Image URL', 'metatag' ),
			array( $this, 'render_default_og_image_field' ),
			'metatag-settings',
			'metatag_social'
		);

		// Homepage section.
		add_settings_section(
			'metatag_homepage',
			__( 'Homepage Overrides', 'metatag' ),
			array( $this, 'render_homepage_section' ),
			'metatag-settings'
		);

		add_settings_field(
			'homepage_title',
			__( 'Homepage Title', 'metatag' ),
			array( $this, 'render_homepage_title_field' ),
			'metatag-settings',
			'metatag_homepage'
		);

		add_settings_field(
			'homepage_description',
			__( 'Homepage Description', 'metatag' ),
			array( $this, 'render_homepage_description_field' ),
			'metatag-settings',
			'metatag_homepage'
		);
	}

	/**
	 * Sanitize all settings on save.
	 *
	 * @param array $input Raw settings input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['title_separator']      = sanitize_text_field( $input['title_separator'] ?? '|' );
		$sanitized['title_format']         = sanitize_text_field( $input['title_format'] ?? '%title% | %sitename%' );
		$sanitized['default_description']  = sanitize_textarea_field( $input['default_description'] ?? '' );
		$sanitized['twitter_handle']       = sanitize_text_field( $input['twitter_handle'] ?? '' );
		$sanitized['facebook_url']         = esc_url_raw( $input['facebook_url'] ?? '' );
		$sanitized['homepage_title']       = sanitize_text_field( $input['homepage_title'] ?? '' );
		$sanitized['homepage_description'] = sanitize_textarea_field( $input['homepage_description'] ?? '' );
		$sanitized['default_og_image']     = esc_url_raw( $input['default_og_image'] ?? '' );

		return $sanitized;
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'metatag_settings_group' );
				do_settings_sections( 'metatag-settings' );
				submit_button( __( 'Save Settings', 'metatag' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the General section description.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure global title and description defaults.', 'metatag' ) . '</p>';
	}

	/**
	 * Render the Social section description.
	 */
	public function render_social_section() {
		echo '<p>' . esc_html__( 'Set your social media profiles for Open Graph and Twitter Cards.', 'metatag' ) . '</p>';
	}

	/**
	 * Render the Homepage section description.
	 */
	public function render_homepage_section() {
		echo '<p>' . esc_html__( 'Override the SEO title and description for your homepage.', 'metatag' ) . '</p>';
	}

	/**
	 * Render the title separator field.
	 */
	public function render_separator_field() {
		$value      = MetaTag::get_setting( 'title_separator', '|' );
		$separators = array( '|', '-', '–', '—', '·', '•', '»', '/' );
		echo '<select name="' . esc_attr( self::OPTION_NAME ) . '[title_separator]">';
		foreach ( $separators as $sep ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $sep ),
				selected( $value, $sep, false ),
				esc_html( $sep )
			);
		}
		echo '</select>';
	}

	/**
	 * Render the title format field.
	 */
	public function render_title_format_field() {
		$value = MetaTag::get_setting( 'title_format', '%title% | %sitename%' );
		printf(
			'<input type="text" name="%s[title_format]" value="%s" class="regular-text" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $value )
		);
		echo '<p class="description">' . esc_html__( 'Available tokens: %title%, %sitename%', 'metatag' ) . '</p>';
	}

	/**
	 * Render the default description field.
	 */
	public function render_default_description_field() {
		$value = MetaTag::get_setting( 'default_description' );
		printf(
			'<textarea name="%s[default_description]" rows="3" class="large-text">%s</textarea>',
			esc_attr( self::OPTION_NAME ),
			esc_textarea( $value )
		);
		echo '<p class="description">' . esc_html__( 'Used as fallback when no per-post description is set.', 'metatag' ) . '</p>';
	}

	/**
	 * Render the Twitter handle field.
	 */
	public function render_twitter_handle_field() {
		$value = MetaTag::get_setting( 'twitter_handle' );
		printf(
			'<input type="text" name="%s[twitter_handle]" value="%s" class="regular-text" placeholder="@username" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $value )
		);
	}

	/**
	 * Render the Facebook URL field.
	 */
	public function render_facebook_url_field() {
		$value = MetaTag::get_setting( 'facebook_url' );
		printf(
			'<input type="url" name="%s[facebook_url]" value="%s" class="regular-text" placeholder="https://facebook.com/yourpage" />',
			esc_attr( self::OPTION_NAME ),
			esc_url( $value )
		);
	}

	/**
	 * Render the default OG image field.
	 */
	public function render_default_og_image_field() {
		$value = MetaTag::get_setting( 'default_og_image' );
		printf(
			'<input type="url" name="%s[default_og_image]" value="%s" class="regular-text" placeholder="https://example.com/image.jpg" />',
			esc_attr( self::OPTION_NAME ),
			esc_url( $value )
		);
		echo '<p class="description">' . esc_html__( 'Fallback image used when a post has no featured image.', 'metatag' ) . '</p>';
	}

	/**
	 * Render the homepage title field.
	 */
	public function render_homepage_title_field() {
		$value = MetaTag::get_setting( 'homepage_title' );
		printf(
			'<input type="text" name="%s[homepage_title]" value="%s" class="regular-text" />',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $value )
		);
	}

	/**
	 * Render the homepage description field.
	 */
	public function render_homepage_description_field() {
		$value = MetaTag::get_setting( 'homepage_description' );
		printf(
			'<textarea name="%s[homepage_description]" rows="3" class="large-text">%s</textarea>',
			esc_attr( self::OPTION_NAME ),
			esc_textarea( $value )
		);
	}
}
