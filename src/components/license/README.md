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

## Lock theme updates with license

If you use the WBF Custom Updater, you can use an hook or two to lock the updates once your license is registered.

In your License constructor:

```php
add_filter("wbf/custom_theme_updater/can_update", [$this, "allow_updates"], 10, 2);
add_action("wbf/custom_theme_updater/after_update_inject", [$this, "show_update_notice"], 10, 2);
```

Then, in your License class:

```php
public function allow_updates($can_update, Theme_Update_Checker $checker){
    if(!$this->is_valid()){
        $can_update = false;
    }
    return $can_update;
}

public function show_update_notice(Theme_Update_Checker $checker, $can_update){
    if(!$can_update){
        $message = sprintf(__( 'A new version of %s is available! <a href="%s" title="Enter a valid license">Enter a valid license</a> to get latest updates.', 'wbf' ),"my-theme","admin.php?page=wbf_licenses");
        //Show notice whatever you like
        //...
    }
}
```

## Lock plugins updates with

For now WBF alone does not automatically binds plugins licenses to update mechanism. On the other hand Plugins Framework does it within the `set_update_server()` method.

[...]


