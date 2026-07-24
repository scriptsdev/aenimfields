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
	 * Whether scripts have already been registered for this request.
	 *
	 * @var bool
	 */
	protected static bool $registered = false;

	/**
	 * Whether scripts have already been enqueued for this request.
	 *
	 * @var bool
	 */
	protected static bool $enqueued = false;

	/**
	 * Register scripts.
	 *
	 * Only describes each script to WordPress (handle, src, deps,
	 * version); does not enqueue or output anything. Safe to call at any
	 * time, repeatedly — only does real work once per request.
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
	}

	/**
	 * Enqueue scripts.
	 *
	 * Called by Renderer each time a field is rendered, so a script only
	 * loads on pages that actually render a FieldsBox field. Safe to
	 * call repeatedly; only enqueues once per request.
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
}
