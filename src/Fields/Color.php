<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

/**
 * Color field.
 *
 * @since 1.0.0
 */
class Color extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return string
	 */
	public function sanitize( $value ): string {

		$value = sanitize_hex_color( (string) $value );

		return $value ? $value : '';
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

		if ( '' !== (string) $value && ! sanitize_hex_color( (string) $value ) ) {
			return __( 'Please select a valid color.', 'fieldsbox' );
		}

		return true;
	}
}
