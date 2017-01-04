import $ from "jquery";
import * as Backbone from "backbone";
import * as _ from "underscore";

class MultipleFontSelectorView
{
    static init(fontsData){
        let counter = 1,
            cssSelectors,
            objFonts = {},
            importStoredValues = $( "input[name='wbf-options-fonts-import-stored-values']" ),
            assignStoredValues = $( "input[name='wbf-options-fonts-assign-stored-values']" ),
            selectorInput = $( "input[name='wbf-options-fonts-css-selectors']"),
            button = $("#multiple-font-selector"),
            optionsName = button.attr('data-option-name'),
            self = this;

        // check if we have css selectors
        if (selectorInput != undefined) {
            cssSelectors = selectorInput.attr('data-css-selectors');
            if(typeof cssSelectors !== "undefined" && cssSelectors.length > 0){
                cssSelectors = cssSelectors.replace(/\s+/g, '');
                cssSelectors = cssSelectors.replace(/,/g, '-');
                cssSelectors = cssSelectors.split("|");
            }
        }

        // check if we have values from the database
        if (importStoredValues != undefined && importStoredValues.attr('data-stored-values') != null) {

            let jsonFonts = importStoredValues.attr('data-stored-values');

            objFonts = JSON.parse(jsonFonts);

            $.each( objFonts, function( i, obj ) {
                let $new_sel = $("<div data-font-select-"+counter+"></div>");
                self.appendSelect(button, $new_sel);
                let fsv = new FontSelectorView({
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
                let $new_ass_div = $("<div class='section'><div data-font-assign></div></div>");
                this.appendFontAssigner(button, $new_ass_div);

                // now add a new select view for EACH font assigner
                $.each(cssSelectors, function (i, selector) {

                    let newAssignContainer = "data-font-"+selector;
                    $('[data-font-assign]').append("<div "+newAssignContainer+"></div>");

                    // check if we have values from the database
                    if (assignStoredValues != undefined && assignStoredValues.attr('data-stored-values') != null) {
                        let jsonAssignedFonts = assignStoredValues.attr('data-stored-values'),
                            objAssignedFonts = JSON.parse(jsonAssignedFonts);

                        // retrieve user selected weight
                        let wrapper = button.closest('[data-section]'),
                            fontWeights = {},
                            sel = wrapper.find("[data-fontlist]"); // these are the fonts <select>

                        // take the values of each select and push it to the final array
                        for (let y = 0; y < sel.length; y++) {
                            let family = jQuery(sel[y]).val(),
                                weights = jQuery(sel[y]).closest('.font-select-wrapper').find('.font-weight-checkbox'), // these are the checkboxes
                                newWeights = []; // init the array for the weights of the selected font

                            for (let j = 0; j < weights.length; j++) {
                                if (weights[j].checked) {
                                    let val = jQuery(weights[j]).val();
                                    newWeights.push(val);
                                }
                            }
                            // add the array of selected weights to the object
                            fontWeights[family] = newWeights;
                        }
                        let fav = new FontAssignerView({
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
                    } else {

                        // if no info from db is found just init some empty assigner
                        let fav = new FontAssignerView({
                            optionName: optionsName,
                            cssSelector: selector,
                            alreadySelectedFonts: objFonts,
                            container: newAssignContainer,
                            fontWeights: '',
                            selectedFont: '',
                            selectedWeight: '',
                            el: $new_ass_div,
                            model: new FontAssignerModel(fontsData)
                        });
                    }
                })
            }
        }

        // click on selector button
        button.on('click', function(e) {
            e.preventDefault();

            if (button.closest('[data-section]').find(".all-fonts-select").length == 0) {
                counter = 1;
            }
            // if is the first time we click append all the div we need
            if (counter == 1) {
                let $new_el = $("<div class='font-assign-wrapper'><div data-font-assign></div></div>");
                self.appendContainer($(this), $new_el);

                $.each(cssSelectors, function (i, selector) {
                    let newAssignContainer = "data-font-"+selector;
                    $('[data-font-assign]').append("<div "+newAssignContainer+"></div>");

                    let fav = new FontAssignerView({
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

            let $new_select = $("<div data-font-select-"+counter+"></div>");
            self.appendSelect($(this), $new_select);
            let fsv = new FontSelectorView({
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
    static appendSelect(selector, template) {
        selector
            .closest('[data-section]')
            .find('[data-font-selector]')
            .append(template);
    }
    static appendContainer(selector, template) {
        selector
            .closest('[data-section]')
            .append(template);
    }
    static appendFontAssigner(selector, template) {
        selector
            .closest('[data-section]')
            .append(template);
    }
    static appendAssignContainer(selector, template) {
        selector.append(template);
    }
    static loadWebFonts(families){
        if(!_.isEmpty(families)){
            WebFont.load({
                google: {
                    families: families
                }
            });
        }
    }    
}

/**
 * FontSelector View
 */
class FontSelectorView extends Backbone.View{
    initialize(options){
        this.template = _.template(jQuery('#font-select-tpl').html());

        //set letiables
        this.model.set("selected_font",options.selectedFont);
        this.model.set("optionName", options.optionName);
        this.model.set("counter", options.counter);
        this.model.set("selected_charset", options.selectedCharset);
        this.model.set("selected_weight", options.selectedWeight);

        // listener
        this.listenTo(this.model,"fontChanged",this.render);

        // render
        this.render();
    }
    render(){
        let self = this;

        // template
        this.$el.html(this.template({
            counter: this.model.get('counter'),
            fonts: this.model.get('fonts'),
            optionName: this.model.get('optionName'),
            selected_font: this.model.get("selected_font"),
            selected_charset: this.model.get("selected_charset"),
            selected_weight: this.model.get("selected_weight")
        }));

        // listen to change in select
        this.$el.find("[data-fontlist]").on("change",function(){
            let new_font = jQuery(this).val();
            self.model.changeFont(new_font);
        });

        //listen to remove font button
        this.$el.find('.remove-font-button').on('click', function(e){
            e.preventDefault();
            jQuery(this).closest('.font-select-wrapper').remove();
        })
    }
}

/**
 * FontSelector Model
 */
class FontSelectorModel extends Backbone.Model{
    defaults(){
        return {
            "fonts": "",
            "selected_font": "",
            "selected_charset": "latin",
            "selected_weight": "regular",
            "selected_fonts": []
        };
    }
    initialize(fontsData){
        this.set('fonts', fontsData);
    }
    changeFont(new_font){
        this.set("selected_font",new_font);
        this.set("selected_charset",'latin');
        //this.set("selected_weight",'regular');
        this.trigger("fontChanged");
    }
}

/**
 * FontAssigner View
 */
class FontAssignerView extends Backbone.View{
    initialize(options){
        this.template = _.template(jQuery('#font-assign-inner-tpl').html());
        let fontSubsets = [];
        jQuery.each( options.alreadySelectedFonts, function( i, obj ) {
            fontSubsets.push(obj.family);
        });

        // set fonts saved in theme options to the list of available fonts
        this.model.set("fontSubset", fontSubsets);
        if (options.fontWeights != '') {this.model.set('fontWeights', options.fontWeights);}
        this.model.set("optionName", options.optionName);
        this.model.set("selectedFont", options.selectedFont);
        this.model.set("selectedWeight", options.selectedWeight);
        this.model.set("cssSelector", options.cssSelector);
        this.model.set("container", options.container);


        // listen to font changes
        this.listenTo(this.model,"fontListUpdated", this.render);
        this.listenTo(this.model,"fontChanged", this.render);
        this.listenTo(this.model,"fontWeightsUpdated", this.render);

        // render
        this.render(true);
    }
    render(rebind_actions){
        let self = this,
            $ = jQuery,
            wrapper = $(this.$el).closest('[data-section]'),
            container = this.model.get("container");

        if(typeof rebind_actions == "undefined"){
            rebind_actions = false;
        }

        this.$el.find('['+container+']').html(this.template({
            cssSelector: this.model.get('cssSelector'),
            optionName: this.model.get('optionName'),
            fonts: this.model.get('fontSubset'),
            selectedFont: this.model.get('selectedFont'),
            fontWeights: this.model.get('fontWeights'),
            selectedWeight: this.model.get('selectedWeight')
        }));

        if(rebind_actions){
            this.bind_actions(wrapper);
        }
    }
    bind_actions(wrapper){
        let self = this,
            container = this.model.get("container");
        wrapper.on("change", function(e){
            if($(e.target).hasClass('all-fonts-select')){
                // call the updater with reference to the wrapper
                self.model.updateFontsList(wrapper);
            }
        });
        wrapper.on("click", function (e) {
            if ($(e.target).hasClass('remove-font-button')) {
                let family = $(e.target).closest('.font-select-wrapper').find('.all-fonts-select').val();

                // call the updater with reference to the wrapper
                self.model.removeFontFromList(wrapper, family);

                // if is the last select remove also the assigner
                if (wrapper.find(".all-fonts-select").length == 0) {
                    let fontassign = wrapper.find("[data-font-assign]");
                    fontassign.closest('.font-assign-wrapper').remove();
                }
            } else if ( $(e.target).hasClass('font-weight-checkbox')){
                self.model.updateFontWeights(e.target);
            }
        });
        this.$el.find('['+container+']').on("change",function(event){
            if($(event.target).hasClass('font-assigned-list')){
                let newFont = jQuery(this).find('[data-font-assigned-list]').val();
                self.model.changeFont(newFont, wrapper);
            }
        });
    }
}

/**
 * FontAssigner Model
 */
class FontAssignerModel extends Backbone.Model{
    initialize(fontsData, selectorModel){
        this.set('fonts', fontsData);
    }
    updateFontsList(wrapper){
        let newVal = [],
            fontWeights = this.get('fontWeights'),
            sel = wrapper.find("[data-fontlist]"); // theese are the fonts <select>

        // take the values of each select and push it to the final array
        for (let i = 0; i < sel.length; i++) {
            let family = jQuery(sel[i]).val(),
                weights = jQuery(sel[i]).closest('.font-select-wrapper').find('.font-weight-checkbox'), // theese are the checkboxes
                newWeights = []; // init the array for the weights of the selected font

            for (let j = 0; j < weights.length; j++) {
                if (weights[j].checked) {
                    newWeights.push(jQuery(weights[j]).val());
                }
            }
            // add the array of selected weights to the object
            fontWeights[family] = newWeights;
            // push the family to the array of new values
            newVal.push(family);
        }

        // set the letiables
        this.set('fontWeights', fontWeights);
        this.set('fontSubset', newVal);

        // trigger event
        this.trigger("fontListUpdated");
    }
    removeFontFromList(wrapper, removedFamily){
        let newVal = [],
            fontWeights = this.get('fontWeights'),
            sel = wrapper.find("[data-fontlist]"); // theese are the fonts <select>
        fontWeights = {};

        // take the values of each select and push it to the final array
        for (let i = 0; i < sel.length; i++) {
            let family = jQuery(sel[i]).val(),
                weights = jQuery(sel[i]).closest('.font-select-wrapper').find('.font-weight-checkbox'), // theese are the checkboxes
                newWeights = []; // init the array for the weights of the selected font

            for (let j = 0; j < weights.length; j++) {
                if (weights[j].checked) {
                    newWeights.push(jQuery(weights[j]).val());
                }
            }
            // add the array of selected weights to the object
            fontWeights[family] = newWeights;
            // push the family to the array of new values
            newVal.push(family);
        }

        // set the letiables
        this.set('fontWeights', fontWeights);
        this.set('fontSubset', newVal);

        // trigger event
        this.trigger("fontListUpdated");
    }
    /**
     * function to update the selected font in the "font assigner" select
     */
    changeFont(newFont, wrapper) {
        //Setting up new font:
        this.set('selectedFont', newFont);

        //Search for weights
        let newVal = [],
            fontWeights = {},
            sel = wrapper.find("[data-fontlist]"); // theese are the fonts <select>

        // take the values of each select and push it to the final array
        for (let i = 0; i < sel.length; i++) {
            let family = jQuery(sel[i]).val(),
                newWeights = [];

            if (family == newFont) {
                // theese are the checkboxes
                let weights = jQuery(sel[i]).closest('.font-select-wrapper').find('.font-weight-checkbox');
            }
            if(typeof weights !== "undefined") {
                for (let j = 0; j < weights.length; j++) {
                    if (weights[j].checked) {
                        newWeights.push(jQuery(weights[j]).val());
                    }
                }
                // add the array of selected weights to the object
                fontWeights[family] = newWeights;
            }
        }

        // set the letiables
        this.set('fontWeights', fontWeights);

        this.trigger("fontChanged");
    }
    updateFontWeights(checkbox) {
        let newWeights = [],
            fontSelectWrapper = checkbox.closest('.font-select-wrapper'),   // Selected font wrapper
            family = jQuery(fontSelectWrapper).find("[data-fontlist]").val(),       // Retrieve the family
            fontWeights = this.get('fontWeights'),
            weights = jQuery(fontSelectWrapper).find('.font-weight-checkbox');     // Checkboxes for the letiants
    
        for (let i = 0; i < weights.length; i++) {
            if (weights[i].checked) {
                newWeights.push(jQuery(weights[i]).val());
            }
        }
        fontWeights[family] = newWeights;
        this.set('fontWeights', fontWeights);
        this.trigger("fontWeightsUpdated");
    }
}

export { MultipleFontSelectorView };