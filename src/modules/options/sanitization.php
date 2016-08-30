<?php
/**
 * @package   Options Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * 
 * Based on Devin Price' Options_Framework
 */

namespace WBF\modules\options;

/*
 * LEGACY PART:
 * Following parts are unmodified from the orignal work by @author Devin Price <devin@wptheming.com>
 */

/* Text */

use WBF\components\utils\Utilities;

add_filter( 'of_sanitize_text', 'sanitize_text_field' );

/* Password */

add_filter( 'of_sanitize_password', 'sanitize_text_field' );

/* Textarea */

function of_sanitize_textarea(  $input) {
    global $allowedposttags;
    $output = wp_kses( $input, $allowedposttags);
    return $output;
}

add_filter( 'of_sanitize_textarea', '\WBF\modules\options\of_sanitize_textarea' );

/* Select */

add_filter( 'of_sanitize_select', '\WBF\modules\options\of_sanitize_enum', 10, 2);

/* Radio */

add_filter( 'of_sanitize_radio', '\WBF\modules\options\of_sanitize_enum', 10, 2);

/* Images */

add_filter( 'of_sanitize_images', '\WBF\modules\options\of_sanitize_enum', 10, 2);

/* Checkbox */

function of_sanitize_checkbox( $input ) {
    if ( $input ) {
        $output = '1';
    } else {
        $output = false;
    }
    return $output;
}
add_filter( 'of_sanitize_checkbox', '\WBF\modules\options\of_sanitize_checkbox' );

/* Multicheck */

function of_sanitize_multicheck( $input, $option ) {
    $output = '';
    if ( is_array( $input ) ) {
        foreach( $option['options'] as $key => $value ) {
            $output[$key] = false;
        }
        foreach( $input as $key => $value ) {
            if ( array_key_exists( $key, $option['options'] ) && $value ) {
                $output[$key] = "1";
            }
        }
    }
    return $output;
}
add_filter( 'of_sanitize_multicheck', '\WBF\modules\options\of_sanitize_multicheck', 10, 2 );

/* Color Picker */

add_filter( 'of_sanitize_color', '\WBF\modules\options\of_sanitize_hex' );


/* Advanced Color Picker */

add_filter( 'of_sanitize_advanced_color', '\WBF\modules\options\of_sanitize_advanced_color' );

/* Uploader */

function of_sanitize_upload( $input ) {
    $output = '';
    $filetype = wp_check_filetype($input);
    if ( $filetype["ext"] ) {
        $output = $input;
    }
    return $output;
}
add_filter( 'of_sanitize_upload', '\WBF\modules\options\of_sanitize_upload' );

/* Editor */

function of_sanitize_editor($input) {
    if ( current_user_can( 'unfiltered_html' ) ) {
        $output = $input;
    }
    else {
        global $allowedtags;
        $output = wpautop(wp_kses( $input, $allowedtags));
    }
    return $output;
}
add_filter( 'of_sanitize_editor', '\WBF\modules\options\of_sanitize_editor' );

/* Allowed Tags */

function of_sanitize_allowedtags( $input ) {
    global $allowedtags;
    $output = wpautop( wp_kses( $input, $allowedtags ) );
    return $output;
}

/* Allowed Post Tags */

function of_sanitize_allowedposttags( $input ) {
    global $allowedposttags;
    $output = wpautop(wp_kses( $input, $allowedposttags));
    return $output;
}
add_filter( 'of_sanitize_info', '\WBF\modules\options\of_sanitize_allowedposttags' );

/* Check that the key value sent is valid */

function of_sanitize_enum( $input, $option ) {
    $output = '';
    if ( array_key_exists( $input, $option['options'] ) ) {
        $output = $input;
    }
    return $output;
}

/* Background */

function of_sanitize_background( $input ) {
    $output = wp_parse_args( $input, array(
        'color' => '',
        'image'  => '',
        'repeat'  => 'repeat',
        'position' => 'top center',
        'attachment' => 'scroll'
    ) );

    $output['color'] = apply_filters( 'of_sanitize_hex', $input['color'] );
    $output['image'] = apply_filters( 'of_sanitize_upload', $input['image'] );
    $output['repeat'] = apply_filters( 'of_background_repeat', $input['repeat'] );
    $output['position'] = apply_filters( 'of_background_position', $input['position'] );
    $output['attachment'] = apply_filters( 'of_background_attachment', $input['attachment'] );

    return $output;
}
add_filter( 'of_sanitize_background', '\WBF\modules\options\of_sanitize_background' );

function of_sanitize_background_repeat( $value ) {
    $recognized = of_recognized_background_repeat();
    if ( array_key_exists( $value, $recognized ) ) {
        return $value;
    }
    return apply_filters( 'of_default_background_repeat', current( $recognized ) );
}
add_filter( 'of_background_repeat', '\WBF\modules\options\of_sanitize_background_repeat' );

function of_sanitize_background_position( $value ) {
    $recognized = of_recognized_background_position();
    if ( array_key_exists( $value, $recognized ) ) {
        return $value;
    }
    return apply_filters( 'of_default_background_position', current( $recognized ) );
}
add_filter( 'of_background_position', '\WBF\modules\options\of_sanitize_background_position' );

