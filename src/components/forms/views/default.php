<?php

switch ($view):
	case 'form_start': ?>
        <form action="<?php echo $action; ?>" method="<?php echo $method; ?>"></form>
		<?php break;
	case 'checkbox': ?>
		<div>
			<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
			<input type="checkbox" id="<?php echo $id; ?>" placeholder="<?php echo $placeholder; ?>">
		</div>
		<?php break;
endswitch;
