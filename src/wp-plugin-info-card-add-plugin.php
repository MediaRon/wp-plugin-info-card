<?php
/***************************************************************
 * SECURITY : Exit if accessed directly
***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}


/***************************************************************
 * WPPIC Plugins filters
***************************************************************/
add_filter( 'wppic_add_api_parser', 'wppic_plugin_api_parser', 9, 3 );
add_filter( 'wppic_add_template', 'wppic_plugin_template', 9, 2 );
add_filter( 'wppic_add_mce_type', 'wppic_plugin_mce_type' );
add_filter( 'wppic_add_list_form', 'wppic_plugin_list_form' );
add_filter( 'wppic_add_widget_type', 'wppic_plugin_widget_type' );
add_filter( 'wppic_add_list_valdiation', 'wppic_plugin_list_valdiation' );
add_filter( 'wppic_add_widget_item', 'wppic_plugin_widget_item', 9, 3 );


/***************************************************************
 * Fetching plugins data with WordPress.org Plugin API
 ***************************************************************/
function wppic_plugin_api_parser( $wppic_data, $type, $slug ){

	if ( $type == 'plugin' ) {

		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		$plugin_info = $api = plugins_api( 'plugin_information', array(
			'slug' 		=> $slug,
			'is_ssl' 	=> is_ssl(),
			'fields' 	=> array(
				'sections' 			=> false,
				'tags' 				=> false,
				'short_description' => true,
				'icons' 			=> true,
				'banners' 			=> true,
				'reviews' 			=> false,
				'active_installs' 	=> true
			)
		) );

		if( !is_wp_error( $plugin_info ) ){
			$wppic_data  = (object) array(
				'url' 				=> 'https://wordpress.org/plugins/'.$slug.'/', //depreciated since 2.5
				'name' 				=> $plugin_info->name,
				'slug' 				=> $slug,
				'version' 			=> $plugin_info->version,
				'author' 			=> $plugin_info->author,
				'author_profile'	=> $plugin_info->author_profile,
				'contributors'		=> $plugin_info->contributors,
				'requires' 			=> $plugin_info->requires,
				'tested' 			=> $plugin_info->tested,
				'requires' 			=> $plugin_info->requires,
				'rating' 			=> $plugin_info->rating,
				'num_ratings' 		=> $plugin_info->num_ratings,
				'ratings'			=> $plugin_info->ratings,
				'downloaded'		=> $plugin_info->active_installs,
				'active_installs' 	=> $plugin_info->active_installs,
				'last_updated' 		=> $plugin_info->last_updated,
				'last_updated_mk' 	=> $plugin_info->last_updated,
				'added' 			=> $plugin_info->added,
				'homepage' 			=> $plugin_info->homepage,
				'short_description' => $plugin_info->short_description,
				'download_link' 	=> $plugin_info->download_link,
				'donate_link' 		=> $plugin_info->donate_link,
				'icons' 			=> $plugin_info->icons,
				'banners' 			=> $plugin_info->banners,
			);
		}

	}

	return $wppic_data;

}


/***************************************************************
 * Plugin shortcode template prepare
 ***************************************************************/
function wppic_plugin_template( $content, $data ){
	$type = $data[0];
	$wppic_data = $data[1];
	$image = $data[2];
	$layout = '-' . $data[3];

	if ( $type == 'plugin' ) {

		//load custom user template if exists
		$WPPICtemplatefile = '/wppic-templates/wppic-template-plugin';

		ob_start();
		if ( file_exists( get_stylesheet_directory() . $WPPICtemplatefile .  $layout . '.php' ) ) {
			include( get_stylesheet_directory() . $WPPICtemplatefile .  $layout . '.php' );
		} else if ( file_exists(WPPIC_PATH . $WPPICtemplatefile .  $layout . '.php' ) ) {
			include( WPPIC_PATH . $WPPICtemplatefile .  $layout . '.php' );
		} else {
			include( WPPIC_PATH . $WPPICtemplatefile . '.php' );
		}
		$content .= ob_get_clean();

	}

	return $content;

}


/***************************************************************
 * Add plugin type to mce list
 ***************************************************************/
function wppic_plugin_mce_type( $parameters ){
	$parameters[ 'types' ][] = array( 'text' => 'Plugin', 'value' => 'plugin' );
	return $parameters;
}


/***************************************************************
 * Plugin input option list
 ***************************************************************/
function wppic_plugin_list_form( $parameters ){
	$parameters[] = array(
		'list',
		__( 'Add a plugin', 'wp-plugin-info-card' ),
		__( 'Please refer to the plugin URL on wordpress.org to determine its slug',
		'wp-plugin-info-card' ),
		'https://wordpress.org/plugins/<strong>THE-SLUG</strong>/'
	);
	return $parameters;
}


/***************************************************************
 * Plugin input validation
 ***************************************************************/
function wppic_plugin_list_valdiation( $parameters ){
	$parameters[] = array( 'list', __( 'is not a valid plugin name format. This key has been deleted.', 'wp-plugin-info-card' ), '/^[a-z0-9\-]+$/' );
	return $parameters;
}


/***************************************************************
 * Plugin widget list
 ***************************************************************/
function wppic_plugin_widget_type( $parameters ){
	$parameters[] = array( 'plugin', 'list', __( 'Plugins', 'wp-plugin-info-card' ) );
	return $parameters;
}

/***************************************************************
 * Theme widget item render
 ***************************************************************/
function wppic_plugin_widget_item( $content, $wppic_data, $type ){
	if( $type == 'plugin' ){

		//Date format Internationalizion
		global 	$wppic_date_format;
		$wppic_data->last_updated = date_i18n( $wppic_date_format, strtotime( $wppic_data->last_updated ) );

		$content .= '<div class="wp-pic-item">';
		$content .= '<a class="wp-pic-widget-name" href="' . $wppic_data->url . '" target="_blank" title="' . __( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ) . '">' . $wppic_data->name .'</a>';
		$content .= '<span class="wp-pic-widget-rating"><span>' . __( 'Ratings:', 'wp-plugin-info-card' ) . '</span> ' . $wppic_data->rating .'%';
		if( !empty( $wppic_data->num_ratings ) )
			$content .= ' ( ' . $wppic_data->num_ratings . ' votes)';
		$content .= '</span>';
		$content .= '<span class="wp-pic-widget-downloaded"><span>' . __( 'Active Installs:', 'wp-plugin-info-card' ) . '</span> ' . number_format_i18n( $wppic_data->active_installs ) .'+</span>';
		$content .= '<p class="wp-pic-widget-updated"><span>' . __( 'Last Updated:', 'wp-plugin-info-card' ) . '</span> ' . $wppic_data->last_updated;
		if( !empty( $wppic_data->version ) )
			$content .= ' (v.' . $wppic_data->version .' )';
		$content .= '</p>';
		$content .= '</div>';

	}
	return $content;
}
