<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

use ScriptsDev\FieldsBox\Sanitization\TextareaSanitizer;

/**
 * Textarea field.
 *
 * @since 1.0.0
 */
class Textarea extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function sanitize( $value ): string {
		return TextareaSanitizer::sanitize( $value );
	}

	/**
	 * Validate the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return true|string
	 */
	public function validate( $value ) {
		return parent::validate( $value );
	}
}
