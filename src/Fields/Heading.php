<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

/**
 * Heading field.
 *
 * A display-only field for a standalone label/heading — with an
 * optional `label_description` sub-line — and no input of its own.
 * Unlike Separator, it renders no dividing line.
 *
 * @since 1.0.0
 */
class Heading extends BaseField {

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Unused; a heading carries no value.
	 *
	 * @return null
	 */
	public function sanitize( $value ) {
		return null;
	}

	/**
	 * Validate the field value.
	 *
	 * A heading is display-only and always passes validation.
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
	 * A heading carries no value, so it is never required.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_required(): bool {
		return false;
	}
}
