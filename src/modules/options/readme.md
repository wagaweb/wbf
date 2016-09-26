#Options Module

This module enables developers to enhance themes with options.

##Defining options
You can define new options by hooking to `wbf/theme_options/register` action from within theme `functions.php`.

Then you can use `Organizer` class to add options.

    use WBF\modules\options\Organizer;

	add_action('wbf/theme_options/register', function(){
	    //Get an instance of Organizer
	    $orgzr = Organizer::getInstance();
	    
	    $orgzr->set_group("std_options");
	    $orgzr->add_section("global",_x("Global", "Theme options","my_textdomain"));

		$orgzr->add([
			'name' => __( 'my name', 'my_textdomain' ),
			'desc' => __( 'my desc', 'my_textdomain ),
			'id'   => 'my_id',
			'std'  => '1',
			'type' => 'checkbox'
		]);

		//[...]

		$orgzr->reset_group();
		$orgzr->reset_section();
	});

##The Organizer
Organizer allows to organize options into group and sections. These are some of the available methods:

**`set_section(string $section_name)`**

Set the current section to $section_name. This section will be used if no section is specified when adding a new option.

**`set_group(string $group_name)`**

Set the current group to $group_name. This group will be used if no group is specified when adding a new option.

**`add_section(string $id, string $label, string $group = null, array $params = [])`**

Adds a new section.

**`add(array $option, string $section = null, string $group = null, array $params = [])`**

Adds a new option. For $option params see below. You can specify a group and some additional params.

**`update(string $id, array $values,$section = null, $group = null, $params = null)`**

Update an already existing options or create a new option. For $values params see below.

##Options values

Options are defined by an array of properties.

Any option must have at least an "id" and a "type". 

The id can be any ^[a-z_]+$ string.

##Available types

You can find available fields `fields/*`. Every field implements `Field` interface and therefore has `get_html()` and `sanitize($input, $option)` methods.

It is possible to adds new field by hooking into `wbf/modules/options/fields/available`

    add_action('wbf/modules/options/fields/available', function($fields){
        $fields['my_new_field'] = 'Namespace\to\my\new\Field'
        return $fields;
    });
    
You must respect the `Field` interface and extend `BaseField`.

`sanitize` method receives the option input and the option array as specified in `wbf/theme_options/register` and must return the validated value.

**Remeber**: fields are registered during `wbf_init` action, which is called during `init` with a priority of 11 (See PluginCore.php @ init()).