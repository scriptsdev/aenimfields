<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

/**
 * Gallery field.
 *
 * A multi-image WordPress media library picker. Stores an array of
 * attachment IDs.
 *
 * Built for wp-admin: it depends on wp.media(), which requires
 * wp_enqueue_media() and is not ordinarily loaded on the frontend.
 *
 * @since 1.0.0
 */
class Gallery extends BaseField {

	/**
	 * Assets this field needs enqueued.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function assets(): array {
		return array( 'media' );
	}

	/**
	 * Sanitize the field value.
	 *
	 * Accepts either an array of IDs, or the comma-separated string the
	 * field's own hidden input submits. Does not check whether each
	 * attachment actually exists or is an image here — that happens in
	 * validate(), so an invalid ID produces a helpful error message
	 * instead of silently disappearing.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return array
	 */
	public function sanitize( $value ): array {

		$raw = is_array( $value ) ? $value : explode( ',', (string) $value );
		$ids = array_map( 'absint', array_filter( $raw ) );

		return array_values( array_filter( $ids ) );
	}

	/**
	 * Validate the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return true|string
	 */
	public function validate( $value ) {

		$result = parent::validate( $value );

		if ( true !== $result ) {
			return $result;
		}

		if ( ! is_array( $value ) || empty( $value ) ) {
			return true;
		}

		foreach ( $value as $id ) {
			if ( ! wp_attachment_is_image( absint( $id ) ) ) {
				return sprintf(
					__( 'The "%s" field must only contain valid images.', 'fieldsbox' ),
					$this->get_arg( 'label', $this->get_name() )
				);
			}
		}

		return true;
	}
}
