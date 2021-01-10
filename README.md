# PinkCrab Framework Core #

![alt text](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg?style=flat " ")

Welcome the main package of the PinkCrab Framwework. 

For more details please visit our docs.
https://app.gitbook.com/@glynn-quelch/s/pinkcrab/~/drafts/-MQX_cCgS6Q37RjFODUp/


## Version ##
**Release 0.3.0**

With version 0.3 we have moved away from the submodule driven approach and thanks to PHP Scoper we can now use actual composer libraries.

The Core only provides access to the Loader, Registration, Collection, DI (DICE Dependency Injection Container), App_Config and basic (native) PHP render engine for view.

## Why? ##
WordPress is powerful tool for building a wide range of website, but due to its age and commitment to backwards compatibility. Its often fustrating to work with using more modern tools. 

The PinkCrab Framework allows the creation of Plugins, Themes and MU Libraries for use on more complex websites.

## Setup ##

To use the Framework, a few files are needed for the framework to be loaded.
*bootstrap.php*
This file can be anywhere in your plugin, although we reccomend keeping it in your root directory with the plugin.php file.

````php
    <?php
    // @file bootstrap.php

    // Namespaces
    use PinkCrab\Core\Application\App;
    use PinkCrab\Core\Services\Dice\Dice;
    use PinkCrab\Core\Collection\Collection;
    use PinkCrab\Core\Services\Dice\WP_Dice;
    use PinkCrab\Core\Application\App_Config;
    use PinkCrab\Core\Services\Registration\Loader;
    use PinkCrab\Core\Services\ServiceContainer\Container;
    use PinkCrab\Core\Services\Registration\Register_Loader;

    $loader    = Loader::boot();
    $config    = new App_Config( require( 'config/settings.php' ) ); // Change if using custom path for config.
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
            $app->get( 'di' )->addRules(
                apply_filters( 'PinkCrab\\di_rules', require( 'config/dependencies.php' ) ) // Change if using custom path for config.
            );
            // Initalise all registerable classes.
            Register_Loader::initalise(
                $app,
                apply_filters( 'PinkCrab\\registration_rules', require( 'config/registration.php' ) ), // Change if using custom path for config.
                $loader
            );

            // Register Loader hooks.
            $loader->register_hooks();
        },
        1
    );

````
If you are planning to give all of your vendor libraries custom namespaces using Php Scoper (more details below), to use the new mapped namespaces.

Once you have your bootstrap file created, its just a case of hooking it up in your plugin.php file.

````php
    <?php
    // @file plugin.php
    
    /**
     * @wordpress-plugin
     * Plugin Name:     ##PLUGIN NAME##
     * Plugin URI:      ##YOUR URL##
     * Description:     ##YOUR PLUGIN DESC##
     * Version:         ##VERSION##
     * Author:          ##AUTHOR##
     * Author URI:      ##YOUR URL##
     * License:         GPL-2.0+
     * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
     * Text Domain:     ##TEXT DOMAIN##
     */



    if ( ! defined( 'ABSPATH' ) ) {
        die;
    }

    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/bootstrap.php';

    // Optional activation hooks
````

The framework requires 3 config files, these are usually placed in the /config directory, but can be placed elsewhere. If you do use these elsewhere, please upadate the paths in the bootstrap.php file.

### dependencies.php ###
````php
    <?php
    // @file config/dependencies.php

    /**
     * Handles all depenedency injection rules and config.
     *
     * @package Your Plugin
     * @author Awesome Devs <awesome.devs@rock.com>
     * @since 1.2.3
     */

    use PinkCrab\Core\Application\App;
    use PinkCrab\Core\Interfaces\Renderable;
    use PinkCrab\Core\Services\View\PHP_Engine;
    
    return array(
        // Gloabl Rules
        '*' => array(
            'substitutions' => array(
                App::class        => App::getInstance(),
                Renderable::class => PHP_Engine::class,
			    wpdb::class       => new wpdb( \DB_USER, \DB_PASSWORD, \DB_NAME, \DB_HOST ),
            ),
        ),
    );
````
### dependencies.php ###
````php
    <?php
    // @file config/dependencies.php
    declare(strict_types=1);


    /**
     * Holds all classes which are to be loaded on initalisation.
     *
     * @package Your Plugin
     * @author Awesome Devs <awesome.devs@rock.com>
     * @since 1.2.3
     */

    return array(
        /** Include all your classes which implemenet Registerable here */
    );
````
### settings.php ###
````php
    // @file config/settings.php
    <?php
    
    declare(strict_types=1);

    /**
     * Handles all the data used by App_Config
     *
     * @package Your Plugin
     * @author Awesome Devs <awesome.devs@rock.com>
     * @since 1.2.3
     */

    // Get the path of the plugin base.
    $base_path  = \dirname( __DIR__, 1 );
    $plugin_dir = \basename( $base_path );
    $wp_uploads = \wp_upload_dir();

    return array(
        'additional' => array(
            // Register your custom config data.
        ),

    );
````

## Testing ##

### PHP Unit ###
If you would like to run the tests for this package, please ensure you add your database details into the test/wp-config.php file before running phpunit.

### PHP Stan ###
The module comes with a pollyfill for all WP Functions, allowing for the testing of all core files. The current config omits the Dice file as this is not ours. To run the suite call.
````bash vendor/bin/phpstan analyse src/ -l7 ````

## Building ##
If you wish to use PHP Scoper to rebase the namespaces, to remove the risk of conflicts feel free. The Core has been tested and will run for other namespaces without too many issues. 

The only issues that soemtimes arise if the the namespacing of core wp functions. That can be avoided for this package by adding the following exclusions to your scoper.inc.php file.
````php
    <?php
        // Omit from remapping.
        // wp_upload_dir();    Used in settings.php
        // plugins_url();      Used in settings.php

        // ....
    	'patchers' => array(
		function ( $file_path, $prefix, $contents ) {
			// Your other functions to omit
                $contents = str_replace( "\\$prefix\\wp_upload_dir", '\\wp_upload_dir', $contents );
			$contents = str_replace( "\\$prefix\\plugins_url", '\\plugins_url', $contents );

            return $contents;
		},
        // .......
	),
````

## License ##

### MIT License ###
http://www.opensource.org/licenses/mit-license.html  
