<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Multi-choice <select multiple>, storing an array of selected option keys.
 *
 * Rendered with name="{name}[]" so PHP collects every selected option into
 * a single $_POST['{name}'] array automatically.
 */
class MultiSelectField extends Field
{
    protected function render_input(mixed $value): string
    {
        // Normalize the stored/default value to a flat array of strings so
        // it can be compared against option keys below.
        $values = array_map('strval', is_array($value) ? $value : (array) $value);

        $html = sprintf(
            '<select id="%1$s" name="%2$s[]" multiple%3$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            $this->render_attributes()
        );

        foreach ($this->options as $key => $label) {
            $html .= sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr((string) $key),
                in_array((string) $key, $values, true) ? ' selected' : '',
                esc_html($label)
            );
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Nothing selected submits no field at all, so the container passes an
     * empty array through here rather than a scalar. Also drops any
     * submitted entry that isn't actually one of this field's
     * set_options() choices - guards against a forged direct POST
     * bypassing the rendered <select>.
     */
    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        $sanitized = array_map('sanitize_text_field', $value);

        return array_values(array_intersect($sanitized, $this->get_option_keys()));
    }
}
