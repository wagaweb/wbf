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

use WBF\components\mvc\HTMLView;

class GUI{
	static function getOrganizer(){
		return Organizer::getInstance();
	}

	/**
	 * Generates the options fields that are used in the form.
	 *
	 * @todo: refactoring needed. Embedded HTML is just wrong.
	 *
	 * @param array|null $options
	 */
    static function optionsframework_fields($options = null) {
        global $allowedtags, $wbf_options_framework;

	    $options_db_key = Framework::get_options_root_id(); //the option name under which the options are saved
	    
        $saved_options = Framework::get_saved_options(); //the current saved options
	    
        if(!isset($options)){
            $options = &Framework::get_registered_options(); //the current registered options (some of which may not be saved already)
        }

        $counter = 0;
        $menu = '';

		$options = apply_filters("wbf/modules/options/gui/options_to_render",$options);

	    if(is_array($options) && !empty($options)){
            foreach ($options as $current_option) {

	            $val = '';
	            $select_value = '';
	            $output = '';

	            // Set default value to $val
	            if (isset($current_option['std'])) {
	                $val = $current_option['std'];
	            }

	            // If the option is already saved, override $val
                if(isset($saved_options[$current_option['id']])){
                    $val = $saved_options[($current_option['id'])];
                    // Striping slashes of non-array options
                    if(!is_array($val)) {
                        $val = stripslashes($val);
                    }
                }

	            // If there is a description save it for labels
	            $current_option_description = '';
	            if(isset($current_option['desc'])) {
	                $current_option_description = $current_option['desc'];
		            $current_option_description = wp_kses($current_option_description, $allowedtags);
	            }

	            // Wrap all options
	            if(Framework::is_valuable_option($current_option)){

	                $current_option['id'] = Framework::sanitize_option_id($current_option['id']);

	                $id = 'section-' . $current_option['id'];

	                $class = 'section';
	                if (isset($current_option['type'])) {
	                    $class .= ' section-' . $current_option['type'];
	                }
	                if (isset($current_option['class'])) {
	                    $class .= ' ' . $current_option['class'];
	                }

	                $output .= '<div id="' . esc_attr($id) . '" class="' . esc_attr($class) . '">' . "\n";
	                if (isset($current_option['name'])) {
	                    $output .= '<h4 class="heading">' . esc_html($current_option['name']) . '</h4>' . "\n";
	                }
	                if (($current_option['type'] != "heading") && ($current_option['type'] != "info")) {
	                    if (($current_option['type'] != "checkbox") && ($current_option['type'] != "editor")) {
	                        $output .= '<div class="explain">' . $current_option_description . '</div>' . "\n";
	                    }
	                }
	                if ($current_option['type'] != 'editor') {
	                    $output .= '<div class="option">' . "\n" . '<div class="controls">' . "\n";
	                } else {
	                    $output .= '<div class="option">' . "\n" . '<div>' . "\n";
	                }
	            }

	            if (has_filter('optionsframework_' . $current_option['type'])) {
	                $output .= apply_filters('optionsframework_' . $current_option['type'], $options_db_key, $current_option, $val);
	            }

	            $registered_fields = $wbf_options_framework->fields;
	            if(isset($registered_fields[$current_option['type']])){
	            	$field = $registered_fields[$current_option['type']];
		            $field->build($val,$current_option);
	            	$output .= $field->get_html();
	            }

	            switch ($current_option['type']) {

	                // Waboot CSS Editor [WABOOT MOD]
	                case "csseditor":
	                    $output .= CodeEditor::display($current_option['id'], $val, null);
	                    break;

	                // Typography [WABOOT MOD]
		            // Waboot GFont Selector [WABOOT MOD]
	                case 'typography':
	                case "gfont":
						$output .= FontSelector::output($current_option['id'], $val, $current_option['std']);
						break;

	                // Textarea
	                case 'textarea':
	                    $rows = '8';

	                    if (isset($current_option['settings']['rows'])) {
	                        $custom_rows = $current_option['settings']['rows'];
	                        if (is_numeric($custom_rows)) {
	                            $rows = $custom_rows;
	                        }
	                    }

	                    $val = stripslashes($val);
	                    $output .= '<textarea id="' . esc_attr($current_option['id']) . '" class="of-input" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . ']') . '" rows="' . $rows . '">' . esc_textarea($val) . '</textarea>';
	                    break;

	                // Select Box
	                case 'select':
	                    $output .= '<select class="of-input" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . ']') . '" id="' . esc_attr($current_option['id']) . '">';

	                    foreach ($current_option['options'] as $key => $option) {
	                        $output .= '<option' . selected($val, $key, false) . ' value="' . esc_attr($key) . '">' . esc_html($option) . '</option>';
	                    }
	                    $output .= '</select>';
	                    break;


	                // Radio Box
	                case "radio":
	                    $name = $options_db_key . '[' . $current_option['id'] . ']';
	                    foreach ($current_option['options'] as $key => $option) {
	                        $id = $options_db_key . '-' . $current_option['id'] . '-' . $key;
	                        $output .= '<div class="radio-wrapper"><input class="of-input of-radio" type="radio" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" value="' . esc_attr($key) . '" ' . checked($val, $key, false) . ' /><label for="' . esc_attr($id) . '">' . esc_html($option) . '</label></div>';
	                    }
	                    break;

	                // Image Selectors
	                case "images":
	                    $name = $options_db_key . '[' . $current_option['id'] . ']';
	                    foreach ($current_option['options'] as $key => $option) {
	                        $selected = '';
	                        if ($val != '' && ($val == $key)) {
	                            $selected = ' of-radio-img-selected';
	                        }

	                        if(is_array($option)){
	                            $option_value = $option['value'];
	                        }else{
	                            $option_value = $option;
	                        }

	                        $output .= '<input type="radio" id="' . esc_attr($current_option['id'] . '_' . $key) . '" class="of-radio-img-radio" value="' . esc_attr($key) . '" name="' . esc_attr($name) . '" ' . checked($val, $key, false) . ' />';
	                        $output .= '<div class="of-radio-img-label">' . esc_html($key) . '</div>';
	                        $output .= '<div class="option-wrap">';
	                        if(is_array($option) && isset($option['label'])){
	                            $output .= '<span>'. esc_attr($option['label']) . '</span>';
	                        }
	                        $output .= '<img src="' . esc_url($option_value) . '" alt="' . $option_value . '" class="of-radio-img-img' . $selected . '" onclick="document.getElementById(\'' . esc_attr($current_option['id'] . '_' . $key) . '\').checked=true;" /></div>';
	                    }
	                    break;

	                // Checkbox
	                case "checkbox":
	                    $output .= '<div class="wb-onoffswitch">';
	                    $output .= '<div class="check_wrapper"><input id="' . esc_attr($current_option['id']) . '" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . ']') . '" ' . checked($val, 1, false) . ' />';
	                    $output .= '<label class="wb-onoffswitch-label" for="' . esc_attr($current_option['id']) . '"><span class="wb-onoffswitch-inner"></span><span class="wb-onoffswitch-switch"></span></label></div>';
	                    $output .= '</div>';
	                    $output .= '<span class="explain">' . $current_option_description . '</span>';
	                    break;

	                // Multicheck
	                case "multicheck":
	                    foreach ($current_option['options'] as $key => $option) {
	                        $checked = '';
	                        $label = $option;
	                        $option = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($key));

	                        $id = $options_db_key . '-' . $current_option['id'] . '-' . $option;
	                        $name = $options_db_key . '[' . $current_option['id'] . '][' . $option . ']';

	                        if (isset($val[$option])) {
	                            $checked = checked($val[$option], 1, false);
	                        }

	                        $output .= '<div class="check-wrapper"><input id="' . esc_attr($id) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr($name) . '" ' . $checked . ' /><label for="' . esc_attr($id) . '">' . esc_html($label) . '</label></div>';
	                    }
	                    break;

