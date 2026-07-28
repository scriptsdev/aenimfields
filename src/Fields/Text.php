<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

use ScriptsDev\FieldsBox\Sanitization\TextSanitizer;

/**
 * Text field.
 *
 * @since 1.0.0
 */
class Text extends BaseField {

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
		return TextSanitizer::sanitize( $value );
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
		$result = parent::validate( $value );

		if ( $result !== true ) {
			return $result;
		}

		return true;
	}
}
