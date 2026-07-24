<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

/**
 * Email field.
 *
 * @since 1.0.0
 */
class Email extends BaseField {

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
		return sanitize_email( (string) $value );
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

		if ( '' !== $value && ! is_email( $value ) ) {
			return __( 'Please enter a valid email address.', 'fieldsbox' );
		}

		return true;
	}
}
