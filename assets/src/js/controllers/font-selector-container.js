module.exports = {

    appendSelect: function (selector, template) {
        selector
            .closest('#section-fonts')
            .find('[data-font-selector]')
            .append(template);
    },
    appendContainer: function (selector, template) {
        selector
            .closest('#section-fonts')
            .append(template);
    },
    appendFontAssigner: function (selector, template) {
        selector
            .closest('#section-fonts')
            .append(template);
    },
    appendAssignContainer: function (selector, template) {
        selector.append(template);
    },
    loadWebFonts: function(families){
        if(!_.isEmpty(families)){
            WebFont.load({
                google: {
                    families: families
                }
            });
        }
    }
};
