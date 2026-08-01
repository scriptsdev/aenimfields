<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Sanitization;

/**
 * Select sanitizer.
 *
 * Shared by option-based fields: select, multiselect, radio, and
 * checkbox groups.
 *
 * @since 1.0.0
 */
class SelectSanitizer {

	/**
	 * Sanitize a single selected value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function sanitize( $value ): string {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Sanitize multiple selected values.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function sanitize_multiple( $value ): array {

		if ( ! is_array( $value ) ) {
			return array();
		}

		return array_map( 'sanitize_text_field', $value );
	}
}
