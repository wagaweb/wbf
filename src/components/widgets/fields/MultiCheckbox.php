<?php

namespace WBF\components\widgets\fields;

class MultiCheckbox extends Field {

	public function get_html() { ?>

			<p style="margin-bottom: 0;" class="wbf_widget_label" >
				<?php echo $this->options['label'] ?>
			</p>
			<ul style="max-height: 70px; overflow: scroll; border: 1px solid lightgray; padding: 5px 8px; margin-top: 1px;">
				<?php foreach ( $this->options['options'] as $opt_id => $opt_label ) : ?>
					<li>
						<input
							<?php echo ( isset($this->instance[$this->slug]) && array_key_exists($opt_id, $this->instance[$this->slug]) ) ? 'checked="checked"' : ''; ?>
							type="checkbox"
							id="<?php echo $this->id; ?>"
							name="<?php echo $this->name.'['.$opt_id.']'; ?>"
						>
						<label for="<?php echo $this->id; ?>"><?php echo $opt_label ?></label>
					</li>
				<?php endforeach; ?>
			</ul>
	<?php }
}