<?php

declare(strict_types=1);

namespace FieldsBox\Validation\Rules;

/**
 * Numeric rule.
 *
 * @since 1.0.0
 */
class Numeric {

	/**
	 * Validate that a value is numeric.
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

		if ( ! is_numeric( $value ) ) {
			return sprintf( __( 'The "%s" field must be a number.', 'fieldsbox' ), $label );
		}

		return true;
	}
}
