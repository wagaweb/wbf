WBF Modules
===================
Modules implements framework main functionalities.

Modules are loaded during `after_setup_theme` hook with a priority of `10`.

Every module has a defined structure:

- Lives under `/wbf/modules/<md_name>` directory
- Has a `bootstrap.php` file
- Can have an `activation.php` file
- Can have a `deactivation.php` file

WBF will require `bootstrap.php` files of all enabled modules during boot.

`activation.php` and `deactivation.php` files will be required during WBF activation\deactivation.

Developers can hook to `wbf/modules/available` filter to alter the available modules.

Available modules can be read programmatically via: `WBF::getInstance()->modules`.

Functions
---------

`WBF::module_is_loaded($module_name)`
   
Checks if $module_name is loaded.