<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Sanitization;

/**
 * Boolean sanitizer.
 *
 * Used by single-value boolean controls such as a lone checkbox or toggle.
 *
 * @since 1.0.0
 */
class BooleanSanitizer {

	/**
	 * Sanitize a value to 1 or 0.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return int
	 */
	public static function sanitize( $value ): int {
		return ! empty( $value ) ? 1 : 0;
	}
}
