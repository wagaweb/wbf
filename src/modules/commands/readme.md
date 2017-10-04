# Commands Module

This module enables developers to develop custom cli commands. It is a wrapper of the [WordPress CLI API](http://wp-cli.org/).

## Define a new command
A new command can be defined in two ways:

1. By creating a new class file under `inc/cli` directory in your theme.
2. By hooking to `wbf/commands/registered` filter and define the command with a callable.

### Create a new command with a class
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

If you do not want to use `\WBF\modules\commands\BaseCommand` you can simply extend `\WP_CLI_Command` and code your command as you normally would do with standard [WP CLI API](https://make.wordpress.org/cli/handbook/commands-cookbook/).

### Create a new command with a callable

You can define a new command with a callable by hooking to `wbf/commands/registered`:

```php
add_filter('wbf/commands/registered', function($registered_commands){
	$registered_commands[] = [
		'type' => 'callable',
		'name' => 'MyCallableCmd',
		'runner' => function(){
			WP_CLI::success('Hello World!');
		}
	];
	return $registered_commands;
});

//Or:

add_filter('wbf/commands/registered', function($registered_commands){
	$registered_commands[] = [
		'type' => 'callable',
		'name' => 'MyCallableCmd',
		'runner' => function($args){
			WP_CLI::success('Hello: '.$args[0].'!');
		}
	];
	return $registered_commands;
});
```

You can refer to the [official commands cookbook](https://make.wordpress.org/cli/handbook/commands-cookbook/) for more information about defining commands this way.