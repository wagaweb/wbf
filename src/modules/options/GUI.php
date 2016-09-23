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
	 * @param array|null $options
	 */
    static function optionsframework_fields($options = null) {
        global $allowedtags, $wbf_options_framework;

        $saved_options = Framework::get_saved_options(); //the current saved options
	    
        if(!isset($options)){
            $options = &Framework::get_registered_options(); //the current registered options (some of which may not be saved already)
        }

        $counter = 0;
	    $in_group = false;
	    $wrapper_start = new HTMLView( "src/modules/options/views/admin/parts/option-wrapper-start.php", "wbf");
	    $wrapper_end = new HTMLView( "src/modules/options/views/admin/parts/option-wrapper-end.php", "wbf");
	    $group_wrapper_start = new HTMLView( "src/modules/options/views/admin/parts/options-group-wrapper-start.php", "wbf");
	    $group_wrapper_end = new HTMLView( "src/modules/options/views/admin/parts/options-group-wrapper-end.php", "wbf");

		$options = apply_filters("wbf/modules/options/gui/options_to_render",$options);

	    if(is_array($options) && !empty($options)){

            foreach ($options as $current_option) {
	            $val = '';
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

	            // Print wrapper start
	            if(Framework::option_can_have_value($current_option)){

		            // If there is a description save it for labels
		            $current_option_description = '';
		            if(isset($current_option['desc'])) {
			            $current_option_description = $current_option['desc'];
			            $current_option_description = wp_kses($current_option_description, $allowedtags);
		            }

		            $output .= $wrapper_start->get([
		            	'id' => esc_attr(Framework::sanitize_option_id($current_option['id'])),
			            'type' => isset($current_option['type']) ? $current_option['type'] : "notype",
			            'additional_classes' => isset($current_option['class']) ? esc_attr(" ".$current_option['class']) : "",
			            'name' => isset($current_option['name']) ? $current_option['name'] : false,
			            'description' => $current_option_description != '' ? $current_option_description : false,
			            'inner_classes' => $current_option['type'] != 'editor' ? "controls" : false
		            ]);
	            }

	            // Print options
	            $registered_fields = $wbf_options_framework->fields;

	            if(isset($registered_fields[$current_option['type']])){

	            	$field = $registered_fields[$current_option['type']];

		            if($field instanceof BaseField){

			            $field->setup($val,$current_option);

			            if($current_option['type'] == "heading"){

			            	if($in_group){
					            $output .= $group_wrapper_end->get();
					            $in_group = false;
				            }

				            $counter++;

				            $class = !empty($current_option['id']) ? $current_option['id'] : $current_option['name'];
				            $class = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($class));
				            $section_id = isset($current_option['section_id']) ? $current_option['section_id'] : "";
				            if($section_id !== "") $class = $class." ".$section_id;

				            $in_group = true;

				            $output .= $group_wrapper_start->get([
				            	'count' => $counter,
					            'class' => $class
				            ]);
			            }

			            $output .= $field->get_html();
		            }
	            }

	            // Print wrapper end
	            if(Framework::option_can_have_value($current_option)) {
		            $output .= $wrapper_end->get();
	            }

	            echo $output;
            }
	    }else{
		    echo '<p>'.__("There is no options available","wbf")."</p>";
	    }

	    // Outputs closing div if there tabs
	    // o.O If you remove this, you can add the closing div to the component page, BUT the options page won't work... o.O Oh, fuck vendors code.
	    if (GUI::optionsframework_tabs() != '') {
		    echo '</div><!-- strange closing div -->';
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