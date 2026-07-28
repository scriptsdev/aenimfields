<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

/**
 * Date field.
 *
 * Renders as a text input progressively enhanced by flatpickr
 * (assets/js/datepicker.js). Accepts a `date_format` arg using PHP's
 * date() tokens (default 'Y-m-d'); pick a format flatpickr also
 * understands (Y, m, d, H, i, S are shared by both).
 *
 * `min_date` / `max_date` restrict the selectable range. Each accepts
 * either a literal date string in `date_format`, or the keyword
 * 'today' (resolved using the site's configured timezone). The range
 * is enforced both by flatpickr in the browser and by validate() here
 * — the browser restriction alone is never trustworthy.
 *
 * @since 1.0.0
 */
class Date extends BaseField {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( array $args = array() ) {
		$args = array_merge(
			array(
				'date_format' => 'Y-m-d',
				'min_date'    => '',
				'max_date'    => '',
			),
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Assets this field needs enqueued.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function assets(): array {
		return array( 'datepicker' );
	}

	/**
	 * Sanitize the field value.
	 *
	 * Only trims the value; does not enforce the date format here, so
	 * that an invalid date reaches validate() and produces a helpful
	 * error message instead of silently becoming an empty (and thus
	 * "valid") value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return string
	 */
	public function sanitize( $value ): string {
		return trim( (string) $value );
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

		$format = (string) $this->get_arg( 'date_format', 'Y-m-d' );
		$date   = \DateTime::createFromFormat( $format, (string) $value );

		if ( ! $date instanceof \DateTime || $date->format( $format ) !== $value ) {
			return sprintf(
				__( 'Please enter a valid date (%s).', 'fieldsbox' ),
				$format
			);
		}

		$label = (string) $this->get_arg( 'label', $this->get_name() );

		$min = $this->get_min_date();

		if ( '' !== $min && $date < \DateTime::createFromFormat( $format, $min ) ) {
			return sprintf(
				__( 'The "%1$s" field must not be before %2$s.', 'fieldsbox' ),
				$label,
				$min
			);
		}

		$max = $this->get_max_date();

		if ( '' !== $max && $date > \DateTime::createFromFormat( $format, $max ) ) {
			return sprintf(
				__( 'The "%1$s" field must not be after %2$s.', 'fieldsbox' ),
				$label,
				$max
			);
		}

		return true;
	}

	/**
	 * Determine whether a value matches this field's date format exactly.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	protected function is_valid_date( string $value ): bool {

		$format = (string) $this->get_arg( 'date_format', 'Y-m-d' );
		$date   = \DateTime::createFromFormat( $format, $value );

		return $date instanceof \DateTime && $date->format( $format ) === $value;
	}

	/**
	 * Get the resolved minimum selectable date, formatted per date_format.
	 *
	 * Resolves the special 'today' keyword using the site's configured
	 * timezone, so the browser and the server always agree on what
	 * "today" means regardless of the visitor's local clock.
	 *
	 * @since 1.0.0
	 *
	 * @return string Empty string when no minimum is set or configured
	 *                improperly.
	 */
	public function get_min_date(): string {
		return $this->resolve_date_bound( (string) $this->get_arg( 'min_date', '' ) );
	}

	/**
	 * Get the resolved maximum selectable date, formatted per date_format.
	 *
	 * @since 1.0.0
	 *
	 * @return string Empty string when no maximum is set or configured
	 *                improperly.
	 */
	public function get_max_date(): string {
		return $this->resolve_date_bound( (string) $this->get_arg( 'max_date', '' ) );
	}

	/**
	 * Resolve a min_date/max_date arg into a concrete, formatted date.
	 *
	 * @since 1.0.0
	 *
	 * @param string $bound
	 *
	 * @return string
	 */
	protected function resolve_date_bound( string $bound ): string {

		if ( '' === $bound ) {
			return '';
		}

		$format = (string) $this->get_arg( 'date_format', 'Y-m-d' );

		if ( 'today' === strtolower( $bound ) ) {
			return ( new \DateTime( 'now', wp_timezone() ) )->format( $format );
		}

		$date = \DateTime::createFromFormat( $format, $bound );

		return $date instanceof \DateTime ? $date->format( $format ) : '';
	}
}
