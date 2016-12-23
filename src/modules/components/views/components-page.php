<?php use WBF\modules\components\ComponentFactory;
use WBF\modules\components\GUI;
?>

<?php WBF()->notice_manager->show_manual_notices(); ?>

<?php if(count($registered_components) <= 0) : ?>
<div class="wrap">
	<h2><?php _e("Components", "wbf"); ?></h2>
	<p>
		<?php _e("No components available in the current theme. You can create components into /components/ directory under theme directory.","wbf"); ?>
	</p>
</div>
<?php return; endif; ?>


<div id="componentframework-wrapper" class="componentframework-wrapper admin-wrapper wrap" data-components-gui>

    <div class="componentframework-header">
        <h2><?php _e("Components", "wbf"); ?></h2>
    </div>

    <div class="componentframework-nav" data-nav>

        <ul>
            <li id="component-main-tab" data-show-comp-settings='component-main'><?php _e("Available components","wbf"); ?></li>
            <?php foreach($registered_components as $comp_data): if(!\WBF\modules\components\ComponentsManager::is_active($comp_data)) continue; ?>
                <?php $data = ComponentFactory::get_component_data( $comp_data->file ); ?>
                <li id="component-<?php echo $comp_data->name; ?>-link" data-show-comp-settings='component-<?php echo $comp_data->name; ?>'><?php if(isset($data['Name'])) echo $data['Name']; else echo ucfirst($comp_data->name); ?></li>
            <?php endforeach; ?>
        </ul>

    </div>

    <div id="componentframework-content" class="componentframework-content">

        <form method="post" action="admin.php?page=<?php echo GUI::$wp_menu_slug; ?>">
            <div id="component-main" class="group" data-components-list>
                <table class="wp-list-table widefat components">
                    <thead>
                        <tr>
                            <th scope="col" id="name" class="manage-column column-name"><?php _e( "Component", "wbf" ) ?></th>
                            <th scope="col" id="description" class="manage-column column-description"><?php _e( 'Enable\Disable', "wbf" ) ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th scope="col" id="name" class="manage-column column-name"><?php _e( "Component", "wbf" ) ?></th>
                            <th scope="col" id="description" class="manage-column column-description"><?php _e( 'Enable\Disable', "wbf" ) ?></th>
                        </tr>
                    </tfoot>
                    <tbody id="the-list">
                        <!-- Components List -->
                        <?php $i=1; foreach($categorized_registered_components as $category => $components): ?>
                            <tr class="category" data-category="<?php echo $category; ?>">
                                <th><h2 class="category-<?php echo str_replace(" ","_",strtolower($category)); ?>"><?php echo $category; ?></h2></th>
                            </tr>
                            <?php foreach($components as $comp_data): ?>
                            <tr id="<?php echo $comp_data->name; ?>" class="<?php WBF\modules\components\print_component_status( $comp_data ); ?> <?php if($i%2 == 0) echo "even"; else echo "odd"; ?>">
                                <?php $data = ComponentFactory::get_component_data( $comp_data->file ); ?>
                                <td class="component-data column-description desc">
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
                                    <?php if(isset($comp_data->tags) && !empty($comp_data->tags)): ?>
                                    <div class="tags">
                                        <strong><?php _ex("Tags:","Components Page","wbf"); ?></strong>
                                        <ul style="list-style-type: none; display: inline; margin-left: 5px;">
                                        <?php foreach ($comp_data->tags as $tag): ?>
                                            <li class="tag-<?php echo str_replace(" ","_",strtolower($tag)); ?>" style="margin-right: 5px; padding: 0 3px; border: 1px solid #ddd; display: inline;"><?php echo $tag ?></li>
                                        <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif ?>
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
                                </td>
                                <td class="component-actions">
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
                                </td>
                            </tr>
                            <?php $i++; endforeach; ?>
                        <?php endforeach; ?>
                        <!-- /Components List -->
                    </tbody>
                </table>
            </div>
            <!-- Active components tabs -->
            <?php foreach($registered_components as $comp_data): if(!\WBF\modules\components\ComponentsManager::is_active($comp_data)) continue; ?>
                <?php $data = ComponentFactory::get_component_data( $comp_data->file ); ?>
                <div id="component-<?php echo $comp_data->name; ?>" class="group" style="display: none;" data-fieldgroup="component-<?php echo $comp_data->name; ?>">
                    <h3><?php _e(sprintf("%s Settings",isset($data['Name']) ? $data['Name'] : ucfirst($comp_data->name)),"wbf"); ?></h3>
                    <?php \WBF\modules\options\GUI::print_fields($compiled_components_options[$comp_data->name]); ?>
                </div>
            <?php endforeach; ?>
            <!-- /Active components tabs -->
            <div id="componentframework-submit">
                <input type="submit" name="submit-components-options" id="submit" class="button button-primary" value="Save Changes">
                <input type="submit" class="reset-button button-secondary" name="restore_defaults_components" value="<?php esc_attr_e( 'Restore default component status', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to restore defaults. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
                <input type="submit" class="reset-button button-secondary" name="reset_components" value="<?php esc_attr_e( 'Reset components status', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
            </div>
        </form>
        
	</div><!-- #componentframework-wrap -->
	<?php \WBF::print_copyright(); ?>
</div><!-- .wrap: end -->