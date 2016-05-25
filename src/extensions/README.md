WBF Extensions
===================
Extensions provide a compatibility\enhancement layer to other plugins.

Extensions are loaded during `plugins_loaded` hook with a priority of `10`.

Every extension has a defined structure:

- Lives under `/wbf/extensions/<ext_name>` directory
- Has a `bootstrap.php` file

WBF will require `bootstrap.php` files of all enabled extensions on boot.

Developers can hook to `wbf/extensions/available` filter to alter the available extensions.