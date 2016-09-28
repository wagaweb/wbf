module.exports = Backbone.Model.extend({
    defaults: {
        "fonts": "",
        "fontSubset": [], // the subset of fonts selected in the font selector above
        "selectedFont": "", // the font already selected to be assigned
        "selectedWeight": "", // the weight already selected to be assigned
        "fontWeights": {}
    },
    initialize: function(fontsData, selectorModel){
        this.set('fonts', fontsData);
    },
    updateFontsList: function(wrapper){
        var newVal = [],
            fontWeights = this.get('fontWeights'),
            sel = wrapper.find("[data-fontlist]"); // theese are the fonts <select>

        // take the values of each select and push it to the final array
        for (var i = 0; i < sel.length; i++) {
            var family = jQuery(sel[i]).val(),
                weights = jQuery(sel[i]).closest('.font-select-wrapper').find('.font-weight-checkbox'), // theese are the checkboxes
                newWeights = []; // init the array for the weights of the selected font

            for (var j = 0; j < weights.length; j++) {
                if (weights[j].checked) {
                    newWeights.push(jQuery(weights[j]).val());
                }
            }
            // add the array of selected weights to the object
            fontWeights[family] = newWeights;
            // push the family to the array of new values
            newVal.push(family);
        }

        // set the variables
        this.set('fontWeights', fontWeights);
        this.set('fontSubset', newVal);

        // trigger event
        this.trigger("fontListUpdated");
    },
    removeFontFromList: function(wrapper, removedFamily){
        var newVal = [],
            fontWeights = this.get('fontWeights'),
            sel = wrapper.find("[data-fontlist]"); // theese are the fonts <select>
        fontWeights = {};

        // take the values of each select and push it to the final array
        for (var i = 0; i < sel.length; i++) {
            var family = jQuery(sel[i]).val(),
                weights = jQuery(sel[i]).closest('.font-select-wrapper').find('.font-weight-checkbox'), // theese are the checkboxes
                newWeights = []; // init the array for the weights of the selected font

            for (var j = 0; j < weights.length; j++) {
                if (weights[j].checked) {
                    newWeights.push(jQuery(weights[j]).val());
                }
            }
            // add the array of selected weights to the object
            fontWeights[family] = newWeights;
            // push the family to the array of new values
            newVal.push(family);
        }

        // set the variables
        this.set('fontWeights', fontWeights);
        this.set('fontSubset', newVal);

        // trigger event
        this.trigger("fontListUpdated");
    },
    // function to update the selected font in the "font assigner" select
    changeFont: function (newFont, wrapper) {
        //Setting up new font:
        this.set('selectedFont', newFont);

        //Search for weights
        var newVal = [],
            fontWeights = {},
            sel = wrapper.find("[data-fontlist]"); // theese are the fonts <select>

        // take the values of each select and push it to the final array
        for (var i = 0; i < sel.length; i++) {
            var family = jQuery(sel[i]).val(),
                newWeights = [];

            if (family == newFont) {
                // theese are the checkboxes
                var weights = jQuery(sel[i]).closest('.font-select-wrapper').find('.font-weight-checkbox');
            }
            if(typeof weights !== "undefined") {
                for (var j = 0; j < weights.length; j++) {
                    if (weights[j].checked) {
                        newWeights.push(jQuery(weights[j]).val());
                    }
                }
                // add the array of selected weights to the object
                fontWeights[family] = newWeights;
            }
        }

        // set the variables
        this.set('fontWeights', fontWeights);

        this.trigger("fontChanged");

    },
    updateFontWeights: function (checkbox) {
        var newWeights = [],
            fontSelectWrapper = checkbox.closest('.font-select-wrapper'),   // Selected font wrapper
            family = jQuery(fontSelectWrapper).find("[data-fontlist]").val(),       // Retrieve the family
            fontWeights = this.get('fontWeights'),
            weights = jQuery(fontSelectWrapper).find('.font-weight-checkbox');     // Checkboxes for the variants

        for (var i = 0; i < weights.length; i++) {
            if (weights[i].checked) {
                newWeights.push(jQuery(weights[i]).val());
            }
        }
        fontWeights[family] = newWeights;
        this.set('fontWeights', fontWeights);
        this.trigger("fontWeightsUpdated");
    }
});
