<?php

declare(strict_types=1);

namespace FieldsBox\Fields;

/**
 * Map field.
 *
 * A location picker combining an address search box, a draggable-pin
 * map, and latitude/longitude inputs, backed by either the free
 * OpenStreetMap/Leaflet stack (`provider => 'osm'`, no API key) or the
 * Google Maps JavaScript API (`provider => 'google'`, requires an API
 * key — see `Core\Assets::set_google_maps_api_key()`).
 *
 * Stores `['address' => string, 'lat' => float|string, 'lng' => float|string, 'zoom' => int]`.
 * `lat`/`lng` are '' when no location has been picked yet.
 *
 * @since 1.0.0
 */
class Map extends BaseField {

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
				'provider'           => 'osm',
				'default_lat'        => '',
				'default_lng'        => '',
				'zoom'               => 14,
				'min_zoom'           => 2,
				'max_zoom'           => 20,
				'height'             => 400,
				'show_address'       => true,
				'marker_draggable'   => true,
				'map_type'           => 'roadmap',
				'search_placeholder' => __( 'Search address…', 'fieldsbox' ),
				'default'            => array(
					'address' => '',
					'lat'     => '',
					'lng'     => '',
					'zoom'    => $args['zoom'] ?? 14,
				),
			),
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Assets this field needs enqueued.
	 *
	 * Which key is returned depends on `provider` — only the stack the
	 * field actually uses gets loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function assets(): array {
		return array( 'google' === $this->get_arg( 'provider' ) ? 'map_google' : 'map_osm' );
	}

	/**
	 * Sanitize the field value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Field value.
	 *
	 * @return array
	 */
	public function sanitize( $value ): array {

		$value = is_array( $value ) ? $value : array();

		return array(
			'address' => sanitize_text_field( (string) ( $value['address'] ?? '' ) ),
			'lat'     => $this->sanitize_coordinate( $value['lat'] ?? '', 90.0 ),
			'lng'     => $this->sanitize_coordinate( $value['lng'] ?? '', 180.0 ),
			'zoom'    => max( 1, min( 21, absint( $value['zoom'] ?? $this->get_arg( 'zoom', 14 ) ) ) ),
		);
	}

	/**
	 * Sanitize a single latitude/longitude value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value
	 * @param float $bound Maximum absolute value allowed (90 for latitude,
	 *                     180 for longitude).
	 *
	 * @return float|string Float when valid, '' when absent or out of range.
	 */
	protected function sanitize_coordinate( $value, float $bound ) {

		if ( '' === $value || null === $value || ! is_numeric( $value ) ) {
			return '';
		}

		$value = (float) $value;

		return abs( $value ) <= $bound ? $value : '';
	}

	/**
	 * Validate the field value.
	 *
	 * Assumes an already-sanitized value (per the sanitize-then-validate
	 * flow in Core\Sanitizer / Core\Validator), so `lat`/`lng` are either
	 * a float or ''.
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

		$value = is_array( $value ) ? $value : array();

		if ( $this->is_required()
			&& ( '' === ( $value['lat'] ?? '' ) || '' === ( $value['lng'] ?? '' ) )
		) {
			return sprintf(
				__( 'The "%s" field requires a location to be picked on the map.', 'fieldsbox' ),
				$this->get_arg( 'label', $this->get_name() )
			);
		}

		return true;
	}
}
