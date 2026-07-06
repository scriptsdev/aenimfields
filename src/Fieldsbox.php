<?php

namespace Fieldsbox;

use Fieldsbox\Container\Container;

/**
 * Package entry point.
 *
 * Boots shared, one-time behaviour (currently just asset registration) that
 * every Field/Container needs, regardless of how many containers a
 * consuming plugin registers.
 */
final class Fieldsbox {

	/** Leaflet build used by the 'map' field - OpenStreetMap tiles, no API key required. */
	private const LEAFLET_VERSION = '1.9.4';

	/** Flatpickr build used by the 'date'/'datetime'/'time' fields. */
	private const FLATPICKR_VERSION = '4.6.13';

	/**
	 * Prevents re-registering hooks if init() is somehow called more than
	 * once (e.g. multiple plugins requiring fieldsbox.php).
	 */
	private static bool $booted = false;

	/**
	 * Wire up the package's WordPress hooks. Safe to call multiple times.
	 */
	public static function init(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;

		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue the shared admin CSS/JS, but only on a screen where a
	 * registered container will actually render - and only the extra
	 * assets (wp.media, Leaflet, Flatpickr) that the fields on *that*
	 * screen actually use. Every Container::make() call registers itself
	 * in Container::$registry, so by the time this runs (admin_enqueue_scripts
	 * fires after every container on the current request has been built
	 * and after add_meta_boxes/admin_menu have already run) we know exactly
	 * which containers apply to this screen and what field types they use.
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

		wp_enqueue_style( 'fieldsbox', FIELDSBOX_URL . '/assets/css/fieldsbox.css', array(), FIELDSBOX_VERSION );

		$needs_media     = self::any_container_uses( $containers, array( 'image', 'file', 'gallery' ) );
		$needs_leaflet   = self::any_container_uses( $containers, array( 'map' ) );
		$needs_flatpickr = self::any_container_uses( $containers, array( 'date', 'datetime', 'time' ) );

		if ( $needs_media ) {
			// Powers the image/file/gallery fields' "Select"/"Add" buttons.
			wp_enqueue_media();
		}

		$script_deps = array();

		if ( $needs_leaflet ) {
			// Powers the map field's draggable pin - free OpenStreetMap
			// tiles, no API key. Vendored locally under assets/vendor.
			wp_enqueue_style( 'leaflet', FIELDSBOX_URL . '/assets/vendor/css/leaflet.css', array(), self::LEAFLET_VERSION );
			wp_enqueue_script( 'leaflet', FIELDSBOX_URL . '/assets/vendor/js/leaflet.js', array(), self::LEAFLET_VERSION, true );
			$script_deps[] = 'leaflet';
		}

		if ( $needs_flatpickr ) {
			// Powers the date/date-time/time fields' calendar popup.
			// Vendored locally under assets/vendor.
			wp_enqueue_style( 'flatpickr', FIELDSBOX_URL . '/assets/vendor/css/flatpickr.min.css', array(), self::FLATPICKR_VERSION );
			wp_enqueue_script( 'flatpickr', FIELDSBOX_URL . '/assets/vendor/js/flatpickr.min.js', array(), self::FLATPICKR_VERSION, true );
			$script_deps[] = 'flatpickr';
		}

		wp_enqueue_script( 'fieldsbox', FIELDSBOX_URL . '/assets/js/fieldsbox.js', $script_deps, FIELDSBOX_VERSION, true );
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
