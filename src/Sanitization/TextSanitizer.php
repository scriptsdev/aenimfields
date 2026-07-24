<?php

declare(strict_types=1);

namespace FieldsBox\Sanitization;

/**
 * Text sanitizer.
 *
 * @since 1.0.0
 */
class TextSanitizer {

	/**
	 * Sanitize a single-line text value.
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
}
