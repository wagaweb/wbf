# Waboot Framework

WBF is a modular framework for WordPress which can speed up development time.

WBF is composed by different kind of parts: 

- **Components**: which implement functionality that can be used separately, even without the whole framework.

- **Modules**: which makes available a number of API that can be implemented by theme developers (like theme options and theme components).

- **Extensions**: through which WBF supports and\or enhance third-party plugins.

Please note that the present documentation is in its early stage.

## Getting started

The quickest way to start developing with WBF is to install it as WordPress plugin.

You can download the latest standalone version [here](http://update.waboot.org/resource/get/plugin/wbf) or clone this repo.

In future version WBF will supports a composer-based installation.

## WBF Components

WBF Components implements tools and functionality not strictly related to WordPress hooks system. You can embed use each component even without the whole framework by [installing it via composer](https://packagist.org/search/?q=wbf).

- **Assets**  
A number of tools for managing static assets.

    Assets can be registered through an Assets Manager that take care of:
    - Checking the actual existence of the asset and notify its absence.
    - Automatically assign a version number to assets (to avoid issues with caching systems)
    - Enqueue the assets during WordPress execution

- **Breadcrumb**  
Makes available a number of tools for managing breadcrumbs.

- **Compiler**  
Allows the live compilations of LESS files (SASS will be available in the future) and the generation of CSS files based on templates. Useful to implement certain type of theme options.

- **CustomUpdater**  
Makes available a number of tools to implement custom updating systems for themes and plugins.

- **License**  
Allows the management of customizable licenses for plugins and themes.
 
- **MVC**  
Allows developer to adopt a clean MVC pattern to templating.

- **NavWalker**  
A number of navwalker for WordPress menu. At the moment only a bootstrap-compatible walker is available.

- **Notices**  
Allows developer to centralize the management of dashboard notices.

- **Plugins Framework**  
A vast collection of classes with many build-in functionality to speed up plugin development.

- **Utils**  
A toolbox of useful functions

You can learn more about each components by reading their specific readme.

## WBF Modules

To use modules functionality WBF has to be installed as plugin or embedded into a theme and initialized within the `functions.php` file.

- **Theme Options**  
Allows developers to easily create a series of options that final users can use to customize the theme.

- **Theme Components**  
Allows developers to create 'components'; a component is a self-contained and reusable theme-specific functionality that can be enabled or disabled through a GUI.  
 
    Components can be used to reduce clutter in `functions.php` file by splitting specific functionality; by this way those functionality are even easier to debug.
     
     Components can be also moved from one theme to another for a clean DRY (Don't Repeat Yourself) approach.