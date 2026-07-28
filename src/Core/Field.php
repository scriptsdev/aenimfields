<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Core;

use ScriptsDev\FieldsBox\Contracts\FieldInterface;
use ScriptsDev\FieldsBox\Validation\ValidationResult;
use BadMethodCallException;

/**
 * Field builder facade.
 *
 * A Carbon Fields-style fluent entry point around the existing array-based
 * field engine (Registry/FieldFactory/Renderer/Sanitizer/Validator) — no
 * change to how fields are actually built, sanitized, or rendered.
 *
 * ```php
 * echo Field::make( 'text', 'site_tagline', 'Site Tagline' )
 *     ->set_required()
 *     ->set_description( 'Shown under the site title.' )
 *     ->render();
 * ```
 *
 * @since 1.0.0
 */
class Field {

	/**
	 * Whether the underlying Application has already been bootstrapped
	 * (field types + assets registered) for this request.
	 *
	 * @var bool
	 */
	protected static bool $booted = false;

	/**
	 * Wrapped field instance.
	 *
	 * @var FieldInterface
	 */
	protected FieldInterface $field;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param FieldInterface $field
	 */
	protected function __construct( FieldInterface $field ) {
		$this->field = $field;
	}

	/**
	 * Start building a field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type  Registered field type, e.g. 'text', 'repeater'.
	 * @param string $name  Field name.
	 * @param string $label Field label.
	 *
	 * @return static
	 */
	public static function make( string $type, string $name, string $label = '' ) {

		if ( ! self::$booted ) {
			new Application();
			self::$booted = true;
		}

		$factory = new FieldFactory();

		return new static(
			$factory->make(
				array(
					'type'  => $type,
					'name'  => $name,
					'label' => $label,
				)
			)
		);
	}

	/**
	 * Show/hide this field based on a sibling field's value.
	 *
	 * Bridges to the `depends_on` arg used by the array API and by
	 * `assets/js/dependency.js`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field Sibling field name.
	 * @param mixed  $value Expected value.
	 *
	 * @return $this
	 */
	public function set_conditional_logic( string $field, $value ) {
		$this->field->set_arg(
			'depends_on',
			array(
				'field' => $field,
				'value' => $value,
			)
		);

		return $this;
	}

	/**
	 * Set the current value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function set_value( $value ) {
		$this->field->set_value( $value );

		return $this;
	}

	/**
	 * Generic `set_<arg>( $value )` passthrough to the underlying field's
	 * args array, e.g. `set_required()`, `set_options( [...] )`,
	 * `set_min_rows( 1 )`, `set_date_format( 'Y-m-d' )`.
	 *
	 * A boolean-style call with no argument (`set_required()`) sets `true`.
	 * `set_fields( [...] )` (repeater/group sub-fields) also accepts a mix
	 * of raw arg arrays and other `Field` builders.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return $this
	 */
	public function __call( string $method, array $arguments ) {

		if ( strncmp( $method, 'set_', 4 ) !== 0 ) {
			throw new BadMethodCallException(
				sprintf( 'Call to undefined method %s::%s()', static::class, $method )
			);
		}

		$key   = substr( $method, 4 );
		$value = array_key_exists( 0, $arguments ) ? $arguments[0] : true;

		if ( 'fields' === $key && is_array( $value ) ) {
			$value = array_map(
				static fn( $sub_field ) => $sub_field instanceof self ? $sub_field->to_array() : $sub_field,
				$value
			);
		}

		$this->field->set_arg( $key, $value );

		return $this;
	}

	/**
	 * Render the field's markup.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function render(): string {
		return ( new Renderer() )->render( $this->field );
	}

	/**
	 * Sanitize a submitted value for this field.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function sanitize( $value ) {
		return Sanitizer::sanitize( $this->field, $value );
	}

	/**
	 * Validate a submitted value for this field.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return \ScriptsDev\FieldsBox\Validation\ValidationResult
	 */
	public function validate( $value ) {
		return Validator::validate( $this->field, $value );
	}

	/**
	 * Get the field's full args array — the same shape the array-based
	 * `Application::render()` API accepts, e.g. to nest inside a raw
	 * `repeater`/`group` `fields` array.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function to_array(): array {
		return $this->field->get_args();
	}

	/**
	 * Get the underlying field instance.
	 *
	 * @since 1.0.0
	 *
	 * @return FieldInterface
	 */
	public function get_field(): FieldInterface {
		return $this->field;
	}
}
