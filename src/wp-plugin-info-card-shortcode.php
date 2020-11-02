<?php
/***************************************************************
 * SECURITY : Exit if accessed directly
 ***************************************************************/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct acces not allowed!' );
}


/***************************************************************
 * Register default plugin scripts
 ***************************************************************/
function wppic_register_sripts() {
	$min_or_not = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_enqueue_style( 'wppic-style', WPPIC_URL . 'css/wppic-style' . $min_or_not . '.css', null, WPPIC_VERSION );
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_script( 'wppic-script', WPPIC_URL . 'js/wppic-script' . $min_or_not . '.js', array( 'jquery' ), WPPIC_VERSION, true );
}
add_action( 'wppic_enqueue_scripts', 'wppic_register_sripts' );


/***************************************************************
 * Enqueue Scripts action hook
 ***************************************************************/
function wppic_print_sripts() {
	global  $wppicSettings;

	$wppicAjax = '<script>// <![CDATA[
	var wppicAjax = { ajaxurl : "' . admin_url( 'admin-ajax.php' ) . '" };
	 // ]]></script>';

	if ( isset( $wppicSettings['enqueue'] ) && $wppicSettings['enqueue'] == true ) {

		echo $wppicAjax;
		do_action( 'wppic_enqueue_scripts' );

	} else {

		// Enqueue Scripts when shortcode is in page
		global $wppicSripts;
		if ( ! $wppicSripts ) {
			return;
		}

		echo $wppicAjax;
		do_action( 'wppic_enqueue_scripts' );

	}

}
add_action( 'wp_footer', 'wppic_print_sripts' );

/**
 * Register route for getting plugin shortcode
 *
 * @since 3.0.0
 */
