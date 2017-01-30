<?php

namespace WBF\components\widgets\fields;

class Select extends Field {

	public function get_html() {

		$selected_key = (isset($this->instance[$this->slug])) ? $this->instance[$this->slug] : '';

		?>

		<p>
			<label class="wbf_widget_label" for="<?php echo $this->id; ?>"><?php echo $this->options['label']; ?></label>
			<select
				class="widefat"
				id="<?php echo $this->id; ?>"
				name="<?php echo $this->name; ?>"
			>
				<option value=""></option>
				<?php foreach ( $this->options['options'] as $key => $val ) : ?>
					<option
						value="<?php echo $key ?>"
						<?php echo ($key == $selected_key) ? 'selected="selected"' : '' ?>
					><?php echo $val ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	<?php }
}