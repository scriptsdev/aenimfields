<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Sanitization;

use ScriptsDev\FieldsBox\Core\FieldFactory;
use ScriptsDev\FieldsBox\Core\Sanitizer;

/**
 * Repeater sanitizer.
 *
 * Sanitizes each row of a repeater's submitted value against its
 * sub-field schema, using each sub-field's own sanitize() logic.
 *
 * @since 1.0.0
 */
class RepeaterSanitizer {

	/**
	 * Sanitize a repeater's value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value      Raw submitted rows.
	 * @param array $sub_fields Sub-field definitions (the repeater's
	 *                          `fields` arg).
	 *
	 * @return array
	 */
	public static function sanitize( $value, array $sub_fields ): array {

		if ( ! is_array( $value ) || empty( $sub_fields ) ) {
			return array();
		}

		$factory = new FieldFactory();
		$clean   = array();

		foreach ( $value as $row ) {

			if ( ! is_array( $row ) ) {
				continue;
			}

			$clean_row = array();

			foreach ( $sub_fields as $sub_field_args ) {

				$name = (string) ( $sub_field_args['name'] ?? '' );

				if ( '' === $name ) {
					continue;
				}

				$sub_field = $factory->make( $sub_field_args );
				$raw       = $row[ $name ] ?? '';

				$clean_row[ $name ] = Sanitizer::sanitize( $sub_field, $raw );
			}

			$clean[] = $clean_row;
		}

		return array_values( $clean );
	}
}
