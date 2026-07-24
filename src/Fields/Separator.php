<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

/**
 * Separator field.
 *
 * A display-only field for breaking a set of fields into labeled
 * sections. Carries no value of its own and is never required.
 *
 * @since 1.0.0
 */
class Separator extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Unused; a separator carries no value.
	 *
	 * @return null
	 */
	public function sanitize( $value ) {
		return null;
	}

	/**
	 * Validate the field value.
	 *
	 * A separator is display-only and always passes validation.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Unused.
	 *
	 * @return true
	 */
	public function validate( $value ) {
		return true;
	}

	/**
	 * Whether the field is required.
	 *
	 * A separator carries no value, so it is never required.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_required(): bool {
		return false;
	}
}
