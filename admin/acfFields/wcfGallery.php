<?php

namespace WBF\admin\acfFields;

class wcfGallery extends \acf_field{

    function __construct(){
        $this->name = 'wcf_gallery';
        $this->label = __("WBF Gallery",'wbf');
        $this->category = 'content';
        $this->defaults = array(
            'preview_size'	=> 'thumbnail',
            'library'		=> 'all',
            'min'			=> 0,
            'max'			=> 0,
            'min_width'		=> 0,
            'min_height'	=> 0,
            'min_size'		=> 0,
            'max_width'		=> 0,
            'max_height'	=> 0,
            'max_size'		=> 0,
            'mime_types'	=> ''
        );
        $this->l10n = array(
            'select'		=> __("Add Image to Gallery",'wbf'),
            'edit'			=> __("Edit Image",'wbf'),
            'update'		=> __("Update Image",'wbf'),
            'uploadedTo'	=> __("uploaded to this post",'wbf'),
            'max'			=> __("Maximum selection reached",'wbf')
        );
        add_action('save_post', array($this,'saveGalleryMeta'));
        parent::__construct();
    }

    /**
     * Render field settings during field group creation
     * @param $field
     */
    function render_field_settings( $field ) {
        acf_render_field_setting( $field, array(
            'label'			=> __('Maximum file number','waboot'),
            'instructions'	=> '',
            'type'			=> 'number',
            'name'			=> 'max'
        ));

        // allowed type
        acf_render_field_setting( $field, array(
            'label'			=> __('Allowed file types','waboot'),
            'instructions'	=> __('Comma separated list. Leave blank for all types','waboot'),
            'type'			=> 'text',
            'name'			=> 'mime_types',
        ));
    }

    /**
     * Render field into post editing
     * @param $field
     */
    function render_field( $field ) {
        global $post_id;
        wp_enqueue_media();?>
        <div>
            <label for="image_url">Image</label>
            <input type="hidden" name="imgId" id="imgId" value="1,2,3,4">
            <!--<input type="text" name="image_url" id="image_url" class="regular-text">-->
            <input type="button" name="upload-btn" id="upload-btn" class="button-primary button" value="Upload Image">
            <div>
            <?php $this->renderGalleryMeta($post_id); ?>
            </div>
        </div>
        <?php
    }

    function saveGalleryMeta($postId){
        if(isset($_POST['imgId'])) {
            $fields = get_field('field_wbf_gallery', $postId);
            $ids = array();
            $ids = explode(',', $_POST['imgId']);
            $ids = array_merge($fields, $ids);
            update_field('field_wbf_gallery', $ids, $postId);

        }
    }
    function renderGalleryMeta($postId){
        $fields = get_field('field_wbf_gallery', $postId);
        foreach($fields as $field){
            $img = wp_get_attachment_url($field);
            $imgExt = strrchr($img, ".");
            $imgUrl = substr($img,0,strlen($img) - strlen($imgExt));
            echo '<img class="imgGalleryAdmin" src=" '. $imgUrl . '-150x150' . $imgExt .'">';
        }
    }

}