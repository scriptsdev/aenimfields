<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

use ScriptsDev\FieldsBox\Sanitization\BooleanSanitizer;

/**
 * Toggle field.
 *
 * @since 1.0.0
 */
class Toggle extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return int
	 */
	public function sanitize( $value ): int {
		return BooleanSanitizer::sanitize( $value );
	}

	/**
	 * Validate the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return true|string
	 */
	public function validate( $value ) {

		return parent::validate( $value );
	}
}
