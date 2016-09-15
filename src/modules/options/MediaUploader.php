<?php
/**
 * @package   Options Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 *
 * Based on Devin Price' Options_Framework
 */

namespace WBF\modules\options;

class MediaUploader{

	/**
	 * Media Uploader Using the WordPress Media Library.
	 *
	 * Parameters:
	 *
	 * string $_id - A token to identify this field (the name).
	 * string $_value - The value of the field, if present.
	 * string $_desc - An optional description of the field.
	 *
	 */
	static function optionsframework_uploader( $_id, $_value, $_desc = '', $_name = '' ) {

		$optionsframework_settings = Framework::get_options_framework_settings();

		// Gets the unique option id
		$option_name = $optionsframework_settings['id'];

		$output = '';
		$id = '';
		$class = '';
		$int = '';
		$value = '';
		$name = '';

		$id = strip_tags( strtolower( $_id ) );

		// Get the option... options :)
		$option_object = Framework::get_option_object($id);

		// If a value is passed and we don't have a stored value, use the value that's passed through.
		if ( $_value != '' && $value == '' ) {
			$value = $_value;
		}

		if ($_name != '') {
			$name = $_name;
		}
		else {
			$name = $option_name.'['.$id.']';
		}

		if ( $value ) {
			$class = ' has-file';
		}

		if(isset($option_object['readonly']) && $option_object['readonly'])
			$output .= '<input id="' . $id . '" class="upload' . $class . '" type="text" name="'.$name.'" value="' . $value . '" placeholder="' . __('No file chosen', 'textdomain') .'" readonly />' . "\n";
		else
			$output .= '<input id="' . $id . '" class="upload' . $class . '" type="text" name="'.$name.'" value="' . $value . '" placeholder="' . __('No file chosen', 'textdomain') .'" />' . "\n";

		if ( function_exists( 'wp_enqueue_media' ) ) {
			if ( ( $value == '' ) ) {
				$output .= '<input id="upload-' . $id . '" class="upload-button button" type="button" value="' . __( 'Upload', 'textdomain' ) . '" />' . "\n";
			} else {
				$output .= '<input id="remove-' . $id . '" class="remove-file button" type="button" value="' . __( 'Remove', 'textdomain' ) . '" />' . "\n";
			}
		} else {
			$output .= '<p><i>' . __( 'Upgrade your version of WordPress for full media support.', 'textdomain' ) . '</i></p>';
		}

		if ( $_desc != '' ) {
			$output .= '<span class="of-metabox-desc">' . $_desc . '</span>' . "\n";
		}
		// insert thumbnail


		$output .= '<div class="screenshot" id="' . $id . '-image">' . "\n";

		if ( $value != '' ) {
			$remove = '<a class="remove-image">Remove</a>';

			$allowed_formats_preg = call_user_func(function() use ($option_object){
				$allowed_extensions = isset($option_object['allowed_extensions']) && is_array($option_object['allowed_extensions']) && !empty($option_object['allowed_extensions']) ? $option_object['allowed_extensions'] : array('jpg','jpeg','png','gif','ico','svg');
				return implode("|",$allowed_extensions);
			});

			$image = preg_match( '/(^.*\.'.$allowed_formats_preg.'*)/i', $value ); // $image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );
			if ( $image ) {
				// find the attachment thumbnail if any
				$attachment_id = \WBF\components\utils\Utilities::get_attachment_id_by_url( $value );
				$attachment_thumbnail = wp_get_attachment_image_src($attachment_id);

				if (!empty($attachment_thumbnail)) { $value = $attachment_thumbnail[0]; }
				$output .= '<img src="' . $value . '" alt="" />' . $remove;
			} else {
				$parts = explode( "/", $value );
				for( $i = 0; $i < sizeof( $parts ); ++$i ) {
					$title = $parts[$i];
				}

				// No output preview if it's not an image.
				$output .= '';

				// Standard generic output if it's not an image.
				$title = __( 'View File', 'textdomain' );
				$output .= '<div class="no-image"><span class="file_link"><a href="' . $value . '" target="_blank" rel="external">'.$title.'</a></span></div>';
			}
		}
		$output .= '</div>' . "\n";
		return $output;
	}

	/**
	 * Initialize the media uploader class
	 */
	public function init() {
		add_filter('upload_mimes', function($mimes){
			$mimes['svg'] = 'image/svg+xml';
			return $mimes;
		});
		add_action('admin_enqueue_scripts', array($this, 'enqueue_media_scripts'));
		add_filter('wbf/js/admin/localization', function($loc_array){
			$loc_array['of_media_uploader'] = [
				'upload' => __('Upload', 'wbf'),
				'remove' => __('Remove', 'wbf')
			];
			return $loc_array;
		});
	}

	/**
	 * Enqueue scripts for file uploader
	 */
	function enqueue_media_scripts($hook){
		$menu = Admin::menu_settings();

		if('toplevel_page_' . $menu['menu_slug'] != $hook){
			return;
		}

		if(function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}
}