function of_sanitize_background_attachment( $value ) {
    $recognized = of_recognized_background_attachment();
    if ( array_key_exists( $value, $recognized ) ) {
        return $value;
    }
    return apply_filters( 'of_default_background_attachment', current( $recognized ) );
}
add_filter( 'of_background_attachment', '\WBF\modules\options\of_sanitize_background_attachment' );

/* Typography */

/*function of_sanitize_typography( $input, $option ) {

	$output = wp_parse_args( $input, array(
		'size'  => '',
		'face'  => '',
        'weight' => '',
		'style' => '',
		'color' => ''
	) );

	if ( isset( $option['options']['faces'] ) && isset( $input['face'] ) ) {
		if ( !( array_key_exists( $input['face'], $option['options']['faces'] ) ) ) {
			$output['face'] = '';
		}
	}
	else {
		$output['face']  = apply_filters( 'of_font_face', $output['face'] );
	}

	$output['size']  = apply_filters( 'of_font_size', $output['size'] );
	$output['weight'] = apply_filters( 'of_font_weight', $output['weight'] );
    $output['style'] = apply_filters( 'of_font_style', $output['style'] );
	$output['color'] = apply_filters( 'of_sanitize_color', $output['color'] );
	return $output;
}*/
//add_filter( 'of_sanitize_typography', 'of_sanitize_typography', 10, 2 );

function of_sanitize_font_size( $value ) {
    $recognized = of_recognized_font_sizes();
    $value_check = preg_replace('/px/','', $value);
    if ( in_array( (int) $value_check, $recognized ) ) {
        return $value;
    }
    return apply_filters( 'of_default_font_size', $recognized );
}
add_filter( 'of_font_size', '\WBF\modules\options\of_sanitize_font_size' );

function of_sanitize_font_style( $value ) {
    $recognized = of_recognized_font_styles();
    if ( array_key_exists( $value, $recognized ) ) {
        return $value;
    }
    return apply_filters( 'of_default_font_style', current( $recognized ) );
}
add_filter( 'of_font_style', '\WBF\modules\options\of_sanitize_font_style' );

function of_sanitize_font_weight( $value ) {
    $recognized = of_recognized_font_weight();
    if ( array_key_exists( $value, $recognized ) ) {
        return $value;
    }
    return apply_filters( 'of_default_font_weight', current( $recognized ) );
}
add_filter( 'of_font_weight', '\WBF\modules\options\of_sanitize_font_weight' );

function of_sanitize_font_face( $value ) {
    $recognized = of_recognized_font_faces();
    if ( array_key_exists( $value, $recognized ) ) {
        return $value;
    }
    return apply_filters( 'of_default_font_face', current( $recognized ) );
}
add_filter( 'of_font_face', '\WBF\modules\options\of_sanitize_font_face' );

/**
 * Get recognized background repeat settings
 *
 * @return   array
 *
 */
function of_recognized_background_repeat() {
    $default = array(
        'no-repeat' => __( 'No Repeat', 'textdomain' ),
        'repeat-x'  => __( 'Repeat Horizontally', 'textdomain' ),
        'repeat-y'  => __( 'Repeat Vertically', 'textdomain' ),
        'repeat'    => __( 'Repeat All', 'textdomain' ),
    );
    return apply_filters( 'of_recognized_background_repeat', $default );
}

/**
 * Get recognized background positions
 *
 * @return   array
 *
 */
function of_recognized_background_position() {
    $default = array(
        'top left'      => __( 'Top Left', 'textdomain' ),
        'top center'    => __( 'Top Center', 'textdomain' ),
        'top right'     => __( 'Top Right', 'textdomain' ),
        'center left'   => __( 'Middle Left', 'textdomain' ),
        'center center' => __( 'Middle Center', 'textdomain' ),
        'center right'  => __( 'Middle Right', 'textdomain' ),
        'bottom left'   => __( 'Bottom Left', 'textdomain' ),
        'bottom center' => __( 'Bottom Center', 'textdomain' ),
        'bottom right'  => __( 'Bottom Right', 'textdomain')
    );
    return apply_filters( 'of_recognized_background_position', $default );
}

/**
 * Get recognized background attachment
 *
 * @return   array
 *
 */
function of_recognized_background_attachment() {
    $default = array(
        'scroll' => __( 'Scroll Normally', 'textdomain' ),
        'fixed'  => __( 'Fixed in Place', 'textdomain')
    );
    return apply_filters( 'of_recognized_background_attachment', $default );
}

/**
 * Sanitize a color represented in hexidecimal notation.
 *
 * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
 * @param    string    The value that this function should return if it cannot be recognized as a color.
 * @return   string
 *
 */
function of_sanitize_hex( $hex, $default = '' ) {
    if ( of_validate_hex( $hex ) ) {
        return $hex;
    }
    return $default;
}



