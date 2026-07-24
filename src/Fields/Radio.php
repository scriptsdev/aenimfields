<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

use FieldsBox\Sanitization\SelectSanitizer;

/**
 * Radio field.
 *
 * @since 1.0.0
 */
class Radio extends BaseField {

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
		return SelectSanitizer::sanitize( $value );
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

		$result = parent::validate( $value );

		if ( true !== $result ) {
			return $result;
		}

		$options = $this->get_arg( 'options' );

		if ( ! empty( $options ) && '' !== (string) $value ) {

			if ( ! array_key_exists( (string) $value, $options ) ) {
				return __( 'Please select a valid option.', 'fieldsbox' );
			}
		}

		return true;
	}
}
