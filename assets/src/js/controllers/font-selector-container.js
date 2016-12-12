module.exports = {

    appendSelect: function (selector, template) {
        selector
            .closest('[data-section]')
            .find('[data-font-selector]')
            .append(template);
    },
    appendContainer: function (selector, template) {
        selector
            .closest('[data-section]')
            .append(template);
    },
    appendFontAssigner: function (selector, template) {
        selector
            .closest('[data-section]')
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
