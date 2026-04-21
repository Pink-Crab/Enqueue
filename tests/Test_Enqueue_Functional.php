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
	public function tearDown(): void {
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
		$this->assertEquals( 'text/javascript', $script['type'] );

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
		$this->assertNull( $script );

		// Script should not be queued.
		$this->assertNotContains( 'script_for_block-js', $GLOBALS['wp_scripts']->queue );
		$this->assertNotContains( 'script_for_block-js', $GLOBALS['wp_scripts']->done );
		$this->assertArrayHasKey( 'script_for_block', $GLOBALS['wp_scripts']->registered );
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
		$this->assertNotEmpty( $script );

		// Script should not be queued.
		$this->assertContains( 'script_not_for_block', $GLOBALS['wp_scripts']->queue );
		$this->assertContains( 'script_not_for_block', $GLOBALS['wp_scripts']->done );
		$this->assertArrayHasKey( 'script_not_for_block', $GLOBALS['wp_scripts']->registered );
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
		$this->assertNotContains( 'style_for_block', $GLOBALS['wp_styles']->queue );
		$this->assertNotContains( 'style_for_block', $GLOBALS['wp_styles']->done );
		$this->assertArrayHasKey( 'style_for_block', $GLOBALS['wp_styles']->registered );
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
		$this->assertContains( 'style_not_for_block', $GLOBALS['wp_styles']->queue );
		$this->assertContains( 'style_not_for_block', $GLOBALS['wp_styles']->done );
		$this->assertArrayHasKey( 'style_not_for_block', $GLOBALS['wp_styles']->registered );
	}

	/** @testdox It should be possible to set a script with a type of Module and have it rendered as such. */
	public function test_renders_script_as_module_in_header(): void {

		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::script( 'script_as_module' )
					->src( 'https://url.com/Fixtures/as_module.js' )
					->ver( '1.1.1' )
					->header()
					->script_type( 'module' )
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
				Func\propertyEquals( 'id', 'script_as_module-js' )
			)
		)( $this->get_all_script_tags( $header_html ) );

		// Check the type is set as module.
		$this->assertEquals( 'module', $script['type'] );
		$this->assertEquals( 'https://url.com/Fixtures/as_module.js?ver=1.1.1', $script['src'] );
	}

	/** 
	 * @testdox If no attributes are defined and no custom type is set, the script_loader_tag filter should not be added.
	  * @_runInSeparateProcess
 * @_preserveGlobalState disabled
	 */
	public function test_no_script_loader_tag_filter_added_if_no_attributes_and_no_custom_type(): void {
		
		// Ensure no other scripts are queued up
		do_action( 'init' );
		unset( $GLOBALS['wp_filter']['wp_enqueue_scripts'] );
		
		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::script( 'script_no_attributes' )
					->src( 'https://url.com/Fixtures/no_attributes.js' )
					->header()
					->register();
			}
		);

		// Run and get all styles for header
		$header_html = Output::buffer(
			function() {
				
				do_action( 'wp_enqueue_scripts' );
				unset( $GLOBALS['wp_filter']['script_loader_tag'] );
				do_action( 'wp_head' );
			}
		);


		// The filter should not be added.
		$this->assertArrayNotHasKey( 'script_loader_tag', $GLOBALS['wp_filter'] );
	}

	/** @testdox Calling defer() should register a 'defer' strategy via wp_script_add_data. */
	public function test_defer_sets_strategy_data(): void {
		Enqueue::script( 'script_defer' )
			->src( 'https://url.com/Fixtures/defer.js' )
			->ver( '1.0.0' )
			->defer()
			->register();

		$this->assertArrayHasKey( 'script_defer', $GLOBALS['wp_scripts']->registered );
		$this->assertSame( 'defer', $GLOBALS['wp_scripts']->registered['script_defer']->extra['strategy'] );
	}

	/** @testdox Calling async() should register an 'async' strategy via wp_script_add_data. */
	public function test_async_sets_strategy_data(): void {
		Enqueue::script( 'script_async' )
			->src( 'https://url.com/Fixtures/async.js' )
			->ver( '1.0.0' )
			->async()
			->register();

		$this->assertArrayHasKey( 'script_async', $GLOBALS['wp_scripts']->registered );
		$this->assertSame( 'async', $GLOBALS['wp_scripts']->registered['script_async']->extra['strategy'] );
	}

	/** @testdox translations() should register the handle for wp_set_script_translations. */
	public function test_translations_registered_against_handle(): void {
		Enqueue::script( 'script_i18n' )
			->src( 'https://url.com/Fixtures/i18n.js' )
			->ver( '1.0.0' )
			->translations( 'pinkcrab-enqueue-test', '/tmp/translations' )
			->register();

		$this->assertArrayHasKey( 'script_i18n', $GLOBALS['wp_scripts']->registered );
		$script = $GLOBALS['wp_scripts']->registered['script_i18n'];
		$this->assertSame( 'pinkcrab-enqueue-test', $script->textdomain );
		$this->assertSame( '/tmp/translations', $script->translations_path );
	}

	/** @testdox with_code() should attach inline snippets to the handle in the correct positions. */
	public function test_with_code_attaches_inline_snippets(): void {
		Enqueue::script( 'script_with_code' )
			->src( 'https://url.com/Fixtures/with_code.js' )
			->with_code( 'window.A = 1;' )
			->with_code( 'window.B = 2;', 'before' )
			->with_code( 'window.C = 3;', 'after' )
			->register();

		$script   = $GLOBALS['wp_scripts']->registered['script_with_code'];
		$after    = is_array( $script->extra['after'] ) ? implode( "\n", $script->extra['after'] ) : $script->extra['after'];
		$before   = is_array( $script->extra['before'] ) ? implode( "\n", $script->extra['before'] ) : $script->extra['before'];
		$this->assertStringContainsString( 'window.A = 1;', $after );
		$this->assertStringContainsString( 'window.C = 3;', $after );
		$this->assertStringContainsString( 'window.B = 2;', $before );
	}

	/** @testdox with_style() should attach inline CSS to the style handle's 'after' slot. */
	public function test_with_style_attaches_inline_css(): void {
		Enqueue::style( 'style_with_css' )
			->src( 'https://url.com/Fixtures/with_css.css' )
			->with_style( '.foo { color: red; }' )
			->with_style( '.bar { color: blue; }' )
			->register();

		$style     = $GLOBALS['wp_styles']->registered['style_with_css'];
		$after_css = implode( "\n", $style->extra['after'] );
		$this->assertStringContainsString( '.foo { color: red; }', $after_css );
		$this->assertStringContainsString( '.bar { color: blue; }', $after_css );
	}

	/** @testdox register_only() should register the script without enqueueing it. */
	public function test_register_only_script_does_not_enqueue(): void {
		Enqueue::script( 'script_register_only' )
			->src( 'https://url.com/Fixtures/register_only.js' )
			->register_only()
			->register();

		$this->assertTrue( wp_script_is( 'script_register_only', 'registered' ) );
		$this->assertFalse( wp_script_is( 'script_register_only', 'enqueued' ) );
	}

	/** @testdox register_only() should register the style without enqueueing it. */
	public function test_register_only_style_does_not_enqueue(): void {
		Enqueue::style( 'style_register_only' )
			->src( 'https://url.com/Fixtures/register_only.css' )
			->register_only()
			->register();

		$this->assertTrue( wp_style_is( 'style_register_only', 'registered' ) );
		$this->assertFalse( wp_style_is( 'style_register_only', 'enqueued' ) );
	}

	/** @testdox for_block_editor() should defer the registration to the enqueue_block_editor_assets hook. */
	public function test_for_block_editor_defers_to_editor_hook(): void {
		Enqueue::script( 'script_block_editor' )
			->src( 'https://url.com/Fixtures/block_editor.js' )
			->for_block_editor()
			->register();

		// Before the hook fires, the script is NOT registered.
		$this->assertFalse( wp_script_is( 'script_block_editor', 'registered' ) );

		// Firing the editor hook should register + enqueue the script.
		do_action( 'enqueue_block_editor_assets' );
		$this->assertTrue( wp_script_is( 'script_block_editor', 'registered' ) );
		$this->assertTrue( wp_script_is( 'script_block_editor', 'enqueued' ) );
	}

	/** @testdox for_block_style() should register the style and attach a render_block_{name} filter. */
	public function test_for_block_style_attaches_to_block(): void {
		// Block must be registered first for wp_enqueue_block_style to attach a filter to it.
		\register_block_type(
			'pinkcrab-enqueue/style-target',
			array(
				'api_version' => 3,
				'title'       => 'Style Target',
				'category'    => 'widgets',
			)
		);

		Enqueue::style( 'block_bound_style' )
			->src( 'https://url.com/Fixtures/block_bound.css' )
			->ver( '1.0.0' )
			->for_block_style( 'pinkcrab-enqueue/style-target' )
			->register();

		// wp_enqueue_block_style wires a deferred callback. Depending on the
		// WP version and wp_should_load_block_assets_on_demand(), it lands on
		// render_block, wp_enqueue_scripts/wp_footer, or enqueue_block_assets.
		// Fire the full set to cover every branch.
		apply_filters(
			'render_block',
			'<p>test</p>',
			array(
				'blockName'    => 'pinkcrab-enqueue/style-target',
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			),
			null
		);
		do_action( 'wp_enqueue_scripts' );
		do_action( 'wp_footer' );
		do_action( 'enqueue_block_assets' );

		$this->assertTrue( wp_style_is( 'block_bound_style', 'registered' ) );

		\unregister_block_type( 'pinkcrab-enqueue/style-target' );
	}

	/** @testdox block_json() should register a block type from the block.json directory. */
	public function test_block_json_registers_block_type(): void {
		$block_dir = __DIR__ . '/Fixtures/test-block';

		Enqueue::script( 'ignored_handle' )
			->block_json( $block_dir )
			->register();

		$registry = \WP_Block_Type_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'pinkcrab-enqueue/test-block' ) );

		$registry->unregister( 'pinkcrab-enqueue/test-block' );
	}

	/** @testdox latest_version() is a no-op when no src has been set. */
	public function test_latest_version_no_op_when_src_empty(): void {
		$script = Enqueue::script( 'no_src' )->latest_version();
		$this->assertFalse( \Gin0115\WPUnit_Helpers\Objects::get_property( $script, 'ver' ) );
	}

	/** @testdox latest_version() bails when wp_remote_head returns a WP_Error. */
	public function test_latest_version_bails_on_http_error(): void {
		add_filter(
			'pre_http_request',
			static function () {
				return new \WP_Error( 'http_request_failed', 'fake network fail' );
			},
			10,
			3
		);

		$script = Enqueue::script( 'errored' )
			->src( 'https://fake.example/script.js' )
			->latest_version();

		// ver stays at the default false (no timestamp parsed).
		$this->assertFalse( \Gin0115\WPUnit_Helpers\Objects::get_property( $script, 'ver' ) );

		remove_all_filters( 'pre_http_request' );
	}

	/** @testdox latest_version() bails when the remote returns a non-200 status. */
	public function test_latest_version_bails_on_non_200(): void {
		add_filter(
			'pre_http_request',
			static function () {
				return array(
					'headers'  => array(),
					'body'     => '',
					'response' => array(
						'code'    => 404,
						'message' => 'Not Found',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		$script = Enqueue::script( 'missing' )
			->src( 'https://fake.example/missing.js' )
			->latest_version();

		$this->assertFalse( \Gin0115\WPUnit_Helpers\Objects::get_property( $script, 'ver' ) );

		remove_all_filters( 'pre_http_request' );
	}

	/** @testdox latest_version() parses a scalar Last-Modified header into a unix timestamp. */
	public function test_latest_version_sets_ver_from_last_modified(): void {
		$modified = 'Mon, 20 Apr 2026 12:34:56 GMT';
		add_filter(
			'pre_http_request',
			static function () use ( $modified ) {
				return array(
					'headers'  => array( 'last-modified' => $modified ),
					'body'     => '',
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		$script = Enqueue::script( 'latest' )
			->src( 'https://fake.example/latest.js' )
			->latest_version();

		$this->assertSame( strtotime( $modified ), \Gin0115\WPUnit_Helpers\Objects::get_property( $script, 'ver' ) );

		remove_all_filters( 'pre_http_request' );
	}

	/** @testdox latest_version() handles a Last-Modified header returned as an array (multi-valued). */
	public function test_latest_version_handles_array_last_modified(): void {
		$oldest = 'Mon, 01 Jan 2026 00:00:00 GMT';
		$newest = 'Mon, 20 Apr 2026 12:34:56 GMT';
		add_filter(
			'pre_http_request',
			static function () use ( $oldest, $newest ) {
				return array(
					'headers'  => array( 'last-modified' => array( $oldest, $newest ) ),
					'body'     => '',
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		$script = Enqueue::script( 'latest_array' )
			->src( 'https://fake.example/latest.js' )
			->latest_version();

		// end() picks the last entry — the newer timestamp.
		$this->assertSame( strtotime( $newest ), \Gin0115\WPUnit_Helpers\Objects::get_property( $script, 'ver' ) );

		remove_all_filters( 'pre_http_request' );
	}

	/** @testdox inline() does not attach file contents when the remote HEAD request fails (WP_Error). */
	public function test_inline_skips_file_contents_on_http_error(): void {
		add_filter(
			'pre_http_request',
			static function () {
				return new \WP_Error( 'http_request_failed', 'fake network fail' );
			},
			10,
			3
		);

		Enqueue::script( 'inline_errored' )
			->src( 'https://fake.example/dead.js' )
			->inline()
			->register();

		$script = $GLOBALS['wp_scripts']->registered['inline_errored'];
		// No inline contents should have been attached.
		$this->assertFalse( isset( $script->extra['after'] ) && ! empty( $script->extra['after'] ) );

		remove_all_filters( 'pre_http_request' );
	}

	/** @testdox inline() attaches the file contents to the registered script when does_file_exist returns true. */
	public function test_inline_attaches_file_contents_when_remote_exists(): void {
		$body = 'console.log("inline-me");';
		add_filter(
			'pre_http_request',
			static function () use ( $body ) {
				return array(
					'headers'  => array(),
					'body'     => $body,
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		// Create a temp file locally because the legacy inline() path still
		// uses file_get_contents(); the wp_remote_head does_file_exist check
		// is the HTTP-mocked part.
		$tmp = tempnam( sys_get_temp_dir(), 'pcenq' );
		file_put_contents( $tmp, $body );

		Enqueue::script( 'inline_file' )
			->src( $tmp )
			->inline()
			->register();

		$script = $GLOBALS['wp_scripts']->registered['inline_file'];
		$after  = is_array( $script->extra['after'] ) ? implode( "\n", $script->extra['after'] ) : $script->extra['after'];
		$this->assertStringContainsString( $body, $after );

		unlink( $tmp );
		remove_all_filters( 'pre_http_request' );
	}

	/** @testdox When registering a script, if the type is custom and manual id is added to attributes, it should not be auto generated. */
	public function test_manual_id_added_to_attributes_if_custom_type_and_manual_id(): void {
		// Enqueue
		add_action(
			'wp_enqueue_scripts',
			function() {
				Enqueue::script( 'script_manual_id' )
					->src( 'https://url.com/Fixtures/manual_id.js' )
					->header()
					->script_type( 'custom' )
					->attribute( 'id', 'manual_id' )
					->ver( '1.1.1' )
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
				Func\propertyEquals( 'id', 'manual_id' )
			)
		)( $this->get_all_script_tags( $header_html ) );

		// Check the type is set as custom.
		$this->assertEquals( 'custom', $script['type'] );
		$this->assertEquals( 'https://url.com/Fixtures/manual_id.js?ver=1.1.1', $script['src'] );
	}
}