	                // Color picker
	                case "color":
	                    $default_color = '';
	                    if (isset($current_option['std'])) {
	                        if ($val != $current_option['std']) {
	                            $default_color = ' data-default-color="' . $current_option['std'] . '" ';
	                        }
	                    }
	                    $output .= '<input name="' . esc_attr($options_db_key . '[' . $current_option['id'] . ']') . '" id="' . esc_attr($current_option['id']) . '" class="of-color"  type="text" value="' . esc_attr($val) . '"' . $default_color . ' />';

	                    break;

					// RGBA Color picker
		            case "advanced_color":

			            $output .= Advanced_Color::display($current_option, $val, $options_db_key);

			            break;

	                // Uploader
	                case "upload":
	                    $output .= MediaUploader::optionsframework_uploader($current_option['id'], $val, null);

	                    break;

	                // Background
	                case 'background':

	                    $background = $val;

	                    // Background Color
	                    $default_color = '';
	                    if (isset($current_option['std']['color'])) {
	                        if ($val != $current_option['std']['color']) {
	                            $default_color = ' data-default-color="' . $current_option['std']['color'] . '" ';
	                        }
	                    }
	                    $output .= '<input name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][color]') . '" id="' . esc_attr($current_option['id'] . '_color') . '" class="of-color of-background-color"  type="text" value="' . esc_attr($background['color']) . '"' . $default_color . ' />';

	                    // Background Image
	                    if (!isset($background['image'])) {
	                        $background['image'] = '';
	                    }

	                    $output .= MediaUploader::optionsframework_uploader($current_option['id'], $background['image'], null, esc_attr($options_db_key . '[' . $current_option['id'] . '][image]'));

	                    $class = 'of-background-properties';
	                    if ('' == $background['image']) {
	                        $class .= ' hide';
	                    }
	                    $output .= '<div class="' . esc_attr($class) . '">';

	                    // Background Repeat
	                    $output .= '<select class="of-background of-background-repeat" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][repeat]') . '" id="' . esc_attr($current_option['id'] . '_repeat') . '">';
	                    $repeats = of_recognized_background_repeat();

	                    foreach ($repeats as $key => $repeat) {
	                        $output .= '<option value="' . esc_attr($key) . '" ' . selected($background['repeat'], $key, false) . '>' . esc_html($repeat) . '</option>';
	                    }
	                    $output .= '</select>';

	                    // Background Position
	                    $output .= '<select class="of-background of-background-position" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][position]') . '" id="' . esc_attr($current_option['id'] . '_position') . '">';
	                    $positions = of_recognized_background_position();

	                    foreach ($positions as $key => $position) {
	                        $output .= '<option value="' . esc_attr($key) . '" ' . selected($background['position'], $key, false) . '>' . esc_html($position) . '</option>';
	                    }
	                    $output .= '</select>';

	                    // Background Attachment
	                    $output .= '<select class="of-background of-background-attachment" name="' . esc_attr($options_db_key . '[' . $current_option['id'] . '][attachment]') . '" id="' . esc_attr($current_option['id'] . '_attachment') . '">';
	                    $attachments = of_recognized_background_attachment();

	                    foreach ($attachments as $key => $attachment) {
	                        $output .= '<option value="' . esc_attr($key) . '" ' . selected($background['attachment'], $key, false) . '>' . esc_html($attachment) . '</option>';
	                    }
	                    $output .= '</select>';
	                    $output .= '</div>';

	                    break;

	                // Editor
	                case 'editor':
	                    $output .= '<div class="explain">' . $current_option_description . '</div>' . "\n";
	                    echo $output;
	                    $textarea_name = esc_attr($options_db_key . '[' . $current_option['id'] . ']');
	                    $default_editor_settings = array(
	                        'textarea_name' => $textarea_name,
	                        'media_buttons' => false,
	                        'tinymce' => array('plugins' => 'wordpress')
	                    );
	                    $editor_settings = array();
	                    if (isset($current_option['settings'])) {
	                        $editor_settings = $current_option['settings'];
	                    }
	                    $editor_settings = array_merge($default_editor_settings, $editor_settings);
	                    wp_editor($val, $current_option['id'], $editor_settings);
	                    $output = '';
	                    break;

	                // Info
	                case "info":
	                    $id = '';
	                    $class = 'section';
	                    if (isset($current_option['id'])) {
	                        $id = 'id="' . esc_attr($current_option['id']) . '" ';
	                    }
	                    if (isset($current_option['type'])) {
	                        $class .= ' section-' . $current_option['type'];
	                    }
	                    if (isset($current_option['class'])) {
	                        $class .= ' ' . $current_option['class'];
	                    }

	                    $output .= '<div ' . $id . 'class="' . esc_attr($class) . '">' . "\n";
	                    if (isset($current_option['name'])) {
	                        $output .= '<h4 class="heading">' . esc_html($current_option['name']) . '</h4>' . "\n";
	                    }
	                    if ($current_option['desc']) {
	                        $output .= apply_filters('of_sanitize_info', $current_option['desc']) . "\n";
	                    }
	                    $output .= '</div>' . "\n";
	                    break;

	                // Heading for Navigation
	                case "heading":
	                    $counter++;
	                    if ($counter >= 2) {
	                        $output .= '</div>' . "\n";
	                    }
	                    $class = '';
	                    $class = !empty($current_option['id']) ? $current_option['id'] : $current_option['name'];
	                    $class = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($class));
						$section_id = isset($value['section_id']) ? $value['section_id'] : "";
						if($section_id !== "") $class = $class." ".$section_id;
	                    $output .= '<div id="options-group-' . $counter . '" class="group ' . $class . '">';
	                    $output .= '<h3>' . esc_html($current_option['name']) . '</h3>' . "\n";
	                    break;
	            }

	            if (($current_option['type'] != "heading") && ($current_option['type'] != "info")) {
	                $output .= '</div><!-- end control -->';
	                $output .= '</div><!-- end option --></div><!-- end section -->' . "\n";
	            }

	            echo $output;
            }
	    }else{
		    echo '<p>'.__("There is no options available","wbf")."</p>";
	    }

	    // Outputs closing div if there tabs
	    // o.O If you remove this, you can add the closing div to the component page, BUT the options page won't work... o.O Oh, fuck vendors code.
	    if (GUI::optionsframework_tabs() != '') {
		    echo '</div>';
	    }
    }

    /**
     * Generates the tabs that are used in the options menu
     */
    static function optionsframework_tabs() {
        $options = & Framework::get_registered_options();
        $tabs = [];
        if(is_array($options) && !empty($options)){
			$tabs = array_filter($options, function($el){
				if ($el['type'] == "heading") {
					return true;
				}
				return false;
			});
			$tabs = apply_filters("wbf/modules/options/gui/tab_section/tabs",$tabs,$options);
        }
        return $tabs;
    }
}