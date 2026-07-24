<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

use FieldsBox\Sanitization\BooleanSanitizer;
use FieldsBox\Sanitization\SelectSanitizer;

/**
 * Checkbox field.
 *
 * @since 1.0.0
 */
class Checkbox extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return mixed
	 */
	public function sanitize( $value ) {

		$options = $this->get_arg( 'options' );

		// Multiple checkboxes.
		if ( ! empty( $options ) ) {
			return SelectSanitizer::sanitize_multiple( $value );
		}

		// Single checkbox.
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
