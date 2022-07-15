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
	 * Also used for any locaized variables.
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
	 * The file loaction (URI)
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
	 * Creates a static instace of the Enqueue class for a script.
	 *
	 * @param string $handle
	 * @return self
	 */
	public static function script( string $handle ): self {
		return new self( $handle, 'script' );
	}

	/**
	 * Creates a static instace of the Enqueue class for a style.
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
	 * Sets the version as last modified file time.
	 * Doesnt set the version if the fileheader can be read.
	 *
	 * @return self
	 */
	public function lastest_version(): self {
		if ( $this->does_file_exist( $this->src ) ) {

			// If php8 or above set as bool, else int
			$associate = ( PHP_VERSION_ID >= 80000 ) ? true : 1;

			$headers = get_headers( $this->src, $associate );

			if ( is_array( $headers )
			&& array_key_exists( 'Last-Modified', $headers )
			) {
				$this->ver = strtotime( $headers['Last-Modified'] );
			}
		}
		return $this;
	}

	/**
	 * Checks to see if a file exist using URL (not path).
	 *
	 * @param string $url The URL of the file being checked.
	 * @return boolean true if it does, false if it doesnt.
	 */
	private function does_file_exist( string $url ): bool {
		$ch = curl_init( $url );
		if ( ! $ch ) {
			return false;
		}
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT_MS, 50 );
		curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
		return $http_code === 200;
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
	public function inline( bool $inline = true ):self {
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
	 * Marks the script or style as deferred loaded.
	 *
	 * @return self
	 */
	public function defer(): self {
		// Remove ASYNC if set.
		if ( \array_key_exists( 'async', $this->attributes ) ) {
			unset( $this->attributes['async'] );
		}

		$this->attributes['defer'] = '';
		return $this;
	}

	/**
	 * Marks the script or style as async loaded.
	 *
	 * @return self
	 */
	public function async(): self {
		// Remove DEFER if set.
		if ( \array_key_exists( 'defer', $this->attributes ) ) {
			unset( $this->attributes['defer'] );
		}

		$this->attributes['async'] = '';
		return $this;
	}

	/**
	 * Set if being enqueued for a block.
	 *
	 * @param bool $for_block Denotes if being enqueued for a block.
	 * @return self
	 */
	public function for_block( bool $for_block = true ) : self {
		$this->for_block = $for_block;
		return $this;
	}

	/**
	 * Registers the file as either enqueued or inline parsed.
	 *
	 * @return void
	 */
	public function register(): void {
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

		\wp_register_style(
			$this->handle,
			$this->src,
			$this->deps,
			$this->ver,
			$this->media
		);
		if ( false === $this->for_block ) {
			wp_enqueue_style( $this->handle );
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

		// Maybe add as an inline script.
		if ( $this->inline && $this->does_file_exist( $this->src ) ) {
			\wp_add_inline_script( $this->handle, file_get_contents( $this->src ) ?: '' );
		}

		// Localize all values if defined.
		if ( ! empty( $this->localize ) ) {
			\wp_localize_script( $this->handle, $this->handle, $this->localize );
		}

		// Enqueue file if not used for a block.
		if ( false === $this->for_block ) {
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
			function( string $tag, string $handle, string $source ) use ( $attributes ): string {
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
			function( string $tag, string $handle, string $href, string $media ) use ( $attributes ): string {
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
	private function get_attributes():array {
		return array_map(
			function( string $key, ?string $value ): string {
				return null === $value
					? "{$key}"
					: "{$key}='{$value}'";
			},
			array_keys( $this->attributes ),
			$this->attributes
		);
	}

}
