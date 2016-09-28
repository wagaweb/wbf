module.exports = Backbone.View.extend({
    template: _.template(jQuery('#font-select-tpl').html()),
    initialize: function(options){

        //set variables
        this.model.set("selected_font",options.selectedFont);
        this.model.set("optionName", options.optionName);
        this.model.set("counter", options.counter);
        this.model.set("selected_charset", options.selectedCharset);
        this.model.set("selected_weight", options.selectedWeight);

        // listener
        this.listenTo(this.model,"fontChanged",this.render);

        // render
        this.render();
    },
    render: function(){
        var self = this;

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
            var new_font = jQuery(this).val();
            self.model.changeFont(new_font);
        });

        //listen to remove font button
        this.$el.find('.remove-font-button').on('click', function(e){
            e.preventDefault();
            jQuery(this).closest('.font-select-wrapper').remove();
        })
    }
});