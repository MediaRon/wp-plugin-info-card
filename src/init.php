<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function wp_plugin_info_card_cgb_block_assets() { // phpcs:ignore
	// Styles.
	wp_enqueue_style(
		'wp_plugin_info_card-cgb-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-editor' ),
		WPPIC_VERSION,
		'all'
	);
}

// Hook: Frontend assets.
add_action( 'enqueue_block_assets', 'wp_plugin_info_card_cgb_block_assets' );

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function wp_plugin_info_card_cgb_editor_assets() { // phpcs:ignore

	// Scripts.
	wp_enqueue_script(
		'wp_plugin_info_card-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		WPPIC_VERSION,
		true // Enqueue the script in the footer.
	);
	wp_localize_script(
		'wp_plugin_info_card-cgb-block-js',
		'wppic',
		array(
			'rest_url'             => get_rest_url(),
			'query_preview'        => plugins_url( 'img/wp-query-preview.jpg', __FILE__ ),
			'wppic_preview'        => plugins_url( 'img/wp-pic-preview.jpg', __FILE__ ),
			'wppic_banner_default' => plugins_url( 'img/default-banner.png', __FILE__ ),
		)
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'wp_plugin_info_card-cgb-block-js', 'wp-plugin-info-card' );
	} elseif ( function_exists( 'gutenberg_get_jed_locale_data' ) ) {
		$locale  = gutenberg_get_jed_locale_data( 'wp-plugin-info-card' );
		$content = 'wp.i18n.setLocaleData( ' . wp_json_encode( $locale ) . ', "wp-plugin-info-card" );';
		wp_script_add_data( 'wp_plugin_info_card-cgb-block-js', 'data', $content );
	} elseif ( function_exists( 'wp_get_jed_locale_data' ) ) {
		/* for 5.0 */
		$locale  = wp_get_jed_locale_data( 'wp-plugin-info-card' );
		$content = 'wp.i18n.setLocaleData( ' . wp_json_encode( $locale ) . ', "wp-plugin-info-card" );';
		wp_script_add_data( 'wp_plugin_info_card-cgb-block-js', 'data', $content );
	}

	// Styles.
	wp_enqueue_style(
		'wp_plugin_info_card-cgb-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ),
		WPPIC_VERSION,
		'all'
	);
}

// Hook: Editor assets.
add_action( 'enqueue_block_editor_assets', 'wp_plugin_info_card_cgb_editor_assets' );

function wppic_register_block() {
	$options = get_option( 'wppic_settings' );

	$default_scheme = isset( $options['colorscheme'] ) ? $options['colorscheme'] : 'default';
	$default_layout = isset( $options['default_layout'] ) ? $options['default_layout'] : 'card';
	register_block_type(
		'wp-plugin-info-card/wp-plugin-info-card',
		array(
			'attributes'      => array(
				'assetData' => array(
					'type' => 'array',
					'default' => [],
				),
				'type'        => array(
					'type'    => 'string',
					'default' => 'plugin',
				),
				'slug'        => array(
					'type'    => 'string',
					'default' => 'wp-plugin-info-card',
				),
				'loading'     => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'html'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'align'       => array(
					'type'    => 'string',
					'default' => 'full',
				),
				'image'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'containerid' => array(
					'type'    => 'string',
					'default' => '',
				),
				'margin'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'clear'       => array(
					'type'    => 'string',
					'default' => 'none',
				),
				'expiration'  => array(
					'type'    => 'int',
					'default' => 0,
				),
				'ajax'        => array(
					'type'    => 'string',
					'default' => 'false',
				),
				'scheme'      => array(
					'type'    => 'string',
					'default' => $default_scheme,
				),
				'layout'      => array(
					'type'    => 'string',
					'default' => $default_layout,
				),
				'custom'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'width'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'preview'     => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'multi'       => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
			'render_callback' => 'wppic_block_editor',
		)
	);

	register_block_type(
		'wp-plugin-info-card/wp-plugin-info-card-query',
		array(
			'attributes'      => array(
				'search'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'tag'         => array(
					'type'    => 'string',
					'default' => '',
				),
				'author'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'user'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'browse'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'per_page'    => array(
					'type'    => 'int',
					'default' => 24,
				),
				'per_page'    => array(
					'type'    => 'int',
					'default' => 24,
				),
				'cols'        => array(
					'type'    => 'int',
					'default' => 2,
				),
				'type'        => array(
					'type'    => 'string',
					'default' => 'plugin',
				),
				'slug'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'loading'     => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'html'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'align'       => array(
					'type'    => 'string',
					'default' => 'full',
				),
				'image'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'containerid' => array(
					'type'    => 'string',
					'default' => '',
				),
				'margin'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'clear'       => array(
					'type'    => 'string',
					'default' => 'none',
				),
				'expiration'  => array(
					'type'    => 'int',
					'default' => 0,
				),
				'ajax'        => array(
					'type'    => 'string',
					'default' => 'false',
				),
				'scheme'      => array(
					'type'    => 'string',
					'default' => 'default',
				),
				'layout'      => array(
					'type'    => 'string',
					'default' => 'card',
				),
				'custom'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'width'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'preview'     => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
			'render_callback' => 'wppic_block_editor_query',
		)
	);
}

function wppic_block_editor_query( $attributes ) {
	if ( is_admin() ) {
		return;
	}
	$args = array(
		'cols'        => $attributes['cols'],
		'per_page'    => $attributes['per_page'],
		'type'        => $attributes['type'],
		'align'       => $attributes['align'],
		'image'       => $attributes['image'],
		'containerid' => $attributes['containerid'],
		'margin'      => $attributes['margin'],
		'clear'       => $attributes['clear'],
		'expiration'  => $attributes['expiration'],
		'ajax'        => $attributes['ajax'],
		'scheme'      => $attributes['scheme'],
		'layout'      => $attributes['layout'],
	);
	if ( ! empty( $attributes['browse'] ) ) {
		$args['browse'] = $attributes['browse'];
	}
	if ( ! empty( $attributes['search'] ) ) {
		$args['search'] = $attributes['search'];
	}
	if ( ! empty( $attributes['tag'] ) ) {
		$args['tag'] = $attributes['tag'];
	}
	if ( ! empty( $attributes['user'] ) ) {
		$args['user'] = $attributes['user'];
	}
	if ( ! empty( $attributes['author'] ) ) {
		$args['author'] = $attributes['author'];
	}
	$html = '';
	if ( '' !== $attributes['width'] ) {
		$html = sprintf( '<div class="wp-pic-full-width">%s</div>', wppic_shortcode_query_function( $args ) );
	} else {
		$html = wppic_shortcode_query_function( $args );
	}
	return $html;
}

function wppic_block_editor( $attributes ) {
	if ( is_admin() ) {
		return;
	}
	$args = array(
		'type'        => $attributes['type'],
		'slug'        => $attributes['slug'],
		'align'       => $attributes['align'],
		'image'       => $attributes['image'],
		'containerid' => $attributes['containerid'],
		'margin'      => $attributes['margin'],
		'clear'       => $attributes['clear'],
		'expiration'  => $attributes['expiration'],
		'ajax'        => $attributes['ajax'],
		'scheme'      => $attributes['scheme'],
		'layout'      => $attributes['layout'],
		'multi'       => true,
	);
	$html = '';
	if ( '' !== $attributes['width'] ) {
		$html = sprintf( '<div class="wp-pic-full-width">%s</div>', wppic_shortcode_function( $args ) );
	} else {
		$html = wppic_shortcode_function( $args );
	}
	return $html;
}

add_action( 'init', 'wppic_register_block' );
