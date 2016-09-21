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
use WBF\modules\options\fields\BaseField;
use WBF\modules\options\fields\Field;

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
		            if($field instanceof BaseField){
			            $field->setup($val,$current_option);
			            $output .= $field->get_html();
		            }
	            }

	            switch ($current_option['type']) {

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