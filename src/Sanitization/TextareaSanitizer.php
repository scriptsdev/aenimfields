<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Sanitization;

/**
 * Textarea sanitizer.
 *
 * @since 1.0.0
 */
class TextareaSanitizer {

	/**
	 * Sanitize a multi-line text value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function sanitize( $value ): string {
		return sanitize_textarea_field( (string) $value );
	}
}
