<?php

declare(strict_types=1);
/**
 * Enqueue functional tests
 *
 * @since 1..2.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\Core
 */

namespace PinkCrab\Core\Tests\Application;

use PinkCrab\Enqueue\Enqueue;
use Gin0115\WPUnit_Helpers\Output;
use Symfony\Component\DomCrawler\Crawler;
use PinkCrab\FunctionConstructors\Arrays as Arr;
use PinkCrab\FunctionConstructors\Comparisons as Comp;
use PinkCrab\FunctionConstructors\GeneralFunctions as Func;

class Test_Enqueue_Functional extends \WP_UnitTestCase {

	/**
	 * Resets the global scripts
	 *
	 * @return void
	 */
	public function tearDown() {
		$GLOBALS['wp_scripts'] = array();
	}

	/** @testdox When an enqueue object is registered, it should be added to the global scripts array. */
	public function test_is_add_to_enqueue_stack(): void {
		// Not inlined and in footer
		$script = Enqueue::script( 'script_handle' )
			->src( 'https://url.com/Fixtures/script_file.js' )
			->deps( 'jquery', 'angularjs' )
			->ver( '1.2.3' )
			->localize(
				array(
					'key_int'   => 1,
					'key_array' => array( 'string', 'val' ),
				)
			)
			->footer()
			->register();

		$this->assertArrayHasKey( 'script_handle', $GLOBALS['wp_scripts']->registered );
		$dependency = $GLOBALS['wp_scripts']->registered['script_handle'];

		$this->assertEquals( 'script_handle', $dependency->handle );
		$this->assertEquals( 'https://url.com/Fixtures/script_file.js', $dependency->src );
		$this->assertEquals( '1.2.3', $dependency->ver );

		$this->assertIsArray( $dependency->deps );
		$this->assertEquals( 'jquery', $dependency->deps[0] );
		$this->assertEquals( 'angularjs', $dependency->deps[1] );

		// Localized values.
		$expected = sprintf(
			'var %s = %s;',
			'script_handle',
			json_encode(
				(object) array(
					'key_int'   => '1',
					'key_array' => array( 'string', 'val' ),
				)
			)
		);
		$this->assertEquals( $expected, $dependency->extra['data'] );

		// Check is in footer (extra group 1)
		$this->assertEquals( '1', $dependency->extra['group'] );
	}

