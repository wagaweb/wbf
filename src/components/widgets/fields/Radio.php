<?php

namespace WBF\components\widgets\fields;

class Radio extends Field {

	public function get_html() { ?>

			<p style="margin-bottom: 0;" class="wbf_widget_label" >
				<?php echo $this->options['label'] ?>
			</p>
			<ul style="border: 1px solid lightgray; padding: 5px 8px; margin-top: 1px">
				<?php foreach ( $this->options['options'] as $opt_id => $opt_label ) : ?>
					<li>
						<input
							<?php echo ( isset($this->instance[$this->slug]) && $opt_id == $this->instance[$this->slug] ) ? 'checked="checked"' : ''; ?>
							type="radio"
							id="<?php echo $this->id; ?>"
							name="<?php echo $this->name; ?>"
							value="<?php echo $opt_id; ?>"
						>
						<label for="<?php echo $this->id; ?>"><?php echo $opt_label ?></label>
					</li>
				<?php endforeach; ?>
			</ul>
	<?php }
}