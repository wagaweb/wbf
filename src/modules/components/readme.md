#Components Module

This module enables developers to build self-contained modifications to Wordpress themes that can be enabled and disabled (among other options) via dashboard. This avoids cluttering functions.php file with code, enhances code quality and simplifies debugging.

##Defining a component
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

## Project objectives

Components can be used for many things by advanced developers, and could be somewhat intimidating to beginners ones or to WordPress advanced users which only toss snippets of code found online into functions.php file.

The latter practices can be easy at early stages but they could easily lead to a nightmare code base later on. Even some premiun plugins and themes are completely un-moddable.

Components can be used as a standard procedure to encaplulate distinct functionality, improving code quality and code usability.