	/** @testdox It should be possible to set additional attributes to a script tag. */
	public function test_add_script_attributes(): void {

		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::script( 'script_with_atts' )
					->src( 'https://url.com/Fixtures/script_file_with_atts.js' )
					->ver( '1.2.3' )
					->footer( false )
					->flag( 'script_flag' )
					->attribute( 'ATT', 'ribute' )
					->register();
			}
		);

		// Run and get all scripts/styles for header
		$header_html = Output::buffer(
			function() {
				do_action( 'init' );
				do_action( 'wp_enqueue_scripts' );
				do_action( 'wp_head' );
			}
		);

		$crawler = new Crawler( $header_html );

		// Script tags
		$script = Arr\filterFirst(
			Comp\all(
				Func\hasProperty( 'src' ),
				Func\propertyEquals( 'src', 'https://url.com/Fixtures/script_file_with_atts.js?ver=1.2.3' )
			)
		)( $this->get_all_script_tags( $header_html ) );

		$this->assertNotNull( $script );
		$this->assertArrayHasKey( 'script_flag', $script );
		$this->assertArrayHasKey( 'att', $script );
		$this->assertEquals( '', $script['script_flag'] );
		$this->assertEquals( 'ribute', $script['att'] );

	}

	/**
	 * Gets all script tags from a block of html
	 * Returns an array of attributes, with the SRC as the array key.
	 *
	 * @param string $html
	 * @return array
	 */
	private function get_all_script_tags( string $html ): array {
		$crawler = new Crawler( $html );
		$scripts = array();

		// Loop through each script tag
		foreach ( $crawler->filter( 'script' ) as $index => $Node ) {
			// Loop through based on the number of attributes.
			for ( $i = 0; $i < $Node->attributes->length; $i++ ) {
				$scripts[ $index ][ $Node->attributes->item( $i )->name ] = $Node->attributes->item( $i )->value;
			}
		}

		return $scripts;
	}

	/** @testdox It should be possible to set additional attributes to a style tag. */
	public function test_add_style_attributes(): void {

		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::style( 'style_with_atts' )
					->src( 'https://url.com/Fixtures/script_file.css' )
					->ver( '1.2.3' )
					->flag( 'style_flag' )
					->attribute( 'att', 'ribute' )
					->register();
			}
		);

		// Run and get all styles for header
		$header_html = Output::buffer(
			function() {
				do_action( 'init' );
				do_action( 'wp_enqueue_scripts' );
				do_action( 'wp_head' );
			}
		);

		$crawler = new Crawler( $header_html );
		// Look for style flags.
		foreach ( $crawler->filter( '#style_with_atts-css' )->first() as $style_node ) {
			for ( $i = 0; $i < $style_node->attributes->length; $i++ ) {
				// Check attribute has correct value
				if ( $style_node->attributes->item( $i )->name === 'att' ) {
					$this->assertEquals( 'ribute', $style_node->attributes->item( $i )->value );
				}

				// Check flag is set with no value.
				if ( $style_node->attributes->item( $i )->name === 'style_flag' ) {
					$this->assertEquals( '', $style_node->attributes->item( $i )->value );
				}
			}
		}
	}

    /** @testdox It should be possible to denote a script for use with a block. This should register not enqueue the script. */
	public function test_only_registers_script_for_block(): void {
		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::script( 'script_for_block' )
					->src( 'https://url.com/Fixtures/for_block.js' )
                    ->header()
					->for_block()
					->register();
			}
		);


		// Run and get all styles for header
		$header_html = Output::buffer(
			function() {
				do_action( 'init' );
				do_action( 'wp_enqueue_scripts' );
				do_action( 'wp_head' );
			}
		);

        // Attempt to find script in header, should not exist.
		$script = Arr\filterFirst(
			Comp\all(
				Func\hasProperty( 'id' ),
				Func\propertyEquals( 'id', 'script_for_block-js' )
			)
		)( $this->get_all_script_tags( $header_html ) );
        $this->assertNull($script);
        
        // Script should not be queued.
        $this->assertNotContains('script_for_block-js', $GLOBALS['wp_scripts']->queue);
        $this->assertNotContains('script_for_block-js', $GLOBALS['wp_scripts']->done);
        $this->assertArrayHasKey('script_for_block', $GLOBALS['wp_scripts']->registered);
	}

    /** @testdox When a script is not defined for use with a block, it should be registered and enqueued. */
	public function test_enqueues_script_for_none_block(): void {
		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::script( 'script_not_for_block' )
					->src( 'https://url.com/Fixtures/not_for_block.js' )
                    ->header()
					->register();
			}
		);

		// Run and get all styles for header
		$header_html = Output::buffer(
			function() {
				do_action( 'init' );
				do_action( 'wp_enqueue_scripts' );
				do_action( 'wp_head' );
			}
		);

        // Attempt to find script in header, should not exist.
		$script = Arr\filterFirst(
			Comp\all(
				Func\hasProperty( 'id' ),
				Func\propertyEquals( 'id', 'script_not_for_block-js' )
			)
		)( $this->get_all_script_tags( $header_html ) );
        $this->assertNotEmpty($script);
        
        // Script should not be queued.
        $this->assertContains('script_not_for_block', $GLOBALS['wp_scripts']->queue);
        $this->assertContains('script_not_for_block', $GLOBALS['wp_scripts']->done);
        $this->assertArrayHasKey('script_not_for_block', $GLOBALS['wp_scripts']->registered);
	}

    /** @testdox It should be possible to denote a style for use with a block. This should register not enqueue the style. */
	public function test_only_registers_style_for_block(): void {
		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::style( 'style_for_block' )
					->src( 'https://url.com/Fixtures/for_block.css' )
                    ->header()
					->for_block()
					->register();
			}
		);

		Output::buffer(
			function() {
				do_action( 'init' );
				do_action( 'wp_enqueue_scripts' );
				do_action( 'wp_head' );
			}
		);
        
        // Script should not be queued.
        $this->assertNotContains('style_for_block', $GLOBALS['wp_styles']->queue);
        $this->assertNotContains('style_for_block', $GLOBALS['wp_styles']->done);
        $this->assertArrayHasKey('style_for_block', $GLOBALS['wp_styles']->registered);
	}

    /** @testdox When a style is not defined for use with a block, it should be registered and enqueued. */
	public function test_enqueues_style_for_none_block(): void {
		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::style( 'style_not_for_block' )
					->src( 'https://url.com/Fixtures/not_for_block.js' )
                    ->header()
					->register();
			}
		);

		Output::buffer(
			function() {
				do_action( 'init' );
				do_action( 'wp_enqueue_scripts' );
				do_action( 'wp_head' );
			}
		);
        
        // Script should not be queued.
        $this->assertContains('style_not_for_block', $GLOBALS['wp_styles']->queue);
        $this->assertContains('style_not_for_block', $GLOBALS['wp_styles']->done);
        $this->assertArrayHasKey('style_not_for_block', $GLOBALS['wp_styles']->registered);
	}

}
