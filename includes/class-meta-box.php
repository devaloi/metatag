<?php
/**
 * Meta box for per-post SEO fields.
 *
 * @package MetaTag
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaTag_Meta_Box
 *
 * Registers and renders the per-post SEO meta box.
 */
class MetaTag_Meta_Box {

	/** Recommended maximum characters for SEO title. */
	const TITLE_MAX_CHARS = 60;

	/** Recommended maximum characters for meta description. */
	const DESC_MAX_CHARS = 160;

	/** Hard limit for meta description field. */
	const DESC_HARD_LIMIT = 320;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the meta box for posts and pages.
	 */
	public function register_meta_box() {
		$post_types = apply_filters( 'metatag_meta_box_post_types', array( 'post', 'page' ) );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'metatag-seo',
				__( 'MetaTag — SEO Settings', 'metatag' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Enqueue admin assets on post edit screens.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_style(
			'metatag-admin',
			METATAG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			METATAG_VERSION
		);

		wp_enqueue_script(
			'metatag-admin',
			METATAG_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			METATAG_VERSION,
			true
		);
	}

	/**
	 * Render the meta box content.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'metatag_save_meta', 'metatag_nonce' );

		$fields = $this->get_field_values( $post->ID );
		?>
		<div class="metatag-meta-box">
			<div class="metatag-field">
				<label for="metatag-title">
					<?php esc_html_e( 'SEO Title', 'metatag' ); ?>
				</label>
				<input
					type="text"
					id="metatag-title"
					name="metatag_title"
					value="<?php echo esc_attr( $fields['title'] ); ?>"
					class="widefat metatag-char-count"
					data-target="metatag-title-count"
					data-limit="<?php echo esc_attr( self::TITLE_MAX_CHARS ); ?>"
					maxlength="120"
				/>
				<span class="metatag-counter">
					<span id="metatag-title-count"><?php echo esc_html( strlen( $fields['title'] ) ); ?></span>/<?php echo esc_html( self::TITLE_MAX_CHARS ); ?>
				</span>
			</div>

			<div class="metatag-field">
				<label for="metatag-description">
					<?php esc_html_e( 'Meta Description', 'metatag' ); ?>
				</label>
				<textarea
					id="metatag-description"
					name="metatag_description"
					class="widefat metatag-char-count"
					data-target="metatag-desc-count"
					data-limit="<?php echo esc_attr( self::DESC_MAX_CHARS ); ?>"
					rows="3"
					maxlength="<?php echo esc_attr( self::DESC_HARD_LIMIT ); ?>"
				><?php echo esc_textarea( $fields['description'] ); ?></textarea>
				<span class="metatag-counter">
					<span id="metatag-desc-count"><?php echo esc_html( strlen( $fields['description'] ) ); ?></span>/<?php echo esc_html( self::DESC_MAX_CHARS ); ?>
				</span>
			</div>

			<div class="metatag-field">
				<label for="metatag-keyword">
					<?php esc_html_e( 'Focus Keyword', 'metatag' ); ?>
				</label>
				<input
					type="text"
					id="metatag-keyword"
					name="metatag_keyword"
					value="<?php echo esc_attr( $fields['keyword'] ); ?>"
					class="widefat"
				/>
			</div>

			<div class="metatag-field">
				<label for="metatag-canonical">
					<?php esc_html_e( 'Canonical URL', 'metatag' ); ?>
				</label>
				<input
					type="url"
					id="metatag-canonical"
					name="metatag_canonical"
					value="<?php echo esc_url( $fields['canonical'] ); ?>"
					class="widefat"
					placeholder="<?php esc_attr_e( 'Leave blank for default', 'metatag' ); ?>"
				/>
			</div>

			<div class="metatag-field metatag-checkboxes">
				<label>
					<input
						type="checkbox"
						name="metatag_noindex"
						value="1"
						<?php checked( $fields['noindex'], '1' ); ?>
					/>
					<?php esc_html_e( 'noindex — Prevent search engines from indexing this page', 'metatag' ); ?>
				</label>
				<br />
				<label>
					<input
						type="checkbox"
						name="metatag_nofollow"
						value="1"
						<?php checked( $fields['nofollow'], '1' ); ?>
					/>
					<?php esc_html_e( 'nofollow — Prevent search engines from following links on this page', 'metatag' ); ?>
				</label>
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['metatag_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['metatag_nonce'] ) ), 'metatag_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'title'       => isset( $_POST['metatag_title'] ) ? sanitize_text_field( wp_unslash( $_POST['metatag_title'] ) ) : '',
			'description' => isset( $_POST['metatag_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['metatag_description'] ) ) : '',
			'keyword'     => isset( $_POST['metatag_keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['metatag_keyword'] ) ) : '',
			'canonical'   => isset( $_POST['metatag_canonical'] ) ? esc_url_raw( wp_unslash( $_POST['metatag_canonical'] ) ) : '',
			'noindex'     => isset( $_POST['metatag_noindex'] ) ? '1' : '0',
			'nofollow'    => isset( $_POST['metatag_nofollow'] ) ? '1' : '0',
		);

		foreach ( $fields as $key => $value ) {
			update_post_meta( $post_id, MetaTag_Helpers::META_PREFIX . $key, $value );
		}
	}

	/**
	 * Get saved meta field values for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array Field values.
	 */
	public function get_field_values( $post_id ) {
		$keys = array( 'title', 'description', 'keyword', 'canonical', 'noindex', 'nofollow' );
		$values = array();

		foreach ( $keys as $key ) {
			$values[ $key ] = get_post_meta( $post_id, MetaTag_Helpers::META_PREFIX . $key, true );
			if ( false === $values[ $key ] ) {
				$values[ $key ] = '';
			}
		}

		return $values;
	}
}
