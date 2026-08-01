<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Validation\Rules;

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

		if ( '' === $value || array() === $value ) {
			return true;
		}

		$max = (float) $parameter;

		if ( is_array( $value ) ) {
			if ( count( $value ) > $max ) {
				return sprintf( __( 'The "%1$s" field must have %2$s item(s) at most.', 'aenimfields' ), $label, $parameter );
			}

			return true;
		}

		// Only a genuine int/float counts as a number here — a sanitized
		// text value is always a string even when it looks numeric (e.g.
		// a zip code), and should be measured by length, not magnitude.
		if ( is_int( $value ) || is_float( $value ) ) {
			if ( (float) $value > $max ) {
				return sprintf( __( 'The "%1$s" field must not be greater than %2$s.', 'aenimfields' ), $label, $parameter );
			}

			return true;
		}

		if ( mb_strlen( (string) $value ) > $max ) {
			return sprintf( __( 'The "%1$s" field must not be greater than %2$s characters.', 'aenimfields' ), $label, $parameter );
		}

		return true;
	}
}
