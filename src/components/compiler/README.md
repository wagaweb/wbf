# Styles Compiler
An extensible style compiler.

By now it has a build-in Less compiler, but developers can implement new compilers thanks to Base_Compiler interface.

## Usage example for LESS files

```php
//Setup the base compiler:
$output_dir = "...."

$base_compiler = new WBF\components\compiler\less\Less_Compiler([
    "sets" => [
        "theme_frontend" => [
            "input" => get_stylesheet_directory()."/assets/src/less/input.less",
            "output" => $output_dir."/output.css",
            "map" => $output_dir."/output.css.map",
            "map_url" => $output_uri."/output.css.map",
            "cache" => get_stylesheet_directory()."/assets/cache",
            "import_url" => get_stylesheet_directory_uri(),
            "primary" => true //This is the primary set (by now this is just a label for further usage)
        ]
    ],
    "sources_path" => get_stylesheet_directory()."/assets/src/less/"    
]);

//Create a new instance of compiler
$c = new components\compiler\Styles_Compiler($base_compiler);

$c->compile();
```

The compile actions is more useful if hooked at some WordPress or user action.
