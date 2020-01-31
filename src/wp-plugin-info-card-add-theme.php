<?php
/***************************************************************
 * SECURITY : Exit if accessed directly
***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}


/***************************************************************
 * WPPIC Themes filters
***************************************************************/
add_filter( 'wppic_add_api_parser', 'wppic_theme_api_parser', 9, 3 );
add_filter( 'wppic_add_template', 'wppic_theme_template', 9, 2 );
add_filter( 'wppic_add_mce_type', 'wppic_theme_mce_type' );
add_filter( 'wppic_add_list_form', 'wppic_theme_list_form' );
add_filter( 'wppic_add_widget_type', 'wppic_theme_widget_type' );
add_filter( 'wppic_add_list_valdiation', 'wppic_theme_list_valdiation' );
add_filter( 'wppic_add_widget_item', 'wppic_theme_widget_item', 9, 3 );


/***************************************************************
 * Fetching themes data with WordPress.org Theme API
 ***************************************************************/
function wppic_theme_api_parser( $wppic_data, $type, $slug ){
	if ( $type == 'theme' ) {

		require_once( ABSPATH . 'wp-admin/includes/theme.php' );
		$theme_info = themes_api( 'theme_information', array(
				'slug' 		=> $slug,
			)
		);
		if( !is_wp_error( $theme_info ) ){
			$wppic_data  = (object) array(
				'url'				=> 'https://wordpress.org/themes/'.$slug.'/',
				'name' 				=> $theme_info->name,
				'slug' 				=> $slug,
				'version' 			=> $theme_info->version,
				'preview_url'		=> $theme_info->preview_url,
				'author_def'		=> $theme_info->author,
				'author' 			=> sprintf( '<a href="%s" target="_blank" title="%s">%s</a>', $theme_info->author['profile'], $theme_info->author['display_name'], $theme_info->author['display_name'] ),
				'screenshot_url'	=> $theme_info->screenshot_url,
				'rating' 			=> $theme_info->rating,
				'num_ratings' 		=> $theme_info->num_ratings,
				'downloaded' 		=> number_format($theme_info->downloaded, 0, ',', ',' ),
				'last_updated_mk' 	=> $theme_info->last_updated,
				'last_updated' 		=> $theme_info->last_updated,
				'homepage' 			=> $theme_info->homepage,
				'download_link' 	=> $theme_info->download_link
			);
		}

	}

	return $wppic_data;

}


/***************************************************************
 * Theme shortcode template prepare
 ***************************************************************/
function wppic_theme_template( $content, $data ){

	$type = $data[0];
	$wppic_data = $data[1];
	$image = $data[2];
	$layout = '-' . $data[3];

	if ( $type == 'theme' ) {

		//load custom user template if exists
		$WPPICtemplatefile = '/wppic-templates/wppic-template-theme';
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
 * Add theme type to mce list
 ***************************************************************/
function wppic_theme_mce_type( $parameters ){
	$parameters[ 'types' ][] = array( 'text' => 'Theme', 'value' => 'theme' );
	return $parameters;
}


/***************************************************************
 * Theme input option list
 ***************************************************************/
function wppic_theme_list_form( $parameters ){
	$parameters[] = array(
		'theme-list',
		__( 'Add a theme', 'wp-plugin-info-card' ),
		__( 'Please refer to the theme URL on wordpress.org to determine its slug', 'wp-plugin-info-card' ),
		'https://wordpress.org/themes/<strong>THE-SLUG</strong>/'
	);
	return $parameters;
}


/***************************************************************
 * Theme input validation
 ***************************************************************/
function wppic_theme_list_valdiation( $parameters ){
	$parameters[] = array( 'theme-list', __( 'is not a valid theme name format. This key has been deleted.', 'wp-plugin-info-card' ), '/^[a-z0-9\-]+$/' );
	return $parameters;
}


/***************************************************************
 * Theme widget list
 ***************************************************************/
function wppic_theme_widget_type( $parameters ){
	$parameters[] = array( 'theme', 'theme-list', __( 'Themes', 'wp-plugin-info-card' ) );
	return $parameters;
}


/***************************************************************
 * Theme widget item render
 ***************************************************************/
function wppic_theme_widget_item( $content, $wppic_data, $type ){
	if( $type == 'theme' ){

		//Date format Internationalizion
		global 	$wppic_date_format;
		$wppic_data->last_updated = date_i18n( $wppic_date_format, strtotime( $wppic_data->last_updated ) );

		$content .= '<div class="wp-pic-item">';
		$content .= '<a class="wp-pic-widget-name" href="' . $wppic_data->url . '" target="_blank" title="' . __( 'WordPress.org Plugin Page', 'wp-plugin-info-card' ) . '">' . $wppic_data->name .'</a>';
		$content .= '<span class="wp-pic-widget-rating"><span>' . __( 'Ratings:', 'wp-plugin-info-card' ) . '</span> ' . $wppic_data->rating .'%';
		if( !empty( $wppic_data->num_ratings ) )
			$content .= ' ( ' . $wppic_data->num_ratings . ' votes)';
		$content .= '</span>';
		$content .= '<span class="wp-pic-widget-downloaded"><span>' . __( 'Downloads:', 'wp-plugin-info-card' ) . '</span> ' . $wppic_data->downloaded .'</span>';
		$content .= '<p class="wp-pic-widget-updated"><span>' . __( 'Last Updated:', 'wp-plugin-info-card' ) . '</span> ' . $wppic_data->last_updated;
		if( !empty( $wppic_data->version ) )
			$content .= ' (v.' . $wppic_data->version .' )';
		$content .= '</p>';
		$content .= '</div>';

	}
	return $content;
}
