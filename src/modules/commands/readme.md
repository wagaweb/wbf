# Commands Module

This module enables developers to develop custom cli commands. It is a wrapper of the WordPress CLI API.

## Define a new command
A new command can be defined in two ways:

1. By creating a new class file under `inc/cli` directory in your theme.
2. By hooking to `wbf/commands/registered` filter.

### Create a new command class
You can create a command by extending `\WP_CLI_Command` or `\WBF\modules\commands\BaseCommand`. The latter offer a more object oriented API to define commands args and description and implements some new functionality.

For example, put the following lines in a file called `MyHelloWorld.php` under `/wp-content/themes/yourtheme/inc/cli`.

```php
use WBF\modules\commands\BaseCommand;

class MyHelloWorld extends BaseCommand{
	public function configure() {
		$this->set_name("foobar:hello-world");
		$this->set_shortdesc('This command prints hello world');
	}
}
```

Now you can call `wp foobar:hello-world` from the command line to get a simple `Command ready` message.

To customize the command behavior you can override the `__invoke()` function of `\WBF\modules\commands\BaseCommand`.

```php
use WBF\modules\commands\BaseCommand;

class MyHelloWorld extends BaseCommand{
	public function configure() {
		$this->set_name("foobar:hello-world");
		$this->set_shortdesc('This command prints hello world');
	}
	
    public function __invoke( $args, $assoc_args ) {
        \WP_CLI::success('Hello World!');
    }
}
```

To make the command accepts parameters, you can specify them in the `configure()` method.

```php
use WBF\modules\commands\BaseCommand;

class MyHelloWorld extends BaseCommand{
	public function configure() {
		$this->set_name("foobar:my-hello-world");
		$this->set_shortdesc('This command prints hello world');
		$this->add_arg('message',self::ARG_TYPE_POSITIONAL);
	}

	public function __invoke( $args, $assoc_args ) {
		\WP_CLI::success($args[0]);
	}
}

//Call this command with: "wp foobar:my-hello-world Hola!"
```

You can use `get_cli_value()` to ask for inputs.

```php
use WBF\modules\commands\BaseCommand;

class MyHelloWorld extends BaseCommand{
	public function configure() {
		$this->set_name("foobar:my-hello-world");
		$this->set_shortdesc('This command prints hello world');
	}

	public function __invoke( $args, $assoc_args ) {
		$message = $this->get_cli_value('What I should repeat?');
		\WP_CLI::success($message);
	}
}
```
