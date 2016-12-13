import * as Backbone from "backbone";
import * as _ from "underscore";
import $ from "jquery";

export default class{
    init(){
        "use strict";
        let $ = jQuery;

        $(".nav-tab-wrapper a").on("click",function(){
            let $selected_component_div = $('#'+$(this).attr("data-show-comp-settings"));
            $("#componentframework-metabox .group").hide();
            $selected_component_div.show();
        });
    }
}