#Behaviors Module

This module enables developers to have custom metaboxes for post types with default values specified within theme options interface. 
Let's say, for example, that you want to set an option to let user decide whatever or not display the site sidebar, both site-wide and post-specific. You then can implement a very complicated theme option... or use a behavior!

##Defining behaviors
You can define new options by hooking to `wbf/modules/behaviors/available` filter from within theme `functions.php`.

Then you can proceed to specify yours behaviors.

```php
add_filter('wbf/modules/behaviors/available', function($behaviors){
    
    $behaviors[] = [
        "name" => "my_name",
        "title" => __("Display sidebar","my_textdomain"),
        "desc" => __("Default rendering value for sidebar","my_textdomain"),
        "options" => [
            [
                "name" => __("Yes"),
                "value" => 1
            ],
            [
                "name" => __("No"),
                "value" => 0
            ]
        ],
        "type" => "select",
        "default" => 1,
        "valid" => [...]
    ];
    
    [...]
    
    return $behaviors;
});
```

##Behaviors specifications

Every behavior must have at least a name and a type.

##Available types

In future releases the behaviors types will be aligned with Theme Options types. By now you can choose between: text, textarea, checkbox, multicheck, radio, images, select.

###Samples

```php
/**
 * TEXT
 */
$behaviors[] = array(
    "name" => "testinput",
    "title" => "Test Input",
    "desc" => "This is a test input",
    "type" => "text",
    "default" => "testme!",
    "valid" => array("post","page")
);

/**
 * TEXTAREA
 */
$behaviors[] = array(
    "name" => "testarea",
    "title" => "Test Input",
    "desc" => "This is a test textarea",
    "type" => "textarea",
    "default" => "testme!",
    "valid" => array("post","page")
);

/**
 * SINGLE CHECKBOX
 */
$behaviors[] = array(
    "name" => "testcheck",
    "title" => "Test Checkboxes",
    "desc" => "This is a test checkbox",
    "type" => "checkbox",
    "default" => "1",
    "valid" => array("post","page")
);

/**
 * MULTIPLE CHECKBOX
 */
$behaviors[] = array(
    "name" => "testmulticheck",
    "title" => "Test Checkboxes",
    "desc" => "This is a test checkbox",
    "type" => "checkbox",
    "options" => array(
        array(
            "name" => "test1",
            "value" => "test1"
        ),
        array(
            "name" => "test2",
            "value" => "test2"
        ),
    ),
    "default" => "test1",
    "valid" => array("post","page")
);

/**
 * RADIO
 */
$behaviors[] = array(
    "name" => "testradio",
    "title" => "Test Radio",
    "desc" => "This is a test radio",
    "type" => "radio",
    "options" => array(
        array(
            "name" => "test1",
            "value" => "test1"
        ),
        array(
            "name" => "test2",
            "value" => "test2"
        ),
    ),
    "default" => "test2",
    "valid" => array("post","page")
);

/**
 * IMAGES
 */
$behaviors[] = [
    "name" => "testradio",
    "title" => "Test Images",
    "desc" => "This is a test images",
    "type" => "images",
    "options" => [
        [
            "name" => "test1",
            "thumb" => "test1",
            "value" => "test1"
        ],
        [
            "name" => "test2",
            "thumb" => "test2",
            "value" => "test2"
        ],
    ],
    "default" => "test2",
    "valid" => ["post","page"]
];

/**
 * SELECT
 */
$behaviors[] = [
    "name" => "show-title",
    "title" => "This Select",
    "desc" => "This is a test select",
    "options" => [
        [
            "name" => __("Yes"),
            "value" => 1
        ],
        [
            "name" => __("No"),
            "value" => 0
        ]
    ],
    "type" => "select",
    "default" => 1,
    "valid" => ["post","page"]
];
```
    
### The 'valid' property

This property can be used to specify where the metabox relative to the behavior have to display itself or its value must be interpreted. It is an array of filter. Available filters:

- "post": visible \ interpreted for posts

- "page": visible \ interpreted for pages

- "{cpt}": visible \ interpreted for all custom post types

- "{blog}": visible \ interpreted for blog page

- "{ctag:< tag name >}": visibility specified by a conditional tag

    The tag must be a function name which must return a boolean. If TRUE, the metabox is shown or its value is interpreted.
    
    The id of the current node is passed as param to the speciefied function.
    
    Eg: `"valid" => ["post","page","{ctag:is_shop}"]`

- < post_id >: visible only for post of specified id

- < post_type >: visible for specified post type

