# PinkCrab Enqueue #

[![Latest Stable Version](http://poser.pugx.org/pinkcrab/pinkcrab/enqueue/v)](https://packagist.org/packages/pinkcrab/pinkcrab/enqueue)
[![Total Downloads](http://poser.pugx.org/pinkcrab/pinkcrab/enqueue/downloads)](https://packagist.org/packages/pinkcrab/pinkcrab/enqueue) 
[![License](http://poser.pugx.org/pinkcrab/pinkcrab/enqueue/license)](https://packagist.org/packages/pinkcrab/pinkcrab/enqueue)
[![PHP Version Require](http://poser.pugx.org/pinkcrab/pinkcrab/enqueue/require/php)](https://packagist.org/packages/pinkcrab/pinkcrab/enqueue)
![GitHub contributors](https://img.shields.io/github/contributors/Pink-Crab/Enqueue?label=Contributors)
![GitHub issues](https://img.shields.io/github/issues-raw/Pink-Crab/Enqueue)
[![WP5.9 Test Suite [PHP7.2-8.1]](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_5_9.yaml/badge.svg)](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_5_9.yaml)[![WP6.0 Test Suite [PHP7.2-8.1]](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_0.yaml/badge.svg)](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_0.yaml)[![WP6.1 [PHP7.2-8.1] Tests](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_1.yaml/badge.svg)](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_1.yaml)
[![codecov](https://codecov.io/gh/Pink-Crab/Enqueue/branch/master/graph/badge.svg?token=9O27LAKVWI)](https://codecov.io/gh/Pink-Crab/Enqueue) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Pink-Crab/Enqueue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Pink-Crab/Enqueue/?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/cbf72c7619f39ea64d2b/maintainability)](https://codeclimate.com/github/Pink-Crab/Enqueue/maintainability)


The PinkCrab Enqueue class allows for a clean and fluent alternative for enqueuing scripts and styles in WordPress.

To install 
```bash
composer require pinkcrab/enqueue
```

## Version ##
**Release 1.3.0**



```php
<?php 
add_action('wp_enqueue_scripts', function(){
    
    // Enqueue a script
    Enqueue::script('My_Script')
        ->src('https://url.tld/wp-content/plugins/my_plugn/assets/js/my-script.js')
        ->deps('jquery')
        ->latest_version()
        ->register();
    
    // Enqueue a stylesheet
    Enqueue::style('My_Stylesheet')
        ->src('https://url.tld/wp-content/plugins/my_plugn/assets/css/my-stylesheet.css')
        ->media('all and (min-width: 1200px)')
        ->latest_version()
        ->register();
});

```
The above examples would enqueue the script and stylesheet using wp_enqueue_script() and wp_enqueue_style()


## Features ##

### Instantiation of \PinkCrab\Enqueue::class ###

You have 2 options when creating an instance of the Enqueue object.

```php
<?php

$enqueue_script = new Enqueue( 'my_script', 'script');
$enqueue_style = new Enqueue( 'my_style', 'style');

// OR 

$enqueue_script = Enqueue::script('my_script');
$enqueue_style = Enqueue::style('my_style');

```
When you call using the static methods script() or style(), the current instance is returned, allowing for chaining into a single call. Rather than doing it in the more verbose methods.

```php
<?php

$enqueue_script = new Enqueue( 'my_script', 'script');
$enqueue_script->src('.....');
$enqueue_script->register();

// OR 

Enqueue::script('my_script')
    ->src('.....')
    ->register();
```

### File Location ###

The URI to the defined js or css file can be defined here. This must be passed as a url and not the file path.

*This is the same for both styles and scripts*

```php
<?php
Enqueue::script('my_script')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-script.js')
    ->register();
```

### Version  ###

Like the underlying wp_enqueue_script() and wp_enqueue_style() function, we can define a verison number to our scripts. This can be done using the ver('1.2.2') method.

*This is the same for both styles and scripts*

```php
<?php
Enqueue::script('my_script')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-script.js')
    ->ver('1.2.2') // Set to your current version number.
    ->register();
```
However this can be frustrating while developing, so rather than using the current timestamp as a temp version. You can use the *latest_version()*, this grabs the last modified date from the defined script or style sheet, allowing reducing the frustrations of caching during development. While this is really handy during development, it should be changed to **->ver('1.2.2')** when used in production.

*This is the same for both styles and scripts*

```php
<?php
Enqueue::script('my_script')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-script.js')
    ->latest_version() 
```

### Dependencies  ###
As with all wp_enqueue_script() and wp_enqueue_style() function, required dependencies can be called. This allows for your scripts and styles to be called in order.

*This is the same for both styles and scripts*

```php
<?php
Enqueue::script('my_script')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-script.js')
    ->deps('jquery') // Only enqueued after jQuery.
```


### Localized Values  ###
One of the most useful parts of of enqueuing scripts in WordPress is passing values form the server to your javascript files. Where as using the regular functions, this requires registering the style, localizing your data and then registering the script. While it works perfectly fine, it can be a bit on the verbose side. 

The localize() method allows this all to be done within the single call.

*This can only be called for scripts*

```php
<?php
Enqueue::script('MyScriptHandle')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-script.js')
    ->localize([ 
        'key1' => 'value1', 
        'key2' => 'value2', 
    ])
    ->register();
```
Usage within js file (my-script.js)
```js
console.log(MyScriptHandle.key1) // value1
console.log(MyScriptHandle.key2) // value2
```

### Footer  ###
By default all scripts are enqueued in the footer, but this can be changed if it needs to be called in the head. By calling either *footer(false)* or *header()*

*This can only be called for scripts*

```php
<?php
Enqueue::script('my_script')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-script.js')
    ->footer(false)
    ->register();
// OR 
Enqueue::script('my_script')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-script.js')
    ->header()
    ->register();

```
### Media  ###
As with wp_enqueue_style() you can specify the media for which the sheet is defined for. Accepts all the same values as wp_enqueue_style()

*This can only be called for styles*

```php
<?php
Enqueue::style('my_style')
    ->src(PLUGIN_BASE_URL . 'assets/js/my-style.css')
    ->media('(orientation: portrait)')
    ->register();

```
### Attributes ###
It is possible (since v1.2.0) to add attributes and flags (value free attributes) to either script or style tags.
```php
<?php
Enqueue::style('my_style')
    ->src('http://www.site.com/my-style.css')
    ->attribute('key', 'value')
    ->register();

// Rendered as
// <link href="[.css/bootstrap.min.css](http://www.site.com/my-style.css)" rel="stylesheet" type="text/css" key="value">
```
or

```php
<?php
Enqueue::script('my_style')
    ->src('http://www.site.com/my-scripts.js')
    ->flag('key')
    ->register();

// Rendered as
// <script src="http://www.site.com/my-scripts.js" key type="text/javascript"></script>
```
### Async & Defer ###
There is also some shortcuts for making any script or style be deferred or async tagged.
```php
<?php
Enqueue::style('my_style')
    ->src('http://www.site.com/my-style.css')
    ->async()
    ->register();

// Rendered as
// <link href="http://www.site.com/my-style.css" rel="stylesheet" type="text/css" async="">
```
or

```php
<?php
Enqueue::script('my_style')
    ->src('http://www.site.com/my-scripts.js')
    ->defer()
    ->register();

// Rendered as
// <script src="http://www.site.com/my-scripts.js" defer="" type="text/javascript"></script>
```

### Registration  ###
Once your Enqueue object has been populated all you need to call **register()** for wp_enqueue_script() or wp_enqueue_style() to be called. You can either do all this inline (like the first example) or the Enqueue object can be populated and only called when required.

*This is the same for both styles and scripts*

```php
<?php
class My_Thingy{
    /**
     * Returns a partly finalised Enqueue scripts, with defined url.
     * 
     * @param string $script The file location.
     * @return Enqueue The populated enqueue object.
     */ 
    protected function enqueue($script): Enqueue {
        return Enqueue::script('My_Script')
            ->src($script)
            ->deps('jquery')
            ->latest_version();
    } 

    /**
     * Called to initialize the class.
     * Registers our JS based on a conditional.
     * 
     * @return void
     */
    public function init(): void {
        if(some_conditional()){
            add_action('wp_enqueue_scripts', function(){
                $this->enqueue(SOME_FILE_LOCATION_CONSTANT)->register()
            });
        } else {
            add_action('wp_enqueue_scripts', function(){
                $this->enqueue(SOMEOTHER_FILE_LOCATION_CONSTANT)->register()
            });
        }
    }
}

add_action('wp_loaded', [new My_Thingy, 'init']);
```

## Gutenberg ##

When registering scripts and styles for use with Gutenberg blocks, it is necessary to only register the assets before `wp_enqueue_scripts` hook is called. To do this, all you need to is set `for_block()`.

```php
add_action('init', function(){
    Enqueue::script('my_style')
        ->src('http://www.site.com/my-scripts.js')
        ->defer()
        ->for_block()
        ->register();

    // Register block as normal
});
```

## Public Methods

```php
/**
  * Creates an Enqueue instance.
  *
  * @param string $handle
  * @param string $type
  */
 public function __construct( string $handle, string $type )

/**
  * Creates a static instance of the Enqueue class for a script.
  *
  * @param string $handle
  * @return self
  */
 public static function script( string $handle ): self

/**
  * Creates a static instance of the Enqueue class for a style.
  *
  * @param string $handle
  * @return self
  */
 public static function style( string $handle ): self

/**
  * Defined the SRC of the file.
  *
  * @param string $src
  * @return self
  */
 public function src( string $src ): self

/**
  * Defined the Dependencies of the enqueue.
  *
  * @param string ...$deps
  * @return self
  */
 public function deps( string ...$deps ): self

/**
  * Defined the version of the enqueue
  *
  * @param string $ver
  * @return self
  */
 public function ver( string $ver ): self

/**
  * Define the media type.
  *
  * @param string $media
  * @return self
  */
 public function media( string $media ): self

/**
  * Sets the version as last modified file time.
  *
  * @return self
  */
 public function lastEditedVersion(): self

/**
  * Should the script be called in the footer.
  *
  * @param boolean $footer
  * @return self
  */
 public function footer( bool $footer = true ): self

/**
  * Should the script be called in the inline.
  *
  * @param boolean $inline
  * @return self
  */
 public function inline( bool $inline = true ):self

/**
  * Pass any key => value pairs to be localised with the enqueue.
  *
  * @param array $args
  * @return self
  */
 public function localize( array $args ): self

/**
  * Adds a Flag (attribute with no value) to a script/style tag
  *
  * @param string $flag
  * @return self
  */
 public function flag( string $flag ): self 

/**
  * Adds an attribute tto a script/style tag
  *
  * @param string $key
  * @param string $value
  * @return self
  */
 public function attribute( string $key, string $value ): self 

/**
  * Marks the script or style as deferred loaded.
  *
  * @return self
  */
 public function defer(): self 

/**
  * Marks the script or style as async loaded.
  *
  * @return self
  */
 public function async(): self 

/**
 * Set denotes the script type.
 *
 * @param string $script_type  Denotes the script type.
 * @return self
 */
public function script_type( string $script_type ): self

/**
  * Set if being enqueued for a block.
  *
  * @param bool $for_block Denotes if being enqueued for a block.
  * @return self
  */
 public function for_block( bool $for_block = true ): self

/**
  * Registers the file as either enqueued or inline parsed.
  *
  * @return void
  */
 public function register(): void

```
This obviously can be passed around between different classes/functions

### Changelog ###
* 1.3.0 - Updated for php8, includes setting of custom script types, renamed lastest_version() to latest_version() and set deprecation notice.
* 1.2.1 : Now supports block use. If defined for block, scripts and styles will only be registered, not enqueued.
* 1.2.0 : Added in Attribute and Flag support with helpers for Aysnc and Defer 

### Contributions  ###
If you would like to make any suggestions or contributions to this little class, please feel free to submit a pull request or reach out to speak to me. at glynn@pinkcrab.co.uk.

### WordPress Core Functions ###
This package uses the following wp core functions. To use PHP Scoper, please add the following functions.
['wp_enqueue_style', 'wp_register_script', 'wp_add_inline_script', 'wp_localize_script', 'wp_enqueue_script']
