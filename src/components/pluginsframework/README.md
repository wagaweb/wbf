#Plugins Framework
Plugins Framework component is an set of tools and extensible classes to help WordPress plugins developers.  

It supports a "convention over configuration" principle to keep the environment clean and maintainable.

## Getting started

You start the development of a new plugin either by extending BasePlugin or TemplatePlugin. 

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

If you have a complex plugin with many classes and files, you can choose to further split the structure:

```
.
|-src/
|---includes/
|-----index.php
|-----wbf-plugin-check-functions.php
|---admin/
|-----Admin.php
|-----more-files...
|-----more-files...
|---frontend/
|-----Frontend.php
|-----more-files...
|-----more-files...
|---Plugin.php
|-index.php
|-waboot-sample.php
```

An example can be found [here](https://github.com/wagaweb/wbf-sample-plugin/tree/standard-structure-complex).

### Template plugin

Plugins that extends TemplatePlugin can inject their template into Wordpress "template_include" mechanism.

**Register common templates**

You can add a common wordpress template (selectable through admin dashboard) with:

```php
$this->add_template("Custom Page Template",$this->get_src_dir()."/templates/custom-page-template.php");
```

**Register hierarchy templates**

The hierarchy templates are loaded automatically. During 'init' the framework register any templates (not previously registered) under `/src/templates` as hierarchy template.
Then during 'template_include' it will serve any registered templates not overridden by the theme from the plugin directory.

You can manually register hierarchy template with:

```php
$this->add_hierarchy_template("single-sample-post-type.php", $this->get_src_dir()."/custom_hierarchy_templates/single-sample-post-type.php");
```

An example con be found [here](https://github.com/wagaweb/wbf-sample-plugin/tree/template-plugin-standard).

### More features

**Using built-in cache mechanism**

BasePlugin has a built-in caching feature based on transients.



**Adding custom links for the plugin in Wordpress plugins list**

From constructor of the class that extends BasePlugin:

```php
$this->add_action_links([
    [
        'name' => "New link"
        'link' => "/path/to/link"
    ],
    //more links
]);
```

**Setting up an update server**

From the constructor of the class that extends BasePlugin: [...]

```php
$this->set_update_server("my/end/point");
```

Endpoint must be compatible with CustomUpdater WBF component.

For more info about CustomUpdater WBF component: [click here](https://github.com/wagaweb/wbf/tree/master/src/components/customupdater).

**Register a license**

From the constructor of the class that extends BasePlugin:

```php
$license = new \WBF\components\license\License("my-license")
$this->register_license($license);
```

You can also link a license to the custom updater (the update will be blocked with invalid licenses)

```php
$license = new \WBF\components\license\License("my-license")
$this->set_update_server("my/end/point",$license);
```

For more info about License WBF component: [click here](https://github.com/wagaweb/wbf/tree/master/src/components/license).