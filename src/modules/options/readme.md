#Options Module

This module enables developers to enhance themes with options.

##Defining options
You can define new options by hooking to `wbf/modules/options/available` filter from within theme `functions.php`.

Then you can use `Organizer` class to add options.

    use WBF\modules\options\Organizer;

	add_filter('wbf/modules/options/available', function(){
	    //Get an instance of Organizer
	    $orgzr = Organizer::getInstance();
	    
	    $orgzr->set_group("std_options");
	    $orgzr->add_section("global",_x("Global", "Theme options","domain"));

		$orgzr->add([
			'name' => __( 'my name', 'waboot' ),
			'desc' => __( 'my desc', 'waboot' ),
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

**set_section(string $section_name)**

Set the current section to $section_name. This section will be used if no section is specified when adding a new option.

**set_group(string $group_name)**

Set the current group to $group_name. This group will be used if no group is specified when adding a new option.

**add_section(string $id, string $label, string $group = null, array $params = [])**

Adds a new section.

**add(array $option, string $section = null, string $group = null, array $params = [])**

Adds a new option. For $option params see below. You can specify a group and some additional params.

**update(string $id, array $values,$section = null, $group = null, $params = null)**

Update an already existing options or create a new option. For $values params see below.

##Options values

Options are defined by an array of properties.

Any option must have at least an "id" and a "type". 

The id can be any ^[a-z_]+$ string.

###Available types

...


