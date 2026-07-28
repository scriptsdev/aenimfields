<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

/**
 * Hidden field.
 *
 * @since 1.0.0
 */
class Hidden extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return string
	 */
	public function sanitize( $value ): string {
		return sanitize_text_field( (string) $value );
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
		return true;
	}
}
