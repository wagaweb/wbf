module.exports = {
    init_interface: function(){
        var $ = jQuery;
        var custom_uploader;
        $('#upload-btn').click(function(e) {
            e.preventDefault();
            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: true
            });
            custom_uploader.on('select', function() {
                var selection = custom_uploader.state().get('selection');
                var imgIds = [];
                selection.map( function( attachment ) {
                    attachment = attachment.toJSON();
                    imgIds.push(attachment.id);
                    console.log(imgIds);
                    $.ajax(wbfData.ajaxurl,{ //ajax url is not available in the front end. Needs to wp_localize_script
                        data: {
                            action: "wcf_get_thumbnail", //the action specified in ajax wordpress hooks
                            id: attachment.id
                        },
                        dataType: "json", //Default is an "intelligent guess"; does not work very often
                        method: "POST" //Default is GET
                    }).done(function(data, textStatus, jqXHR){
                        console.log(data);
                        var newData = JSON.stringify(data);
                        var imgUrl = JSON.parse(newData);
                        var newImgUrl = imgUrl.thumb;
                        var newDataIndex = $('.containerImgGalleryAdmin').length;
                        $("#prova").append("<div class='containerImgGalleryAdmin'>" +
                        "<img class='imgGalleryAdmin' src=" +newImgUrl+" data-id='"+ attachment.id +"'>" +
                        "<div class='deleteImg'>"+
                        "<a class='acf-icon dark remove-attachment ' data-index='"+ newDataIndex  +"' href='#' data-id='"+ attachment.id +"'>"+
                        "<i class='acf-sprite-delete'></i>"+
                        "</a>"+
                        "</div></div>");
                        $("#prova").sortable('refresh');
                    }).fail(function(jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }).always(function(result, textStatus, type){
                        console.log(type);
                    });
                    //FIN QUI

                });
                var savedVal = $('#imgId').val();
                if(savedVal==' '){
                    $('#imgId').val(imgIds);
                }else{
                    $('#imgId').val(savedVal + ',' + imgIds);
                }

            });
            custom_uploader.open();
        });
        $('.containerImgGalleryAdmin').on('mouseover',function(){
            $(this).addClass('on');
        });
        $('.deleteImg').on('click', function(e){
            console.log("click");
            e.preventDefault();
            var oldValues = $('#imgId').val();
            var arrayValues = oldValues.split(",").map(Number);
            var elemIndex = $(this).children().attr('data-index');
            var imgIds = [];
            console.log(arrayValues, elemIndex);
            $.each(arrayValues, function (index, value) {
                console.log(index, elemIndex);
                if (index != elemIndex) {
                    imgIds.push($(value)[0]);
                }
            });
            $('.containerImgGalleryAdmin.on').remove();
            $('#imgId').val(imgIds);
            $.each($('.deleteImg'), function (index, value) {
                $(this).children().attr('data-index', index);
            });
        });

        $('.containerImgGalleryAdmin').on('mouseout',function(){
            $(this).removeClass('on');

        });

        $('#prova').sortable({
            stop: function(event, ui) {
                var newImgArray = $('.imgGalleryAdmin');
                $('#imgId').val('');
                var imgIds = [];
                $.each(newImgArray, function(index, value){
                    imgIds.push($(value).attr('data-id'));
                });
                $('#imgId').val(imgIds);
            }
        });
        $('#prova').disableSelection();


    }
};
