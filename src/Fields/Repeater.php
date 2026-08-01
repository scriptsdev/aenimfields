<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Fields;

use AenimTech\AenimFields\Contracts\ContainerFieldInterface;
use AenimTech\AenimFields\Core\FieldFactory;
use AenimTech\AenimFields\Core\Validator;
use AenimTech\AenimFields\Sanitization\RepeaterSanitizer;

/**
 * Repeater field.
 *
 * Renders an add/remove list of repeated rows, each containing the
 * sub-fields declared in the `fields` arg. Stores an array of rows,
 * each row an associative array of `sub_field_name => value`.
 *
 * `min_rows` / `max_rows` (both optional, default unlimited) constrain
 * the number of rows, enforced both in the browser and in validate().
 *
 * A sub-field's `depends_on.field` may reference another sub-field in
 * the same row by its plain (unqualified) name — it is rewritten to
 * that row's fully-qualified field name automatically when rendered.
 *
 * @since 1.0.0
 */
class Repeater extends BaseField implements ContainerFieldInterface {

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
	 * Whether the repeater has any child field definitions.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fields(): bool {
		return ! empty( $this->get_fields() );
	}

	/**
	 * Assets this field needs enqueued.
	 *
	 * Only its own add/remove-row script — any sub-fields' assets
	 * (e.g. an `image` or `date` sub-field) are picked up automatically
	 * when each row is rendered, since every sub-field goes through the
	 * same Renderer::render() as a top-level field.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function assets(): array {
		return array( 'repeater' );
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
		return RepeaterSanitizer::sanitize( $value, $this->get_fields() );
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

		$label    = (string) $this->get_arg( 'label', $this->get_name() );
		$min_rows = (int) $this->get_arg( 'min_rows', 0 );
		$max_rows = (int) $this->get_arg( 'max_rows', 0 );
		$count    = count( $value );

		if ( $min_rows > 0 && $count < $min_rows ) {
			return sprintf(
				__( 'The "%1$s" field must have at least %2$d row(s).', 'aenimfields' ),
				$label,
				$min_rows
			);
		}

		if ( $max_rows > 0 && $count > $max_rows ) {
			return sprintf(
				__( 'The "%1$s" field must have %2$d row(s) at most.', 'aenimfields' ),
				$label,
				$max_rows
			);
		}

		$factory = new FieldFactory();

		foreach ( $value as $row_index => $row ) {

			foreach ( $this->get_fields() as $sub_field_args ) {

				$sub_name = (string) ( $sub_field_args['name'] ?? '' );

				if ( '' === $sub_name ) {
					continue;
				}

				$sub_field  = $factory->make( $sub_field_args );
				$row_value  = is_array( $row ) ? ( $row[ $sub_name ] ?? '' ) : '';
				$sub_result = Validator::validate( $sub_field, $row_value );

				if ( ! $sub_result->passed() ) {
					return sprintf(
						/* translators: 1: row number, 2: the sub-field's own validation error message */
						__( 'Row %1$d: %2$s', 'aenimfields' ),
						$row_index + 1,
						$sub_result->message()
					);
				}
			}
		}

		return true;
	}

	/**
	 * Build the args for one row's sub-fields, ready to pass to
	 * Application::render().
	 *
	 * Qualifies each sub-field's `name` (so submitted values arrive as
	 * `{repeater_name}[{index}][{sub_field_name}]`) and `id` (unique per
	 * row), fills in the row's stored value, and rewrites any
	 * `depends_on.field` that references a sibling sub-field's plain
	 * name to that sibling's row-qualified name.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $index     Row index, or a placeholder token for
	 *                              the JS row template.
	 * @param array      $row_value Stored values for this row, keyed by
	 *                              sub-field name.
	 *
	 * @return array
	 */
	public function build_row_field_args( $index, array $row_value = array() ): array {

		$sub_fields = $this->get_fields();
		$name_map   = array();

		foreach ( $sub_fields as $sub_field ) {

			$sub_name = (string) ( $sub_field['name'] ?? '' );

			if ( '' !== $sub_name ) {
				$name_map[ $sub_name ] = $this->get_name() . '[' . $index . '][' . $sub_name . ']';
			}
		}

		$row_fields = array();

		foreach ( $sub_fields as $sub_field ) {

			$sub_name = (string) ( $sub_field['name'] ?? '' );

			if ( '' === $sub_name ) {
				$row_fields[] = $sub_field;
				continue;
			}

			$sub_field['name']  = $name_map[ $sub_name ];
			$sub_field['id']    = $this->get_id() . '_' . $index . '_' . $sub_name;
			$sub_field['value'] = $row_value[ $sub_name ] ?? ( $sub_field['value'] ?? null );

			if ( ! empty( $sub_field['depends_on']['field'] )
				&& isset( $name_map[ $sub_field['depends_on']['field'] ] )
			) {
				$sub_field['depends_on']['field'] = $name_map[ $sub_field['depends_on']['field'] ];
			}

			$row_fields[] = $sub_field;
		}

		return $row_fields;
	}
}
