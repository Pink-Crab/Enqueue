<?php

declare(strict_types=1);

/**
 * A chainable helper class for enqueuing scripts and styles.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\Enqueue
 */

namespace PinkCrab\Enqueue;

/**
 * WordPress Script and Style enqueuing class.
 *
 * @version 1.1.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 */
class Enqueue {

	/**
	 * The handle to enqueue the script or style with.
	 * Also used for any localised variables.
	 *
	 * @var string
	 */
	protected $handle;

	/**
	 * The type of file to enqueue.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The file location (URI)
	 *
	 * @var string
	 */
	protected $src;

	/**
	 * Dependencies which must be loaded prior.
	 *
	 * @var array<int, string>
	 */
	protected $deps = array();

	/**
	 * Version tag for file enqueued
	 *
	 * @var mixed
	 */
	protected $ver = false;

	/**
	 * Defines if script should be loaded in footer (true) or header (false)
	 *
	 * @var boolean
	 */
	protected $footer = true;

	/**
	 * Values to be localized when script enqueued.
	 *
	 * @var array<string, mixed>|null
	 */
	protected $localize = null;

	/**
	 * Defines if script should be parsed inline or enqueued.
	 * Please note this should only be used for simple and small JS files.
	 *
	 * @var boolean
	 */
	protected $inline = false;

	/**
	 * Style sheet which has been defined.
	 * Accepts media types like wp_enqueue_styles.
	 *
	 * @var string
	 */
	protected $media = 'all';

	/**
	 * All custom flags and attributes to add to the script and style tags
	 *
	 * @var array<string, string|null>
	 */
	protected $attributes = array();

	/**
	 * Denotes if being enqueued for a block.
	 *
	 * @var bool
	 */
	protected $for_block = false;

	/**
	 * Denotes the script type.
	 *
	 * @var string
	 */
	protected $script_type = 'text/javascript';

	/**
	 * Script translations domain + optional path for wp_set_script_translations().
	 *
	 * @var array{domain: string, path: string|null}|null
	 */
	protected $translations = null;

	/**
	 * Inline script snippets to attach to the handle via wp_add_inline_script().
	 *
	 * @var array<int, array{code: string, position: string}>
	 */
	protected $inline_scripts = array();

	/**
	 * Inline CSS strings to attach to the handle via wp_add_inline_style().
	 *
	 * @var array<int, string>
	 */
	protected $inline_styles = array();

	/**
	 * Defer/async strategy forwarded to wp_script_add_data( $handle, 'strategy', $strategy ).
	 *
	 * @var string|null
	 */
	protected $strategy = null;

	/**
	 * When true, register() attaches the registration/enqueue flow to enqueue_block_editor_assets.
	 *
	 * @var bool
	 */
	protected $for_block_editor = false;

	/**
	 * When set, register_style() calls wp_enqueue_block_style() against this block name.
	 *
	 * @var string|null
	 */
	protected $block_name = null;

	/**
	 * Register the asset with WP but do not enqueue it. Handle remains available for deps.
	 *
	 * @var bool
	 */
	protected $register_only = false;

	/**
	 * Path to a block.json directory for register_block_type_from_metadata().
	 *
	 * @var string|null
	 */
	protected $block_json_path = null;

	/**
	 * Creates an Enqueue instance.
	 *
	 * @param string $handle
	 * @param string $type
	 */
	public function __construct( string $handle, string $type ) {
		$this->handle = $handle;
		$this->type   = $type;
	}

	/**
	 * Creates a static instance of the Enqueue class for a script.
	 *
	 * @param string $handle
	 * @return self
	 */
	public static function script( string $handle ): self {
		return new self( $handle, 'script' );
	}

	/**
	 * Creates a static instance of the Enqueue class for a style.
	 *
	 * @param string $handle
	 * @return self
	 */
	public static function style( string $handle ): self {
		return new self( $handle, 'style' );
	}

	/**
	 * Defined the SRC of the file.
	 *
	 * @param string $src
	 * @return self
	 */
	public function src( string $src ): self {
		$this->src = $src;
		return $this;
	}

	/**
	 * Defined the Dependencies of the enqueue.
	 *
	 * @param string ...$deps
	 * @return self
	 */
	public function deps( string ...$deps ): self {
		$this->deps = array_values( $deps );
		return $this;
	}

	/**
	 * Defined the version of the enqueue
	 *
	 * @param string $ver
	 * @return self
	 */
	public function ver( string $ver ): self {
		$this->ver = $ver;
		return $this;
	}

