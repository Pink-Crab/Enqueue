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
use org\bovigo\vfs\vfsStream;
use PinkCrab\Enqueue\Enqueue;
use PinkCrab\PHPUnit_Helpers\Objects;
use org\bovigo\vfs\vfsStreamDirectory;

class Test_Enqueue extends WP_UnitTestCase {


	public function setUp() {



	}

	/**
	 * Retruns a fully populated enqueue script isntance..
	 *
	 * @return \PinkCrab\Enqueue\Enqueue
	 */
	protected static function create_script(): Enqueue {
		return Enqueue::script( 'script_handle' )
			->src( 'script_url.js' )
			->deps( 'jquery', 'angularjs' )
			->ver( '1.2.3' )
			->footer( false )
			->inline()
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
			->ver('2.3')
			->media( '(orientation: portrait)' );
	}

	/**
	 * Test can be concstrcuted
	 *
	 * @return void
	 */
	public function test_can_create_from_constructor(): void {
		$enqueue = new Enqueue( 'hook', 'script' );
		$this->assertEquals( 'hook', Objects::get_private_property( $enqueue, 'handle' ) );
		$this->assertEquals( 'script', Objects::get_private_property( $enqueue, 'type' ) );
	}

	/**
	 * Test script and stype statics create with type
	 *
	 * @return void
	 */
	public function test_static_constructors(): void {
		$script = self::create_script();
		$this->assertEquals( 'script_handle', Objects::get_private_property( $script, 'handle' ) );
		$this->assertEquals( 'script', Objects::get_private_property( $script, 'type' ) );

		$style = self::create_style();
		$this->assertEquals( 'style_handle', Objects::get_private_property( $style, 'handle' ) );
		$this->assertEquals( 'style', Objects::get_private_property( $style, 'type' ) );
	}

	/**
	 * Tests all script setters.
	 *
	 * @return void
	 */
	public function test_script_setters(): void {

		$script = self::create_script();

		$this->assertEquals( 'script', Objects::get_private_property( $script, 'type' ) );
		$this->assertEquals( 'script_handle', Objects::get_private_property( $script, 'handle' ) );
		$this->assertEquals( 'script_url.js', Objects::get_private_property( $script, 'src' ) );
		$this->assertEquals( '1.2.3', Objects::get_private_property( $script, 'ver' ) );
		$this->assertFalse( Objects::get_private_property( $script, 'footer' ) );

		$this->assertIsArray( Objects::get_private_property( $script, 'deps' ) );
		$this->assertEquals( 'jquery', Objects::get_private_property( $script, 'deps' )[0] );
		$this->assertEquals( 'angularjs', Objects::get_private_property( $script, 'deps' )[1] );

		$this->assertIsArray( Objects::get_private_property( $script, 'localize' ) );
		$this->assertArrayHasKey( 'key_int', Objects::get_private_property( $script, 'localize' ) );
		$this->assertIsInt( Objects::get_private_property( $script, 'localize' )['key_int'] );
		$this->assertIsArray( Objects::get_private_property( $script, 'localize' )['key_array'] );

	}

	/**
	 * Tests all script setters.
	 *
	 * @return void
	 */
	public function test_style_setters(): void {

		$style = self::create_style();

		$this->assertEquals( 'style', Objects::get_private_property( $style, 'type' ) );
		$this->assertEquals( 'style_handle', Objects::get_private_property( $style, 'handle' ) );
		$this->assertEquals( 'style_file.css', Objects::get_private_property( $style, 'src' ) );
		$this->assertEquals( '2.3', Objects::get_private_property( $style, 'ver' ) );
		$this->assertEquals( '(orientation: portrait)', Objects::get_private_property( $style, 'media' ) );

		$this->assertIsArray( Objects::get_private_property( $style, 'deps' ) );
		$this->assertEquals( 'theme_styles', Objects::get_private_property( $style, 'deps' )[0] );
		$this->assertEquals( 'ache_plugin_styles', Objects::get_private_property( $style, 'deps' )[1] );

	}

}
