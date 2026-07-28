<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

/**
 * URL field.
 *
 * @since 1.0.0
 */
class Url extends BaseField {

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

		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		return esc_url_raw( $value );
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

		if ( '' !== $value && ! wp_http_validate_url( $value ) ) {
			return __( 'Please enter a valid URL.', 'fieldsbox' );
		}

		return true;
	}
}
