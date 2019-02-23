<?php
class S2_Block_Editor {
	/**
	 * Constructor
	 */
	public function __construct() {
		// maybe use dev scripts
		$this->script_debug = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		add_action( 'init', array( &$this, 'register_s2_meta' ) );
		add_action( 'rest_api_init', array( $this, 'register_preview_endpoint' ) );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( &$this, 'gutenberg_block_editor_assets' ), 6 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'gutenberg_i18n' ), 6 );
		}
	}

	/**
	 * Register _s2mail meta data for Block Editor
	 */
	public function register_s2_meta() {
		register_meta(
			'post',
			'_s2mail',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Register REST endpointsfor preview email
	 */
	public function register_preview_endpoint() {
		register_rest_route(
			's2/v1',
			'/preview/(?P<id>[0-9]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'preview' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return preg_match( '/\\d/', $param ) > 0;
						},
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Function to trigger Preview email on REST API request
	 */
	public function preview( $data ) {
		global $mysubscribe2;
		$post = get_post( intval( $data['id'] ) );

		$current_user = wp_get_current_user();
		if ( 0 === $current_user->ID ) {
			return false;
		}
		$mysubscribe2->publish( $post, $current_user->user_email );
		return true;
	}

	/**
	 * Enqueue Block Editor assets
	 */
	public function gutenberg_block_editor_assets() {
		global $pagenow;
		if ( ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'page-new.php', 'page.php' ), true ) ) {
			return;
		}

		wp_enqueue_script(
			'subscribe2-shortcode',
			S2URL . 'gutenberg/shortcode' . $this->script_debug . '.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
			'1.1',
			true
		);

		register_block_type(
			'subscribe2-html/shortcode',
			array(
				'editor_script' => 'subscribe2-shortcode',
			)
		);

		wp_enqueue_script(
			'subscribe2-sidebar',
			S2URL . 'gutenberg/sidebar' . $this->script_debug . '.js',
			array( 'wp-plugins', 'wp-element', 'wp-i18n', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-compose', 'wp-api-fetch' ),
			'1.0',
			true
		);
	}

	/**
	 * Handle translation of Block Editor assets
	 */
	public function gutenberg_i18n() {
		global $pagenow;
		if ( ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'page-new.php', 'page.php' ), true ) ) {
			return;
		}

		$translations = get_translations_for_domain( 'subscribe2' );

		$locale_data = array(
			'' => array(
				'domain'       => 'subscribe2',
				'lang'         => get_user_locale(),
				'plural_forms' => 'nplurals=2; plural=n != 1;',
			),
		);

		foreach ( $translations->entries as $msgid => $entry ) {
			$locale_data[ $msgid ] = $entry->translations;
		}

		wp_add_inline_script(
			'wp-i18n',
			'wp.i18n.setLocaleData( ' . wp_json_encode( $locale_data ) . ', "subscribe2" );'
		);
	}
}
