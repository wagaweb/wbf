module.exports = {
    init: function(){
        var upload_window,
            $ = jQuery;

        $('.remove-image, .remove-file').on('click', function() {
            remove_file( $(this).parents('.section') );
        });

        $('.upload-button').click( function( event ) {
            add_file(event, $(this).parents('.section'));
        });

        /**
         * Open a WP Media Modal
         * @param event the event fired by click
         * @param $field_container the option field container
         */
        function add_file(event, $field_container) {

            var upload = $(".uploaded-file"),
                frame,
                $el = $(this);

            event.preventDefault();

            // If the media frame already exists, reopen it.
            if ( upload_window ) {
                upload_window.open();
            } else {
                // Create the media frame.
                upload_window = new wp.media.view.MediaFrame.Select({
                    // Set the title of the modal.
                    title: $el.data('choose'),

                    library: {
                        type: 'image'
                    },

                    // Customize the submit button.
                    button: {
                        // Set the text of the button.
                        text: $el.data('update'),
                        // Tell the button not to close the modal, since we're
                        // going to refresh the page when the image is selected.
                        close: false
                    }
                });

                // When an image is selected, run a callback.
                upload_window.on( 'select', function() {
                    var attachment = upload_window.state().get('selection').first(); // Grab the selected attachment.
                    upload_window.close();

                    $field_container.find('.upload').val(attachment.attributes.url);
                    if ( attachment.attributes.type == 'image' ) {
                        $field_container.find('.screenshot').empty().hide().append('<img src="' + attachment.attributes.url + '"><a class="remove-image">Remove</a>').slideDown('fast');
                    }
                    $field_container.find('.upload-button').unbind().addClass('remove-file').removeClass('upload-button').val(wbfData.of_media_uploader.remove);
                    $field_container.find('.of-background-properties').slideDown();
                    $field_container.find('.remove-image, .remove-file').on('click', function() {
                        remove_file( $(this).parents('.section') );
                    });
                });

                // Finally, open the modal.
                upload_window.open();
            }
        }

        /**
         * Remove a file
         * @param $field_container the option field container
         */
        function remove_file($field_container) {
            $field_container.find('.remove-image').hide();
            $field_container.find('.upload').val('');
            $field_container.find('.of-background-properties').hide();
            $field_container.find('.screenshot').slideUp();
            $field_container.find('.remove-file').unbind().addClass('upload-button').removeClass('remove-file').val(wbfData.of_media_uploader.upload);
            // We don't display the upload button if .upload-notice is present
            // This means the user doesn't have the WordPress 3.5 Media Library Support
            if ( $('.section-upload .upload-notice').length > 0 ) {
                $('.upload-button').remove();
            }
            $field_container.find('.upload-button').on('click', function(event) {
                add_file(event, $(this).parents('.section'));
            });
        }
    }
};