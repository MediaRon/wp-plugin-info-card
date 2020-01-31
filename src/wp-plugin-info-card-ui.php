<?php
/**
 * Add TinyMCE Button for plugin info card.
 *
 * @package WP Plugin Info Card
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}


/***************************************************************
 * Hooks custom TinyMCE button function
 ***************************************************************/
function wppic_add_mce_button() {

	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	if ( 'true' == get_user_option( 'rich_editing' ) ) { // phpcs:ignore

		add_filter( 'mce_external_plugins', 'wppic_tinymce_plugin' );
		add_filter( 'mce_buttons', 'wppic_register_mce_button' );

		// Load stylesheet for tinyMCE button only.
		wp_enqueue_style( 'wppic-admin-css', WPPIC_URL . 'css/wppic-admin-style.css', array(), WPPIC_VERSION );
		wp_enqueue_script( 'wppic-ui-scripts', WPPIC_URL . 'js/wppic-ui-scripts.js', array( 'jquery' ), WPPIC_VERSION, true );

		// Define additionnal hookable MCE parameters.
		$mce_add_param = array(
			'types'   => array(),
			'layouts' => array(
				array(
					'text'  => __( 'Card (default)', 'wp-plugin-info-card' ),
					'value' => '',
				),
				array(
					'text'  => __( 'Large', 'wp-plugin-info-card' ),
					'value' => 'large',
				),
				array(
					'text'  => __( 'WordPress', 'wp-plugin-info-card' ),
					'value' => 'wordpress',
				),
			),

		);
		$mce_add_param = apply_filters( 'wppic_add_mce_type', $mce_add_param );
		$mce_add_param = wp_json_encode( $mce_add_param );

		echo '<script>// <![CDATA[
		  var wppicMceList = ' . esc_js( $mce_add_param ) . ';
		// ]]></script>';

	}

}
add_action( 'admin_head', 'wppic_add_mce_button' );


/**
 * Load plugin translation for - TinyMCE API.
 *
 * @param array $arr The translation array for TinyMCE.
 *
 * @return array Updated translation array for TinyMCE.
 */
function wppic_add_tinymce_lang( $arr ) {
	$arr[] = plugin_dir_path( __FILE__ ) . 'wp-plugin-info-card-ui-lang.php';
	return $arr;
}
add_filter( 'mce_external_languages', 'wppic_add_tinymce_lang', 10, 1 );


/**
 * Load custom JS options for - TinyMCE API.
 *
 * @param array $plugin_array Scripts for TinyMCE.
 *
 * @return array Updated scripts array for TinyMCE.
 */
function wppic_tinymce_plugin( $plugin_array ) {
	$plugin_array['wppic_mce_button'] = WPPIC_URL . 'js/wppic-ui-mce.js';
	return $plugin_array;
}


/**
 * Load custom button for - TinyMCE API.
 *
 * @param array $buttons Tiny MCE button.
 *
 * @return array Updated buttons for TinyMCE.
 */
function wppic_register_mce_button( $buttons ) {
	array_push( $buttons, 'wppic_mce_button' );
	return $buttons;
}
