# Custom Updater
This component enable developers to update themes and plugins from custom servers.

## Custom Theme Updater

Thanks to `Theme_Updater_Checker` class it is possible to update theme through custom endpoint.

### Getting started

Before implementing the update mechanism in WordPress you need an endpoint which respond with data in json format. That JSON must contain at least four information:

- `theme`: the theme slug
- `version`: the theme version
- `download_url`: the url of the theme package (a zip file
- `details_url`: the url of the theme changelog \ additional information

After this is inital setup, the implementation is just an one-liner:

```php
/**
 * Manage Update Server
 */
function set_update_server(){
	$tup = new Theme_Update_Checker("my-theme","my-endpoint");
}
add_action("init", "set_update_server");
```

#### Block updates

It is possible to block updates (if you sell licenses for example) by hooking at `"wbf/custom_theme_updater/can_update"`

```php
add_filter("wbf/custom_theme_updater/can_update", "disallow_updates", 10, 2);
function disallow_updates($can_update, Theme_Update_Checker $checker){
    if(<some_reason>){
        $can_update = false;
    }
    return $can_update;
}
```

By this way the update notice will still be visible, but the update process will return an error.

You could show a notice to better explain this error:

```php
add_action("wbf/custom_theme_updater/after_update_inject", "update_notice"], 10, 2);
function update_notice(Theme_Update_Checker $checker, $can_update){
    if(!$can_update){
        //Display the notice...
    }
}
```

