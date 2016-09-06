<h2><?php _e( "Theme Options Manager", "wbf" ); ?></h2>

<h3><?php _e( "Export or Backup Theme Options", "wbf" ); ?></h3>

<form action="admin.php?page=<?php echo $wp_menu_slug; ?>" method="POST" id="export-themeoptions">
	<p><label><input type="radio" name="option" value="backup"> <?php _e( "Backup current Theme Options on the disk", "wbf" ); ?></label></p>
	<p class="submit"><input type="submit" name="submit-backup" id="submit" class="button button-primary" value="<?php _e( "Backup" ) ?>"></p>
</form>

<h3><?php _e( "Import or Restore Theme Options", "wbf" ); ?></h3>

<form action="admin.php?page=<?php echo $wp_menu_slug; ?>" method="POST" enctype="multipart/form-data"
      id="export-themeoptions">
	<p><?php _e( "Select a file to restore, or upload one:" ); ?></p>
	<?php if ( ! empty( $backup_files ) ) : ?>
		<?php foreach ( $backup_files as $file ): ?>
			<p><label><input type="radio" name="local-backup-file" value="<?php echo $file['path'] ?>"><?php echo $file['name'] ?></label>&nbsp;<a href='<?php echo $file['url']; ?>' target="_blank" title="<?php _e( "Download: " . $file['name'] ); ?>">[<?php _e( "download" ) ?>]</a></p>
		<?php endforeach; ?>
	<?php else: ?>
		<p><?php _e( "No backup files available at the moment.", "wbf" ); ?></p>
	<?php endif; ?>
	<p>
		<label>
			<input type="file" name="remote-backup-file" id="backup-file"/>
		</label>
	</p>

	<p class="submit"><input type="submit" name="submit-restore" id="submit" class="button button-primary" value="<?php _e( "Import" ) ?>"></p>
</form>
<?php \WBF::print_copyright(); ?>