	/**
	 * Define the media type.
	 *
	 * @param string $media
	 * @return self
	 */
	public function media( string $media ): self {
		$this->media = $media;
		return $this;
	}

	/**
	 * Attaches an inline CSS snippet to the registered style handle.
	 *
	 * Calls stack — each call adds another entry. Only applies to styles; a
	 * silent no-op on a script Enqueue.
	 *
	 * @param string $css CSS source.
	 * @return self
	 */
	public function with_style( string $css ): self {
		$this->inline_styles[] = $css;
		return $this;
	}

	/**
	 * DEPRECATED DUE TO TYPO
	 *
	 * see latest_version()
	 * @deprecated 1.3.0
	 */
	public function lastest_version(): self {
		_deprecated_function( __METHOD__, '1.3.0', __CLASS__ . '::latest_version()' );
		return $this->latest_version();
	}

	/**
	 * Sets the version as last modified file time.
	 * Doesnt set the version if the fileheader can be read.
	 *
	 * @return self
	 */
	public function latest_version(): self {
		if ( empty( $this->src ) ) {
			return $this;
		}

		$response = \wp_remote_head( $this->src, array( 'timeout' => 1 ) );
		if ( \is_wp_error( $response ) || \wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return $this;
		}

		$last_modified = \wp_remote_retrieve_header( $response, 'last-modified' );
		if ( is_array( $last_modified ) ) {
			$last_modified = end( $last_modified );
		}
		if ( ! empty( $last_modified ) ) {
			$this->ver = strtotime( (string) $last_modified );
		}
		return $this;
	}

	/**
	 * Checks to see if a file exist using URL (not path).
	 *
	 * Uses wp_remote_head() so test mocks via the pre_http_request filter can
	 * fake the response without a real network round-trip.
	 *
	 * @param string $url The URL of the file being checked.
	 * @return boolean true if it does, false if it doesnt.
	 */
	private function does_file_exist( string $url ): bool {
		$response = \wp_remote_head( $url, array( 'timeout' => 1 ) );
		if ( \is_wp_error( $response ) ) {
			return false;
		}
		return \wp_remote_retrieve_response_code( $response ) === 200;
	}

	/**
	 * Should the script be called in the footer.
	 *
	 * @param boolean $footer
	 * @return self
	 */
	public function footer( bool $footer = true ): self {
		$this->footer = $footer;
		return $this;
	}

	/**
	 * Alias for footerfalse
	 *
	 * @return self
	 */
	public function header(): self {
		$this->footer = false;
		return $this;
	}

	/**
	 * Should the script be called in the inline.
	 *
	 * @param boolean $inline
	 * @return self
	 */
	public function inline( bool $inline = true ): self {
		$this->inline = $inline;
		return $this;
	}

	/**
	 * Pass any key => value pairs to be localised with the enqueue.
	 *
	 * @param array<string, mixed> $args
	 * @return self
	 */
	public function localize( array $args ): self {
		$this->localize = $args;
		return $this;
	}

	/**
	 * Registers the script for translation via wp_set_script_translations().
	 *
	 * Only applies to scripts. On a style Enqueue this is a silent no-op so
	 * fluent chains don't need to branch.
	 *
	 * @param string      $domain Text domain.
	 * @param string|null $path   Optional path to the JSON translation files.
	 * @return self
	 */
	public function translations( string $domain, ?string $path = null ): self {
		$this->translations = array(
			'domain' => $domain,
			'path'   => $path,
		);
		return $this;
	}

	/**
	 * Attaches an inline JavaScript snippet to the registered handle.
	 *
	 * Calls stack — each call adds another entry. `$position` is `'before'` or
	 * `'after'` (matching wp_add_inline_script). Only applies to scripts; a
	 * silent no-op on a style Enqueue.
	 *
	 * @param string $code     JavaScript source.
	 * @param string $position 'before' or 'after'. Default 'after'.
	 * @return self
	 */
	public function with_code( string $code, string $position = 'after' ): self {
		$this->inline_scripts[] = array(
			'code'     => $code,
			'position' => $position,
		);
		return $this;
	}

	/**
	 * Adds a Flag (attribute with no value) to a script/style tag
	 *
	 * @param string $flag
	 * @return self
	 */
	public function flag( string $flag ): self {
		$this->attributes[ $flag ] = null;
		return $this;
	}

	/**
	 * Adds an attribute tto a script/style tag
	 *
	 * @param string $key
	 * @param string $value
	 * @return self
	 */
	public function attribute( string $key, string $value ): self {
		$this->attributes[ $key ] = $value;
		return $this;
	}

