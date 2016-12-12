<?php
namespace WBF\modules\options\fields;


use WBF\components\mvc\HTMLView;
use WBF\includes\GoogleFontsRetriever;
use WBF\modules\options\Framework;

class MultipleFontSelector extends BaseField implements Field {
	public function init() {
		// render template for font selector
		add_action( 'admin_footer', function () {
			$v = new HTMLView("src/modules/options/views/admin/font-selector.php","wbf");
			//Display the view
			$v->display();
		});
		add_action( 'wp_ajax_getFontsForAjax', [$this, 'getFontsForAjax']);
		add_action( 'wp_ajax_nopriv_getFontsForAjax', [$this, 'getFontsForAjax']);
		add_filter( 'wbf/modules/options/differences', [$this,'compute_differences_upon_saving'], 10, 3);
	}

	/**
	 * Return the field html
	 *
	 * @return string
	 */
	public function get_html() {
		$_id = $this->related_option['id'];
		$val = $this->value;

		$option = Framework::get_option_object($_id);

		$output = ''; $id = strip_tags(strtolower($_id));

		$output .= "<button id ='multiple-font-selector' class='button-primary' data-option-name='". $this->get_field_name()."'>Add Google Font</button>";

		if (isset($option['css_selectors']) && !empty($option['css_selectors'])) {
			$output .= "<input type='hidden' name='wbf-options-fonts-css-selectors' value='' data-css-selectors='".implode('|', $option['css_selectors'])."'>";
		}
		if (isset($option['preview']) && !empty($option['preview'])) {
			$output .= "<input type='hidden' name='wbf-options-fonts-tryIt-preview' value='' data-triIt-preview='".$option['preview']."'>";
		}

		// start the section
		$output .= '<div class="font-selector" data-font-selector>';

		if (!empty($val)) {
			$output .= $this->selectedOutput();
		}

		$output .= '</div>';
		return $output;
	}

	private function selectedOutput(){
		$values = $this->value;
		$_id = $this->related_option['id'];

		$output = '';

		//encode the json with the real values
		if (isset($values['import'])){
			$json_import_stored_values = json_encode($values['import']);
			$output .= "<input type='hidden' name='wbf-options-fonts-import-stored-values' value='' data-stored-values='".$json_import_stored_values."'>";
		}
		if (isset($values['assign'])){
			$json_assign_stored_values = json_encode($values['assign']);
			$output .= "<input type='hidden' name='wbf-options-fonts-assign-stored-values' value='' data-stored-values='".$json_assign_stored_values."'>";
		}

		// this empty input is for a bug
		$output .= '<input type="hidden" data-fontlist name="'. $this->get_field_name().'[import][0][family]">';

		$output .= '<option value=""></option>';

		$output .= '</input>';

		return $output;
	}

	public function getFonts(){
		$gfontfetcher = GoogleFontsRetriever::getInstance();

		//$os_fonts = self::getOSFonts();
		$g_fonts = $gfontfetcher->get_webfonts();
		if(!$g_fonts){
			$g_fonts = new \stdClass();
			$g_fonts->items = array();
		}
		//$fonts = array_merge($os_fonts,$g_fonts->items);

		return $g_fonts->items;
	}

	public function getFontsForAjax(){

		$fonts = $this->getFonts();

		$json_fonts = json_encode($fonts);
		// I want an array of arrays and not objects
		$array_fonts = json_decode($json_fonts, true);

		$result = json_encode($array_fonts);
		echo $result;
		die();
	}

	/**
	 * Sanitize the field. This function is hooked to 'of_sanitize_{field_type}' during module initialization.
	 *
	 * @param string|array $input
	 * @param array $option the option as defined by the developer
	 *
	 * @return mixed
	 */
	public function sanitize( $input, $option ) {
		$output = [];

		if(!isset($input['import'])) $input['import'] = [];
		$import = $input['import'];
		if(!isset($input['assign'])) $input['assign'] = [];
		$assign = $input['assign'];

		unset($import[0]);

		foreach ($import as $k => $font){
			$output['import'][$k] = wp_parse_args($font, [
				'family'  => '',
				'subset' => [],
				'weight' => []
			]);
		}

		foreach ($assign as $k => $font){
			$output['assign'][$k] = wp_parse_args($font, [
				'family'  => '',
				'weight' => '',
				'style' => ''
			]);
		}

		if(isset($output['import'])) {
			$output['import'] = array_values($output['import']);
		}

		return $output;
	}

	public function compute_differences_upon_saving($diff, $new_values, $old_values){
		$options = Framework::get_registered_options_of_type("fonts_selector");
		foreach($options as $opt){
			if(isset($new_values[$opt['id']]) && isset($old_values[$opt['id']])){
				$diff[$opt['id']] = $new_values[$opt['id']]; //todo: compute a real difference
			}
		}
		return $diff;
	}
}