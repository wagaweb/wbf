# Notice Manager

An integrated notice manager for Wordpress; with WBF Notice Manager you can:

- Easily add \ remove notices without adding tons of hooks.
- Group notices into categories.
- Manage notices outside "admin_notice" hook.
- Define notice conditions that must be met before the notice can disappear.

## Usage

- Initialize a new Notice Manager instance (better in early hooks; any hooks before "admin_notices" - see WP admin-header.php is fine)

- Adds notices

- Call `enqueue_notices()` (this will add an hook to "admin_notices" to print all notices)

- Profit

If you use WBF as plugin, you can skip the initialization and use `WBF()->notice_manager` directly.

WBF Utilities component has some shortcuts:

- `Utilities::add_admin_notice($id,$message,$level,$args = [])`  
Adds a notices with specified params.

- `Utilities::add_admin_notice($message,$level)` 
Adds a flash notice (see below)

### Automatic, flash and manual notices

The default behavior for `add_notice()` is to add an **automatic notice**. Automatic notices are hooked to "admin_notices" action and are displayed automatically by WordPress.

**Manual notices** can be displayed at any time by calling `show_manual_notices()`. They are useful if you want to display notices in other place than the top of the single admin page.

You can add a manual notice by passing `true` as the last argument of `add_notice()` (see below).

**Flash notices** are notices added with `_flash_` as category. Flash notices will be removed after the first display.

## Essential API

- Adding a notice

```php
/**
 * Add a new notice to the system
 *
 * @param String $id an unique identifier
 * @param String $message the notice content
 * @param String $level (can be: "updated","error","nag"
 * @param String $category (can be anything. Categories are used to group notices for easy clearing them later. If the category is set to "_flash_", however, the notice will be cleared after displaying.
 * @param null|String $condition a class name that implements Condition interface
 * @param null|mixed $cond_args parameters to pass to $condition constructor
 * @param bool $manual_display if TRUE, the notice will not be displayed at "admin_notices" hook.
 */
add_notice($id,$message,$level,$category = 'base', $condition = null, $cond_args = null, $manual_display = false)
```  

- Remove a notice

```php
/**
 * Remove a notice
 * 
 * @param String $id the notice identifier
 */
remove_notice($id)
```

- Clear notice

```php
/**
 * Clear notices
 *
 * @param String $category clear the notices of the specified category
 */
clear_notices($category = null)
```

## Notice conditions

Normally you can add a notice and remove it manually if something happens. `WBF\components\notices\conditions\Condition` interface allows you to abstract this behavior in a class.

If you provide to `add_notice()` a class name of a class that implements this interface, the Notice_Manager will calls the `verify()` method automatically, removing the notices which return a `true` value.

Let's say you want to display a notice unless a certain option has a specified value.

```php
class OptionHasValue implements \WBF\components\notices\conditions\Condition{
    public function verify(){
        $opt = get_option("my-option","undesidered value")
        if($opt == "desidered value"){
            return true;
        }
        return false;
    }    
}

WBF()->notice_manager->("test","Test content","error","base", "OptionHasValue");
```

For a more general purpose class, you can use the `$cond_args` param of `add_notice()` to customize the Condition instance upon initialization.

```php
class OptionHasValue implements \WBF\components\notices\conditions\Condition{
    var $opt_name = "";
    var $desidered_value = "";
    public function __construct($args){
        $this->opt_name = $args['opt_name'];
        $this->desidered_value = $args['desidered_value'];
    }
    public function verify(){
        $opt = get_option($opt_name)
        if($opt == $this->desidered_value){
            return true;
        }
        return false;
    }    
}

WBF()->notice_manager->add_notice("test","Test content","error","base", "OptionHasValue",['opt_name' => 'foo', 'desidered_value' => 'bar']);
```