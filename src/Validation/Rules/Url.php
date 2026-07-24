<?php

declare(strict_types=1);

namespace FieldsBox\Validation\Rules;

/**
 * URL rule.
 *
 * @since 1.0.0
 */
class Url {

	/**
	 * Validate that a value is a valid URL.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed       $value     Value to validate.
	 * @param string|null $parameter Unused for this rule.
	 * @param string      $label     Field label, used in the error message.
	 *
	 * @return true|string
	 */
	public static function validate( $value, ?string $parameter, string $label ) {

		if ( '' === (string) $value ) {
			return true;
		}

		if ( ! wp_http_validate_url( (string) $value ) ) {
			return sprintf( __( 'The "%s" field must be a valid URL.', 'fieldsbox' ), $label );
		}

		return true;
	}
}
