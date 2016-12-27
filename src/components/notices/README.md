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

## API

...



