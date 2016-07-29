<div id="optionsframework-wrap" class="wrap">
	<div class="optionsframework-header">
		<h2><?php echo esc_html( $menu['page_title'] ); ?></h2>
	</div>
	<div id="optionsframework-content-wrapper">
		<div class="nav-tab-wrapper">
			<ul>
				<?php $counter = 0; foreach ($tabs as $value) : ?>
					<?php
						$counter++;
						$class = !empty( $value['id'] ) ? $value['id'] : $value['name'];
						$class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) ) . '-tab';
					?>
					<li><a id="options-group-<?php echo $counter; ?>-tab" class="nav-tab <?php echo $class ?>" title="<?php echo esc_attr( $value['name'] ) ?>" href="<?php echo esc_attr( '#options-group-'.  $counter ) ?>"><?php echo esc_html( $value['name'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php settings_errors( 'options-framework' ); ?>
		<div id="optionsframework-metabox" class="metabox-holder">
			<div id="optionsframework" class="postbox">
				<form action="" method="post">
					<?php wp_nonce_field( 'update_'.\WBF\modules\options\Framework::get_options_root_id() ); ?>
					<?php \WBF\modules\options\GUI::optionsframework_fields(); /* Settings */ ?>
					<div id="optionsframework-submit">
						<input type="submit" class="button-primary" name="update_theme_options" value="<?php esc_attr_e( 'Save Options', "wbf" ); ?>" />
						<input type="submit" class="reset-button button-secondary" name="restore_theme_options" value="<?php esc_attr_e( 'Restore Defaults', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to restore defaults', 'wbf' ) ); ?>' );" />&nbsp;
						<input type="submit" class="reset-button button-secondary" name="reset_theme_options" value="<?php esc_attr_e( 'Reset Options', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
						<?php do_action("wbf/modules/options/view/options_page/after_submit_buttons",WBF::getInstance()->wp_menu_slug); ?>
						<div class="clear"></div>
					</div>
				</form>
			</div> <!-- / #container -->
		</div>
	</div> <!-- / #content-wrapper -->
	<?php do_action( 'optionsframework_after' ); ?>
</div> <!-- / .wrap -->

