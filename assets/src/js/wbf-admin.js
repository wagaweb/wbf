jQuery(document).ready(function($) {
    //Init ACF Custom Fields
    var acf_fields_views = require("./views/acf-fields.js");
    _.each(acf_fields_views,function(element,index,list){
        if(!_.isUndefined(element.init_interface)){
            element.init_interface();
        }
    });

    //Init component page
    if(wbfData.wp_screen.base.match(/wbf_components/)){
        var component_page_view = require("./views/component-page.js");
        component_page_view.init_interface();
    }

    if(wbfData.wp_screen.base.match(/wbf_options/) || wbfData.wp_screen.base.match(/wbf_components/)){
        //Init code editor view
        var code_editor_view = require("./views/code-editor.js");
        code_editor_view.init_interface();
        //Init font selector
        if(!_.isUndefined(wbfData.wbfOfFonts)){
            var font_selector_controller = require("./controllers/font-selector.js"),
                font_selector_view = require("./views/font-selector.js");
            font_selector_controller.loadWebFonts(wbfData.wbfOfFonts.families);
            font_selector_view.init_interface(font_selector_controller);
        }
        //Init media uploader
        var media_uploader = require("./controllers/media-uploader");
        media_uploader.init();
        //Init options custom
        var options_custom = require("./controllers/options-custom");
        options_custom.init();
    }

    //Init behavior view
    var behavior_view = require("./views/behavior.js");
    behavior_view.init_interface();
});
