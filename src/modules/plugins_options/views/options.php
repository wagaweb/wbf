<!-- Menu -->
<ul>
<?php foreach ($tabs as $tab): ?>
	<li><a href="<?php echo $tab->get_href(); ?>" title="<?php echo $tab->get_title(); ?>"><?php echo $tab->get_label(); ?></a></li>
<?php endforeach; ?>
</ul>
<!-- Current active tab -->
<div class="wbf-plugin-options-tab <?php echo $active_tab->get_slug() ?>-tab active">
    <h2><?php echo $active_tab->get_title(); ?></h2>
    <form method="post" action="<?php echo $current_uri; ?>">
	<?php if($active_tab->has_sections()) : ?>
		<ul>
			<?php foreach ($active_tab->get_sections() as $section): ?>
				<li><a href="<?php echo $section->get_href(); ?>" title="<?php echo $section->get_title(); ?>"><?php echo $section->get_label(); ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if(isset($active_section) && $active_section): ?>
        <?php echo $active_section->get_title(); ?>
		<?php call_user_func($active_section->render()); ?>
	<?php else: ?>
		<?php call_user_func($active_tab->render()) ?>
	<?php endif; ?>
        <button style="display: block; margin-top: 10px;" class="button button-primary" type="submit"><?php _ex('Save settings','WBF Plugin Options page','wbf'); ?></button>
        <input type="hidden" name="save-wbf-plugins-settings" value="<?php echo $active_tab->get_slug(); ?>">
        <?php wp_nonce_field('save-wbf-plugins-settings'); ?>
    </form>
</div>