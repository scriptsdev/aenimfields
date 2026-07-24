<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

use FieldsBox\Sanitization\SelectSanitizer;

/**
 * Multi-select field.
 *
 * @since 1.0.0
 */
class MultiSelect extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return array
	 */
	public function sanitize( $value ): array {
		return SelectSanitizer::sanitize_multiple( $value );
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

		if ( ! is_array( $value ) ) {
			return true;
		}

		$options = (array) $this->get_arg( 'options' );

		foreach ( $value as $selected ) {

			if ( ! array_key_exists( (string) $selected, $options ) ) {
				return __( 'Please select valid options.', 'fieldsbox' );
			}
		}

		return true;
	}
}
