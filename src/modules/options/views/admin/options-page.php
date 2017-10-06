<div id="optionsframework-wrapper" class="optionsframework-wrapper admin-wrapper wrap" data-options-gui>

    <div class="optionsframework-header">
		<h2><?php echo esc_html( $menu['page_title'] ); ?></h2>
		<?php WBF()->notice_manager->show_notices(); ?>
	</div>

    <!-- Navigation -->
    <div class="optionsframework-nav" data-nav>
        <ul>
            <?php $counter = 0; foreach ($tabs as $value) : ?>
                <?php
                $counter++;
                $class = !empty( $value['id'] ) ? $value['id'] : $value['name'];
                $class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) ) . '-tab';
                ?>
                <li data-category="category-<?php echo $counter; ?>"><?php echo esc_html( $value['name'] ); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <!-- /Navigation -->

    <div id="optionsframework-content" class="optionsframework-content">

        <?php settings_errors( 'options-framework' ); ?>

            <form action="" method="post">
                <?php wp_nonce_field( 'update_'.\WBF\modules\options\Framework::get_options_root_id() ); ?>

                <?php \WBF\modules\options\GUI::print_fields(); /* Settings */ ?>

                <div id="optionsframework-submit" class="optionsframework-submit">
                    <input type="submit" class="button-primary" name="update_theme_options" value="<?php esc_attr_e( 'Save Options', "wbf" ); ?>" />
                    <input type="submit" class="reset-button button-secondary" name="restore_theme_options" value="<?php esc_attr_e( 'Restore Defaults', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to restore defaults', 'wbf' ) ); ?>' );" />&nbsp;
                    <input type="submit" class="reset-button button-secondary" name="reset_theme_options" value="<?php esc_attr_e( 'Reset Options', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
                    <?php do_action("wbf/modules/options/view/options_page/after_submit_buttons",WBF()->wp_menu_slug); ?>
                    <div class="clear"></div>
                </div>
            </form>

        <?php do_action( 'optionsframework_after' ); ?>

	</div> <!-- / #optionsframework-content -->
</div> <!-- / .wrap -->

