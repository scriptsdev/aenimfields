<?php

declare(strict_types=1);

namespace FieldsBox\Core;

/**
 * Assets class.
 *
 * Registers FieldsBox's scripts on construction — cheap, since this only
 * describes them to WordPress and does not load them — then enqueues
 * them on demand, keyed by name, only when a field that actually needs
 * them is rendered (see FieldInterface::assets() / Renderer::render()).
 * A page that never renders a FieldsBox field never loads FieldsBox's JS.
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * Flatpickr's bundled version.
	 *
	 * @var string
	 */
	const FLATPICKR_VERSION = '4.6.13';

	/**
	 * Leaflet's bundled version.
	 *
	 * @var string
	 */
	const LEAFLET_VERSION = '1.9.4';

	/**
	 * Whether scripts have already been registered for this request.
	 *
	 * @var bool
	 */
	protected static bool $registered = false;

	/**
	 * Which asset keys have already been enqueued for this request.
	 *
	 * @var array<string, bool>
	 */
	protected static array $enqueued = array();

	/**
	 * The Google Maps JavaScript API key, set by the consuming plugin via
	 * set_google_maps_api_key(). Empty until then.
	 *
	 * @var string
	 */
	protected static string $google_maps_api_key = '';

	/**
	 * Set the Google Maps JavaScript API key.
	 *
	 * Call this once from the consuming plugin's own bootstrap (it's a
	 * site-wide credential, not something to repeat on every field
	 * definition). A `map` field with `provider => 'google'` renders an
	 * inline notice instead of a map if no key has been set by the time
	 * it's enqueued.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public static function set_google_maps_api_key( string $key ): void {
		self::$google_maps_api_key = $key;
	}

	/**
	 * Get the configured Google Maps JavaScript API key.
	 *
	 * @since 1.0.0
	 *
	 * @return string Empty string if none has been set.
	 */
	public static function get_google_maps_api_key(): string {
		return self::$google_maps_api_key;
	}

	/**
	 * Register scripts and styles.
	 *
	 * Only describes each asset to WordPress (handle, src, deps, version);
	 * does not enqueue or output anything. Safe to call at any time,
	 * repeatedly — only does real work once per request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function register(): void {

		if ( self::$registered ) {
			return;
		}

		self::$registered = true;

		wp_register_script(
			'fieldsbox-dependency',
			FIELDSBOX_URL . 'assets/js/dependency.js',
			array(),
			FIELDSBOX_VERSION,
			true
		);

		wp_register_style(
			'fieldsbox-flatpickr',
			FIELDSBOX_URL . 'assets/vendor/css/flatpickr.min.css',
			array(),
			self::FLATPICKR_VERSION
		);

		wp_register_script(
			'fieldsbox-flatpickr',
			FIELDSBOX_URL . 'assets/vendor/js/flatpickr.min.js',
			array(),
			self::FLATPICKR_VERSION,
			true
		);

		wp_register_script(
			'fieldsbox-datepicker',
			FIELDSBOX_URL . 'assets/js/datepicker.js',
			array( 'fieldsbox-flatpickr' ),
			FIELDSBOX_VERSION,
			true
		);

		wp_register_script(
			'fieldsbox-media',
			FIELDSBOX_URL . 'assets/js/media.js',
			array( 'media-editor' ),
			FIELDSBOX_VERSION,
			true
		);

		wp_register_script(
			'fieldsbox-repeater',
			FIELDSBOX_URL . 'assets/js/repeater.js',
			array( 'fieldsbox-dependency' ),
			FIELDSBOX_VERSION,
			true
		);

		wp_register_style(
			'fieldsbox-leaflet',
			FIELDSBOX_URL . 'assets/vendor/css/leaflet.css',
			array(),
			self::LEAFLET_VERSION
		);

		wp_register_script(
			'fieldsbox-leaflet',
			FIELDSBOX_URL . 'assets/vendor/js/leaflet.js',
			array(),
			self::LEAFLET_VERSION,
			true
		);

		wp_register_style(
			'fieldsbox-map',
			FIELDSBOX_URL . 'assets/css/map.css',
			array(),
			FIELDSBOX_VERSION
		);

		wp_register_script(
			'fieldsbox-map-osm',
			FIELDSBOX_URL . 'assets/js/map-osm.js',
			array( 'fieldsbox-leaflet' ),
			FIELDSBOX_VERSION,
			true
		);

		// 'fieldsbox-map-google' is registered lazily, from the 'map_google'
		// manifest entry below, once its dependency on 'fieldsbox-google-maps'
		// (which itself needs the API key) can be declared correctly.
	}

	/**
	 * The asset manifest.
	 *
	 * Maps an asset key (as returned by FieldInterface::assets()) to the
	 * callback that actually enqueues it. Adding a new field type that
	 * needs a new heavy asset means registering its handles in
	 * register() and adding one entry here — no new method, no new
	 * guard property.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, callable>
	 */
	protected static function manifest(): array {
		return array(
			'dependency' => static function (): void {
				wp_enqueue_script( 'fieldsbox-dependency' );
			},
			'datepicker' => static function (): void {
				wp_enqueue_style( 'fieldsbox-flatpickr' );
				wp_enqueue_script( 'fieldsbox-flatpickr' );
				wp_enqueue_script( 'fieldsbox-datepicker' );
			},
			'media'      => static function (): void {
				wp_enqueue_media();
				wp_enqueue_script( 'fieldsbox-media' );
			},
			'repeater'   => static function (): void {
				wp_enqueue_script( 'fieldsbox-repeater' );
			},
			'map_osm'    => static function (): void {
				wp_enqueue_style( 'fieldsbox-leaflet' );
				wp_enqueue_style( 'fieldsbox-map' );
				wp_enqueue_script( 'fieldsbox-leaflet' );
				wp_enqueue_script( 'fieldsbox-map-osm' );
			},
			'map_google' => static function (): bool {

				$api_key = self::$google_maps_api_key;

				if ( '' === $api_key ) {
					// No key configured; the field template renders its
					// own inline notice, so there's nothing to enqueue.
					// Returning false stops enqueue() from caching this
					// key as done, so a later render (once the key is
					// set) can still succeed.
					return false;
				}

				wp_register_script(
					'fieldsbox-google-maps',
					add_query_arg(
						array(
							'key'       => $api_key,
							'libraries' => 'places',
						),
						'https://maps.googleapis.com/maps/api/js'
					),
					array(),
					null,
					true
				);

				wp_register_script(
					'fieldsbox-map-google',
					FIELDSBOX_URL . 'assets/js/map-google.js',
					array( 'fieldsbox-google-maps' ),
					FIELDSBOX_VERSION,
					true
				);

				wp_enqueue_style( 'fieldsbox-map' );
				wp_enqueue_script( 'fieldsbox-google-maps' );
				wp_enqueue_script( 'fieldsbox-map-google' );

				return true;
			},
		);
	}

	/**
	 * Enqueue an asset by key.
	 *
	 * Safe to call repeatedly with the same key; only enqueues once per
	 * request. Unknown keys are silently ignored. If a manifest callback
	 * returns false (e.g. 'map_google' with no API key configured yet),
	 * the key is NOT cached as done, so a later call — once the
	 * precondition is met — can still succeed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Asset key, e.g. 'datepicker', 'media', 'repeater'.
	 *
	 * @return void
	 */
	public static function enqueue( string $key = 'dependency' ): void {

		if ( isset( self::$enqueued[ $key ] ) ) {
			return;
		}

		self::register();

		$manifest = self::manifest();

		if ( isset( $manifest[ $key ] ) && false === $manifest[ $key ]() ) {
			return;
		}

		self::$enqueued[ $key ] = true;
	}
}
