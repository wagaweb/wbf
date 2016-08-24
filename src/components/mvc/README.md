# MVC
MVC paradigm for Wordpress templates.

This component provide the ability to effectively split code from templates in Wordpress.

## Usage of HTMLView
HTMLView is the default view type provided by component. You can use is to display HTML templates.

- Create a new instance of HTMLView:
```php
$v = new \WBF\components\mvc\HTMLView("path/to/template.php");
```
- Assign some vars to the template and display it
```php
$vars = [
    'title' => "Hello World"
];
$v->clean()->display();
```
- In template.php you can:
```php
<?php echo $title; ?>
```

The View has some really interesting features:
- You can provide a path relative to the current theme or relative to a plugin. The template file will be enqueued in a template hierarchy array, so you can override the template file by placing a new template with the same name/path in a child theme. If the path is relative to a plugin, any similiar template found in child theme or in parent theme will override the original template.
```php
//If you are in a twentysixteen child called "foobar"...
$v = new \WBF\components\mvc\HTMLView("path/to/template.php");
/* Will looking for (in order):
/wp-content/themes/foobar/path/to/template.php
/wp-content/themes/twentysixteen/path/to/template.php
*/
```
- Without the `clean()` the template comes wrapped in a standard Wordpress admin page wrapper:
```html
<div class="wrap">
    Page Title
    ...
</div>
```
- You can easily cache the $vars array to boost performances. The same task can be tedious with a simple `require`.
- You can easily implement some other templating systems by extending View.

### Use default page wrapper
```php
$v = new \WBF\components\mvc\HTMLView("path/to/template.php");
$v->display([
    'page_title' => "My awesome title",
    'my_name' => "Maya"
]);
```
With template.php:
```php
Hello <?php echo $my_name ?>!
```
Will display:
```html
<div class="wrap">
    <h1>My awesome title</h1>
    Hello Maya!
</div>
```

### Use custom page wrapper
```php
$v = new \WBF\components\mvc\HTMLView("path/to/template.php");
$v->display([
    'page_title' => "My awesome title",
    'wrapper_class' => "my_wrap",
    'wrapper_el' => "section"
    'title_wrapper' => "<span>%s</span>"
    'my_name' => "Maya"
]);
```
With template.php:
```php
Hello <?php echo $my_name ?>!
```
Will display:
```html
<section class="my_wrap">
    <span>My awesome title</span>
    Hello Maya!
</section>
```