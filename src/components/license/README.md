# License

A license manager for themes and plugins.

## Simple usage

You first need to create a license class by extending WBF\components\license\License and implementing WBF\components\license\License_Interface, then you can register this license with the License Manager:

```php

$l = new YourLicense();

\WBF\components\license\License_Manager::register_theme_license($l);

```

And that's it! WBF will detect your new license and allows user to enter their license code through the dashboard.

## Creating dashboard interface

If you are using WBF as plugin the admin interface will be automatically created for you. If this is not the case you have to do it yourself.

You can look at the WBF implementation in License_Manager class:

`perform_page_actions()` => saves input data
`admin_license_menu_item()` => adds the menu item
`license_page()` => display the admin page.

## Manage theme updates

Once your license is registered, WBF automatically binds it to the theme custom update mechanism (provided by customupdater component). So updates will be blocked for invalid license owner.

This behavior will be changed in the future to allow more flexibility.

## Manage plugins updates

For now you have WBF does not automatically binds plugins licenses to update mechanism.

TBD



