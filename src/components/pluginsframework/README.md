#Plugins Framework
Plugins Framework component is an set of tools and extensible classes to help WordPress plugins developers.  

It supports a "convention over configuration" principle to keep the environment clean and maintainable.

## Getting started

You start the development of a new plugin either by extensing BasePlugin or TemplatePlugin. 

The latter makes available a set of methods to add theme-overridable templates to the plugin.

### Simple plugin

You can check out [here](https://github.com/wagaweb/wbf-sample-plugin) a barebone plugin.

BasePlugin takes care of:

- Initializing useful vars like `plugin_dir`, `plugin_path`, `plugin_relative_dir`, ect...
- Setting up the localization domain
- Linking together a standard "convention over configuration" structure (see below)
- Storing actions and filters added by plugin

And provides a number of useful functions:

- A complete caching mechanism based on transients
- The ability to setup a custom update server
- and much more...

### Plugin directories structure

WBF Plugin Framework supports an arbitrary standardized structure to enhance plugins maintainability.

The most basic plugin structure is the shown below:

```
.
|-src/
|---includes/
|-----index.php
|-----wbf-plugin-check-functions.php
|---Plugin.php
|-index.php
|-waboot-sample.php
```

If you want to split the code for the frontend from the code for the dashboard, you can use a structure like this:

```
.
|-src/
|---includes/
|-----index.php
|-----wbf-plugin-check-functions.php
|---Admin.php
|---Frontend.php
|---Plugin.php
|-index.php
|-waboot-sample.php
```

The framework automatically recognizes the structure and links Plugin, Admin and Frontend class instances together.

The Loader instance within Plugin will receive a reference to both Frontend and Admin, and those instances will receive a reference to Plugin in their constructors.

A practical example can be found [here](https://github.com/wagaweb/wbf-sample-plugin/tree/standard-structure-base).


### Template plugin

...