# Assets Manager
A simple Assets Manager tailored for Wordpress.

It as some interesting features:
- If a path is provided, the asset (js or css) will be enqueued only if the file exists, so you get no 404.
- If a path is provided, the Assets Manager will use `filemtime()` as asset version. This will prevent browser to serve a cached version of the asset if the file was modified.
- You can provide a callback for each asset to do additional checks before deciding whether enqueue the asset or not.

## Usage
- Create an array of assets:
```php
$libs = [
    "my-style" => [
        'uri' => "...",
        'type' => 'css',
    ],
    "my-js" => [
        'uri' => "...",
        'path' => "...",
        'type' => 'js',
        'enqueue' => false
    ]
];
```
- Instance a new object and enqueue
```php
$a = new AssetsManager($libs);
$a->enqueue();
```
You can pass a number of arguments to AssetsManager constructor:
```php
[
	'uri' => '', //A valid uri
	'path' => '', //A valid path
	'version' => false, //If FALSE, the filemtime will be used (if path is set)
	'deps' => [], //Dependencies
	'i10n' => [], //the Localication array for wp_localize_script
	'type' => '', //js or css
	'enqueue_callback' => false, //A valid callable that must be return true or false
	'in_footer' => false, //Used for scripts
	'enqueue' => true //If FALSE the script\css will only be registered
]
```
### Localization
You can use `i10n` key to pass an array for `wp_localize_script()`.
See [wp_localize_script()](https://codex.wordpress.org/Function_Reference/wp_localize_script) for further informations.
```php
$libs = [
    'my-js' => [
    	'i10n' => [
    	    'name' => myValue
    	    'params' => [
    	        'foo' => 'bar',
    	        'baz' => true
    	    ]
    	]
	]
]
```
Is equivalent to
```php
wp_localize_script( 'my-js', 'myValue', [
    'foo' => 'bar',
    'baz' => true
]);
```