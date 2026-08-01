<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Fields;

/**
 * Wysiwyg field.
 *
 * Renders WordPress's native rich text editor (wp_editor()). The field's
 * `name`/`id` must be lowercase with only letters, numbers, and
 * underscores — wp_editor() requires this for its generated editor id
 * and will misbehave with hyphens or uppercase letters.
 *
 * @since 1.0.0
 */
class Wysiwyg extends BaseField {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( array $args = array() ) {
		$args = array_merge( array( 'rows' => 10 ), $args );

		parent::__construct( $args );
	}

	/**
	 * Sanitize the field value.
	 *
	 * Uses wp_kses_post() rather than sanitize_text_field(), since a
	 * wysiwyg field's value is post-level HTML, not plain text.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return string
	 */
	public function sanitize( $value ): string {
		return wp_kses_post( (string) $value );
	}
}
