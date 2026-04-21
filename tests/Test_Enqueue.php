<?php

declare(strict_types=1);
/**
 * Enqueue tests
 *
 * @since 1.1.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\Core
 */

namespace PinkCrab\Core\Tests\Application;

use WP_UnitTestCase;
use PinkCrab\Enqueue\Enqueue;
use Gin0115\WPUnit_Helpers\Objects;

class Test_Enqueue extends WP_UnitTestCase {


	/**
	 * Retruns a fully populated enqueue script isntance..
	 *
	 * @return \PinkCrab\Enqueue\Enqueue
	 */
	protected static function create_script(): Enqueue {
		return Enqueue::script( 'script_handle' )
			->src( 'https://url.com/Fixtures/script_file.js' )
			->deps( 'jquery', 'angularjs' )
			->ver( '1.2.3' )
			->footer( false )
			->localize(
				array(
					'key_int'   => 1,
					'key_array' => array( 'string', 'val' ),
				)
			);
	}

	/**
	 * Retruns a fully populated enqueue style isntance..
	 * Uses latest file date.
	 *
	 * @return \PinkCrab\Enqueue\Enqueue
	 */
	protected static function create_style(): Enqueue {
		return Enqueue::style( 'style_handle' )
			->src( 'style_file.css' )
			->deps( 'theme_styles', 'ache_plugin_styles' )
			->ver( '2.3' )
			->media( '(orientation: portrait)' );
	}

	/**
	 * Test can be concstrcuted
	 *
	 * @return void
	 */
	public function test_can_create_from_constructor(): void {
		$enqueue = new Enqueue( 'hook', 'script' );
		$this->assertEquals( 'hook', Objects::get_property( $enqueue, 'handle' ) );
		$this->assertEquals( 'script', Objects::get_property( $enqueue, 'type' ) );
	}

	/**
	 * Test script and stype statics create with type
	 *
	 * @return void
	 */
	public function test_static_constructors(): void {
		$script = self::create_script();
		$this->assertEquals( 'script_handle', Objects::get_property( $script, 'handle' ) );
		$this->assertEquals( 'script', Objects::get_property( $script, 'type' ) );

		$style = self::create_style();
		$this->assertEquals( 'style_handle', Objects::get_property( $style, 'handle' ) );
		$this->assertEquals( 'style', Objects::get_property( $style, 'type' ) );
	}

	/**
	 * Tests all script setters.
	 *
	 * @return void
	 */
	public function test_script_setters(): void {

		$script = self::create_script();

		$this->assertEquals( 'script', Objects::get_property( $script, 'type' ) );
		$this->assertEquals( 'script_handle', Objects::get_property( $script, 'handle' ) );
		$this->assertEquals( 'https://url.com/Fixtures/script_file.js', Objects::get_property( $script, 'src' ) );
		$this->assertEquals( '1.2.3', Objects::get_property( $script, 'ver' ) );
		$this->assertFalse( Objects::get_property( $script, 'footer' ) );

		$this->assertIsArray( Objects::get_property( $script, 'deps' ) );
		$this->assertEquals( 'jquery', Objects::get_property( $script, 'deps' )[0] );
		$this->assertEquals( 'angularjs', Objects::get_property( $script, 'deps' )[1] );

		$this->assertIsArray( Objects::get_property( $script, 'localize' ) );
		$this->assertArrayHasKey( 'key_int', Objects::get_property( $script, 'localize' ) );
		$this->assertIsInt( Objects::get_property( $script, 'localize' )['key_int'] );
		$this->assertIsArray( Objects::get_property( $script, 'localize' )['key_array'] );

	}