function wppic_register_route() {
	register_rest_route(
		'wppic/v1',
		'/get_html',
		array(
			'methods'             => 'GET',
			'callback'            => 'wppic_get_shortcode',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'wppic/v2',
		'/get_data',
		array(
			'methods'             => 'GET',
			'callback'            => 'wppic_get_asset_data',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'wppic/v1',
		'/get_query',
		array(
			'methods'             => 'GET',
			'callback'            => 'wppic_get_query_shortcode',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'wppic_register_route' );

function wppic_get_shortcode() {
	$attrs = array(
		'type'        => $_GET['type'],
		'slug'        => $_GET['slug'],
		'image'       => isset( $_GET['image'] ) ? $_GET['image'] : '',
		'align'       => isset( $_GET['align'] ) ? $_GET['align'] : '',
		'containerid' => isset( $_GET['containerid'] ) ? $_GET['containerid'] : '',
		'margin'      => isset( $_GET['margin'] ) ? $_GET['margin'] : '',
		'clear'       => isset( $_GET['clear'] ) ? $_GET['clear'] : '',
		'expiration'  => isset( $_GET['expiration'] ) ? $_GET['expiration'] : '',
		'ajax'        => isset( $_GET['ajax'] ) ? $_GET['ajax'] : '',
		'scheme'      => isset( $_GET['scheme'] ) ? $_GET['scheme'] : '',
		'layout'      => isset( $_GET['layout'] ) ? $_GET['layout'] : '',
		'multi'       => isset( $_GET['multi'] ) ? filter_var( $_GET['multi'], FILTER_VALIDATE_BOOLEAN ) : false,
	);
	die( wppic_shortcode_function( $attrs ) );
}

/**
 * Return plugin data based on passed strings.
 */
function wppic_get_asset_data() {
	$type = isset( $_GET['type'] ) ? sanitize_title( $_GET['type'] ) : 'plugin';
	$slug = isset( $_GET['slug'] ) ? $_GET['slug'] : 'wp-plugin-info-card-not-found';

	// Random slug: comma-separated list.
	$slugs = explode( ',', $slug );
	foreach ( $slugs as &$item_slug ) {
		$item_slug = sanitize_title( trim( $item_slug ) );
	}

	$data = array();
	foreach ( $slugs as $asset_slug ) {
		$slug_data = wppic_api_parser( $type, $asset_slug );
		if ( isset( $slug_data->author ) ) {
			$slug_data->author = wp_strip_all_tags( $slug_data->author );
		}
		if ( isset( $slug_data->author ) ) {
			$slug_data->author = wp_strip_all_tags( $slug_data->author );
		}
		if ( isset( $slug_data->active_installs ) ) {
			$slug_data->active_installs = number_format_i18n( $slug_data->active_installs );
		}
		if ( isset( $slug_data->last_updated ) ) {
			$slug_data->last_updated = human_time_diff( strtotime( $slug_data->last_updated ), time() ) . ' ' . _x( 'ago', 'Last time updated', 'wp-plugin-info-card' );
		}
		$data[] = $slug_data;
	}

	wp_send_json_success( $data );
}

function wppic_get_query_shortcode() {
	$attrs = array(
		'cols'        => $_GET['cols'],
		'per_page'    => $_GET['per_page'],
		'type'        => $_GET['type'],
		'image'       => isset( $_GET['image'] ) ? $_GET['image'] : '',
		'align'       => isset( $_GET['align'] ) ? $_GET['align'] : '',
		'containerid' => isset( $_GET['containerid'] ) ? $_GET['containerid'] : '',
		'margin'      => isset( $_GET['margin'] ) ? $_GET['margin'] : '',
		'clear'       => isset( $_GET['clear'] ) ? $_GET['clear'] : '',
		'expiration'  => isset( $_GET['expiration'] ) ? $_GET['expiration'] : '',
		'ajax'        => isset( $_GET['ajax'] ) ? $_GET['ajax'] : '',
		'scheme'      => isset( $_GET['scheme'] ) ? $_GET['scheme'] : '',
		'layout'      => isset( $_GET['layout'] ) ? $_GET['layout'] : '',
	);
	if ( ! empty( $_GET['browse'] ) ) {
		$attrs['browse'] = $_GET['browse'];
	}
	if ( ! empty( $_GET['search'] ) ) {
		$attrs['search'] = $_GET['search'];
	}
	if ( ! empty( $_GET['tag'] ) ) {
		$attrs['tag'] = $_GET['tag'];
	}
	if ( ! empty( $_GET['user'] ) ) {
		$attrs['user'] = $_GET['user'];
	}
	if ( ! empty( $_GET['author'] ) ) {
		$attrs['author'] = $_GET['author'];
	}
	die( wppic_shortcode_query_function( $attrs ) );
}

/***************************************************************
 * Main shortcode function
 ***************************************************************/
function wppic_shortcode_function( $atts, $content = '' ) {

	// Retrieve & extract shorcode parameters
	extract(
		shortcode_atts(
			array(
				'type'        => '',  // plugin | theme
				'slug'        => '',  // plugin slug name
				'image'       => '',  // image url to replace WP logo (175px X 175px)
				'align'       => '',  // center|left|right
				'containerid' => '',  // custom Div ID (could be use for anchor)
				'margin'      => '',  // custom container margin - eg: "15px 0"
				'clear'       => '',  // clear float before or after the card: before|after
				'expiration'  => '',  // transient duration in minutes - 0 for never expires
				'ajax'        => '',  // load plugin data async whith ajax: yes|no (default: no)
				'scheme'      => '',  // color scheme : default|scheme1->scheme10 (default: empty)
				'layout'      => '',  // card|flat|wordpress
				'custom'      => '',  // value to print : url|name|version|author|requires|rating|num_ratings|downloaded|last_updated|download_link
				'multi'       => false,

			),
			$atts,
			'wppic_default'
		)
	);
	// Use "shortcode_atts_wppic_default" filter to edit shortcode parameters default values or add your owns.

	global  $wppicSettings;

	// Global var to enqueue scripts + ajax param if is set to yes
	global $wppicSripts;
	$wppicSripts = true;

	$addClass = array();

	// Remove unnecessary spaces
	$type        = trim( $type );
	$slug        = trim( esc_html( $slug ) );
	$image       = trim( $image );
	$containerid = trim( $containerid );
	$margin      = trim( $margin );
	$clear       = trim( $clear );
	$expiration  = trim( $expiration );
	$ajax        = trim( $ajax );
	$scheme      = trim( $scheme );
	$layout      = trim( $layout );
	$custom      = trim( $custom );
	$multi       = filter_var( $multi, FILTER_VALIDATE_BOOLEAN );

	if ( empty( $layout ) ) {
		$layout     = 'wp-pic-card';
		$addClass[] = $layout;
	} elseif ( 'flex' === $layout ) {
		$addClass[] = 'flex';
		$addClass[] = 'wp-pic-card';
	} elseif ( 'card' === $layout ) {
		$layout     = 'wp-pic-card';
		$addClass[] = 'wp-pic-card';
	} else {
		$addClass[] = $layout;
	}

	// Random slug: comma-separated list.
	if ( strpos( $slug, ',' ) !== false && ! $multi ) {
		$slug = explode( ',', $slug );
		$slug = $slug[ array_rand( $slug ) ];
	} elseif ( strpos( $slug, ',' ) !== false && $multi ) {
		$slug = explode( ',', $slug );
		foreach ( $slug as &$item_slug ) {
			$item_slug = trim( $item_slug );
		}
	}

	$block_alignment = 'align-center';
	switch ( $align ) {
		case 'left':
			$block_alignment = 'alignleft';
			break;
		case 'right':
			$block_alignment = 'alignright';
			break;
		case 'center':
			$block_alignment = 'aligncenter';
			break;
		case 'wide':
			$block_alignment = 'alignwide';
			break;
		case 'full':
			$block_alignment = 'alignfull';
			break;
	}

	if ( is_array( $slug ) && $multi ) {
		$content .= sprintf( '<div class="wp-pic-multi %s">', esc_attr( $block_alignment ) );
		foreach ( $slug as $asset_slug ) {
			// For old plugin versions
			if ( empty( $type ) ) {
				$type = 'plugin';
			}
			$addClass[] = $type;
			$addClass[] = 'multi';

			if ( ! empty( $custom ) ) {

				$wppic_data = wppic_api_parser( $type, $asset_slug, $expiration );

				if ( ! $wppic_data ) {
					return '<strong>' . __( 'Item not found:', 'wp-plugin-info-card' ) . ' "' . $asset_slug . '" ' . __( 'does not exist.', 'wp-plugin-info-card' ) . '</strong>';
				}

				if ( ! empty( $wppic_data->$custom ) ) {
					$content .= $wppic_data->$custom;
				}
			} else {

				// Ajax required data
				$ajaxData = '';
				if ( $ajax == 'yes' ) {
					$addClass[] = 'wp-pic-ajax';
					$ajaxData   = 'data-type="' . $type . '" data-slug="' . $asset_slug . '" data-image="' . $image . '" data-expiration="' . $expiration . '"  data-layout="' . $layout . '" ';
				}

				// Align card
				$alignCenter = false;
				$alignStyle  = '';

				// Extra container ID
				if ( ! empty( $containerid ) ) {
					$containerid = ' id="' . $containerid . '"';
				} else {
					$containerid = ' id="wp-pic-' . esc_html( $asset_slug ) . '"';
				}

				// Color scheme
				if ( empty( $scheme ) ) {
					$scheme = $wppicSettings['colorscheme'];
					if ( $scheme == 'default' ) {
						$scheme = '';
					}
				}
				$addClass[] = $scheme;

				// Output
				if ( $clear == 'before' ) {
					$content .= '<div style="clear:both"></div>';
				}

				$content .= sprintf( '<div class="wp-pic-wrapper %s %s">', esc_attr( $block_alignment ), esc_attr( $layout ) );
				if ( $alignCenter ) {
					$content .= '<div class="wp-pic-center">';
				}

				// Data attribute for ajax call
				$content .= '<div class="wp-pic ' . implode( ' ', $addClass ) . '" ' . $containerid . $ajaxData . ' >';

				if ( $ajax != 'yes' ) {
					$content .= wppic_shortcode_content( $type, $asset_slug, $image, $expiration, $layout );
				} else {
					$content .= '<div class="wp-pic-body-loading"><div class="signal"></div></div>';
				}

				$content .= '</div>';

				// Align center
				if ( $alignCenter ) {
					$content .= '</div>';
				}

				$content .= '</div><!-- .wp-pic-wrapper-->';

				if ( $clear == 'after' ) {
					$content .= '<div style="clear:both"></div>';
				}
			}
		}
		$content .= '</div>';
	} else {
		// For old plugin versions
		if ( empty( $type ) ) {
			$type = 'plugin';
		}
		$addClass[] = $type;

		if ( ! empty( $custom ) ) {

			$wppic_data = wppic_api_parser( $type, $slug, $expiration );

			if ( ! $wppic_data ) {
				return '<strong>' . __( 'Item not found:', 'wp-plugin-info-card' ) . ' "' . $slug . '" ' . __( 'does not exist.', 'wp-plugin-info-card' ) . '</strong>';
			}

			if ( ! empty( $wppic_data->$custom ) ) {
				$content .= $wppic_data->$custom;
			}
		} else {

			// Ajax required data
			$ajaxData = '';
			if ( $ajax == 'yes' ) {
				$addClass[] = 'wp-pic-ajax';
				$ajaxData   = 'data-type="' . $type . '" data-slug="' . $slug . '" data-image="' . $image . '" data-expiration="' . $expiration . '"  data-layout="' . $layout . '" ';
			}

			// Align card
			$alignCenter = false;
			$alignStyle  = '';

			// Extra container ID
			if ( ! empty( $containerid ) ) {
				$containerid = ' id="' . $containerid . '"';
			} else {
				$containerid = ' id="wp-pic-' . esc_html( $slug ) . '"';
			}

			// Custom container margin
			if ( ! empty( $margin ) ) {
				$margin = 'margin:' . $margin . ';';
			}

			// Custom style
			$style = '';
			if ( ! empty( $margin ) || ! empty( $alignStyle ) ) {
				$style = 'style="' . $margin . $alignStyle . '"';
			}

			// Color scheme
			if ( empty( $scheme ) ) {
				$scheme = $wppicSettings['colorscheme'];
				if ( $scheme == 'default' ) {
					$scheme = '';
				}
			}
			$addClass[] = $scheme;

			// Output
			if ( $clear == 'before' ) {
				$content .= '<div style="clear:both"></div>';
			}

			$content .= sprintf( '<div class="wp-pic-wrapper %s %s" %s>', esc_attr( $block_alignment ), esc_attr( $layout ), $style );
			if ( $alignCenter ) {
				$content .= '<div class="wp-pic-center">';
			}

			// Data attribute for ajax call
			$content .= '<div class="wp-pic ' . implode( ' ', $addClass ) . '" ' . $containerid . $ajaxData . ' >';

			if ( $ajax != 'yes' ) {
				$content .= wppic_shortcode_content( $type, $slug, $image, $expiration, $layout );
			} else {
				$content .= '<div class="wp-pic-body-loading"><div class="signal"></div></div>';
			}

			$content .= '</div>';

			// Align center
			if ( $alignCenter ) {
				$content .= '</div>';
			}

			$content .= '</div><!-- .wp-pic-wrapper-->';

			if ( $clear == 'after' ) {
				$content .= '<div style="clear:both"></div>';
			}
		}
	}

	return $content;

} //end of wppic_Shortcode

add_shortcode( 'wp-pic', 'wppic_shortcode_function' );

/***************************************************************
 * Content shortcode function
 ***************************************************************/
function wppic_shortcode_content( $type = null, $slug = null, $image = null, $expiration = null, $layout = null ) {

	if ( ! empty( $_POST['type'] ) ) {
		$type = $_POST['type'];
	}
	if ( ! empty( $_POST['slug'] ) ) {
		$slug = $_POST['slug'];
	}
	if ( ! empty( $_POST['image'] ) ) {
		$image = $_POST['image'];
	}
	if ( ! empty( $_POST['expiration'] ) ) {
		$expiration = $_POST['expiration'];
	}
	if ( ! empty( $_POST['layout'] ) ) {
		$layout = $_POST['layout'];
	}

	$type       = esc_html( $type );
	$slug       = esc_html( $slug );
	$image      = esc_html( $image );
	$expiration = esc_html( $expiration );
	$layout     = esc_html( $layout );

	$wppic_data = wppic_api_parser( $type, $slug, $expiration );

	// if plugin does not exists
	if ( ! $wppic_data ) {

		$error      = '<div class="wp-pic-flip" style="display: none;">';
			$error .= '<div class="wp-pic-face wp-pic-front error">';

				$error .= '<span class="wp-pic-no-plugin">' . __( 'Item not found:', 'wp-plugin-info-card' ) . '</br><i>"' . $slug . '"</i></br>' . __( 'does not exist.', 'wp-plugin-info-card' ) . '</span>';
				$error .= '<div class="monster-wrapper">
								<div class="eye-left"></div>
								<div class="eye-right"></div>
								<div class="mouth">
									<div class="tooth-left"></div>
									<div class="tooth-right"></div>
								</div>
								<div class="arm-left"></div>
								<div class="arm-right"></div>
								<div class="dots"></div>
							</div>';
			$error     .= '</div>';
		$error         .= '</div>';

		if ( ! empty( $_POST['slug'] ) ) {
			echo $error;
			die();
		} else {
			return $error;
		}
	}

	// Date format Internationalizion
	global  $wppicDateFormat;
	$wppic_data->last_updated = date_i18n( $wppicDateFormat, strtotime( $wppic_data->last_updated ) );

	// Prepare the credit
	global  $wppicSettings;
	$credit = '';
	if ( isset( $wppicSettings['credit'] ) && $wppicSettings['credit'] == true ) {
		$credit .= '<a className="wp-pic-credit" href="https://mediaron.com/wp-plugin-info-card/" target="_blank" data-tooltip="';
		$credit .= __( 'This card has been generated with WP Plugin Info Card', 'wp-plugin-info-card' );
		$credit .= '"></a>';
	}
	$wppic_data->credit = $credit;

	// Load theme or plugin template
	$content = '';
	$content = apply_filters( 'wppic_add_template', $content, array( $type, $wppic_data, $image, $layout ) );

	if ( ! empty( $_POST['slug'] ) ) {
		echo $content;
		die();
	} else {
		return $content;
	}

}
add_action( 'wp_ajax_async_wppic_shortcode_content', 'wppic_shortcode_content' );
add_action( 'wp_ajax_nopriv_async_wppic_shortcode_content', 'wppic_shortcode_content' );
