<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

use ScriptsDev\FieldsBox\Contracts\ContainerFieldInterface;
use ScriptsDev\FieldsBox\Core\FieldFactory;
use ScriptsDev\FieldsBox\Core\Sanitizer;
use ScriptsDev\FieldsBox\Core\Validator;

/**
 * Group field.
 *
 * A fixed set of sub-fields (declared in the `fields` arg) nested under
 * one field — unlike Repeater, there is exactly one instance, not a
 * repeatable list of rows. Stores a single associative array of
 * `sub_field_name => value`.
 *
 * A sub-field's `depends_on.field` may reference another sub-field in
 * the same group by its plain (unqualified) name — it is rewritten to
 * the group's fully-qualified field name automatically when rendered.
 *
 * A group can itself be used as a sub-field of a Repeater or another
 * Group; its own field name is already qualified by its parent by the
 * time it renders, so nesting composes without any special handling.
 *
 * @since 1.0.0
 */
class Group extends BaseField implements ContainerFieldInterface {

	/**
	 * Get child field definitions.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_fields(): array {
		return (array) $this->get_arg( 'fields', array() );
	}

	/**
	 * Set child field definitions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 *
	 * @return static
	 */
	public function set_fields( array $fields ) {
		$this->set_arg( 'fields', $fields );

		return $this;
	}

	/**
	 * Add a child field definition.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field
	 *
	 * @return static
	 */
	public function add_field( array $field ) {
		$fields   = $this->get_fields();
		$fields[] = $field;

		$this->set_arg( 'fields', $fields );

		return $this;
	}

	/**
	 * Whether the group has any child field definitions.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fields(): bool {
		return ! empty( $this->get_fields() );
	}

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return array
	 */
	public function sanitize( $value ): array {

		if ( ! is_array( $value ) ) {
			return array();
		}

		$factory = new FieldFactory();
		$clean   = array();

		foreach ( $this->get_fields() as $sub_field_args ) {

			$sub_name = (string) ( $sub_field_args['name'] ?? '' );

			if ( '' === $sub_name ) {
				continue;
			}

			$sub_field = $factory->make( $sub_field_args );
			$raw       = $value[ $sub_name ] ?? '';

			$clean[ $sub_name ] = Sanitizer::sanitize( $sub_field, $raw );
		}

		return $clean;
	}

	/**
	 * Validate the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return true|string
	 */
	public function validate( $value ) {

		$result = parent::validate( $value );

		if ( true !== $result ) {
			return $result;
		}

		if ( ! is_array( $value ) ) {
			return true;
		}

		$factory = new FieldFactory();

		foreach ( $this->get_fields() as $sub_field_args ) {

			$sub_name = (string) ( $sub_field_args['name'] ?? '' );

			if ( '' === $sub_name ) {
				continue;
			}

			$sub_field  = $factory->make( $sub_field_args );
			$sub_value  = $value[ $sub_name ] ?? '';
			$sub_result = Validator::validate( $sub_field, $sub_value );

			if ( ! $sub_result->passed() ) {
				return $sub_result->message();
			}
		}

		return true;
	}

	/**
	 * Build the args for this group's sub-fields, ready to pass to
	 * Application::render().
	 *
	 * Qualifies each sub-field's `name` (so submitted values arrive as
	 * `{group_name}[{sub_field_name}]`) and `id`, fills in the group's
	 * stored value, and rewrites any `depends_on.field` that references
	 * a sibling sub-field's plain name to that sibling's qualified name.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function build_field_args(): array {

		$sub_fields = $this->get_fields();
		$value      = (array) $this->get_value();
		$name_map   = array();

		foreach ( $sub_fields as $sub_field ) {

			$sub_name = (string) ( $sub_field['name'] ?? '' );

			if ( '' !== $sub_name ) {
				$name_map[ $sub_name ] = $this->get_name() . '[' . $sub_name . ']';
			}
		}

		$fields = array();

		foreach ( $sub_fields as $sub_field ) {

			$sub_name = (string) ( $sub_field['name'] ?? '' );

			if ( '' === $sub_name ) {
				$fields[] = $sub_field;
				continue;
			}

			$sub_field['name']  = $name_map[ $sub_name ];
			$sub_field['id']    = $this->get_id() . '_' . $sub_name;
			$sub_field['value'] = $value[ $sub_name ] ?? ( $sub_field['value'] ?? null );

			if ( ! empty( $sub_field['depends_on']['field'] )
				&& isset( $name_map[ $sub_field['depends_on']['field'] ] )
			) {
				$sub_field['depends_on']['field'] = $name_map[ $sub_field['depends_on']['field'] ];
			}

			$fields[] = $sub_field;
		}

		return $fields;
	}
}
