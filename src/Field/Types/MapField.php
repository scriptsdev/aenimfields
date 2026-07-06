<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Interactive location picker: a Leaflet map (OpenStreetMap tiles, no API
 * key) with a draggable pin, plus a plain-text address/label field.
 *
 * Stores {lat, lng, address} as an array. The address field is a free-text
 * label only - it is not geocoded (no address-to-coordinates lookup); the
 * lat/lng are set by dragging the pin, clicking the map, or the "Use my
 * location" button (browser Geolocation API). See initMap() in
 * fieldsbox.js.
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
        $html .= sprintf(
            '<input type="text" class="fieldsbox-map-address" name="%1$s[address]" value="%2$s" placeholder="%3$s">',
            esc_attr($this->get_html_name()),
            esc_attr((string) $address),
            esc_attr('Label or address (not geocoded)')
        );
        $html .= sprintf(
            '<input type="hidden" class="fieldsbox-map-lat" name="%1$s[lat]" value="%2$s">',
            esc_attr($this->get_html_name()),
            esc_attr((string) $lat)
        );
        $html .= sprintf(
            '<input type="hidden" class="fieldsbox-map-lng" name="%1$s[lng]" value="%2$s">',
            esc_attr($this->get_html_name()),
            esc_attr((string) $lng)
        );
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
