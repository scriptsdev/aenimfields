<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Core;

use ScriptsDev\FieldsBox\Contracts\FieldInterface;

/**
 * Sanitizer class.
 *
 * Public entry point for sanitizing a field's submitted value, paired
 * with Validator::validate(). Delegates to the field's own sanitize()
 * implementation.
 *
 * @since 1.0.0
 */
class Sanitizer {

	/**
	 * Sanitize a field's value.
	 *
	 * @since 1.0.0
	 *
	 * @param FieldInterface $field
	 * @param mixed          $value
	 *
	 * @return mixed
	 */
	public static function sanitize( FieldInterface $field, $value ) {
		return $field->sanitize( $value );
	}
}
