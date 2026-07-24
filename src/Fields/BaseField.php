<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

use FieldsBox\Contracts\FieldInterface;

/**
 * Base field class.
 *
 * Provides shared argument handling, value/default resolution, and
 * default sanitization/validation behavior for all concrete field types.
 *
 * @since 1.0.0
 */
abstract class BaseField implements FieldInterface {

	/**
	 * Field configuration.
	 *
	 * @var array
	 */
	protected array $args = array();

	/**
	 * Shared counter used to generate a fallback id for fields that have
	 * neither an explicit `id` nor a `name` (e.g. a display-only separator).
	 *
	 * @var int
	 */
	protected static int $auto_id = 0;

	/**
	 * Default field arguments.
	 *
	 * @var array
	 */
	protected array $defaults = array(
		'name'              => '',
		'id'                => '',
		'type'              => '',
		'label'             => '',
		'label_description' => '',
		'description'       => '',
		'help'              => '',
		'error'             => '',

		'text' => '',

		'value'         => null,
		'default'       => null,

		'placeholder'   => '',

		'required'      => false,
		'readonly'      => false,
		'disabled'      => false,

		'class'         => '',
		'wrapper_class' => '',

		'attributes'    => array(),
		'input_attr'    => array(),
		'label_attr'    => array(),
		'wrapper_attr'  => array(),

		'rows'          => 5,
		'cols'          => 50,

		'options'       => array(),
		'depends_on'    => array(),
		'validate'      => array(),

		'min'           => '',
		'max'           => '',
		'step'          => '',

		'escape'        => true,

		'prefix'        => '',
		'suffix'        => '',

		'before'        => '',
		'after'         => '',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( array $args = array() ) {
		$this->args = array_merge( $this->defaults, $args );

		if ( empty( $this->args['id'] ) ) {
			$this->args['id'] = '' !== $this->args['name']
				? $this->args['name']
				: 'fieldsbox-field-' . ++self::$auto_id;
		}
	}

	/**
	 * Get all arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_args(): array {
		return $this->args;
	}

	/**
	 * Get a single argument.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_arg( string $key, $default = null ) {
		return $this->args[ $key ] ?? $default;
	}

	/**
	 * Set a field argument.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function set_arg( string $key, $value ) {
		$this->args[ $key ] = $value;

		return $this;
	}

	/**
	 * Get field type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_type(): string {
		return (string) $this->get_arg( 'type' );
	}

	/**
	 * Get field name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_name(): string {
		return (string) $this->get_arg( 'name' );
	}

	/**
	 * Get HTML id.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_id(): string {
		return (string) $this->get_arg( 'id', $this->get_name() );
	}

	/**
	 * Get current value.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_value() {
		$value = $this->get_arg( 'value' );

		if ( $value === null ) {
			$value = $this->get_default();
		}

		return $value;
	}

	/**
	 * Set current value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function set_value( $value ) {
		$this->args['value'] = $value;

		return $this;
	}

	/**
	 * Get default value.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_default() {
		return $this->get_arg( 'default' );
	}

	/**
	 * Is field required?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_required(): bool {
		return (bool) $this->get_arg( 'required' );
	}

	/**
	 * Default sanitization.
	 *
	 * Child classes should override this.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function sanitize( $value ) {
		return $value;
	}

	/**
	 * Default validation.
	 *
	 * Child classes may override this.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return true|string
	 */
	public function validate( $value ) {
		if ( $this->is_required() ) {
			$empty = is_array( $value )
				? empty( array_filter( $value ) )
				: ( $value === null || $value === '' );

			if ( $empty ) {
				return sprintf(
					__( 'The "%s" field is required.', 'fieldsbox' ),
					$this->get_arg( 'label', $this->get_name() )
				);
			}
		}

		return true;
	}

	/**
	 * Escape attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected function esc_attr( $value ): string {
		return esc_attr( (string) $value );
	}

	/**
	 * Escape HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected function esc_html( $value ): string {
		return esc_html( (string) $value );
	}

	/**
	 * Escape textarea.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected function esc_textarea( $value ): string {
		return esc_textarea( (string) $value );
	}
}
