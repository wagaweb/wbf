module.exports = {
    init_interface: function(){
        "use strict";
        var $ = jQuery;

        var editors = [];
        var targets = $("textarea.codemirror[data-lang]");
        var isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;

        //Initialize all editors.
        //The Timeout is necessary due to the lag between window load and the time needed for theme options script to arrange/show/hide the tabs.
        if(typeof CodeMirror !== "undefined"){
            setTimeout(function () {
                targets.each(function (index) {
                    var my_option_group = $(this).closest(".group");
                    var my_options_group_link = $("a#" + my_option_group.attr("id") + "-tab");
                    var my_mode = $(this).attr("data-lang");

                    var editor = CodeMirror.fromTextArea($(this)[0],{
                        mode: {name: my_mode, globalVars: true},
                        lineNumbers: true,
                        theme: "ambiance",
                        extraKeys: (function () {
                            if (isMac) {
                                return {"Ctrl-F": "autocomplete"};
                            } else {
                                return {"Ctrl-Space": "autocomplete"};
                            }
                        })()
                    });

                    editors.push(editor);

                    my_options_group_link.on("click", function () {
                        setTimeout(function () {
                            editor.refresh();
                        }, 1000);
                    });
                });
            }, 1500);
        }
    }
};