module.exports = Backbone.Model.extend({
    defaults: {
        "fonts": "",
        "selected_font": "",
        "selected_charset": "latin",
        "selected_weight": "regular",
        "selected_fonts": []
    },
    initialize: function(fontsData){
        this.set('fonts', fontsData);
    },
    changeFont: function(new_font){
        this.set("selected_font",new_font);
        this.set("selected_charset",'latin');
        //this.set("selected_weight",'regular');
        this.trigger("fontChanged");
    }
});
