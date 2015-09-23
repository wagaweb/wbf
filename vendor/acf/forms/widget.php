<?php

/*
*  ACF Widget Form Class
*
*  All the logic for adding fields to widgets
*
*  @class 		acf_form_widget
*  @package		ACF
*  @subpackage	Forms
*/

if( ! class_exists('acf_form_widget') ) :

class acf_form_widget {
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// actions
		add_action('admin_enqueue_scripts',		array($this, 'admin_enqueue_scripts'));
		add_action('in_widget_form', 			array($this, 'edit_widget'), 10, 3);
		
		
		// filters
		add_filter('widget_update_callback', 	array($this, 'widget_update_callback'), 10, 4);
		
		
		// ajax
		add_action('wp_ajax_update-widget', 	array($this, 'ajax_update_widget'), 0, 1);
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// validate screen
		if( acf_is_screen('widgets') || acf_is_screen('customize') ) {
		
			// valid
			
		} else {
			
			return;
			
		}
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('acf/input/admin_footer', array($this, 'admin_footer'));

	}
	
	
	/*
	*  edit_widget
	*
	*  This function will render the fields for a widget form
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	$widget (object)
	*  @param	$return (null)
	*  @param	$instance (object)
	*  @return	$post_id (int)
	*/
	function edit_widget( $widget, $return, $instance ) {
		
		// vars
		$post_id = 0;
		
		
		// get id
		if( $widget->number !== '__i__' ) {
		
			$post_id = "widget_{$widget->id}";
			
		}
		
		
		// get field groups
		$field_groups = acf_get_field_groups(array(
			'widget' => $widget->id_base
		));
		
		
		// render
		if( !empty($field_groups) ) {
			
			// render post data
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'widget' 
			));
			
			
			foreach( $field_groups as $field_group ) {
				
				$fields = acf_get_fields( $field_group );
				
				acf_render_fields( $post_id, $fields, 'div', 'field' );
				
			}
			
			if( $widget->updated ): ?>
			<script type="text/javascript">
			(function($) {
				
				acf.do_action('append', $('[id^="widget"][id$="<?php echo $widget->id; ?>"]') );
				
			})(jQuery);	
			</script>
			<?php endif;
		
		}
		
	}
	
	
	/*
	*  save_widget
	*
	*  This function will hook in before 'widget_update_callback' and save values to bypass customizer validation issues
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_update_widget() {
		
		// remove default save filter
		remove_filter('widget_update_callback', array($this, 'widget_update_callback'), 10, 4);
		
		
		// bail early if no nonce
		if( !acf_verify_nonce('widget') ) {
		
			return;
			
		}
		
		
		// vars
		$id = acf_maybe_get($_POST, 'widget-id');
		
	    
	    // save data
	    if( $id && acf_validate_save_post() ) {
	    	
			acf_save_post( "widget_{$id}" );		
		
		}
		
	}

	
	
	/*
	*  widget_update_callback
	*
	*  This function will hook into the widget update filter and save ACF data
	*
	*  @type	function
	*  @date	27/05/2015
	*  @since	5.2.3
	*
	*  @param	$instance (array) widget settings
	*  @param	$new_instance (array) widget settings
	*  @param	$old_instance (array) widget settings
	*  @param	$widget (object) widget info
	*  @return	$instance
	*/
	
	function widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
		
		// bail early if no nonce
		if( !acf_verify_nonce('widget') ) {
		
			return $instance;
			
		}
		
		
	    // save
	    if( acf_validate_save_post() ) {
	    	
			acf_save_post( "widget_{$widget->id}" );		
		
		}
		
		
		// return
		return $instance;
		
	}
	
	
	/*
	*  admin_footer
	*
	*  This function will add some custom HTML to the footer of the edit page
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
?>
<script type="text/javascript">
(function($) {
	
	 acf.add_filter('get_fields', function( $fields ){
	 	
	 	return $fields.not('#available-widgets .acf-field');

    });
	
	
	$('#widgets-right').on('click', '.widget-control-save', function( e ){
		
		// vars
		var $form = $(this).closest('form');
		
		
		// bail early if not active
		if( !acf.validation.active ) {
		
			return true;
			
		}
		
		
		// ignore validation (only ignore once)
		if( acf.validation.ignore ) {
		
			acf.validation.ignore = 0;
			return true;
			
		}
		
		
		// bail early if this form does not contain ACF data
		if( !$form.find('#acf-form-data').exists() ) {
		
			return true;
		
		}

		
		// stop WP JS validation
		e.stopImmediatePropagation();
		
		
		// store submit trigger so it will be clicked if validation is passed
		acf.validation.$trigger = $(this);
		
		
		// run validation
		acf.validation.fetch( $form );
		
		
		// stop all other click events on this input
		return false;
		
	});
	
	
	$(document).on('click', '.widget-top', function(){
		
		var $el = $(this).parent().children('.widget-inside');
		
		setTimeout(function(){
			
			acf.get_fields('', $el).each(function(){
				
				acf.do_action('show_field', $(this));	
				
			});
			
		}, 250);
				
	});
	
	$(document).on('widget-added', function( e, $widget ){
		
		acf.do_action('append', $widget );
		
	});
	
	$(document).on('widget-saved widget-updated', function( e, $widget ){
		
		// unlock form
		acf.validation.toggle( $widget, 'unlock' );
		
		
		// submit
		acf.do_action('submit', $widget );
		
	});
	
	<?php if( acf_is_screen('customize') ): ?>
	
	// customizer saves widget on any input change, so unload is not needed
	acf.unload.active = 0;

	<?php endif; ?>
		
})(jQuery);	
</script>
<?php
		
	}
	
}

new acf_form_widget();

endif;

?>
