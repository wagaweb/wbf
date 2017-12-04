# Updates Channels Module

This module enables developers to define different update channels for their custom updaters.

## Register an update channel

Use `wbf/update_channels/available` filter.

```php
/**
 * Adds the My Theme Update channels
 *
 * @param $channels
 *
 * @return mixed
 */
function set_update_channels($channels){
    $channels['mytheme'] = [
        'name' => 'My Theme',
        'slug' => 'my_theme',
        'channels' => [
            'Stable' => 'stable',
            'Beta' => 'beta'
        ]
    ];
    return $channels;
}
add_filter('wbf/update_channels/available','set_update_channels');
```

Now you can find the channel selector in the WBF Status page.

## Use the channel

You can retrieve the selected channel with `\WBF\modules\update_channels\get_update_channel($name)` function.

```php
use function WBF\modules\update_channels\get_update_channel;
$channel = get_update_channel("my_theme");
//Do the update logic with $channel...
//...
```
