<div id="section-<?php echo $id ?>" class="section section-<?php echo $type ?><?php echo $additional_classes ?>">
	<?php if($name): ?>
		<h4 class="heading"><?php echo $name; ?></h4>
	<?php endif; ?>
	<?php if($description): ?>
		<div class="explain"><?php echo $description; ?></div>
	<?php endif; ?>
	<div class="option">
		<div <?php if($inner_classes): ?>class="<?php echo $inner_classes; ?>"<?php endif; ?>>