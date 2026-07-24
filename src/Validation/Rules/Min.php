<?php

declare(strict_types=1);

namespace FieldsBox\Validation\Rules;

/**
 * Min rule.
 *
 * Applies to numbers (minimum value), strings (minimum length), and
 * arrays (minimum item count), depending on the value's type.
 *
 * @since 1.0.0
 */
class Min {

	/**
	 * Validate that a value meets a minimum.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed       $value     Value to validate.
	 * @param string|null $parameter Minimum threshold, e.g. "min:5".
	 * @param string      $label     Field label, used in the error message.
	 *
	 * @return true|string
	 */
	public static function validate( $value, ?string $parameter, string $label ) {

		if ( null === $parameter || '' === $parameter ) {
			return true;
		}

		$min = (float) $parameter;

		if ( is_array( $value ) ) {
			if ( count( $value ) < $min ) {
				return sprintf( __( 'The "%1$s" field must have at least %2$s item(s).', 'fieldsbox' ), $label, $parameter );
			}

			return true;
		}

		if ( is_numeric( $value ) ) {
			if ( (float) $value < $min ) {
				return sprintf( __( 'The "%1$s" field must be at least %2$s.', 'fieldsbox' ), $label, $parameter );
			}

			return true;
		}

		if ( mb_strlen( (string) $value ) < $min ) {
			return sprintf( __( 'The "%1$s" field must be at least %2$s characters.', 'fieldsbox' ), $label, $parameter );
		}

		return true;
	}
}
