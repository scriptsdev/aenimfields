<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Core;

use ScriptsDev\FieldsBox\Fields\Checkbox;
use ScriptsDev\FieldsBox\Fields\Color;
use ScriptsDev\FieldsBox\Fields\Date;
use ScriptsDev\FieldsBox\Fields\DateTime;
use ScriptsDev\FieldsBox\Fields\Email;
use ScriptsDev\FieldsBox\Fields\File;
use ScriptsDev\FieldsBox\Fields\Gallery;
use ScriptsDev\FieldsBox\Fields\Group;
use ScriptsDev\FieldsBox\Fields\Heading;
use ScriptsDev\FieldsBox\Fields\Hidden;
use ScriptsDev\FieldsBox\Fields\Image;
use ScriptsDev\FieldsBox\Fields\Map;
use ScriptsDev\FieldsBox\Fields\MultiSelect;
use ScriptsDev\FieldsBox\Fields\Number;
use ScriptsDev\FieldsBox\Fields\Password;
use ScriptsDev\FieldsBox\Fields\Radio;
use ScriptsDev\FieldsBox\Fields\Repeater;
use ScriptsDev\FieldsBox\Fields\Select;
use ScriptsDev\FieldsBox\Fields\Separator;
use ScriptsDev\FieldsBox\Fields\Text;
use ScriptsDev\FieldsBox\Fields\Textarea;
use ScriptsDev\FieldsBox\Fields\Toggle;
use ScriptsDev\FieldsBox\Fields\Url;
use ScriptsDev\FieldsBox\Fields\Wysiwyg;

/**
 * Application class.
 *
 * Bootstraps the field registry and provides the public API for
 * rendering fields.
 *
 * @since 1.0.0
 */
class Application {

	/**
	 * Field factory.
	 *
	 * @var FieldFactory
	 */
	protected FieldFactory $factory;

	/**
	 * Renderer.
	 *
	 * @var Renderer
	 */
	protected Renderer $renderer;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		self::define_constants();

		$this->factory  = new FieldFactory();
		$this->renderer = new Renderer();

		$this->register_fields();

		Assets::register();
	}

	/**
	 * Define the FIELDSBOX_* constants if they are not already defined.
	 *
	 * FieldsBox is a Composer library only — it has no plugin entry point
	 * of its own, so nothing else defines these. Derived from this file's
	 * own location, which works whether FieldsBox is bundled inside a
	 * plugin's or a theme's `vendor/` directory.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected static function define_constants(): void {

		if ( ! defined( 'FIELDSBOX_DIR' ) ) {
			define( 'FIELDSBOX_DIR', dirname( __DIR__, 2 ) );
		}

		if ( ! defined( 'FIELDSBOX_URL' ) ) {
			define(
				'FIELDSBOX_URL',
				trailingslashit( content_url( str_replace( WP_CONTENT_DIR, '', FIELDSBOX_DIR ) ) )
			);
		}

		if ( ! defined( 'FIELDSBOX_VERSION' ) ) {
			define( 'FIELDSBOX_VERSION', '1.0.0' );
		}
	}

	/**
	 * Register all built-in field types.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function register_fields(): void {
		Registry::register( 'text', Text::class );
		Registry::register( 'textarea', Textarea::class );
		Registry::register( 'number', Number::class );
		Registry::register( 'email', Email::class );
		Registry::register( 'url', Url::class );
		Registry::register( 'password', Password::class );
		Registry::register( 'hidden', Hidden::class );
		Registry::register( 'checkbox', Checkbox::class );
		Registry::register( 'radio', Radio::class );
		Registry::register( 'toggle', Toggle::class );
		Registry::register( 'select', Select::class );
		Registry::register( 'multiselect', MultiSelect::class );
		Registry::register( 'color', Color::class );
		Registry::register( 'date', Date::class );
		Registry::register( 'datetime', DateTime::class );
		Registry::register( 'file', File::class );
		Registry::register( 'image', Image::class );
		Registry::register( 'gallery', Gallery::class );
		Registry::register( 'map', Map::class );
		Registry::register( 'repeater', Repeater::class );
		Registry::register( 'group', Group::class );
		Registry::register( 'wysiwyg', Wysiwyg::class );
		Registry::register( 'separator', Separator::class );
		Registry::register( 'heading', Heading::class );
	}

	/**
	 * Render one or more fields.
	 *
	 * Accepts either a single field's args, e.g. `['type' => 'text', ...]`,
	 * or a list of field args, e.g. `[['type' => 'text', ...], ['type' => 'email', ...]]`.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 *
	 * @return string
	 */
	public function render( array $fields ): string {

		if ( $this->is_single_field( $fields ) ) {
			$fields = array( $fields );
		}

		$output = '';

		foreach ( $fields as $args ) {

			$field = $this->factory->make( $args );

			$output .= $this->renderer->render( $field );
		}

		return $output;
	}

	/**
	 * Determine whether $fields holds a single field's args rather than
	 * a list of field args.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 *
	 * @return bool
	 */
	protected function is_single_field( array $fields ): bool {
		return isset( $fields['type'] ) && is_string( $fields['type'] );
	}
}
