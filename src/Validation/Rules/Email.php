<?php

declare(strict_types=1);

namespace FieldsBox\Validation\Rules;

/**
 * Email rule.
 *
 * @since 1.0.0
 */
class Email {

	/**
	 * Validate that a value is a valid email address.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed       $value     Value to validate.
	 * @param string|null $parameter Unused for this rule.
	 * @param string      $label     Field label, used in the error message.
	 *
	 * @return true|string
	 */
	public static function validate( $value, ?string $parameter, string $label ) {

		if ( '' === (string) $value ) {
			return true;
		}

		if ( ! is_email( (string) $value ) ) {
			return sprintf( __( 'The "%s" field must be a valid email address.', 'fieldsbox' ), $label );
		}

		return true;
	}
}
