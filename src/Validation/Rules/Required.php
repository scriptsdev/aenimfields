<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Validation\Rules;

/**
 * Required rule.
 *
 * @since 1.0.0
 */
class Required {

	/**
	 * Validate that a value is present.
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

		$empty = is_array( $value )
			? empty( array_filter( $value ) )
			: ( $value === null || $value === '' );

		if ( $empty ) {
			return sprintf( __( 'The "%s" field is required.', 'aenimfields' ), $label );
		}

		return true;
	}
}
