<h2><?php _e("Updates channels", "wbf") ?></h2>
<form method="post" id="wbf_update_versions_channels" action="<?php admin_url('admin.php?page=wbf_status'); ?>">
	<table class="widefat striped wbf-admin-console">
		<tbody>
		<?php foreach($registered_updates_channels as $channel): ?>
			<tr>
				<td><?php echo $channel['name'] ?></td>
				<td>
					<?php if(isset($channel['channels']) && is_array($channel['channels']) && !empty($channel['channels'])): ?>
						<label>
							<select name="channels[<?php echo $channel['slug'] ?>]">
								<?php foreach ($channel['channels'] as $ch_name => $ch_value): ?>
									<option value="<?php echo $ch_value; ?>" <?php if(isset($registered_updates_channels_values[$channel['slug']]) && $registered_updates_channels_values[$channel['slug']] === $ch_value): ?>selected="selected"<?php endif; ?>><?php echo $ch_name; ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<td colspan="2"><button type="submit" name="wbf_update_versions_channels" class="button"><?php _ex('Update channels','Update channel selector','wbf'); ?></button></td>
		</tr>
		</tbody>
	</table>
	<?php wp_nonce_field('wbf_update_versions_channels'); ?>
</form>