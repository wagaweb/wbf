<div id="field-option-<?php echo $id ?>" class="field-option field-option-<?php echo $type ?> <?php echo $additional_classes ?>">
	<?php if($name): ?>
		<h4 class="heading"><?php echo $name; ?></h4>
	<?php endif; ?>
	<?php if($description): ?>
		<div class="explain"><?php echo $description; ?></div>
	<?php endif; ?>
	<div class="option">
		<div <?php if($inner_classes): ?>class="<?php echo $inner_classes; ?>"<?php endif; ?>>