<?php
/**
 * Apply custom stylesheet to admin panel
 *
 * @since 0.1.0
 * @uses wbf_locate_template_uri()
 */
function wbf_admin_styles() {
    wp_enqueue_style('waboot-admin-style', \WBF::prefix_url('admin/css/admin.css'), array(), false, 'all');
}
add_action('admin_enqueue_scripts', 'wbf_admin_styles');

/**
 * Apply custom stylesheet to the wordpress visual editor.
 *
 * @since 0.1.0
 * @uses add_editor_style()
 */
function wbf_editor_styles() {
	add_editor_style('wbf/admin/css/tinymce.css');
}
add_action('admin_init', 'wbf_editor_styles');