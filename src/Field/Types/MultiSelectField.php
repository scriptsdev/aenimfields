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
            '<select id="fieldsbox-%1$s" name="%1$s[]" multiple%2$s>',
            esc_attr($this->name),
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
     * empty array through here rather than a scalar.
     */
    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        return array_map('sanitize_text_field', $value);
    }
}
