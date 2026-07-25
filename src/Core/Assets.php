<?php

declare(strict_types=1);

namespace FieldsBox\Core;

/**
 * Assets class.
 *
 * Registers FieldsBox's scripts on construction — cheap, since this only
 * describes them to WordPress and does not load them — then enqueues
 * them on demand from Renderer, only when a field is actually rendered.
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
	 * Whether scripts have already been registered for this request.
	 *
	 * @var bool
	 */
	protected static bool $registered = false;

	/**
	 * Whether the dependency script has already been enqueued for this
	 * request.
	 *
	 * @var bool
	 */
	protected static bool $enqueued = false;

	/**
	 * Whether the datepicker assets have already been enqueued for this
	 * request.
	 *
	 * @var bool
	 */
	protected static bool $datepicker_enqueued = false;

	/**
	 * Whether the media assets have already been enqueued for this request.
	 *
	 * @var bool
	 */
	protected static bool $media_enqueued = false;

	/**
	 * Whether the repeater script has already been enqueued for this
	 * request.
	 *
	 * @var bool
	 */
	protected static bool $repeater_enqueued = false;

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
	}

	/**
	 * Enqueue the dependency (depends_on) script.
	 *
	 * Called by Renderer each time a field is rendered, so it only loads
	 * on pages that actually render a FieldsBox field. Safe to call
	 * repeatedly; only enqueues once per request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue(): void {

		if ( self::$enqueued ) {
			return;
		}

		self::$enqueued = true;

		self::register();

		wp_enqueue_script( 'fieldsbox-dependency' );
	}

	/**
	 * Enqueue flatpickr and its FieldsBox init script.
	 *
	 * Called only by the date/datetime field templates, so the (heavier)
	 * flatpickr library only loads on pages that actually render a date
	 * or datetime field — not on every FieldsBox field like enqueue()
	 * above. Safe to call repeatedly; only enqueues once per request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_datepicker(): void {

		if ( self::$datepicker_enqueued ) {
			return;
		}

		self::$datepicker_enqueued = true;

		self::register();

		wp_enqueue_style( 'fieldsbox-flatpickr' );
		wp_enqueue_script( 'fieldsbox-flatpickr' );
		wp_enqueue_script( 'fieldsbox-datepicker' );
	}

	/**
	 * Enqueue the WordPress media library and its FieldsBox init script.
	 *
	 * Called only by the image/gallery/file field templates, so
	 * wp_enqueue_media() (needed for wp.media(), the media library
	 * picker) only loads on pages that actually render one of those
	 * field types. Safe to call repeatedly; only enqueues once per
	 * request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_media(): void {

		if ( self::$media_enqueued ) {
			return;
		}

		self::$media_enqueued = true;

		self::register();

		wp_enqueue_media();
		wp_enqueue_script( 'fieldsbox-media' );
	}

	/**
	 * Enqueue the repeater init script.
	 *
	 * Called only by the repeater field template, so it only loads on
	 * pages that actually render a repeater field. Safe to call
	 * repeatedly; only enqueues once per request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueue_repeater(): void {

		if ( self::$repeater_enqueued ) {
			return;
		}

		self::$repeater_enqueued = true;

		self::register();

		wp_enqueue_script( 'fieldsbox-repeater' );
	}
}
