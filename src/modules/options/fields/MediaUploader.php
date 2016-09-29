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

namespace WBF\modules\options\fields;

use WBF\modules\options\Admin;
use WBF\modules\options\fields\BaseField;
use WBF\modules\options\fields\Field;
use WBF\modules\options\Framework;

class MediaUploader extends BaseField implements Field {

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

	public function get_html() {
		$args = func_get_args();
		$custom_name = isset($args[0]) ? $args[0] : false;

		$output = '';

		$id = strip_tags( strtolower( $this->related_option['id'] ) );

		// Get the option... options :)
		$option_object = Framework::get_option_object($id);

		$value = $this->value;

		if($custom_name){
			$name = $custom_name;
		}else{
			$name = $this->get_field_name();
		}

		if ( $value && !empty($value) ) {
			$class = ' has-file';
		}

		if(isset($option_object['readonly']) && $option_object['readonly'])
			$output .= '<input id="' . $id . '" class="upload" type="text" name="'.$name.'" value="' . $value . '" placeholder="' . __('No file chosen', 'textdomain') .'" readonly />' . "\n";
		else
			$output .= '<input id="' . $id . '" class="upload" type="text" name="'.$name.'" value="' . $value . '" placeholder="' . __('No file chosen', 'textdomain') .'" />' . "\n";

		if ( function_exists( 'wp_enqueue_media' ) ) {
			if ( ( $value == '' ) ) {
				$output .= '<input id="upload-' . $id . '" class="upload-button button" type="button" value="' . __( 'Upload', 'textdomain' ) . '" />' . "\n";
			} else {
				$output .= '<input id="remove-' . $id . '" class="remove-file button" type="button" value="' . __( 'Remove', 'textdomain' ) . '" />' . "\n";
			}
		} else {
			$output .= '<p><i>' . __( 'Upgrade your version of WordPress for full media support.', 'textdomain' ) . '</i></p>';
		}

		if ( $this->get_description() != '' ) {
			$output .= '<span class="of-metabox-desc">' . $this->get_description() . '</span>' . "\n";
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

	public function sanitize( $input, $option ) {
		$output = isset($option['std']) ? $option['std'] : "";
		$filetype = wp_check_filetype($input);
		if ( $filetype["ext"] ) {
			$output = $input;
		}
		return $output;
	}

	public function get_value($input){
		return $input;
	}
}