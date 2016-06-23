<?php foreach($sections as $slug => $current_section): ?>
	<h2><?php echo $current_section['title']; ?></h2>
	<table class="widefat striped">
		<tbody>
			<?php foreach($current_section['data'] as $key => $data): ?>
				<?php if(array_key_exists("name",$data)) : ?>
					<tr>
						<td class="row-title"><?php echo $data['name']; ?></td>
						<td class="desc">
							<?php if(is_array($data['value'])): ?>
								<pre><?php print_r($data['value']); ?></pre>
							<?php else: ?>
								<?php echo $data['value']; ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php else: ?>
					<tr>
						<td class="row-title"><?php echo $key; ?></td>
						<td class="desc">
							<?php if(is_array($data)): ?>
								<pre><?php print_r($data); ?></pre>
							<?php else: ?>
								<?php echo $data; ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endforeach; ?>