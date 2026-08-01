<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Sanitization;

/**
 * Number sanitizer.
 *
 * @since 1.0.0
 */
class NumberSanitizer {

	/**
	 * Sanitize a numeric value.
	 *
	 * Casts to int or float depending on whether the value contains a
	 * decimal point. Non-numeric input sanitizes to an empty string.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return int|float|string
	 */
	public static function sanitize( $value ) {

		if ( $value === '' || $value === null ) {
			return '';
		}

		if ( is_numeric( $value ) ) {
			return strpos( (string) $value, '.' ) !== false
				? (float) $value
				: (int) $value;
		}

		return '';
	}
}
