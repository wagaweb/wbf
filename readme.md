[Components](#wbf-components) | [Modules](#wbf-modules) | [Roadmap](#roadmap)

# Waboot Framework

WBF is a modular framework for WordPress.

WBF is composed by different kind of parts: 

- **Components**: which implement functionality that can be used separately, even without the whole framework.

- **Modules**: which makes available a number of API that can be implemented by theme developers (like theme options and theme components).

- **Extensions**: through which WBF supports and\or enhance third-party plugins.

Please note that the present documentation is in its early stage.

## Getting started

The quickest way to start developing with WBF is to install it as WordPress plugin.

You can download the latest standalone version [here](http://update.waboot.org/resource/get/plugin/wbf) or clone this repo and run the composer\npm\bower build.

WBF Components can be installed on project basis via [composer](https://packagist.org/search/?q=wbf) (still experimental).

## WBF Components

WBF Components implements tools and functionality not strictly related to WordPress hooks system. You can embed use each component even without the whole framework by [installing it via composer](https://packagist.org/search/?q=wbf).

- **Assets**  
A number of tools for managing static assets.

    Assets can be registered through an Assets Manager that take care of:
    - Checking the actual existence of the asset and notify its absence.
    - Automatically assign a version number to assets (to avoid issues with caching systems)
    - Enqueue the assets during WordPress execution
    
    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/components/assets)

- **Breadcrumb**  
Makes available a number of tools for managing breadcrumbs.

- **Compiler**  
Allows the live compilations of LESS files (SASS will be available in the future) and the generation of CSS files based on templates. Useful to implement certain type of theme options.

    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/components/compiler)

- **CustomUpdater**  
Makes available a number of tools to implement custom updating systems for themes and plugins.

    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/components/customupdater)

- **License**  
Allows the management of customizable licenses for plugins and themes.
 
    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/components/license) 
 
- **MVC**  
Allows developer to adopt a clean MVC pattern to templating.

    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/components/mvc)

- **NavWalker**  
A number of navwalker for WordPress menu. At the moment only a bootstrap-compatible walker is available.

- **Notices**  
Allows developer to centralize the management of dashboard notices.

- **Plugins Framework**  
A vast collection of classes with many build-in functionality to speed up plugin development.

    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/components/pluginsframework)

- **Utils**  
A toolbox of useful functions

<span style="font-size:smaller"><a href="#waboot-framework">Back to top.</a></span>

## WBF Modules

To use module APIs WBF has to be installed as plugin or embedded into a theme and initialized within the `functions.php` file.

- **Theme Options**  
Allows developers to easily create a series of options that final users can use to customize the theme.

    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/modules/options)

- **Theme Components**  
Allows developers to create 'components'; a component is a self-contained and reusable theme-specific functionality that can be enabled or disabled through a GUI.  
 
    Components can be used to reduce clutter in `functions.php` file by splitting specific functionality; by this way these functionality are even easier to debug.
     
    Components can be also moved from one theme to another for a clean DRY (**D**on't **R**epeat **Y**ourself) approach.

    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/modules/components)
     
- **Behaviors**  
Behaviors API allows developer to create metaboxes with default values specified as theme options; by this way you can contradict specific options on post basis.

    [Learn more](https://github.com/wagaweb/wbf/tree/master/src/modules/behaviors)
    
- **Commands**  
WP Cli extension with many adhoc commands for Waboot \ WBF environment (about to be released!)

- **License Manager**
A simple frontend to License_Manager component. It will make available a dashboard page with all registered licenses.

<span style="font-size:smaller"><a href="#waboot-framework">Back to top.</a></span>
  
# Roadmap

**v1.0.x**

Bugfix releases

**v1.0.9**

New hooks logic for components and theme options

**v1.1.x**

- Introduction of Commands module
- Bugfixing

**v1.2.x**

- Implementation of tests
- Make behaviors use theme options fields
- Refactoring of Plugin_Update_Checker to a more modern version.

<span style="font-size:smaller"><a href="#waboot-framework">Back to top.</a></span>
