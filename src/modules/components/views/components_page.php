<?php if($last_error): ?>
	<div class="error">
		<p><?php echo $last_error; ?></p>
	</div>
<?php elseif($options_updated_flag) : ?>
	<div class="updated">
		<p><?php _ex("Options updated successfully","Component Page","wbf"); ?></p>
	</div>
<?php endif; ?>
<?php if(count($registered_components) <= 0) : ?>
<div class="wrap">
	<h2><?php _e("Components", "wbf"); ?></h2>
	<p>
		<?php _e("No components available in the current theme. You can create components into /components/ directory under theme directory.","wbf"); ?>
	</p>
</div>
<?php return; endif; ?>
<div id="componentframework-wrapper" class="wrap">
	<div class="componentframework-header">
		<h2><?php _e("Components", "wbf"); ?></h2>
	</div>
	<div id="componentframework-content-wrapper">
		<div class="nav-tab-wrapper">
			<ul>
				<li><a class="nav-tab" id="component-main-tab" data-show-comp-settings='component-main' href="#component-main">Available components</a></li>
				<?php foreach($registered_components as $comp_data): if(!\WBF\modules\components\ComponentsManager::is_active($comp_data)) continue; ?>
					<li><a class="nav-tab" id="component-<?php echo $comp_data->name; ?>-link" data-show-comp-settings='component-<?php echo $comp_data->name; ?>' href="#component-<?php echo $comp_data->name; ?>"><?php echo ucfirst($comp_data->name); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div id="componentframework-metabox" class="metabox-holder">
			<div id="componentframework" class="postbox">
				<form method="post" action="admin.php?page=<?php echo \WBF\modules\components\ComponentsManager::$wp_menu_slug; ?>">
					<div id="component-main" class="group">
						<table class="wp-list-table widefat components">
							<thead>
							<tr>
								<th scope="col"></th>
								<th scope="col" id="name" class="manage-column column-name"><?php _e( "Component", "wbf" ) ?></th>
								<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Enable\Disable', "wbf" ) ?></th>
							</tr>
							</thead>
							<tfoot>
							<tr>
								<th scope="col"></th>
								<th scope="col" id="name" class="manage-column column-name"><?php _e( "Component", "wbf" ) ?></th>
								<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Enable\Disable', "wbf" ) ?></th>
							</tr>
							</tfoot>
							<tbody id="the-list">
								<?php $i=1; foreach($registered_components as $comp_data): ?>
								<tr id="<?php echo $comp_data->name; ?>" class="<?php WBF\modules\components\print_component_status( $comp_data ); ?> <?php if($i%2 == 0) echo "even"; else echo "odd"; ?>">
									<?php $data = \WBF\modules\components\ComponentsManager::get_component_data($comp_data->file); ?>
									<th></th>
									<th class="component-data column-description desc">
										<strong><?php echo $data['Name']; ?></strong>
										<div class="component-description">
											<?php echo $data['Description']; ?>
											<?php if(\WBF\modules\components\ComponentsManager::is_child_component($comp_data)): ?>
												<p class="child-component-notice">
													<?php _e("This is a component of the current child theme", "wbf"); ?>
													<?php
													if(isset($comp_data->override)) {
														if($comp_data->override){
															_e(", and <strong>override a core component</strong>", "wbf");
														}
													}
													?>
												</p>
											<?php endif; ?>
										</div>
										<div class="<?php WBF\modules\components\print_component_status($comp_data); ?> second plugin-version-author-uri">
											<?php
											$component_meta = array();
											if(empty($data['Version'])){
												$component_meta[] = sprintf( __( 'Version %s' ), $data['Version'] );
											}
											if(!empty($data['Author'])) {
												$author = $data['Author'];
												if(!empty($data['AuthorURI'])){
													$author = '<a href="' . $data['AuthorURI'] . '" title="' . esc_attr__( 'Visit author homepage' ) . '">' . $data['Author'] . '</a>';
												}
												$component_meta[] = sprintf( __( 'By %s' ), $author );
											}
											if(!empty($plugin_data['PluginURI'])){
												$component_meta[] = '<a href="' . $data['ComponentURI'] . '" title="' . esc_attr__( 'Visit plugin site' ) . '">' . __( 'Visit plugin site' ) . '</a>';
											}
											echo implode(' | ', $component_meta);
											?>
										</div>
									</th>
									<th class="component-actions">
										<div class="row-actions visible">
											<div class="wb-onoffswitch">
												<?php if(!\WBF\modules\components\ComponentsManager::is_active($comp_data)): ?>
													<input id="<?php echo $comp_data->name; ?>_status" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="components_status[<?php echo $comp_data->name; ?>]" >
												<?php else: ?>
													<input id="<?php echo $comp_data->name; ?>_status" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="components_status[<?php echo $comp_data->name; ?>]" checked="checked">
												<?php endif; ?>
												<label class="wb-onoffswitch-label" for="<?php echo $comp_data->name; ?>_status"><span class="wb-onoffswitch-inner"></span>
													<span class="wb-onoffswitch-switch"></span>
												</label>
											</div>
										</div>
									</th>
								</tr>
								<?php $i++; endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php foreach($registered_components as $comp_data): if(!\WBF\modules\components\ComponentsManager::is_active($comp_data)) continue; ?>
					<div id="component-<?php echo $comp_data->name; ?>" class="group" style="display: none;">
						<h3><?php _e(sprintf("%s Component Settings",ucfirst($comp_data->name)),"wbf"); ?></h3>
						<?php \WBF\modules\options\GUI::optionsframework_fields($compiled_components_options[$comp_data->name]); ?>
					<?php //</div> not necessary (is echoed from GUI::optionsframework_fields )... THIS MUST BE CHANGED AS SOON AS POSSIBLE, IT'S JUST SO WRONG! ?>
					<?php endforeach; ?>
					<div id="componentframework-submit">
						<input type="submit" name="submit-components-options" id="submit" class="button button-primary" value="Save Changes">
						<input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore default component activation state', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
					</div>
				</form>
			</div>
		</div><!-- #componentframework-content -->
	</div><!-- #componentframework-wrap -->
	<?php \WBF::print_copyright(); ?>
</div><!-- .wrap: end -->