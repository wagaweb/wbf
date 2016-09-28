module.exports = Backbone.View.extend({
    template: _.template(jQuery('#font-assign-inner-tpl').html()),
    initialize: function(options) {
        var fontSubsets = [];
        jQuery.each( options.alreadySelectedFonts, function( i, obj ) {
            fontSubsets.push(obj.family);
        });

        // set fonts saved in theme options to the list of available fonts
        this.model.set("fontSubset", fontSubsets);
        if (options.fontWeights != '') {this.model.set('fontWeights', options.fontWeights);};
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
    },
    render: function(rebind_actions){
        var self = this,
            $ = jQuery,
            wrapper = $(this.$el).closest('#section-fonts'),
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
    },
    bind_actions: function(wrapper){
        var $ = jQuery,
            self = this,
            container = this.model.get("container");
        wrapper.on("change", function(e){
            if($(e.target).hasClass('all-fonts-select')){
                // call the updater with reference to the wrapper
                self.model.updateFontsList(wrapper);
            }
        });
        wrapper.on("click", function (e) {
            if ($(e.target).hasClass('remove-font-button')) {
                var family = $(e.target).closest('.font-select-wrapper').find('.all-fonts-select').val();

                // call the updater with reference to the wrapper
                self.model.removeFontFromList(wrapper, family);

                // if is the last select remove also the assigner
                if (wrapper.find(".all-fonts-select").length == 0) {
                    var fontassign = wrapper.find("[data-font-assign]");
                    fontassign.closest('.section').remove();
                }
            } else if ( $(e.target).hasClass('font-weight-checkbox')){
                self.model.updateFontWeights(e.target);
            }
        });
        this.$el.find('['+container+']').on("change",function(event){
            if($(event.target).hasClass('font-assigned-list')){
                var newFont = jQuery(this).find('[data-font-assigned-list]').val();
                self.model.changeFont(newFont, wrapper);
            }
        });
    }
});