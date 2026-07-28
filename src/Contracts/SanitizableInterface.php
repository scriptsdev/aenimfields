<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Contracts;

/**
 * Defines the contract for sanitizing a field value.
 *
 * @since 1.0.0
 */
interface SanitizableInterface {

	/**
	 * Sanitize the given field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Raw value submitted by the user.
	 *
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value );
}
