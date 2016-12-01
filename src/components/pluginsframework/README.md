#Plugins Framework
Plugins Framework component is an set of tools and extensible classes to help WordPress plugins developers.

## Getting started

You start the development of a new plugin either by extensing BasePlugin or TemplatePlugin. 

The latter makes available a set of methods to add theme-overridable templates to the plugin.

### Simple plugin

You can check out [here](#) a barebone plugin.

BasePlugin takes care of:

- Initializing useful vars like plugin_dir, plugin_path, plugin_relative_dir, ect...
- Setting up the localization domain
- Linking together a standard structure (see below)
- Storing actions and filters added by plugin

And provides a number of useful functions:

- A complete caching mechanism based on transients
- The ability to setup a custom update server
- and much more...

### Template plugin

...