# Waboot Framework

WBF is a modular framework for WordPress which can speed up development time.

WBF is composed by different kind of parts: 

- Components: which implement functionality that can be used separately, even without the whole framework (a composer-ready version will be available soon)
- Modules: which implement a number of functionality based on WordPress hook system (like theme options, assets manager, custom updating procedures, ect...)
- Extensions: through with WBF supports and\or enhance third-party plugins.

## Getting started

The quickest way to start developing with WBF is to install it as WordPress plugin.

You can download the latest standalone version [here](https://www.waboot.io) (available soon) or clone this repo.

## WBF Components

WBF Components implements tools and functionality not strictly related to WordPress hooks system.

- Assets  
A number of tools for managing static assets.

    Assets can be registered through an Assets Manager that take care of:
    - Checking the actual existence of the asset and notify its absence.
    - Automatically assign a version number to assets (to avoid issues with caching systems)
    - Enqueue the assets during WordPress execution

- Breadcrumb  
Makes available a number of tools for managing breadcrumbs.

- Compiler  
Allows the live compilations of LESS files (SASS will be available in the future) and the generation of CSS files based on templates. Useful to implement certain type of theme options.

- CustomUpdater  
Makes available a number of tools to implement custom updating systems for themes and plugins.

- License  
Allows the management of customizable licenses for plugins and themes.
 
- MVC  
Allows developer to adopt a clean MVC pattern to templating.

- NavWalker  
A number of navwalker for WordPress menu. At the moment only a bootstrap-compatible walker is available.

- Notices  
Allows developer to centralize the management of dashboard notices.

- Plugins Framework  
A vast collection of classes with many build-in functionality to speed up plugin development.

- Utils  
A toolbox of useful functions

You can learn more about each components by reading their specific readmes.