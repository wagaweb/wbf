<?php

namespace WBF\components\widgets\fields;

class Checkbox extends Field {

	public function get_html() { ?>

		<p>
			<input class="widefat"
			       type="checkbox"
			       id="<?php echo $this->id; ?>"
			       name="<?php echo $this->name; ?>"
				<?php echo (isset($this->instance[$this->slug])) ? 'checked = checked' : '' ?>
			>
			<label class="wbf_widget_label" for="<?php echo $this->id; ?>"><?php echo $this->options['label']; ?></label>
		</p>
	<?php }
}