<?php
/**
 * Plugin Name: WP Plugin Info Card
 * Plugin URI: https://mediaron.com/wp-plugin-info-card/
 * Description: WP Plugin Info Card displays plugins & themes identity cards in a beautiful box with a smooth rotation effect using WordPress.org Plugin API & WordPress.org Theme API. Dashboard widget included.
 * Author: Brice CAPOBIANCO, Ronald Huereca
 * Author URI: http://b-website.com/
 * Version: 3.1.20
 * Domain Path: /langs
 * Text Domain: wp-plugin-info-card
 *
 * @package wp-plugin-info-card
 */

/***************************************************************
 * SECURITY : Exit if accessed directly
 ***************************************************************/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}


/***************************************************************
 * Define constants
 */
if ( ! defined( 'WPPIC_VERSION' ) ) {
	define( 'WPPIC_VERSION', '3.1.20' );
}
if ( ! defined( 'WPPIC_PATH' ) ) {
	define( 'WPPIC_PATH', plugin_dir_path( __FILE__ ) . '/src/' );
}
if ( ! defined( 'WPPIC_URL' ) ) {
	define( 'WPPIC_URL', plugin_dir_url( __FILE__ ) . '/src/' );
}
if ( ! defined( 'WPPIC_BASE' ) ) {
	define( 'WPPIC_BASE', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'WPPIC_NAME' ) ) {
	define( 'WPPIC_NAME', 'WP Plugin Info Card' );
}
if ( ! defined( 'WPPIC_NAME_FULL' ) ) {
	define( 'WPPIC_NAME_FULL', 'WP Plugin Info Card by b*web and Ronald Huereca' );
}
if ( ! defined( 'WPPIC_ID' ) ) {
	define( 'WPPIC_ID', 'wp-plugin-info-card' );
}


/***************************************************************
 * Get options
 */
global  $wppic_settings;
$wppic_settings = get_option( 'wppic_settings' );

global  $wppic_date_format;
$wppic_date_format = get_option( 'date_format' );


/***************************************************************
 * Load plugin files
 */
$wppic_files = array( 'api', 'shortcode', 'admin', 'widget', 'ui', 'add-plugin', 'add-theme', 'query' );
foreach ( $wppic_files as $wppic_file ) {
	require_once WPPIC_PATH . 'wp-plugin-info-card-' . $wppic_file . '.php';
}


/**
 * Load the plugin's text domain.
 */
function wppic_load_textdomain() {
	$path = dirname( plugin_basename( __FILE__ ) ) . '/langs/';
	load_plugin_textdomain( 'wp-plugin-info-card', false, $path );
}
add_action( 'init', 'wppic_load_textdomain' );


/**
 * Add settings to the plugin's row.
 *
 * @see plugin_action_links
 * @since 3.2.0
 *
 * @param array $links Settings array.
 *
 * @return array Updated settings array.
 */
function wppic_settings_link( $links ) {
	$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . WPPIC_ID ) ) . '" title="' . esc_attr__( 'WP Plugin Info Card Settings', 'wp-plugin-info-card' ) . '">' . esc_html__( 'Settings', 'wp-plugin-info-card' ) . '</a>';
	return $links;
}
add_filter( 'plugin_action_links_' . WPPIC_BASE, 'wppic_settings_link' );

/**
 * Add custom meta link on plugin list page.
 *
 * @see plugin_row_meta
 * @since 3.2.0
 *
 * @param array  $links Add links to the plugin row.
 * @param string $file The path to the plugin file.
 *
 * @return array Updated links to the plugin row.
 */
function wppic_meta_links( $links, $file ) {
	if ( 'wp-plugin-info-card/wp-plugin-info-card.php' === $file ) {
		$links[] = '<a href="https://mediaron.com/wp-plugin-info-card/" target="_blank" title="' . __( 'Documentation and examples', 'wp-plugin-info-card' ) . '"><strong>' . __( 'Documentation and examples', 'wp-plugin-info-card' ) . '</strong></a>';
		$links[] = '<a href="http://b-website.com/category/plugins" target="_blank" title="' . __( 'More plugins by b*web', 'wp-plugin-info-card' ) . '">' . __( 'More plugins by b*web', 'wp-plugin-info-card' ) . '</a>';
		$links[] = '<a href="https://mediaron.com/project-type/wordpress-plugins/" target="_blank" title="' . __( 'More plugins by MediaRon', 'wp-plugin-info-card' ) . '">' . __( 'More plugins by MediaRon', 'wp-plugin-info-card' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'wppic_meta_links', 10, 2 );


/**
 * Add a site icon to WP Plugin Info Card admin page.
 *
 * @see admin_head
 * @since 3.2.0
 */
function wppic_add_favicon() {
	$screen = get_current_screen();
	if ( 'toplevel_page_' . WPPIC_ID !== $screen->id ) {
		return;
	}

	$favicon_url = WPPIC_URL . 'img/wppic.svg';
	echo '<link rel="shortcut icon" href="' . esc_url( $favicon_url ) . '" />';
}
add_action( 'admin_head', 'wppic_add_favicon' );


/**
 * Purge all expired transients.
 */
function wppic_delete_transients() {
	global $wpdb;
	$wppic_transients = $wpdb->get_results( // phpcs:ignore
		"SELECT option_name AS name,
		option_value AS value FROM $wpdb->options
		WHERE option_name LIKE '_transient_wppic_%'", // phpcs:ignore
		ARRAY_A
	);
	foreach ( $wppic_transients as $single_transient ) {
		delete_transient( str_replace( '_transient_', '', $single_transient['name'] ) );
	}
}


/**
 * Cron to schedule weekly cleaning of transients.
 *
 * @see cron_schedules
 * @since 3.2.0
 *
 * @param array $schedules The cron schedules.
 *
 * @return array Updated schedules.
 */
function wppic_add_weekly( $schedules ) {
	$schedules['wppic-weekly'] = array(
		'interval' => WEEK_IN_SECONDS,
		'display'  => __( 'Once Weekly' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'wppic_add_weekly' );

/**
 * Schedule an event to delete outdated transients.
 *
 * @see register_activation_hook
 * @since 3.2.0
 */
function wppic_cron_activation() {
	wp_schedule_event( time(), 'wppic-weekly', 'wppic_daily_cron' );
}
add_action( 'wppic_daily_cron', 'wppic_delete_transients' );


/**
 * Uninstall callback for uninstalling WP Plugin Info Card.
 */
function wppic_uninstall() {
	// Remove option from DB.
	delete_option( 'wppic_settings' );
	// deactivate cron.
	wp_clear_scheduled_hook( 'wppic_daily_cron' );
	// Purge transients.
	wppic_delete_transients();
}


/***************************************************************
 * Hooks for install & uninstall
 ***************************************************************/
function wppic_activation() {
	register_uninstall_hook( __FILE__, 'wppic_uninstall' );
}
register_activation_hook( __FILE__, 'wppic_activation' );
register_activation_hook( __FILE__, 'wppic_cron_activation' );
register_activation_hook( __FILE__, 'wppic_delete_transients' );


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
if ( function_exists( 'register_block_type' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
}
