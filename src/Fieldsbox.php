<?php

namespace Fieldsbox;

use Fieldsbox\Container\Container;

/**
 * Package entry point. Boots the shared asset-enqueue hooks every
 * Field/Container needs.
 *
 * Self-boots at the bottom of this file instead of relying on
 * fieldsbox.php, since Composer's autoloader can satisfy "Fieldsbox\Fieldsbox"
 * straight from here without fieldsbox.php ever running.
 */
final class Fieldsbox {

	private const VERSION = '0.1.0';

	/** Leaflet build used by the 'map' field - OpenStreetMap tiles, no API key required. */
	private const LEAFLET_VERSION = '1.9.4';

	/** Flatpickr build used by the 'date'/'datetime'/'time' fields. */
	private const FLATPICKR_VERSION = '4.6.13';

	private static bool $booted = false;

	/** Set via set_google_maps_api_key() - unset by default, so nothing Google-related loads unless a plugin opts in. */
	private static string $google_maps_api_key = '';

	/**
	 * Wire up the package's WordPress hooks. Safe to call multiple times.
	 */
	public static function init(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;

		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
		// Fallback for containers registered later, e.g. from add_meta_boxes.
		add_action( 'admin_footer', array( self::class, 'enqueue_late_assets' ) );
	}

	/**
	 * Register the Google Maps API key needed by the 'google_map' field
	 * type. Without it, GoogleMapField shows a setup notice instead of a map.
	 */
	public static function set_google_maps_api_key( string $api_key ): void {
		self::$google_maps_api_key = $api_key;
	}

	/**
	 * @internal Read by GoogleMapField and enqueue_assets() only.
	 */
	public static function get_google_maps_api_key(): string {
		return self::$google_maps_api_key;
	}

	/**
	 * This package's own base URL, computed from this file's location.
	 */
	private static function url(): string {
		static $url = null;

		if ( null === $url ) {
			$url = plugins_url( '', dirname( __DIR__ ) . '/fieldsbox.php' );
		}

		return $url;
	}

	/**
	 * admin_footer fallback, re-running enqueue_assets() for containers
	 * registered after admin_enqueue_scripts already fired.
	 */
	public static function enqueue_late_assets(): void {
		global $hook_suffix;

		self::enqueue_assets( (string) $hook_suffix );
	}

	/**
	 * Enqueue shared admin CSS/JS on screens where a registered container
	 * will render, plus only the extra assets (wp.media, Leaflet, Flatpickr,
	 * Google Maps) the field types on that screen actually use.
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		$containers = array_filter(
			Container::get_registry(),
			static function ( Container $container ) use ( $hook_suffix ) {
				return $container->matches_screen( $hook_suffix );
			}
		);

		if ( ! $containers ) {
			return;
		}

		$url = self::url();

		$style_handles = array( 'fieldsbox' );
		wp_enqueue_style( 'fieldsbox', $url . '/assets/css/fieldsbox.css', array(), self::VERSION );

		$needs_media       = self::any_container_uses( $containers, array( 'image', 'file', 'gallery' ) );
		$needs_leaflet     = self::any_container_uses( $containers, array( 'map' ) );
		$needs_flatpickr   = self::any_container_uses( $containers, array( 'date', 'datetime', 'time' ) );
		$needs_google_maps = self::any_container_uses( $containers, array( 'google_map' ) );

		if ( $needs_media ) {
			wp_enqueue_media();
		}

		$script_deps = array();

		if ( $needs_leaflet ) {
			wp_enqueue_style( 'leaflet', $url . '/assets/vendor/css/leaflet.css', array(), self::LEAFLET_VERSION );
			wp_enqueue_script( 'leaflet', $url . '/assets/vendor/js/leaflet.js', array(), self::LEAFLET_VERSION, true );
			$style_handles[] = 'leaflet';
			$script_deps[]   = 'leaflet';
		}

		if ( $needs_flatpickr ) {
			wp_enqueue_style( 'flatpickr', $url . '/assets/vendor/css/flatpickr.min.css', array(), self::FLATPICKR_VERSION );
			wp_enqueue_script( 'flatpickr', $url . '/assets/vendor/js/flatpickr.min.js', array(), self::FLATPICKR_VERSION, true );
			$style_handles[] = 'flatpickr';
			$script_deps[]   = 'flatpickr';
		}

		if ( $needs_google_maps && self::$google_maps_api_key ) {
			// Google's hosted script - their terms don't allow self-hosting it.
			wp_enqueue_script(
				'fieldsbox-google-maps',
				'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode( self::$google_maps_api_key ) . '&libraries=places&v=weekly',
				array(),
				false,
				true
			);
			$script_deps[] = 'fieldsbox-google-maps';
		}

		// Styles enqueued after <head> has already printed need a manual push.
		if ( did_action( 'admin_print_styles' ) ) {
			wp_print_styles( $style_handles );
		}

		wp_enqueue_script( 'fieldsbox', $url . '/assets/js/fieldsbox.js', $script_deps, self::VERSION, true );
	}

	/**
	 * @param Container[] $containers
	 * @param string[]    $types
	 */
	private static function any_container_uses( array $containers, array $types ): bool {
		foreach ( $containers as $container ) {
			foreach ( $types as $type ) {
				if ( $container->uses_field_type( $type ) ) {
					return true;
				}
			}
		}

		return false;
	}
}

Fieldsbox::init();
