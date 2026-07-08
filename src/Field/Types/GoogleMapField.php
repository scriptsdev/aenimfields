<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;
use Fieldsbox\Fieldsbox;

/**
 * Interactive location picker backed by the Google Maps JavaScript API,
 * with Places Autocomplete on the address field. Same {lat, lng, address}
 * storage shape as MapField, so a container can switch between 'map' and
 * 'google_map' without a data migration.
 *
 * Requires Fieldsbox::set_google_maps_api_key() to have been called by the
 * consuming plugin - Google Maps needs a billed API key, unlike the
 * key-free 'map' field (Leaflet + OpenStreetMap). If no key is set, this
 * renders an admin-facing notice instead of a broken map, so the missing
 * setup step is obvious rather than silently failing. See initGoogleMap()
 * in fieldsbox.js.
 */
class GoogleMapField extends Field
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
        if (! Fieldsbox::get_google_maps_api_key()) {
            return '<p class="fieldsbox-google-map-missing-key">'
                . esc_html('Google Maps API key not set. Call Fieldsbox::set_google_maps_api_key() from your plugin, or use the map field type instead.')
                . '</p>';
        }

        $value = is_array($value) ? $value : [];
        $lat = $value['lat'] ?? '';
        $lng = $value['lng'] ?? '';
        $address = $value['address'] ?? '';

        $html = sprintf(
            '<div class="fieldsbox-google-map-field" data-default-lat="%s" data-default-lng="%s" data-default-zoom="%d">',
            esc_attr((string) $this->default_lat),
            esc_attr((string) $this->default_lng),
            $this->default_zoom
        );
        $html .= sprintf(
            '<input type="text" class="fieldsbox-google-map-address" name="%1$s[address]" value="%2$s" placeholder="%3$s" autocomplete="off">',
            esc_attr($this->get_html_name()),
            esc_attr((string) $address),
            esc_attr('Search for an address')
        );
        $html .= '<button type="button" class="button fieldsbox-google-map-locate">' . esc_html('Use my location') . '</button>';
        $html .= '<div class="fieldsbox-google-map-canvas"></div>';
        // Read-only, not disabled: disabled inputs don't submit their value.
        // Kept in sync by setPosition() in fieldsbox.js for every way the
        // pin can move (drag, click, address search, "use my location").
        $html .= '<div class="fieldsbox-google-map-coords">';
        $html .= sprintf(
            '<label class="fieldsbox-google-map-coord">%1$s<input type="text" class="fieldsbox-google-map-lat" name="%2$s[lat]" value="%3$s" readonly></label>',
            esc_html('Latitude'),
            esc_attr($this->get_html_name()),
            esc_attr((string) $lat)
        );
        $html .= sprintf(
            '<label class="fieldsbox-google-map-coord">%1$s<input type="text" class="fieldsbox-google-map-lng" name="%2$s[lng]" value="%3$s" readonly></label>',
            esc_html('Longitude'),
            esc_attr($this->get_html_name()),
            esc_attr((string) $lng)
        );
        $html .= '</div>';
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
