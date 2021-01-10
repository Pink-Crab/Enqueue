<?php

use PinkCrab\Core\Application\App;
use PinkCrab\Core\Services\Dice\Dice;
use PinkCrab\Core\Services\Dice\WP_Dice;
use PinkCrab\Core\Application\App_Config;
use PinkCrab\Core\Services\Registration\Loader;
use PinkCrab\Core\Services\ServiceContainer\Container;
use PinkCrab\Core\Services\Registration\Register_Loader;
/**
 * PHPUnit bootstrap file
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function() {
		// test set up, plugin activation, etc.
		// require dirname( __DIR__ ) . '/example-plugin.php';

		// Initialise the core.
		$loader    = Loader::boot();
		$config    = new App_Config( array() );
		$container = new Container();

		// Setup the service container .
		$container->set( 'di', WP_Dice::constructWith( new Dice() ) );
		$container->set( 'config', $config );

		// Boot the app.
		$app = App::init( $container );

		// Add all DI rules and register the actions from loader.
		add_action(
			'init',
			function () use ( $loader, $app, $config ) {

				// Add all DI rules.
				$app->get( 'di' )->addRules( array() );
				// Initalise all registerable classes.
				Register_Loader::initalise( $app, array(), $loader );

				// Register Loader hooks.
				$loader->register_hooks();
			},
			1
		);
	}
);

// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
