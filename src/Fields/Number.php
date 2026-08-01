<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Fields;

use AenimTech\AenimFields\Sanitization\NumberSanitizer;

/**
 * Number field.
 *
 * @since 1.0.0
 */
class Number extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 *
	 * @return int|float|string
	 */
	public function sanitize( $value ) {
		return NumberSanitizer::sanitize( $value );
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

		if ( $value !== '' && ! is_numeric( $value ) ) {
			return __( 'Please enter a valid number.', 'aenimfields' );
		}

		return true;
	}
}