	/**
	 * Marks the script as deferred loaded.
	 *
	 * Uses WordPress 6.3+'s dep-aware script strategy via wp_script_add_data()
	 * instead of splatting the attribute through the script_loader_tag filter.
	 *
	 * @return self
	 */
	public function defer(): self {
		$this->strategy = 'defer';
		return $this;
	}

	/**
	 * Marks the script as async loaded.
	 *
	 * Uses WordPress 6.3+'s dep-aware script strategy via wp_script_add_data()
	 * instead of splatting the attribute through the script_loader_tag filter.
	 *
	 * @return self
	 */
	public function async(): self {
		$this->strategy = 'async';
		return $this;
	}

	/**
	 * Set if being enqueued for a block.
	 *
	 * @param bool $for_block Denotes if being enqueued for a block.
	 * @return self
	 */
	public function for_block( bool $for_block = true ): self {
		$this->for_block = $for_block;
		return $this;
	}

	/**
	 * Defer the registration/enqueue to the enqueue_block_editor_assets hook.
	 *
	 * When set, calling register() attaches the rest of the flow to the block
	 * editor hook instead of running immediately. Use for scripts/styles that
	 * should only load inside the admin block editor.
	 *
	 * @param bool $for_block_editor Default true.
	 * @return self
	 */
	public function for_block_editor( bool $for_block_editor = true ): self {
		$this->for_block_editor = $for_block_editor;
		return $this;
	}

	/**
	 * Attach the style to a named block via wp_enqueue_block_style().
	 *
	 * Only applies to styles. When set, register_style() calls
	 * wp_enqueue_block_style() against the block so the stylesheet loads only
	 * when the block is rendered on the front-end.
	 *
	 * @param string $block_name Block name (e.g. 'core/paragraph' or 'my-plugin/my-block').
	 * @return self
	 */
	public function for_block_style( string $block_name ): self {
		$this->block_name = $block_name;
		return $this;
	}

	/**
	 * Register the asset with WP without enqueueing it.
	 *
	 * Useful when another handle will reference this one via a dep array, or
	 * when a block's block.json names it. Keeps the handle available without
	 * emitting it on every page.
	 *
	 * @param bool $register_only Default true.
	 * @return self
	 */
	public function register_only( bool $register_only = true ): self {
		$this->register_only = $register_only;
		return $this;
	}

	/**
	 * Register a block type from a block.json metadata directory.
	 *
	 * When set, register() short-circuits to register_block_type_from_metadata()
	 * instead of the normal script/style registration path. The handle / src /
	 * deps / ver passed via the fluent API are ignored — block.json is the
	 * source of truth for asset declarations.
	 *
	 * @param string $path Absolute path to the directory containing block.json.
	 * @return self
	 */
	public function block_json( string $path ): self {
		$this->block_json_path = $path;
		return $this;
	}

	/**
	 * Registers the file as either enqueued or inline parsed.
	 *
	 * @return void
	 */
	public function register(): void {
		// block.json registration takes over completely — the handle/src/deps
		// passed via the fluent API are ignored; block.json drives the assets.
		if ( null !== $this->block_json_path ) {
			\register_block_type_from_metadata( $this->block_json_path );
			return;
		}

		// Block-editor scope: defer the rest of the flow to the editor hook.
		if ( $this->for_block_editor ) {
			add_action(
				'enqueue_block_editor_assets',
				function () {
					$this->register_inner();
				}
			);
			return;
		}

		$this->register_inner();
	}

	/**
	 * Dispatches the registration to the script or style path.
	 *
	 * Extracted from register() so it can be re-entered from the
	 * enqueue_block_editor_assets callback.
	 *
	 * @return void
	 */
	private function register_inner(): void {
		if ( $this->type === 'script' ) {
			$this->register_script();
		}

		if ( $this->type === 'style' ) {
			$this->register_style();
		}
	}

	/**
	 * Regsiters the style.
	 *
	 * @return void
	 */
	private function register_style() {
		// Block-bound style: wp_enqueue_block_style() registers and attaches it
		// to the named block, loads only when the block renders.
		if ( null !== $this->block_name ) {
			\wp_enqueue_block_style(
				$this->block_name,
				array(
					'handle' => $this->handle,
					'src'    => $this->src,
					'deps'   => $this->deps,
					'ver'    => $this->ver,
					'media'  => $this->media,
				)
			);
		} else {
			\wp_register_style(
				$this->handle,
				$this->src,
				$this->deps,
				$this->ver,
				$this->media
			);

			if ( false === $this->for_block && false === $this->register_only ) {
				wp_enqueue_style( $this->handle );
			}
		}

		foreach ( $this->inline_styles as $css ) {
			\wp_add_inline_style( $this->handle, $css );
		}

		$this->add_style_attributes();
	}

