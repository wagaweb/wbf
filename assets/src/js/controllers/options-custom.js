module.exports = {
    /**
     * Custom scripts needed for the colorpicker, image button selectors,
     * and navigation tabs.
     */
    init: function(){
        var $ = jQuery;
        var of_elements = {
            'color': $('.of-color'),
            'advanced_color': $(".advanced-color"),
            'radio_img_img': $('.of-radio-img-img'),
            'radio_img_label': $('.of-radio-img-label'),
            'radio_img_radio': $('.of-radio-img-radio')
        };
        var $theme_options_wrapper = $('[data-options-gui]');
        var $theme_options_tabs_wrapper = $theme_options_wrapper.find('[data-nav]');
        var $components_wrapper = $('[data-components-gui]');
        var $components_tabs_wrapper = $components_wrapper.find('[data-nav]');

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
            init_tabs($theme_options_wrapper,"wbf_theme_options_active_tab");
        }

        if( $components_tabs_wrapper.length > 0){
            init_tabs($components_wrapper,"wbf_components_active_tab");
        }

        function init_tabs($wrapper,localStorage_var_name) {
            var $tabs_links = $wrapper.find('[data-nav] a');
            var $tabs_first_link = $wrapper.find('[data-nav] a:first');
            var $components_list = $wrapper.find('[data-components-list]');
            var is_components_window = localStorage_var_name == "wbf_components_active_tab";

            // Hides all the .group sections to start
            $('[data-fieldgroup]').hide();

            // Find if a selected tab is saved in localStorage
            var active_tab = '';
            if ( typeof(localStorage) != 'undefined' ) {
                active_tab = localStorage.getItem(localStorage_var_name); //Check for active tab
                if(active_tab != null && active_tab.match(/http/)){ //Hardcoded fix for some incompatibilities
                    active_tab = '';
                }
            }

            // If active tab is saved and exists, load it's .group
            if (active_tab != '' && $(active_tab).length && active_tab != '#component-main' ) {
                $(active_tab).fadeIn();
                $(active_tab + '-tab').addClass('nav-tab-active');
                if(is_components_window){
                    $components_list.hide();
                }
            } else if(!is_components_window) {
                $('[data-fieldgroup]:first').fadeIn();
                $tabs_first_link.addClass('nav-tab-active');
            }

            // Bind tabs clicks
            $tabs_links.click(function(evt) {

                evt.preventDefault();

                // Remove active class from all tabs
                $tabs_links.removeClass('nav-tab-active');

                $(this).addClass('nav-tab-active').blur();

                var group = $(this).attr('href');

                if (typeof(localStorage) != 'undefined' ) {
                    localStorage.setItem(localStorage_var_name, $(this).attr('href') ); //Store the active tab on click (this will be saved with # included)
                }

                $('[data-fieldgroup]').hide();
                $(group).fadeIn();

                // Editor height sometimes needs adjustment when unhidden
                $('.wp-editor-wrap').each(function() {
                    var editor_iframe = $(this).find('iframe');
                    if ( editor_iframe.height() < 30 ) {
                        editor_iframe.css({'height':'auto'});
                    }
                });

            });
        }
    }
};