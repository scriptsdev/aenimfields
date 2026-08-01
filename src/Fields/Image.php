<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Fields;

/**
 * Image field.
 *
 * A single WordPress media library image picker. Stores the attachment
 * ID, not a URL — resolve the actual image (URL, alt text, sizes, etc.)
 * from that ID wherever the value is used.
 *
 * Built for wp-admin: it depends on wp.media(), which requires
 * wp_enqueue_media() and is not ordinarily loaded on the frontend.
 *
 * @since 1.0.0
 */
class Image extends BaseField {

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
	 * check whether the attachment actually exists or is an image here,
	 * so an invalid ID reaches validate() and produces a helpful error
	 * message instead of silently becoming an empty (and thus "valid")
	 * value.
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

		if ( ! wp_attachment_is_image( absint( $value ) ) ) {
			return sprintf(
				__( 'The "%s" field must reference a valid image.', 'aenimfields' ),
				$this->get_arg( 'label', $this->get_name() )
			);
		}

		return true;
	}
}
