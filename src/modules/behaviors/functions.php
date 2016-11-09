<?php
/**
 * @package   Behavior Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 */

namespace WBF\modules\behaviors;
use WBF\modules\options\Organizer;

/**
 * Get a behaviour.
 *
 * @param string $name
 * @param int $post_id
 * @param string $return (value OR array)
 *
 * @return mixed|\WP_Error the behavior value or \WP_Error. Returns the default value if behavior is not enable for current node (post,page...)
 */
function get_behavior($name, $post_id = 0, $return = "value") {

	static $retrieved_behaviors = array();

	if ($post_id == 0 && !is_archive()) {
		if(is_home() || is_404() || is_search() || is_page()){
			$post_id = get_queried_object_id();
		}else{
			global $post;
			if(isset($post) && isset($post->ID) && $post->ID != 0){
				$post_id = $post->ID;
			}
		}
	}

	if($post_id == 0 && is_archive()){
		$blog_page = get_option('page_for_posts');
		if($blog_page){
			$post_id = $blog_page;
		}
	}

	if(isset($retrieved_behaviors[$name][$post_id])){
		$b = $retrieved_behaviors[$name][$post_id];
	}else{
		$b = BehaviorsManager::get($name, $post_id);
		if($post_id !== 0){
			$retrieved_behaviors[$name][$post_id] = $b;
		}
	}

	if(!$b instanceof Behavior){
		return new \WP_Error("behavior_is_not_an_object", "Unable to retrieve the behavior instance", ['post_id' => $post_id, 'name' => $name]);
	}

	if(!isset($b->value)){
		if(isset($retrieved_behaviors[$name]) && isset($retrieved_behaviors[$name][$post_id])){
			unset($retrieved_behaviors[$name][$post_id]);
		}
		return new \WP_Error("unable_to_retrieve_behavior", "Unable to retrieve the behavior value", ['post_id' => $post_id, 'name' => $name, 'default' => $b->default]);
	}

	if(!$b->is_enable_for_node($post_id)){
		$b->value = $b->default;
	}

	$b = apply_filters("wbf/modules/behaviors/get",$b);
	$b = apply_filters("wbf/modules/behaviors/get/".$b->name,$b);

	if($return == "value"){
		$b->value = apply_filters("wbf/modules/behaviors/get/".$b->name."/value",$b->value);
		return $b->value;
	}else{
		return $b;
	}
}

/**
 * Register the behaviors as theme options. The values of those theme options will serve as behaviors default values.
 *
 * @param Organizer $organizer
 *
 * @hooked 'wbf/theme_options/register'
 *
 * @since 0.13.12
 *
 * @return array
 */
function register_behaviors_as_theme_options($organizer){
	if(\WBF::module_is_loaded("behaviors") && class_exists('\WBF\modules\behaviors\BehaviorsManager') && BehaviorsManager::hasBehaviors()){
		//Behaviors tab heading
		$bh_options[] = [];
		$section_label = apply_filters("wbf/modules/behaviors/options_tab_label",_x( 'Posts & Pages', "Behaviors tab name in theme options page" , 'wbf' ));
		$organizer->add_section("behaviors",$section_label);
		$post_types = wbf_get_filtered_post_types(); //Get post types
		foreach($post_types as $ptSlug => $ptLabel){
			if( BehaviorsManager::count_behaviors_for_post_type($ptSlug) > 0){
				$predef_behavior = BehaviorsManager::getAll(); //get predefined options

				//Post type heading
				$organizer->add([
					'name' => $ptLabel,
					'desc' => sprintf(__( 'Edit default options for "%s" post type', 'waboot' ),strtolower($ptLabel)),
					'type' => 'info'
				],"behaviors","behaviors",['behavior'=>true]);

				//Post type options:s
				foreach($predef_behavior as $b){
					if($b->is_enabled_for_post_type($ptSlug)){
						$option = $b->generate_of_option($ptSlug);
						$organizer->add($option,"behaviors","behaviors",['behavior'=>true]);
					}
				}
			}
		}
	}
}

function create_metabox(){
	$post_id = get_the_ID();
	if($post_id != 0 && BehaviorsManager::count_behaviors_for_node_id($post_id) > 0){
		$behaviors = BehaviorsManager::getAll();
		add_meta_box("behavior","Behaviors",'\WBF\modules\behaviors\display_metabox',null,"advanced","core",array($behaviors));
	}
}

function display_metabox(\WP_Post $post,array $behaviors){
	$behaviors = $behaviors['args'][0];

	wp_nonce_field('behaviors_meta_box','behaviors_meta_box_nonce');

	?>
	<?php $opt_n=0; foreach($behaviors as $b) : ?>
		<?php if($b->is_enable_for_node($post->ID)) : ?>
			<?php
			$opt_n++;
			$b->print_metabox($post->ID);
			?>
		<?php endif; ?>
	<?php endforeach; ?>
	<?php if($opt_n == 0) : ?>
		<p>No behavior available for this post type.</p>
	<?php endif;
}

function save_metabox($post_id){
	// Check if our nonce is set.
	if ( ! isset( $_POST['behaviors_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['behaviors_meta_box_nonce'], 'behaviors_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	// Then save behaviors...

	$behaviors = BehaviorsManager::getAll();
	foreach($behaviors as $b){
		$metaname = $b->metaname;

		if($b->is_enable_for_node($post_id)){
			if(!isset($_POST[$metaname])){
				if($b->type == "checkbox"){
					if($b->has_multiple_choices())
						$_POST[$metaname] = array();
					else
						$_POST[$metaname] = "0";
				}
			}

			if(isset($_POST[$metaname])){
				if(isset($_POST[$metaname."_default"]) || (is_array($_POST[$metaname]) && in_array("_default",$_POST[$metaname]))){
					$b->set_value("_default");
				}else{
					$b->set_value($_POST[$metaname]);
				}
				$b->save_meta($post_id);
			}
		}
	}
}