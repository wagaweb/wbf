<?php foreach ($fields as $index => $field) : ?>
	<?php
	$fullImageUrl = wp_get_attachment_url($field);
	$uploadImageUrl = substr($fullImageUrl,0, strrpos($fullImageUrl,'/'));
	$img = wp_get_attachment_metadata($field);
	?>
	<?php if(isset($img["sizes"])) : $thumbnail = $uploadImageUrl . '/' . $img["sizes"]["thumbnail"]["file"]; ?>
		<div class="containerImgGalleryAdmin">
			<img class="imgGalleryAdmin" src="<?php echo $thumbnail; ?>" data-id="<?php echo $field; ?>">
			<div class="deleteImg">
				<a class="acf-icon dark remove-attachment " data-index="<?php echo $index; ?>" href="#" data-id="<?php echo $field; ?>">
					<i class="acf-sprite-delete"></i>
				</a>
			</div>
		</div>
	<?php endif; ?>
<?php endforeach; ?>