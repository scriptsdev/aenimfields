<?php

declare(strict_types=1);

namespace FieldsBox\Validation\Rules;

/**
 * Max rule.
 *
 * Applies to numbers (maximum value), strings (maximum length), and
 * arrays (maximum item count), depending on the value's type.
 *
 * @since 1.0.0
 */
class Max {

	/**
	 * Validate that a value does not exceed a maximum.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed       $value     Value to validate.
	 * @param string|null $parameter Maximum threshold, e.g. "max:5".
	 * @param string      $label     Field label, used in the error message.
	 *
	 * @return true|string
	 */
	public static function validate( $value, ?string $parameter, string $label ) {

		if ( null === $parameter || '' === $parameter ) {
			return true;
		}

		$max = (float) $parameter;

		if ( is_array( $value ) ) {
			if ( count( $value ) > $max ) {
				return sprintf( __( 'The "%1$s" field must have %2$s item(s) at most.', 'fieldsbox' ), $label, $parameter );
			}

			return true;
		}

		if ( is_numeric( $value ) ) {
			if ( (float) $value > $max ) {
				return sprintf( __( 'The "%1$s" field must not be greater than %2$s.', 'fieldsbox' ), $label, $parameter );
			}

			return true;
		}

		if ( mb_strlen( (string) $value ) > $max ) {
			return sprintf( __( 'The "%1$s" field must not be greater than %2$s characters.', 'fieldsbox' ), $label, $parameter );
		}

		return true;
	}
}
