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
        var $tabs_wrapper = $('#optionsframework-wrap').find('.nav-tab-wrapper');

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
        if ( $tabs_wrapper.length > 0 ) {
            options_framework_tabs();
        }

        function options_framework_tabs() {
            var $wrapper = $('#optionsframework-wrap');
            var $tabs_links = $wrapper.find('.nav-tab-wrapper a');
            var $tabs_first_link = $wrapper.find('.nav-tab-wrapper a:first');

            // Hides all the .group sections to start
            $('.group').hide();

            // Find if a selected tab is saved in localStorage
            var active_tab = '';
            if ( typeof(localStorage) != 'undefined' ) {
                active_tab = localStorage.getItem("wbf_theme_options_active_tab"); //Check for active tab
                if(active_tab.match(/http/)){ //Hardcoded fix for some incompatibilities
                    active_tab = '';
                }
            }

            // If active tab is saved and exists, load it's .group
            if (active_tab != '' && $(active_tab).length ) {
                $(active_tab).fadeIn();
                $(active_tab + '-tab').addClass('nav-tab-active');
            } else {
                $('.group:first').fadeIn();
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
                    localStorage.setItem("wbf_theme_options_active_tab", $(this).attr('href') ); //Store the active tab on click (this will be saved with # included)
                }

                $('.group').hide();
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