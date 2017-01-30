<?php

namespace WBF\components\widgets\fields;

class Text extends Field {

	public function get_html() { ?>
		<p>
			<label class="wbf_widget_label" for="<?php echo $this->id; ?>"><?php echo $this->options['label']; ?></label>
			<input class="widefat"
			       type="text"
			       id="<?php echo $this->id; ?>"
			       name="<?php echo $this->name; ?>"
			       value="<?php echo esc_attr( (isset($instance[$this->slug])) ? $instance[$this->slug] : $this->options['default'] ); ?>"
			>
		</p>
	<?php }
}