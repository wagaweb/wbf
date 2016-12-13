class SingleFontSelectorView{
    static init(){
        $(".font-family-selector").on("change",function(){
            let $familySeletor = $(this),
                $styleSelector = $(this).siblings(".font-style-selector"),
                styleOptName = $styleSelector.find('input:first').attr("name"),
                $charsetSelector = $(this).siblings(".font-charset-selector"),
                charsetOptName = $charsetSelector.find('input:first').attr("name"),
                $categoryInput = $(this).siblings(".font-category-selector"),
                $fontPreview = $(this).siblings(".font-preview");

            let request = $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "gfontfetcher_getFontInfo",
                    family: $(this).val()
                },
                dataType: "json",
                beforeSend: function(){
                    $familySeletor.attr("disabled","disabled");
                    $styleSelector.addClass("disabled");
                    $charsetSelector.addClass("disabled");
                }
            });
            request.done(function(data, textStatus, jqXHR){
                console.log(data);
                //Load GFonts and set the preview
                if(data.kind == "webfonts#webfont"){
                    SingleFontSelectorController.loadWebFonts([$familySeletor.val()]);
                }
                $fontPreview.find("p").css("font-family","'"+data.family+"',"+data.category);
                //Assign new styles to the html select
                $styleSelector.html((function(){
                    var output = "";
                    $.each(data.variants,function(){
                        output += "<input name='"+styleOptName+"' type='checkbox' value='"+this+"' />"+this;
                    });
                    return output;
                })());
                //Assign new charset to the html select
                $charsetSelector.html((function(){
                    var output = "";
                    $.each(data.subsets,function(){
                        output += "<input name='"+charsetOptName+"' type='checkbox' value='"+this+"' />"+this;
                    });
                    return output;
                })());
                //Assign new category to the html input
                $categoryInput.val(data.category);
            });
            request.fail(function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown);
            });
            request.always(function(result, textStatus, returned){
                $familySeletor.removeAttr("disabled");
                $styleSelector.removeClass("disabled");
                $charsetSelector.removeClass("disabled");
            });
        });
    }
}

class SingleFontSelectorController{
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

export { SingleFontSelectorView };
