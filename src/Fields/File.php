<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

/**
 * File field.
 *
 * A single WordPress media library file picker, unrestricted by mime
 * type. Stores the attachment ID, not a URL.
 *
 * Built for wp-admin: it depends on wp.media(), which requires
 * wp_enqueue_media() and is not ordinarily loaded on the frontend.
 *
 * @since 1.0.0
 */
class File extends BaseField {

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
	 * Only normalizes to an attachment ID (or '' when absent); does not
	 * check whether the attachment actually exists here, so an invalid
	 * ID reaches validate() and produces a helpful error message instead
	 * of silently becoming an empty (and thus "valid") value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return int|string
	 */
	public function sanitize( $value ) {
		$id = absint( $value );

		return $id > 0 ? $id : '';
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

		if ( '' === (string) $value ) {
			return true;
		}

		if ( 'attachment' !== get_post_type( absint( $value ) ) ) {
			return sprintf(
				__( 'The "%s" field must reference a valid file.', 'fieldsbox' ),
				$this->get_arg( 'label', $this->get_name() )
			);
		}

		return true;
	}
}