	/**
	 * Tests all script setters.
	 *
	 * @return void
	 */
	public function test_style_setters(): void {

		$style = self::create_style();

		$this->assertEquals( 'style', Objects::get_property( $style, 'type' ) );
		$this->assertEquals( 'style_handle', Objects::get_property( $style, 'handle' ) );
		$this->assertEquals( 'style_file.css', Objects::get_property( $style, 'src' ) );
		$this->assertEquals( '2.3', Objects::get_property( $style, 'ver' ) );
		$this->assertEquals( '(orientation: portrait)', Objects::get_property( $style, 'media' ) );

		$this->assertIsArray( Objects::get_property( $style, 'deps' ) );
		$this->assertEquals( 'theme_styles', Objects::get_property( $style, 'deps' )[0] );
		$this->assertEquals( 'ache_plugin_styles', Objects::get_property( $style, 'deps' )[1] );

	}

	/** @testdox It should be possible to denote a script as async easily. */
	public function test_can_set_async_on_script(): void {
		$script = self::create_script()->async();

		$this->assertSame( 'async', Objects::get_property( $script, 'strategy' ) );
	}

	/** @testdox It should be possible to denote a style as async easily. */
	public function test_can_set_async_style(): void {
		$style = self::create_style()->async();

		// async() sets the strategy flag regardless of type; wp_script_add_data
		// on render is script-only, so on a style this is a harmless no-op.
		$this->assertSame( 'async', Objects::get_property( $style, 'strategy' ) );
	}

	/** @testdox It should be possible to denote a script as defer easily. */
	public function test_can_set_defer_on_script(): void {
		$script = self::create_script()->defer();

		$this->assertSame( 'defer', Objects::get_property( $script, 'strategy' ) );
	}

	/** @testdox It should be possible to denote a style as defer easily. */
	public function test_can_set_defer_style(): void {
		$style = self::create_style()->defer();

		$this->assertSame( 'defer', Objects::get_property( $style, 'strategy' ) );
	}

	/** @testdox It should not be possible to set both async and defer, either should unset the other */
	public function test_can_only_be_async_or_defer(): void {
		$script = self::create_script()->async();
		$this->assertSame( 'async', Objects::get_property( $script, 'strategy' ) );

		$script->defer();
		$this->assertSame( 'defer', Objects::get_property( $script, 'strategy' ) );

		$script->async();
		$this->assertSame( 'async', Objects::get_property( $script, 'strategy' ) );
	}

	/** @testdox It should be possible to define if a script is added to the header */
	public function test_can_set_script_in_header(): void {
		$script = Enqueue::script( 'header' )->header();
		$this->assertFalse( Objects::get_property( $script, 'footer' ) );
	}

	/** @testdox It should be possible to toggle if a script in enqueued inline. */
	public function test_can_set_script_as_inline(): void {
		$script = Enqueue::script( 'header' )->inline();
		$this->assertTrue( Objects::get_property( $script, 'inline' ) );

		// As false
		$script = Enqueue::script( 'header' )->inline( false );
		$this->assertFalse( Objects::get_property( $script, 'inline' ) );

		// Verbose true.
		$script = Enqueue::script( 'header' )->inline( true );
		$this->assertTrue( Objects::get_property( $script, 'inline' ) );
	}

	/** @testdox It should be possible to toggle if a script in enqueued is for a block or not.. */
	public function test_can_set_script_for_block(): void {
		// False by default.
		$script = Enqueue::script( 'for_block' );
		$this->assertFalse( Objects::get_property( $script, 'for_block' ) );

		// Set as true with no value passed.
		$script = Enqueue::script( 'for_block' )->for_block();
		$this->assertTrue( Objects::get_property( $script, 'for_block' ) );

		// As false
		$style = Enqueue::style( 'for_block' )->for_block( false );
		$this->assertFalse( Objects::get_property( $style, 'for_block' ) );

		// Verbose true.
		$style = Enqueue::style( 'for_block' )->for_block( true );
		$this->assertTrue( Objects::get_property( $style, 'for_block' ) );
	}

