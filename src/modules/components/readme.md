# Components Module

This module enables developers to build self-contained modifications to Wordpress themes that can be enabled and disabled (among other options) via dashboard. This avoids cluttering functions.php file with code, enhances code quality and simplifies debugging.

## Defining a component
You can define new options by create a new folder under `/components` inside your theme directory (through filters it is possible to change this directory or even to have multiple directories as well - documented below). 

A component is defined by a main file that contains a **component class** and a **component header**.

Components headers are similar to WordPress plugins ones and are composed like that:

    /**
    Component Name: Test Component
    Description: This is a test component
    Category: TestCategory
    Tags: testtag1, testtag2
    Version: 1.0
    Author: Foobar Devs Inc.
    Author URI: http://www.fbinc.io
    */

Theme child components overrides parent theme components with the same name.

During `wbf_after_setup_theme` action (called during `after_setup_theme` with priority of 11) WBF scan component directory(ies) and search for components main files.
    
WBF then requires these files and initialize components.

### Component class definition

When initialize a component, WBF takes the name of the file that contain the header, builds a class name starting from it and check for its existence.

The transformation rules for passing from file name to class name are simple:

| Filename | Classname  |   
|----------|------------|
| test.php    | Test |
| test_me.php | Test_Me |
| testme.php  | Testme |
| testMe.php  | TestMe |
| TestMe.php  | TestMe |

The class must extend `\WBF\modules\components\Component` and can implements some standard methods that are automatically called at specific hooks during WordPress page rendering.

These methods are:

| Method             | Called at                             |   
|--------------------|---------------------------------------|
| setup()            |  wbf_init (init, 11)                  |
| run()              |  wp                                   |
| script()           |  wp_enqueue_scripts                   |
| styles()           |  wp_enqueue_scripts                   |
| widgets()          |  widgets_init                         |
| register_options() |  wbf/theme_options/register (wbf_init)|
| onActivate()       |  component activation                 |
| onDeactivate()     |  component deactivation               |

Only the setup() method is called always, all other methods are called only for enabled components that can run for the current context.

**Note**: always call the parent methods!

It is possible to see that this procedure gives structure to WordPress mods and, on the long run, could contribute to the avoidance of spaghetti code and could also simplify debugging (is something broken? Then disable components one by one to find the culprit!).

## Sample component

The most basic component could look like that:

	<?php
	/**
	Component Name: Sample
	Description: Sample component
	Category: sample
	Tags: sampletag
	Version: 1.0
	Author: Foobar Devs Inc.
	Author URI: http://www.fbinc.io
	*/

	class Sample extends \WBF\modules\components\Component{
	    public function run(){
	        parent::run();
	        //... do stuff...
	    }
	}

## Project goals

Components are born to address the issue of messy `functions.php` files that are found more often than not among WordPress themes, but can be used for many different things by advanced developers. An example of an advanced usage can be found in [Waboot theme](https://github.com/wagaweb/waboot#components).

Components can also be reused in different themes or overridden in child themes.

We believe that the initial learning cost is compensated by the advantages: tossing snippets of code found online into `functions.php` file can be easier at early stages but could easily lead to a nightmare code base later on. Even some premium plugins and themes are completely un-moddable.

Components can be used as a standard procedure to encapsulate distinct theme functionality, improving code quality and code usability within WordPress community.

## Advanced topics

### register_options() method.

Within this method the Organizer (/src/modules/options/Organizer.php) can be used to adds theme options. The parent method register the components relative options (like "enable on all pages", "load locations" and so on). 

It is possible to adds more components related options by setting a specific section name before adding the options:

    <?php
    $orgzr = Organizer::getInstance();
    
    $orgzr->set_group("components");
    
    $section_name = $this->name."_component";
    $additional_params = [
        'component' => true,
        'component_name' => $this->name
    ];
    
    $orgzr->add(array(
        //...
        'component' => true
    ),null,null,$additional_params);
    
    //...
    		
### Adding and changing components directories.
    		
Functionality currently under testing.
 