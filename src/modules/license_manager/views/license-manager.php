<div class="wrap">
	<h2><?php _e( "Licenses", "wbf" ); ?></h2>
	<p><?php _e("Here you can enter your license.", "wbf"); ?></p>
	<?php if($has_theme_licenses) : ?>
		<h3><?php _e("Theme license:", "wbf"); ?></h3>
		<?php foreach($theme_licenses as $slug => $license): ?>
			<form method="post" action="admin.php?page=wbf_licenses">
				<?php
				$current_license = $license->get(true);
				if(!$current_license) $current_license = "";
				$status = $license->get_license_status();
				?>
				<div class="license">
					<h4><?php echo $license->nicename; ?></h4>
					<div class="license-body">
						<label><?php printf(_x("License code","License","wbf"),$license->nicename); ?>&nbsp;<input id="license_<?php echo $license->slug; ?>" type="text" value="<?php echo $current_license; ?>" name="code"/></label>
						<input type="submit" name="update-license" id="submit" class="button button-primary" value="<?php _ex("Update","License","wbf"); ?>" <?php if($license->is_valid()) echo "disabled"; ?>>
						<input type="submit" name="delete-license" id="delete" class="button button-primary" value="<?php _ex("Delete","License","wbf"); ?>">
						<div id="license-status" class="license-<?php $license->print_license_status(); ?>">
							<p><strong><?php _ex("Status:","License","wbf") ?></strong>&nbsp;<?php $license->print_license_status(); ?></p>
						</div>
					</div>
				</div>
				<input type="hidden" name="slug" value="<?php echo $license->slug; ?>">
				<input type="hidden" name="type" value="theme">
			</form>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if($has_plugin_licenses) : ?>
		<h3><?php _e("Plugin license:", "wbf"); ?></h3>
		<?php foreach($plugin_licenses as $slug => $license): ?>
			<form method="post" action="admin.php?page=wbf_licenses">
				<?php
				$current_license = $license->get(true);
				$status = $license->get_license_status();
				?>
				<div class="license">
					<h4><?php echo $license->nicename; ?></h4>
					<div class="license-body">
						<label><?php _e("License code","wbf"); ?>&nbsp;<input type="text" value="<?php echo $current_license; ?>" name="code"/></label>
						<input type="submit" name="update-license" id="submit" class="button button-primary" value="<?php _ex("Update","License","wbf"); ?>" <?php if($license->is_valid()) echo "disabled"; ?>>
						<input type="submit" name="delete-license" id="delete" class="button button-primary" value="<?php _ex("Delete","License","wbf"); ?>">
						<div id="license-status" class="license-<?php $license->print_license_status(); ?>">
							<p><strong><?php _ex("Status:","License","wbf") ?></strong>&nbsp;<?php $license->print_license_status(); ?></p>
						</div>
					</div>
				</div>
				<input type="hidden" name="slug" value="<?php echo $license->slug; ?>">
				<input type="hidden" name="type" value="plugin">
			</form>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php wp_nonce_field('submit_licence_nonce','license_nonce_field'); ?>
	<?php WBF()->print_copyright(); ?>
</div>