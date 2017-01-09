import $ from "jquery";

class MediaUploaderView{
    static init(){
        let Uploader = new MediaUploaderController();
        $('.upload-button').click( function( event ) {
            Uploader.add_file(event, $(this).parents('[data-section]'));
        });
        $('.remove-image, .remove-file').on('click', function() {
            Uploader.remove_file( $(this).parents('[data-section]') );
        });
    }
}

class MediaUploaderController{
    constructor(){
        this.upload_window = undefined;
    }
    add_file(event, $field_container){
        let upload = $(".uploaded-file"),
            frame,
            $el = $(this);

        this.$field_container = $field_container;

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( this.upload_window ) {
            this.upload_window.open();
        } else {
            let self = this;

            // Create the media frame.
            this.upload_window = new wp.media.view.MediaFrame.Select({
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
            this.upload_window.on( 'select', function(event) {
                let attachment = self.upload_window.state().get('selection').first(); // Grab the selected attachment.
                self.upload_window.close();

                self.$field_container.find('.upload').val(attachment.attributes.url);
                if ( attachment.attributes.type == 'image' ) {
                    self.$field_container.find('.screenshot').empty().hide().append('<img src="' + attachment.attributes.url + '"><a class="remove-image">Remove</a>').slideDown('fast');
                }
                self.$field_container.find('.upload-button').unbind().addClass('remove-file').removeClass('upload-button').val(wbfData.of_media_uploader.remove);
                self.$field_container.find('.of-background-properties').slideDown();
                self.$field_container.find('.remove-image, .remove-file').on('click', function() {
                    self.remove_file( $(this).parents('[data-section]') );
                });
            });

            // Finally, open the modal.
            this.upload_window.open();
        }
    }
    remove_file($field_container){
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
        //Rebind action
        let self = this;
        $field_container.find('.upload-button').on('click', function(event) {
            self.add_file(event, $(this).parents('[data-section]'));
        });
    }
}

export { MediaUploaderView, MediaUploaderController }