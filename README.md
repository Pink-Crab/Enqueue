# PinkCrab Enqueue #

[![Latest Stable Version](https://poser.pugx.org/pinkcrab/enqueue/v)](https://packagist.org/packages/pinkcrab/enqueue)
[![Total Downloads](https://poser.pugx.org/pinkcrab/enqueue/downloads)](https://packagist.org/packages/pinkcrab/enqueue)
[![License](https://poser.pugx.org/pinkcrab/enqueue/license)](https://packagist.org/packages/pinkcrab/enqueue)
[![PHP Version Require](https://poser.pugx.org/pinkcrab/enqueue/require/php)](https://packagist.org/packages/pinkcrab/enqueue)
![GitHub contributors](https://img.shields.io/github/contributors/Pink-Crab/Enqueue?label=Contributors)
![GitHub issues](https://img.shields.io/github/issues-raw/Pink-Crab/Enqueue)

[![WP 6.6 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_6.yaml/badge.svg)](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_6.yaml)
[![WP 6.7 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_7.yaml/badge.svg)](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_7.yaml)
[![WP 6.8 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_8.yaml/badge.svg)](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_8.yaml)
[![WP 6.9 [PHP8.0-8.4] Tests](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_9.yaml/badge.svg)](https://github.com/Pink-Crab/Enqueue/actions/workflows/WP_6_9.yaml)

[![codecov](https://codecov.io/gh/Pink-Crab/Enqueue/branch/master/graph/badge.svg?token=9O27LAKVWI)](https://codecov.io/gh/Pink-Crab/Enqueue)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Pink-Crab/Enqueue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Pink-Crab/Enqueue/?branch=master)


The PinkCrab Enqueue class allows for a clean and fluent alternative for enqueuing scripts and styles in WordPress.

To install 
```bash
composer require pinkcrab/enqueue
```

## Version ##
**Release 1.5.0**



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

Scripts can be marked as `async` or `defer`. As of **v1.5.0** these forward to
WordPress's `wp_script_add_data( $handle, 'strategy', … )` API (WP 6.3+), which
is dependency-chain aware — a deferred script waits for deferred deps to
evaluate in order, rather than racing them via an unordered HTML attribute.

```php
<?php
Enqueue::script('my_script')
    ->src('http://www.site.com/my-scripts.js')
    ->defer()
    ->register();

// Rendered as (WP decides the exact attribute form)
// <script src="http://www.site.com/my-scripts.js?ver=..." id="my_script-js" defer></script>
```

```php
<?php
Enqueue::script('my_script')
    ->src('http://www.site.com/my-scripts.js')
    ->async()
    ->register();
```

Calling `defer()` then `async()` (or vice versa) overwrites the strategy — a
script can only have one. `defer()` / `async()` on a **style** is a no-op
(style tags have no strategy concept).

### Script Translations ###

Register the handle for i18n via `wp_set_script_translations()` so strings
loaded with `wp-i18n` resolve from a `.json` file in the given path.

```php
<?php
Enqueue::script('my-admin-app')
    ->src(PLUGIN_BASE_URL . 'assets/js/admin-app.js')
    ->translations('my-text-domain', PLUGIN_BASE_PATH . 'languages')
    ->register();
```

The path is optional — WP falls back to its language directory when omitted.
Only meaningful on scripts; silent no-op on styles.

### Inline Snippets ###

Append arbitrary inline JavaScript to the registered script handle via
`with_code()`, which wraps `wp_add_inline_script()`. Multiple calls stack, and
the optional second argument is the position (`'before'` or `'after'`,
defaulting to `'after'`).

This is **different** from the existing `inline()` method, which replaces
the `src` with the whole-file contents and inlines them. `with_code()` keeps
the script loading normally from its URL and attaches short snippets around it.

```php
<?php
Enqueue::script('my-app')
    ->src(PLUGIN_BASE_URL . 'assets/js/app.js')
    ->with_code('window.PC_CONFIG = { apiKey: "...", nonce: "..." };', 'before')
    ->with_code('window.PC_APP.boot();')  // runs after the script has loaded
    ->register();
```

### Inline Styles ###

Same idea for styles — `with_style()` attaches inline CSS to a registered
style handle via `wp_add_inline_style()`.

```php
<?php
Enqueue::style('my-theme-style')
    ->src(PLUGIN_BASE_URL . 'assets/css/theme.css')
    ->with_style(':root { --brand: #f06; }')
    ->with_style('.block-editor .is-root-container { padding: 1rem; }')
    ->register();
```

### Register Only ###

Register the asset with WP without enqueueing it. Useful when another script
will reference this one via a dependency array, or when a block's `block.json`
names the handle — the asset is available but doesn't emit on every page load.

```php
<?php
// Registered, not enqueued — available as a dep for other scripts.
Enqueue::script('my-lib')
    ->src(PLUGIN_BASE_URL . 'assets/js/lib.js')
    ->register_only()
    ->register();

// Later, another script can depend on it:
Enqueue::script('my-app')
    ->src(PLUGIN_BASE_URL . 'assets/js/app.js')
    ->deps('my-lib')
    ->register();
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

## Gutenberg / Block Editor ##

### for_block ###

When registering scripts and styles for use with Gutenberg blocks declared in
PHP, it is necessary to only register the assets — the block itself handles
the enqueue. `for_block()` flags the Enqueue as register-only.

```php
add_action('init', function(){
    Enqueue::script('my_block_script')
        ->src('http://www.site.com/block-script.js')
        ->for_block()
        ->register();

    // Register block as normal, naming 'my_block_script' in its script handles.
});
```

### Block Editor Assets ###

For scripts/styles that should only load inside the WordPress admin block
editor screens, use `for_block_editor()`. This defers the registration/enqueue
flow to the `enqueue_block_editor_assets` hook, so you don't need to register
the hook yourself.

```php
<?php
Enqueue::script('my-editor-plugin')
    ->src(PLUGIN_BASE_URL . 'assets/js/editor.js')
    ->deps('wp-blocks', 'wp-element', 'wp-i18n')
    ->translations('my-text-domain')
    ->for_block_editor()
    ->register();
```

### Block Styles ###

Attach a stylesheet to a specific block name via
`wp_enqueue_block_style()` so the CSS only loads on pages that render the
block. Use `for_block_style( $block_name )`.

```php
<?php
Enqueue::style('my-block-style')
    ->src(PLUGIN_BASE_URL . 'assets/css/my-block.css')
    ->ver('1.0.0')
    ->for_block_style('my-plugin/my-block')
    ->register();
```

### Block JSON ###

Register a block type directly from its `block.json` metadata directory. This
short-circuits the normal script/style registration — `block.json` is the
source of truth for the block's asset declarations, so the fluent API's
`src`/`deps`/`ver` aren't used.

```php
add_action('init', function(){
    Enqueue::script('placeholder-handle')
        ->block_json( __DIR__ . '/build/my-block' )
        ->register();
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
  * Performs a HEAD request to the src URL via wp_remote_head().
  *
  * @return self
  */
 public function latest_version(): self

/**
  * Register the script for i18n via wp_set_script_translations().
  *
  * @param string      $domain Text domain.
  * @param string|null $path   Optional path to JSON translation files.
  * @return self
  */
 public function translations( string $domain, ?string $path = null ): self

/**
  * Attach an inline JS snippet to the registered handle via wp_add_inline_script().
  *
  * @param string $code     JavaScript source.
  * @param string $position 'before' or 'after'. Default 'after'.
  * @return self
  */
 public function with_code( string $code, string $position = 'after' ): self

/**
  * Attach an inline CSS snippet to the registered handle via wp_add_inline_style().
  *
  * @param string $css CSS source.
  * @return self
  */
 public function with_style( string $css ): self

/**
  * Register the asset with WP without enqueueing it.
  *
  * @param bool $register_only Default true.
  * @return self
  */
 public function register_only( bool $register_only = true ): self

/**
  * Defer the registration to the enqueue_block_editor_assets hook.
  *
  * @param bool $for_block_editor Default true.
  * @return self
  */
 public function for_block_editor( bool $for_block_editor = true ): self

/**
  * Attach a style to a named block via wp_enqueue_block_style().
  *
  * @param string $block_name e.g. 'core/paragraph' or 'my-plugin/my-block'.
  * @return self
  */
 public function for_block_style( string $block_name ): self

/**
  * Register a block type from a block.json directory.
  *
  * @param string $path Absolute path to the directory containing block.json.
  * @return self
  */
 public function block_json( string $path ): self

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
* 1.5.0 - Drop PHP 7.x, require PHP 8.0+. Modernise the tooling chain (PHPStan 2.x at level max, PHPUnit 8|9, WPCS 3.x, phpunit-polyfills widened to include v4, symfony/var-dumper + css-selector + dom-crawler unpinned). Replace the WP 5.9/6.0/6.1 workflows with the WP 6.6-6.9 matrix (PHP 8.0-8.4, `mysql:8.4`) using `codecov/codecov-action@v4`. Suppress the WP 6.8 `wp_is_block_theme` early-call notice in `tests/wp-config.php`. Guard `latest_version()` against null src (avoids PHP 8 TypeError). Narrow `strtotime()` argument when the `Last-Modified` header is returned as a list. Swap the `trigger_error( …, E_USER_DEPRECATED )` in the legacy `lastest_version()` alias for `_deprecated_function()` so WP_UnitTestCase can track the deprecation properly. Drop the `Codeclimate Maintainability` badge from the README. **Swap direct curl for `wp_remote_head()`** in `does_file_exist()` / `latest_version()` so consumers can filter the HTTP layer and tests can mock via `pre_http_request`. **New features:** `translations()` wraps `wp_set_script_translations()`; `with_code()` attaches inline JS via `wp_add_inline_script()`; `with_style()` attaches inline CSS via `wp_add_inline_style()`; `for_block_editor()` defers registration to `enqueue_block_editor_assets`; `for_block_style( $block_name )` wraps `wp_enqueue_block_style()`; `register_only()` registers without enqueueing; `block_json()` wraps `register_block_type_from_metadata()`. **`defer()` / `async()` repurposed** to use the dep-aware `wp_script_add_data( $handle, 'strategy', … )` API (WP 6.3+) instead of splatting attributes through the `script_loader_tag` filter — runtime behaviour unchanged for consumers, rendering now handled by WP core.
* 1.4.0 - Dev dependency bumps to support WP 6.1.
* 1.3.0 - Updated for php8, includes setting of custom script types, renamed lastest_version() to latest_version() and set deprecation notice.
* 1.2.1 : Now supports block use. If defined for block, scripts and styles will only be registered, not enqueued.
* 1.2.0 : Added in Attribute and Flag support with helpers for Aysnc and Defer 

### Contributions  ###
If you would like to make any suggestions or contributions to this little class, please feel free to submit a pull request or reach out to speak to me. at glynn@pinkcrab.co.uk.

### WordPress Core Functions ###
This package uses the following wp core functions. To use PHP Scoper, please add the following functions.
['wp_register_style', 'wp_enqueue_style', 'wp_register_script', 'wp_enqueue_script', 'wp_add_inline_script', 'wp_add_inline_style', 'wp_localize_script', 'wp_set_script_translations', 'wp_script_add_data', 'wp_enqueue_block_style', 'register_block_type_from_metadata', 'wp_remote_head', 'wp_remote_retrieve_response_code', 'wp_remote_retrieve_header', 'is_wp_error', '_deprecated_function']
