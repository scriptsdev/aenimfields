<?php

declare(strict_types=1);

namespace FieldsBox\Core;

use FieldsBox\Contracts\FieldInterface;

/**
 * Helpers class.
 *
 * Shared utilities used by field templates when rendering markup.
 *
 * @since 1.0.0
 */
class Helpers {

	/**
	 * Convert an associative array into HTML attributes.
	 *
	 * Example:
	 *
	 * [
	 *     'class' => 'form-control',
	 *     'required' => true,
	 *     'data-id' => 15,
	 * ]
	 *
	 * Output:
	 *
	 * class="form-control" required data-id="15"
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function attributes( array $attributes = array() ): string {
		if ( empty( $attributes ) ) {
			return '';
		}

		$output = array();

		foreach ( $attributes as $attribute => $value ) {

			// Skip null and false values.
			if ( $value === null || $value === false ) {
				continue;
			}

			// Skip empty strings but keep 0 and '0'.
			if ( $value === '' ) {
				continue;
			}

			// Boolean HTML attributes.
			if ( $value === true ) {
				$output[] = esc_attr( $attribute );
				continue;
			}

			// Convert class arrays, etc.
			if ( is_array( $value ) ) {
				$value = implode( ' ', array_filter( $value ) );
			}

			$output[] = sprintf(
				'%s="%s"',
				esc_attr( (string) $attribute ),
				esc_attr( (string) $value )
			);
		}

		return implode( ' ', $output );
	}

	/**
	 * Build common input attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param FieldInterface $field
	 * @param array          $extra
	 *
	 * @return array
	 */
	public static function input_attributes( FieldInterface $field, array $extra = array() ): array {
		$attributes = array(
			'id'    => $field->get_id(),
			'name'  => $field->get_name(),
			'value' => $field->get_value(),
		);

		if ( $field->get_arg( 'placeholder' ) !== '' ) {
			$attributes['placeholder'] = $field->get_arg( 'placeholder' );
		}

		if ( $field->get_arg( 'class' ) !== '' ) {
			$attributes['class'] = $field->get_arg( 'class' );
		}

		if ( $field->get_arg( 'readonly' ) ) {
			$attributes['readonly'] = true;
		}

		if ( $field->get_arg( 'disabled' ) ) {
			$attributes['disabled'] = true;
		}

		if ( '' !== $field->get_arg( 'min' ) ) {
			$attributes['min'] = $field->get_arg( 'min' );
		}

		if ( '' !== $field->get_arg( 'max' ) ) {
			$attributes['max'] = $field->get_arg( 'max' );
		}

		if ( '' !== $field->get_arg( 'step' ) ) {
			$attributes['step'] = $field->get_arg( 'step' );
		}

		if ( '' !== $field->get_arg( 'autocomplete' ) ) {
			$attributes['autocomplete'] = $field->get_arg( 'autocomplete' );
		}

		if ( '' !== $field->get_arg( 'pattern' ) ) {
			$attributes['pattern'] = $field->get_arg( 'pattern' );
		}

		if ( $field->is_required() ) {
			$attributes['required'] = true;
		}

		return array_merge(
			$attributes,
			$extra,
			$field->get_arg( 'input_attr' )
		);
	}

	/**
	 * Determine whether a checkbox, radio, or option should be checked/selected.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $current Current field value.
	 * @param mixed $value   Value to compare.
	 *
	 * @return bool
	 */
	public static function is_checked( $current, $value ): bool {

		// Multiple values (checkbox group, multi-select).
		if ( is_array( $current ) ) {
			return in_array( (string) $value, array_map( 'strval', $current ), true );
		}

		// Single value.
		return (string) $current === (string) $value;
	}

	/**
	 * Build common select attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param FieldInterface $field Field instance.
	 * @param array          $extra Additional attributes.
	 *
	 * @return array
	 */
	public static function select_attributes( FieldInterface $field, array $extra = array() ): array {

		$attributes = array(
			'id'   => $field->get_id(),
			'name' => $field->get_name(),
		);

		if ( $field->get_arg( 'class' ) !== '' ) {
			$attributes['class'] = $field->get_arg( 'class' );
		}

		if ( $field->get_arg( 'disabled' ) ) {
			$attributes['disabled'] = true;
		}

		if ( $field->is_required() ) {
			$attributes['required'] = true;
		}

		return array_merge(
			$attributes,
			$extra,
			$field->get_arg( 'input_attr' )
		);
	}
}
