import * as Backbone from "backbone";
import * as _ from "underscore";
import $ from "jquery";

import * as custom_acf_fields from "./acf-fields/custom_fields";

import ComponentsPageView from "./components/component-page";
import OptionsPageView from "./components/options-page";
import {MediaUploaderView} from "./components/options-fields/media-uploader";
import CodeEditorView from "./components/options-fields/code-editor";
import BehaviorMetaboxesView from "./components/behavior"

jQuery(document).ready(function($) {
    //Init ACF Custom Fields
    _.each(custom_acf_fields,function(element,index,list){
        if(!_.isUndefined(element.init_interface)){
            element.init_interface();
        }
    });

    //Init options page
    if(wbfData.wp_screen.base.match(/wbf_options/)){
        OptionsPageView.init();
    }

    //Init component page
    if(wbfData.wp_screen.base.match(/wbf_components/)){
        ComponentsPageView.init();
    }

    //Init options fields components
    if(wbfData.wp_screen.base.match(/wbf_options/) || wbfData.wp_screen.base.match(/wbf_components/)){
        //Init Code Editor
        CodeEditorView.init();
        //Init Multiple Font Selector
        getFonts().then(function(fontsData){
            let multi_font_selector_controller = require("./controllers/font-selector-container.js"),
                multi_font_selector_view = require("./views/font-selector-container.js");
            multi_font_selector_view.init_interface(multi_font_selector_controller, fontsData);
        });
        //Init Media Uploader
        MediaUploaderView.init();
    }

    //Init behavior view
    BehaviorMetaboxesView.init();

    function getFonts(){
        // ajax call for wordpress
        return $.ajax(wbfData.ajaxurl,{
            data: { action: 'getFontsForAjax' },
            dataType: 'json',
            method: 'POST'
        })
            .done(function(data, textStatus, jqXHR){
                console.log('fonts retrieved');
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                alert(errorThrown);
            })
            .always(function(result, textStatus, type){
                return result;
            });
    }
});
