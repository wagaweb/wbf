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
$c = new components\compiler\Styles_Compiler($base_compiler); //This is a wrapper which rely on Base_Compiler implementation of $base_compiler.

$c->compile(); //Wraps the call to $base_compiler compile() method
```

The compile actions is more useful if hooked at some WordPress or user action.

## Sets

Base_Compiler provides `add_set()` and `remove_set()` functions and the `compile()` functions allows to specify a set name to compile. So new compilers have to be developed with this concept in mind.

A set has a name and some properties specified with an array.

This is, for example, a set valid for Less_Compiler:

```
[
    "input" => "...",
    "output" => "...",
    "map" => "...",
    "map_url" => "...",
    "cache" => "...",
    "import_url" => "...",
    "primary" => true
]
```

The number of properties is arbitrary.