	/**
	 * Registers and enqueues or inlines the script, with any passed localised data.
	 *
	 * @return void
	 */
	private function register_script() {

		\wp_register_script(
			$this->handle,
			$this->inline === true ? '' : $this->src,
			$this->deps,
			$this->ver,
			$this->footer
		);

		// Script strategy (defer/async) via WP's dep-aware API.
		if ( null !== $this->strategy ) {
			\wp_script_add_data( $this->handle, 'strategy', $this->strategy );
		}

		// Script translations (i18n JSON lookup).
		if ( null !== $this->translations ) {
			\wp_set_script_translations(
				$this->handle,
				$this->translations['domain'],
				$this->translations['path'] ?? ''
			);
		}

		// Maybe add as an inline script (whole-file inline — legacy behaviour).
		if ( $this->inline && $this->does_file_exist( $this->src ) ) {
			$contents = file_get_contents( $this->src );
			\wp_add_inline_script( $this->handle, false !== $contents ? $contents : '' );
		}

		// Attach inline code snippets added via with_code().
		foreach ( $this->inline_scripts as $snippet ) {
			\wp_add_inline_script( $this->handle, $snippet['code'], $snippet['position'] );
		}

		// Localize all values if defined.
		if ( ! empty( $this->localize ) ) {
			\wp_localize_script( $this->handle, $this->handle, $this->localize );
		}

		// Enqueue file if not used for a block / not register-only.
		if ( false === $this->for_block && false === $this->register_only ) {
			\wp_enqueue_script( $this->handle );
		}

		$this->add_script_attributes();
	}

	/**
	 * Adds any additional attributes to a script.
	 *
	 * @return void
	 */
	private function add_script_attributes(): void {

		$attributes = $this->get_script_attributes();

		// Bail if we have no attributes.
		if ( 0 === count( $this->get_attributes() ) && $this->script_type === 'text/javascript' ) {
			return;
		}

		// Add to any scripts.
		add_filter(
			'script_loader_tag',
			function ( string $tag, string $handle, string $source ) use ( $attributes ): string {
				// Bail if not our script.
				if ( $this->handle !== $handle ) {
					return $tag;
				}
				return sprintf( '<script type="%s" src="%s" %s></script>', $this->script_type, $source, join( ' ', $attributes ) ); //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			},
			1,
			3
		);
	}

	/**
	 * Adds the ID attribute if not set for script and script type is not text/javascript.
	 *
	 * @return string[]
	 */
	private function get_script_attributes(): array {
		$attributes = $this->get_attributes();
		// Loop through and look for any that start with 'id='
		foreach ( $attributes as $key => $value ) {
			if ( \strpos( $value, 'id=' ) === 0 ) {
				return $attributes;
			}
		}

		// Add to attributes
		$attributes[] = \sprintf( "id='%s'", "{$this->handle}-js" );
		return $attributes;
	}

	/**
	 * Adds any additional attributes to a style.
	 *
	 * @return void
	 */
	private function add_style_attributes(): void {

		$attributes = $this->get_attributes();

		// Bail if we have no attributes.
		if ( 0 === count( $attributes ) ) {
			return;
		}

		// Add to any relevant styles.
		add_filter(
			'style_loader_tag',
			function ( string $tag, string $handle, string $href, string $media ) use ( $attributes ): string {
				// Bail if not our script.
				if ( $this->handle !== $handle ) {
					return $tag;
				}
				return sprintf(
					'<link rel="stylesheet" id="%s-css" href="%s" type="text/css" media="%s" %s>', //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					$handle,
					$href,
					$media,
					join( ' ', $attributes )
				);
			},
			1,
			4
		);
	}

	/**
	 * Set denotes the script type.
	 *
	 * @param string $script_type  Denotes the script type.
	 * @return self
	 */
	public function script_type( string $script_type ) {
		$this->script_type = $script_type;
		return $this;
	}

	/**
	 * Gets all attributes mapped as HTML attributes.
	 *
	 * @return string[]
	 */
	private function get_attributes(): array {
		return array_map(
			function ( string $key, ?string $value ): string {
				return null === $value
					? "{$key}"
					: "{$key}='{$value}'";
			},
			array_keys( $this->attributes ),
			$this->attributes
		);
	}
}
