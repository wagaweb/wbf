module.exports = {

    init_interface: function(my_controller, fontsData) {
        "use strict";
        var $ = jQuery,
            counter = 1,
            controller = my_controller,
            importStoredValues = $( "input[name='wbf-options-fonts-import-stored-values']" ),
            assignStoredValues = $( "input[name='wbf-options-fonts-assign-stored-values']" ),
            selectorInput = $( "input[name='wbf-options-fonts-css-selectors']"),
            button = $("#multiple-font-selector"),
            optionsName = button.attr('data-option-name'),
            FontSelectorView = require("../views/font-selector.js"),
            FontSelectorModel = require("../controllers/font-selector.js"),
            FontAssignerView = require("../views/font-assigner.js"),
            FontAssignerModel = require("../controllers/font-assigner.js");

        // check if we have css selectors
        if (selectorInput != undefined) {
            var cssSelectors = selectorInput.attr('data-css-selectors');
            cssSelectors = cssSelectors.replace(/\s+/g, '');
            cssSelectors = cssSelectors.replace(/,/g, '-');
            cssSelectors = cssSelectors.split("|");
        }

        // check if we have values from the database
        if (importStoredValues != undefined && importStoredValues.attr('data-stored-values') != null) {

            var jsonFonts = importStoredValues.attr('data-stored-values'),
                objFonts = JSON.parse(jsonFonts);

            $.each( objFonts, function( i, obj ) {
                var $new_sel = $("<div data-font-select-"+counter+"></div>");
                controller.appendSelect(button, $new_sel);
                var fsv = new FontSelectorView({
                    el: $new_sel,
                    optionName: optionsName,
                    counter: counter,
                    selectedFont: obj.family,
                    selectedCharset: obj.subset,
                    selectedWeight: obj.weight,
                    model: new FontSelectorModel(fontsData)
                });
                counter++;
            });

            // add the "font assign" section
            if (counter > 1) {

                // append a new section with the div we need
                var $new_ass_div = $("<div class='section'><div data-font-assign></div></div>");
                controller.appendFontAssigner(button, $new_ass_div);

                // now add a new select view for EACH font assigner
                $.each(cssSelectors, function (i, selector) {

                    var newAssignContainer = "data-font-"+selector;
                    $('[data-font-assign]').append("<div "+newAssignContainer+"></div>");

                    // check if we have values from the database
                    if (assignStoredValues != undefined) {
                        var jsonAssignedFonts = assignStoredValues.attr('data-stored-values'),
                            objAssignedFonts = JSON.parse(jsonAssignedFonts);

                        // retrieve user selected weight
                        var wrapper = button.closest('#section-fonts'),
                            fontWeights = {},
                            sel = wrapper.find("[data-fontlist]"); // these are the fonts <select>

                        // take the values of each select and push it to the final array
                        for (var y = 0; y < sel.length; y++) {
                            var family = jQuery(sel[y]).val(),
                                weights = jQuery(sel[y]).closest('.font-select-wrapper').find('.font-weight-checkbox'), // these are the checkboxes
                                newWeights = []; // init the array for the weights of the selected font

                            for (var j = 0; j < weights.length; j++) {
                                if (weights[j].checked) {
                                    var val = jQuery(weights[j]).val();
                                    newWeights.push(val);
                                }
                            }
                            // add the array of selected weights to the object
                            fontWeights[family] = newWeights;
                        }
                    }

                    var fav = new FontAssignerView({
                        optionName: optionsName,
                        cssSelector: selector,
                        alreadySelectedFonts: objFonts,
                        container: newAssignContainer,
                        fontWeights: fontWeights,
                        selectedFont: objAssignedFonts[selector]['family'], // the font already selected to be assigned
                        selectedWeight: objAssignedFonts[selector]['weight'], // the weight already selected to be assigned
                        el: $new_ass_div,
                        model: new FontAssignerModel(fontsData)
                    });
                })
            }
        }

        button.on('click', function(e) {
            e.preventDefault();

            if (button.closest('#section-fonts').find(".all-fonts-select").length == 0) {
                counter = 1;
            }
            // if is the first time we click append all the div we need
            if (counter == 1) {
                var $new_el = $("<div class='section'><div data-font-assign></div></div>");
                controller.appendContainer($(this), $new_el);

                $.each(cssSelectors, function (i, selector) {
                    var newAssignContainer = "data-font-"+selector;
                    $('[data-font-assign]').append("<div "+newAssignContainer+"></div>");

                    var fav = new FontAssignerView({
                        optionName: optionsName,
                        cssSelector: selector,
                        alreadySelectedFonts: objFonts,
                        container: newAssignContainer,
                        fontWeights: '',
                        selectedFont: '', // the font already selected to be assigned
                        selectedWeight: '', // the weight already selected to be assigned
                        el: $new_el,
                        model: new FontAssignerModel(fontsData)
                    });
                })
            }

            var $new_select = $("<div data-font-select-"+counter+"></div>");
            controller.appendSelect($(this), $new_select);
            var fsv = new FontSelectorView({
                el: $new_select,
                optionName: optionsName,
                counter: counter,
                selectedFont: '',
                selectedCharset: 'latin',
                selectedWeight: 'regular',
                model: new FontSelectorModel(fontsData)
            });
            counter++;
        });
    }
};
