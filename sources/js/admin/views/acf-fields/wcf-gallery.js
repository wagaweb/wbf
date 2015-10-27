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
                    console.log(attachment);
                    var extNum = attachment.url.lastIndexOf('.');
                    var imgUrl = attachment.url.substring(0,extNum);
                    var imgExt = attachment.url.substring(extNum+1,attachment.url.length);
                    var newImgUrl = imgUrl + '-150x150.' + imgExt;
                    $("#upload-btn").after("<img src=" +newImgUrl+">");
                    imgIds.push(attachment.id);
                    console.log(imgIds);
                });
                var savedVal = $('#imgId').val();
                $('#imgId').val(savedVal + ',' + imgIds);
            });
            custom_uploader.open();
        });
        $('.containerImgGalleryAdmin').on('mouseover',function(){
            $(this).addClass('on');

            $('.containerImgGalleryAdmin.on .deleteImg').on('click', function(){
                var oldValues = $('#imgId').val();
                var arrayValues = oldValues.split(",").map(Number);
                var elemIndex = $(this).children().attr('data-index');
                var elemValie = $(this).children().attr('data-id');
                arrayValues.splice(elemIndex,1);
                console.log(arrayValues);
                $('.on .imgGalleryAdmin').remove();
                $('#imgId').val(arrayValues);
            });

        });
        $('.containerImgGalleryAdmin').on('mouseout',function(){
            $(this).removeClass('on');

        });

    }
};
