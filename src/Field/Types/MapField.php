<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Interactive location picker: a Leaflet map (OpenStreetMap tiles, no API
 * key) with a draggable pin, plus an address field.
 *
 * Stores {lat, lng, address} as an array. Typing in the address field
 * queries OpenStreetMap's free Nominatim geocoding API (no API key, same
 * reasoning as the Leaflet/OSM tile choice) and offers matching places as
 * suggestions; picking one moves the pin and fills lat/lng. The lat/lng can
 * also be set directly by dragging the pin, clicking the map, or the "Use
 * my location" button (browser Geolocation API) - the address text itself
 * is still stored as free-text and isn't re-validated against the chosen
 * coordinates. See initMap()/initAddressSearch() in fieldsbox.js.
 */
class MapField extends Field
{
    protected float $default_lat = 51.5074;
    protected float $default_lng = -0.1278;
    protected int $default_zoom = 13;

    /**
     * Where the map centers when no value is stored yet.
     */
    public function set_default_position(float $lat, float $lng, int $zoom = 13): static
    {
        $this->default_lat = $lat;
        $this->default_lng = $lng;
        $this->default_zoom = $zoom;

        return $this;
    }

    protected function render_input(mixed $value): string
    {
        $value = is_array($value) ? $value : [];
        $lat = $value['lat'] ?? '';
        $lng = $value['lng'] ?? '';
        $address = $value['address'] ?? '';

        $html = sprintf(
            '<div class="fieldsbox-map-field" data-default-lat="%s" data-default-lng="%s" data-default-zoom="%d">',
            esc_attr((string) $this->default_lat),
            esc_attr((string) $this->default_lng),
            $this->default_zoom
        );
        // Own positioning wrapper (not the outer .fieldsbox-map-field, which
        // also contains the map canvas) so the "top: 100%" on the
        // suggestions dropdown lands directly under the address input
        // instead of under the whole field.
        $html .= '<div class="fieldsbox-map-address-wrap">';
        $html .= sprintf(
            '<input type="text" class="fieldsbox-map-address" name="%1$s[address]" value="%2$s" placeholder="%3$s" autocomplete="off">',
            esc_attr($this->get_html_name()),
            esc_attr((string) $address),
            esc_attr('Search for an address')
        );
        $html .= '<div class="fieldsbox-map-suggestions" hidden></div>';
        $html .= '</div>';
        // Read-only, not disabled: disabled inputs don't submit their value.
        // Kept in sync by setPosition() in fieldsbox.js for every way the
        // pin can move (drag, click, address search, "use my location").
        $html .= '<div class="fieldsbox-map-coords">';
        $html .= sprintf(
            '<label class="fieldsbox-map-coord">%1$s<input type="text" class="fieldsbox-map-lat" name="%2$s[lat]" value="%3$s" readonly></label>',
            esc_html('Latitude'),
            esc_attr($this->get_html_name()),
            esc_attr((string) $lat)
        );
        $html .= sprintf(
            '<label class="fieldsbox-map-coord">%1$s<input type="text" class="fieldsbox-map-lng" name="%2$s[lng]" value="%3$s" readonly></label>',
            esc_html('Longitude'),
            esc_attr($this->get_html_name()),
            esc_attr((string) $lng)
        );
        $html .= '</div>';
        $html .= '<button type="button" class="button fieldsbox-map-locate">' . esc_html('Use my location') . '</button>';
        $html .= '<div class="fieldsbox-map-canvas"></div>';
        $html .= '</div>';

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        $value = is_array($value) ? $value : [];

        return [
            'lat' => isset($value['lat']) && $value['lat'] !== '' ? (float) $value['lat'] : '',
            'lng' => isset($value['lng']) && $value['lng'] !== '' ? (float) $value['lng'] : '',
            'address' => isset($value['address']) ? sanitize_text_field($value['address']) : '',
        ];
    }
}
