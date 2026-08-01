<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Contracts;

/**
 * Interface FieldInterface
 *
 * Defines the contract for all AenimFields field types.
 *
 * Every field must:
 * - Accept field configuration.
 * - Return its configuration.
 * - Render itself.
 * - Sanitize its value.
 * - Validate its value.
 *
 * @since 1.0.0
 */
interface FieldInterface extends
	SanitizableInterface,
	ValidatableInterface {

	/**
	 * Create a new field instance.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field configuration.
	 */
	public function __construct( array $args = array() );

	/**
	 * Returns the complete field configuration.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_args(): array;

	/**
	 * Returns a single field argument.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_arg( string $key, $default = null );

	/**
	 * Set or overwrite a field argument.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function set_arg( string $key, $value );

	/**
	 * Returns the field type.
	 *
	 * Example:
	 * text
	 * select
	 * checkbox
	 * repeater
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_type(): string;

	/**
	 * Returns the field name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Returns the HTML ID.
	 *
	 * Defaults to the field name if not supplied.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * Returns the current field value.
	 *
	 * Falls back to the default value if no value exists.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_value();

	/**
	 * Set the current field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function set_value( $value );

	/**
	 * Returns the default value.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_default();

	/**
	 * Returns whether the field is required.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_required(): bool;

	/**
	 * Returns the keys of any assets (scripts/styles) this field needs
	 * enqueued in order to render/function, e.g. `['datepicker']`.
	 *
	 * Keys are resolved by `Core\Assets::enqueue()`. Fields with no
	 * extra asset needs return an empty array.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function assets(): array;
}
