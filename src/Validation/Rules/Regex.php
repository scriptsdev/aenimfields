<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Validation\Rules;

/**
 * Regex rule.
 *
 * @since 1.0.0
 */
class Regex {

	/**
	 * Validate that a value matches a regular expression.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed       $value     Value to validate.
	 * @param string|null $parameter PCRE pattern, e.g. "regex:/^[a-z]+$/".
	 * @param string      $label     Field label, used in the error message.
	 *
	 * @return true|string
	 */
	public static function validate( $value, ?string $parameter, string $label ) {

		if ( null === $parameter || '' === $parameter || '' === (string) $value ) {
			return true;
		}

		if ( ! preg_match( $parameter, (string) $value ) ) {
			return sprintf( __( 'The "%s" field format is invalid.', 'aenimfields' ), $label );
		}

		return true;
	}
}
