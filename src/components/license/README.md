# License

A license manager for themes and plugins.

## Simple usage

You first need to create a license class by extending WBF\components\license\License and implementing WBF\components\license\License_Interface, then you can register this license with the License Manager:

```php

$l = new YourLicense();

\WBF\components\license\License_Manager::register_theme_license($l);

```

And that's it! WBF will detect your new license and allows user to enter their license code through the dashboard.

## Manage theme and plugin updates

Once your license is registered, WBF automatically binds it to the theme \ plugin custom update mechanism (provided by customupdater component). So updates will be blocked for invalid license owner.

