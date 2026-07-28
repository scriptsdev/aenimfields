<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Contracts;

/**
 * Defines the contract for validating a field value.
 *
 * @since 1.0.0
 */
interface ValidatableInterface {

	/**
	 * Validate the given field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Value to validate.
	 *
	 * @return true|string
	 *         Returns true when validation passes.
	 *         Returns an error message when validation fails.
	 */
	public function validate( $value );
}
