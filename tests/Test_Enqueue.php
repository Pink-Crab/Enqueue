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

	/** @testdox It should be possible to denote a scriptas async easily. */
	public function test_can_set_async_on_script(): void {
		$script = self::create_script()->async();

		$attributes = Objects::get_property( $script, 'attributes' );
		$this->assertArrayHasKey( 'async', $attributes );
	}

	/** @testdox It should be possible to denote a style as async easily. */
	public function test_can_set_async_style(): void {
		$style = self::create_style()->async();

		$attributes = Objects::get_property( $style, 'attributes' );
		$this->assertArrayHasKey( 'async', $attributes );
	}

	/** @testdox It should be possible to denote a scriptas defer easily. */
	public function test_can_set_defer_on_script(): void {
		$script = self::create_script()->defer();

		$attributes = Objects::get_property( $script, 'attributes' );
		$this->assertArrayHasKey( 'defer', $attributes );
	}

	/** @testdox It should be possible to denote a style as defer easily. */
	public function test_can_set_defer_style(): void {
		$style = self::create_style()->defer();

		$attributes = Objects::get_property( $style, 'attributes' );
		$this->assertArrayHasKey( 'defer', $attributes );
	}

	/** @testdox It should not be possible to set both async and defer, either should unset the other */
	public function test_can_only_be_async_or_defer(): void {
		$script     = self::create_script()->async();
		$attributes = Objects::get_property( $script, 'attributes' );
		$this->assertArrayHasKey( 'async', $attributes );
		$this->assertArrayNotHasKey( 'defer', $attributes );

		$script->defer();
		$attributes = Objects::get_property( $script, 'attributes' );
		$this->assertArrayHasKey( 'defer', $attributes );
		$this->assertArrayNotHasKey( 'async', $attributes );

		$script->async();
		$attributes = Objects::get_property( $script, 'attributes' );
		$this->assertArrayHasKey( 'async', $attributes );
		$this->assertArrayNotHasKey( 'defer', $attributes );
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
}
