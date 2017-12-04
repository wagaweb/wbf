# Plugins Options Module

This module allows WBF plugins to have their own standardized settings page. If any WBF plugins register it's settings to the module, a link to a general options page will be created under WBF menu.

## Create an option tab

Create a new class by extending `\WBF\modules\plugins_options\OptionsTab`.

```php
namespace MyPlugin\options;

class OptionsTab extends \WBF\modules\plugins_options\OptionsTab{
	public function render() {
		echo 'Hello World!';
	}
}
```

You can put the tab output (input fields and such into the `render()` method).

## Register a setting tab

Use the filter called: `wbf/modules/plugins_options/tabs` and add a new tab.

```php
	/**
	 * @param $tabs
	 *
	 * @hooked 'wbf/modules/plugins_options/tabs'
	 *
	 * @return array
	 */
	public function add_plugin_options($tabs){
		$tab = new MyPlugin\options\OptionsTab($this,'My Plugin'); //$this is the WBF\components\pluginsframework\BasePlugin instance
		$tabs[] = $tab;
		return $tabs;
	}
```

Now you have a fully functional settings page ready.

## Use the settings page

The module does not dictate on how you have to save or render the settings. It wraps your render() output in a `form` like this:

```html
<form method="post" action="http://waboot.dev/wp-admin/admin.php?page=wbf-plugins-options&amp;tab=waboot-woo-eu-taxation">
    <!-- You render() output -->
	<button style="display: block; margin-top: 10px;" class="button button-primary" type="submit">Save settings</button>
    <input type="hidden" name="save-wbf-plugins-settings" value="waboot-woo-eu-taxation">
    <!-- NONCE Example: The nonce action is: save-wbf-plugins-settings -->
    <input type="hidden" id="_wpnonce" name="_wpnonce" value="0bd9dd176c"><input type="hidden" name="_wp_http_referer" value="/wp-admin/admin.php?page=wbf-plugins-options">
</form>
```

So you can intercept the POST event and save the settings as you prefer.

Please note that [https://github.com/wagaweb/wbf/blob/master/src/components/pluginsframework/BasePlugin.php](`WBF\components\pluginsframework\BasePlugin`) provides a nice API to manage plugin setting.