function of_sanitize_advanced_color( $val, $default = '' ) {


	if (strstr($val, 'hsva') !== false) {
		$val = str_replace('hsva(', '', $val);
		$val = str_replace(')', '', $val);
		$values = explode(', ', $val);

		$rgb = Utilities::fGetRGB($values[0], $values[1], $values[2]);
		if (is_null($values[3])) {
			return $rgb;
		}
		$rgba = 'rgba( ' . $rgb . ',' . $values[3] . ')';
		return $rgba;
	}
	return $val;
}



/**
 * Get recognized font sizes.
 *
 * Returns an indexed array of all recognized font sizes.
 * Values are integers and represent a range of sizes from
 * smallest to largest.
 *
 * @return   array
 */
function of_recognized_font_sizes() {
    $sizes = range( 9, 71 );
    $sizes = apply_filters( 'of_recognized_font_sizes', $sizes );
    $sizes = array_map( 'absint', $sizes );
    return $sizes;
}

/**
 * Get recognized font faces.
 *
 * Returns an array of all recognized font faces.
 * Keys are intended to be stored in the database
 * while values are ready for display in in html.
 *
 * @return   array
 *
 */
function of_recognized_font_faces() {
    $default = array(
        'arial'     => 'Arial',
        'verdana'   => 'Verdana, Geneva',
        'trebuchet' => 'Trebuchet',
        'georgia'   => 'Georgia',
        'times'     => 'Times New Roman',
        'tahoma'    => 'Tahoma, Geneva',
        'palatino'  => 'Palatino',
        'helvetica' => 'Helvetica*'
    );
    return apply_filters( 'of_recognized_font_faces', $default );
}

/**
 * Get recognized font styles.
 *
 * Returns an array of all recognized font styles.
 * Keys are intended to be stored in the database
 * while values are ready for display in in html.
 *
 * @return   array
 *
 */
function of_recognized_font_styles() {
    $default = array(
        'normal'  => __( 'normal', 'textdomain' ),
        'italic'  => __( 'italic', 'textdomain' ),
        'oblique' => __( 'oblique', 'textdomain' ),
        'inherit' => __( 'inherit', 'textdomain' )
    );
    return apply_filters( 'of_recognized_font_styles', $default );
}

/**
 * Get recognized font weigth.
 *
 * Returns an array of all recognized font styles.
 * Keys are intended to be stored in the database
 * while values are ready for display in in html.
 *
 * @return   array
 *
 */
function of_recognized_font_weight() {
    $default = array(
        'normal' => __( 'normal', 'textdomain' ),
        'bold' => __( 'bold', 'textdomain' ),
        'lighter' => __( 'lighter', 'textdomain' ),
        'bolder' => __( 'bolder', 'textdomain' ),
        '100' => __( '100', 'textdomain' ),
        '200' => __( '200', 'textdomain' ),
        '300' => __( '300', 'textdomain' ),
        '400' => __( '400', 'textdomain' ),
        '500' => __( '500', 'textdomain' ),
        '600' => __( '600', 'textdomain' ),
        '700' => __( '700', 'textdomain' ),
        '800' => __( '800', 'textdomain' ),
        '900' => __( '900', 'textdomain' ),
        'inherit' => __( 'inherit', 'textdomain' )
    );
    return apply_filters( 'of_recognized_font_weigth', $default );
}

/**
 * Is a given string a color formatted in hexidecimal notation?
 *
 * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
 * @return   bool
 *
 */
function of_validate_hex( $hex ) {
    $hex = trim( $hex );
    /* Strip recognized prefixes. */
    if ( 0 === strpos( $hex, '#' ) ) {
        $hex = substr( $hex, 1 );
    }
    elseif ( 0 === strpos( $hex, '%23' ) ) {
        $hex = substr( $hex, 3 );
    }
    /* Regex match. */
    if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
        return false;
    }
    else {
        return true;
    }
}

/*
 * CUSTOM PART
 */

/**
 * Custom Sanitize functions
 */
add_filter( 'of_sanitize_csseditor', '\WBF\modules\options\of_sanitize_textarea' );
add_filter( 'of_sanitize_typography', '\WBF\modules\options\of_sanitize_typography' );

/**
 * Allow "a", "embed" and "script" tags in theme options text boxes
 */
remove_filter( 'of_sanitize_text', 'sanitize_text_field' );
add_filter( 'of_sanitize_text', '\WBF\modules\options\custom_sanitize_text' );

function custom_sanitize_text( $input ) {
	global $allowedposttags;

	$custom_allowedtags["a"] = array(
		"href"   => array(),
		"target" => array(),
		"id"     => array(),
		"class"  => array()
	);

	$custom_allowedtags = array_merge( $custom_allowedtags, $allowedposttags );
	$output             = wp_kses( $input, $custom_allowedtags );

	return $output;
}

function of_sanitize_typography( $input ) {

	$output = wp_parse_args( $input, array(
		'family'  => '',
		'style'  => array(),
		'charset' => array(),
		'color' => ''
	) );

	/*$output['family'] = apply_filters( 'of_sanitize_text', $output['family'] );
	$output['style'] = apply_filters( 'of_sanitize_text', $output['style'] );
	$output['charset'] = apply_filters( 'of_sanitize_text', $output['charset'] );*/
	$output['color'] = apply_filters( 'of_sanitize_color', $output['color'] );

	return $output;
}