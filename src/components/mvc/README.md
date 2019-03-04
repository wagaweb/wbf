# MVC
MVC paradigm for Wordpress templates.

This component provide the ability to effectively split code from templates in Wordpress.

## Usage of Models and Repositories

Assuming there is a 'Book' custom post type.

It is possible to write a model class like this:

```php
class Book extends \WBF\components\mvc\Model
{
    //...
}
```

And a repository class like this:

```php
class BookRepository extends \WBF\components\mvc\Repository
{
	public function __construct()
	{
		$this->setClassName(Book::class);
		$this->setPostType('book');
	}
}
```

And then use them:

```php
	$bookRepostory = new bookRepository();
	//Retrieve all Books
	$books = $bookRepostory->findAll(\WBF\components\mvc\Repository::FIND_ALL_OBJECT);
	//Retrieve all Books ids
	$books = $bookRepostory->findAll();
	
	//OR:
	$books = \WBF\components\mvc\Repository::get(Book::class)->findAll();
	
	//Find by WP_Query params:
	$books = $bookRepository->findByParams(['meta_query' => [...]]);
	
	//Returns a new \WP_Query...
	$bookQuery = $bookRepository->getQuery();
	//an array of WP_Query params can be passed to the function...
	$bookQuery = $bookRepository->getQuery(['orderby' => 'date']);
```

Default WP_Post repository is also supported:

```php
$postRepostory = \WBF\components\mvc\Repository::get(WP_Post::class);
$posts = $postRepository->findAll();
```

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
$v->display();
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
- You can easily cache the $vars array to boost performances. The same task can be tedious with a simple `require`.
- You can easily implement some other templating systems by extending View.

### Use dashboard page wrapper
```php
$v = new \WBF\components\mvc\HTMLView("path/to/template.php");
$v->for_dashboars()->display([
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