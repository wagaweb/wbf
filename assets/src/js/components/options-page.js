import $ from "jquery";

export default class{
    /**
     * Init theme options and components interface
     */
    static init(){
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
            this.init_theme_options_navigation($theme_options_wrapper,"wbf_theme_options_active_tab");
        }

        if( $components_tabs_wrapper.length > 0){
            this.init_components_navigation($components_wrapper,"wbf_components_active_tab");
        }
    }

    /**
     * Init component page navigation
     *
     * @param $wrapper
     * @param localStorage_var_name
     */
    static init_components_navigation($wrapper,localStorage_var_name){
        let $tabs_links = $wrapper.find('[data-nav] li');
        let $tabs_first_link = $wrapper.find('[data-nav] li:first');
        let $components_list = $wrapper.find('[data-components-list]');
        let $main_tab_link = $wrapper.find("[data-show-comp-settings='component-main']");

        // Hides all the groups sections to start
        //$('[data-fieldgroup]').hide();

        // Find if a selected tab is saved in localStorage
        let active_tab = this.get_active_tab(localStorage_var_name);

        // If active tab is saved and exists, load it's .group
        if (active_tab != '' && $('[data-fieldgroup='+active_tab+']').length > 0) {
            $tabs_links.filter('[data-show-comp-settings='+active_tab+']').addClass('active');
            $components_list.hide();
            $('[data-fieldgroup='+active_tab+']').fadeIn();
        }else{
            $components_list.show();
            $main_tab_link.addClass('active');
        }

        let self = this;
        $tabs_links.click(function(evt) {
            evt.preventDefault();

            // Remove active class from all tabs
            $tabs_links.removeClass('active');

            $(this).addClass('active').blur();

            let component = $(this).data('show-comp-settings');

            if (typeof(localStorage) != 'undefined' ) {
                localStorage.setItem(localStorage_var_name, component ); //Store the active tab on click (this will be saved with # included)
            }

            $('[data-fieldgroup]').hide();
            if($(this).is($main_tab_link)){
                $components_list.show();
            }else{
                $components_list.hide();
                $('[data-fieldgroup='+component+']').fadeIn();
            }
            self.reinit_wp_editor();
        });
    }

    /**
     * Init theme options page navigation
     *
     * @param $wrapper
     * @param localStorage_var_name
     */
    static init_theme_options_navigation($wrapper,localStorage_var_name){
        let $tabs_links = $wrapper.find('[data-nav] li');
        let $tabs_first_link = $wrapper.find('[data-nav] li:first');

        // Hides all the .group sections to start
        $('section[data-category]').hide();

        // Find if a selected tab is saved in localStorage
        let active_tab = this.get_active_tab(localStorage_var_name);

        // If active tab is saved and exists, load it's .group
        if (active_tab != '' && $('section[data-category='+active_tab+']').length > 0) {
            $('section[data-category='+active_tab+']').fadeIn();
            $tabs_links.filter('[data-category='+active_tab+']').addClass('active');
        } else {
            $('section[data-category]:first').fadeIn();
            $tabs_first_link.addClass('active');
        }

        // Bind tabs clicks
        let self = this;
        $tabs_links.click(function(evt) {
            evt.preventDefault();

            // Remove active class from all tabs
            $tabs_links.removeClass('active');

            $(this).addClass('active').blur();

            let category = $(this).data('category');

            if (typeof(localStorage) != 'undefined' ) {
                localStorage.setItem(localStorage_var_name, category ); //Store the active tab on click (this will be saved with # included)
            }

            $('section[data-category]').hide();
            $('section[data-category='+category+']').fadeIn();
            self.reinit_wp_editor();
        });
    }

    /**
     * Get the active tab from local storage
     *
     * @param localStorage_var_name
     * @returns {string}
     */
    static get_active_tab(localStorage_var_name){
        // Find if a selected tab is saved in localStorage
        let active_tab = '';
        if ( typeof(localStorage) != 'undefined' ) {
            active_tab = localStorage.getItem(localStorage_var_name); //Check for active tab
            if(active_tab != null && active_tab.match(/http/)){ //Hardcoded fix for some incompatibilities
                active_tab = '';
            }
        }
        return active_tab;
    }

    /**
     * Make some adjustment to any wp-editor
     */
    static reinit_wp_editor(){
        // Editor height sometimes needs adjustment when unhidden
        $('.wp-editor-wrap').each(function() {
            let editor_iframe = $(this).find('iframe');
            if ( editor_iframe.height() < 30 ) {
                editor_iframe.css({'height':'auto'});
            }
        });
    }
}