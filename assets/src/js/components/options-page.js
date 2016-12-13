import * as Backbone from "backbone";
import * as _ from "underscore";
import $ from "jquery";

export default class{
    static init(){
        /*
         * Custom scripts needed for the colorpicker, image button selectors,
         * and navigation tabs.
         */
        let $ = jQuery;
        let of_elements = {
            'color': $('.of-color'),
            'advanced_color': $(".advanced-color"),
            'radio_img_img': $('.of-radio-img-img'),
            'radio_img_label': $('.of-radio-img-label'),
            'radio_img_radio': $('.of-radio-img-radio')
        };
        let $theme_options_wrapper = $('[data-options-gui]');
        let $theme_options_tabs_wrapper = $theme_options_wrapper.find('[data-nav]');
        let $components_wrapper = $('[data-components-gui]');
        let $components_tabs_wrapper = $components_wrapper.find('[data-nav]');

        // Loads the color pickers
        of_elements.color.wpColorPicker();

        //Init spectrum color picker
        of_elements.advanced_color.spectrum({
            showInput: true,
            showAlpha: true,
            showPalette: true
        });

        // Image Options
        of_elements.radio_img_img.click(function(){
            $(this).parent().parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
            $(this).addClass('of-radio-img-selected');
        });

        of_elements.radio_img_label.hide();
        of_elements.radio_img_img.show();
        of_elements.radio_img_radio.hide();

        // Loads tabbed sections if they exist

        if ( $theme_options_tabs_wrapper.length > 0 ) {
            this.init_navigation($theme_options_wrapper,"wbf_theme_options_active_tab");
        }

        if( $components_tabs_wrapper.length > 0){
            this.init_navigation($components_wrapper,"wbf_components_active_tab");
        }
    }
    static init_navigation($wrapper,localStorage_var_name){
        let $tabs_links = $wrapper.find('[data-nav] a');
        let $tabs_first_link = $wrapper.find('[data-nav] a:first');
        let $components_list = $wrapper.find('[data-components-list]');
        let is_components_window = localStorage_var_name == "wbf_components_active_tab";

        // Hides all the .group sections to start
        $('section[data-category]').hide();

        // Find if a selected tab is saved in localStorage
        let active_tab = '';
        if ( typeof(localStorage) != 'undefined' ) {
            active_tab = localStorage.getItem(localStorage_var_name); //Check for active tab
            if(active_tab != null && active_tab.match(/http/)){ //Hardcoded fix for some incompatibilities
                active_tab = '';
            }
        }

        // If active tab is saved and exists, load it's .group
        if (active_tab != '' && $('section[data-category='+active_tab+']').length > 0 && active_tab != '#component-main' ) {
            $('section[data-category='+active_tab+']').fadeIn();
            $tabs_links.filter('[data-category='+active_tab+']').addClass('nav-tab-active');
            if(is_components_window){
                $components_list.hide();
            }
        } else if(!is_components_window) {
            $('section[data-category]:first').fadeIn();
            $tabs_first_link.addClass('nav-tab-active');
        }

        // Bind tabs clicks
        $tabs_links.click(function(evt) {

            evt.preventDefault();

            // Remove active class from all tabs
            $tabs_links.removeClass('nav-tab-active');

            $(this).addClass('nav-tab-active').blur();

            let category = $(this).data('category');

            if (typeof(localStorage) != 'undefined' ) {
                localStorage.setItem(localStorage_var_name, category ); //Store the active tab on click (this will be saved with # included)
            }

            $('section[data-category]').hide();
            $('section[data-category='+category+']').fadeIn();

            // Editor height sometimes needs adjustment when unhidden
            $('.wp-editor-wrap').each(function() {
                let editor_iframe = $(this).find('iframe');
                if ( editor_iframe.height() < 30 ) {
                    editor_iframe.css({'height':'auto'});
                }
            });

        });
    }
}