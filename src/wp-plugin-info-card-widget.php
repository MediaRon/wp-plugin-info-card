<?php
/**
 * Render a Dashboard Widget.
 *
 * @package WP Plugin Info Card.
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}

/**
 * Enqueue style on dashboard if widget is activated
 *
 * @param string $hook Current admin page.
 */
function wppic_widget_enqueue( $hook ) {
	if ( 'index.php' !== $hook ) {
		return;
	}
	// Enqueue sripts and style.
	wppic_admin_scripts();
	wppic_admin_css();
}


/***************************************************************
 * Register Dashboard Widget
 */
if ( ! function_exists( 'wppic_dashboard_widgets' ) ) {
	/**
	 * Initialize a dashboard widget.
	 */
	function wppic_add_dashboard_widgets() {
		global  $wppic_settings;
		if ( isset( $wppic_settings['widget'] ) && true === filter_var( $wppic_settings['widget'], FILTER_VALIDATE_BOOLEAN ) ) {
			wp_add_dashboard_widget( 'wppic-dashboard-widget', '<img src="' . WPPIC_URL . 'img/wppic.svg" class="wppic-logo" alt="b*web" style="display:none"/>&nbsp;&nbsp;' . WPPIC_NAME . ' board', 'wppic_widgets' );
			add_action( 'admin_enqueue_scripts', 'wppic_widget_enqueue' );
		}
	}
}
add_action( 'wp_dashboard_setup', 'wppic_add_dashboard_widgets' );


/***************************************************************
 * Dashboard Widget function
 ***************************************************************/
function wppic_widgets() {
	global  $wppic_settings;
	$list_state = false;
	$ajax_class = '';

	if ( isset( $wppic_settings['ajax'] ) && true === filter_var( $wppic_settings['ajax'], FILTER_VALIDATE_BOOLEAN ) ) {
		$ajax_class = 'ajax-call';
	}

	$wppic_types = array();
	$wppic_types = apply_filters( 'wppic_add_widget_type', $wppic_types );

	$content = '<div class="wp-pic-list ' . $ajax_class . '">';

	foreach ( $wppic_types as $wppic_type ) {

		$rows = array();

		if ( isset( $wppic_settings[ $wppic_type[1] ] ) && ! empty( $wppic_settings[ $wppic_type[1] ] ) ) {

			$list_state  = true;
			$other_lists = false;

			foreach ( $wppic_types as $wppic_list ) {
				if ( $wppic_type[1] !== $wppic_list[1] ) {
					$rows[] = $wppic_list[1];
				}
			}

			foreach ( $rows as $row ) {
				if ( isset( $wppic_settings[ $row ] ) && ! empty( $wppic_settings[ $row ] ) ) {
					$other_lists = true;
				}
			}

			if ( $other_lists ) {
				$content .= '<h4>' . $wppic_type[2] . '</h4>';
			}

			if ( isset( $wppic_settings['ajax'] ) && true === filter_var( $wppic_settings['ajax'], FILTER_VALIDATE_BOOLEAN ) ) {
				$content .= '<div class="wp-pic-loading" style="background-image: url( ' . admin_url() . 'images/spinner-2x.gif);" data-type="' . $wppic_type[0] . '" data-list="' . htmlspecialchars( wp_json_encode( ( $wppic_settings[ $wppic_type[1] ] ) ), ENT_QUOTES, 'UTF-8' ) . '"></div>';
			} else {
				$content .= wppic_widget_render( $wppic_type[0], $wppic_settings[ $wppic_type[1] ] );
			}
		}
	}

	// Nothing found.
	if ( ! $list_state ) {

		$content .= '<div class="wp-pic-item" style="display:block;">';
		$content .= '<span class="wp-pic-no-item"><a href="admin.php?page=' . WPPIC_ID . '">' . __( 'Nothing found, please add at least one item in the WP Plugin Info Card settings page.', 'wp-plugin-info-card' ) . '</a></span>';
		$content .= '</div>';

	}

	$content .= '</div>';

	echo wp_kses_post( $content );

} //end of wppic_widgets


/**
 * Render the dashboard widget for WP Plugin Info Card.
 *
 * @param string $type  The type to render (plugin/theme).
 * @param string $slugs The slugs to render.
 */
function wppic_widget_render( $type = null, $slugs = null ) {

	if ( isset( $_POST['wppic-type'] ) && ! empty( $_POST['wppic-type'] ) ) { // phpcs:ignore
		$type = esc_html( $_POST['wppic-type'] ); // phpcs:ignore
	}

	if ( isset( $_POST['wppic-list'] ) && ! empty( $_POST['wppic-list'] ) ) { // phpcs:ignore
		$slugs = array( esc_html( $_POST['wppic-list'] ) ); // phpcs:ignore
	}

	$content = '';

	if ( ! empty( $slugs ) ) {
		foreach ( $slugs as $slug ) {
			$wppic_data = wppic_api_parser( $type, $slug, '5', 'widget' );

			if ( ! $wppic_data ) {

				$content .= '<div class="wp-pic-item ' . $slug . '">';
				$content .= '<span class="wp-pic-no-item">' . __( 'Item not found:', 'wp-plugin-info-card' ) . ' "' . $slug . '" ' . __( 'does not exist.', 'wp-plugin-info-card' ) . '</span>';
				$content .= '</div>';

			} else {

				$content = apply_filters( 'wppic_add_widget_item', $content, $wppic_data, $type );

			}
		}
	}

	if ( ! empty( $_POST['wppic-list'] ) ) { // phpcs:ignore
		echo wp_kses_post( $content );
		die();
	} else {
		return $content;
	}

}
add_action( 'wp_ajax_wppic_widget_render', 'wppic_widget_render' );
