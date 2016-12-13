import * as _ from "underscore";
import $ from "jquery";

import * as custom_acf_fields from "./acf-fields/custom_fields";

import OptionsPageView from "./components/options-page";
import {MediaUploaderView} from "./components/options-fields/media-uploader";
import CodeEditorView from "./components/options-fields/code-editor";
import {MultipleFontSelectorView} from "./components/options-fields/multiple-font-selector"
import BehaviorMetaboxesView from "./components/behavior"

jQuery(document).ready(function($) {
    //Init ACF Custom Fields
    _.each(custom_acf_fields,function(element,index,list){
        if(!_.isUndefined(element.init_interface)){
            element.init_interface();
        }
    });

    //Init options fields components
    if(wbfData.wp_screen.base.match(/wbf_options/) || wbfData.wp_screen.base.match(/wbf_components/)){
        //Init Code Editor
        CodeEditorView.init();

        //Init Multiple Font Selector
        let getFonts = function(){
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
        };
        getFonts().then(function(fontsData){
            MultipleFontSelectorView.init(fontsData);
        });

        //Init Media Uploader
        MediaUploaderView.init();

        //Finally, init Options page
        OptionsPageView.init();
    }

    //Init behavior view
    BehaviorMetaboxesView.init();
});