	/** @testdox It should be possible to register a script with custom types */
	public function test_can_register_script_with_custom_types(): void {
		$script = Enqueue::script( 'custom_type' )->script_type( 'custom_type' )->src('gg.js');
		$this->assertEquals( 'custom_type', Objects::get_property( $script, 'script_type' ) );

		// Default should be set
		$script = Enqueue::script( 'custom_type' )->src('gg.js');
		$this->assertEquals( 'text/javascript', Objects::get_property( $script, 'script_type' ) );
	}

	/** @testdox Calling the old, mistyped lastest_version() should throw deprecation notice. */
	public function test_calling_old_latest_version_should_throw_deprecation_notice(): void {
		$this->setExpectedDeprecated( 'PinkCrab\Enqueue\Enqueue::lastest_version' );
		Enqueue::script( 'ff' )->src( 'https://example.test/js.js' )->lastest_version();
	}

	/** @testdox It should be possible to set script translations via translations(). */
	public function test_can_set_translations(): void {
		$script       = self::create_script()->translations( 'my-domain', '/path/to/translations' );
		$translations = Objects::get_property( $script, 'translations' );
		$this->assertSame( 'my-domain', $translations['domain'] );
		$this->assertSame( '/path/to/translations', $translations['path'] );

		// Path is optional.
		$script       = self::create_script()->translations( 'just-domain' );
		$translations = Objects::get_property( $script, 'translations' );
		$this->assertSame( 'just-domain', $translations['domain'] );
		$this->assertNull( $translations['path'] );
	}

	/** @testdox It should be possible to attach inline JS snippets via with_code() and have them accumulate. */
	public function test_can_add_inline_code(): void {
		$script = self::create_script()
			->with_code( 'var foo = 1;' )
			->with_code( 'var bar = 2;', 'before' );

		$snippets = Objects::get_property( $script, 'inline_scripts' );
		$this->assertCount( 2, $snippets );
		$this->assertSame( 'var foo = 1;', $snippets[0]['code'] );
		$this->assertSame( 'after', $snippets[0]['position'] );
		$this->assertSame( 'var bar = 2;', $snippets[1]['code'] );
		$this->assertSame( 'before', $snippets[1]['position'] );
	}

	/** @testdox It should be possible to attach inline CSS snippets via with_style() and have them accumulate. */
	public function test_can_add_inline_style(): void {
		$style = self::create_style()
			->with_style( '.foo { color: red; }' )
			->with_style( '.bar { color: blue; }' );

		$snippets = Objects::get_property( $style, 'inline_styles' );
		$this->assertCount( 2, $snippets );
		$this->assertSame( '.foo { color: red; }', $snippets[0] );
		$this->assertSame( '.bar { color: blue; }', $snippets[1] );
	}

	/** @testdox It should be possible to flag an asset for the block editor context. */
	public function test_can_set_for_block_editor(): void {
		$script = self::create_script()->for_block_editor();
		$this->assertTrue( Objects::get_property( $script, 'for_block_editor' ) );

		// Default arg is true; explicit false also works.
		$script = self::create_script()->for_block_editor( false );
		$this->assertFalse( Objects::get_property( $script, 'for_block_editor' ) );
	}

	/** @testdox It should be possible to attach a style to a named block via for_block_style(). */
	public function test_can_set_for_block_style(): void {
		$style = self::create_style()->for_block_style( 'my-plugin/my-block' );
		$this->assertSame( 'my-plugin/my-block', Objects::get_property( $style, 'block_name' ) );
	}

	/** @testdox It should be possible to register without enqueueing via register_only(). */
	public function test_can_set_register_only(): void {
		$script = self::create_script()->register_only();
		$this->assertTrue( Objects::get_property( $script, 'register_only' ) );

		$script = self::create_script()->register_only( false );
		$this->assertFalse( Objects::get_property( $script, 'register_only' ) );
	}

	/** @testdox It should be possible to point at a block.json directory via block_json(). */
	public function test_can_set_block_json(): void {
		$script = self::create_script()->block_json( '/path/to/block-dir' );
		$this->assertSame( '/path/to/block-dir', Objects::get_property( $script, 'block_json_path' ) );
	}
}
