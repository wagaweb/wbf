<div class="wbf-intro">
	<p>
		<?php _e( '<strong>WBF</strong> is a comprehensive framework that give you a tons of tools for developing with WordPress.' ); ?>
	</p>
	<p>
		<?php printf(__( 'By using it as plugins you can enable <a href="%s" target="_blank" rel="bookmark" title="WBF theme options documentation">theme options</a> and <a href="%s" target="_blank" rel="bookmark" title="WBF components documentation">components</a> functionality.' ),'https://github.com/wagaweb/wbf/tree/master/src/modules/options','https://github.com/wagaweb/wbf/tree/master/src/modules/components'); ?>
	</p>
	<ul style="list-style-type: circle" class="features">
		<li style="margin-left: 50px"><?php _e( 'Declare theme support with <code>add_theme_support(\'wbf\')</code>.' ); ?></li>
		<li style="margin-left: 50px"><?php _e( 'Adds theme options to your theme by hooking to <code>wbf/theme_options/register</code> action.' ); ?></li>
		<li style="margin-left: 50px"><?php printf(__( 'Deploy components (small, self-contained theme customizations) into <code>%s</code>.'),get_stylesheet_directory().'/components'); ?></li>
	</ul>
    <p>
        <?php printf(__('For complete documentations, click <a href="%s" target="_blank" rel="bookmark" title="WBF github page">here</a>.'),'https://github.com/wagaweb/wbf'); ?>
    </p>
	<p class="thankyou">
		<strong><?php _e( 'Thank you for creating with WBF!' ) ?></strong>
	</p>
</div>