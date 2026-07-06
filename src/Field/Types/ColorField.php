<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Native browser color swatch picker (<input type="color">).
 *
 * Deliberately dependency-free rather than wiring up WordPress core's
 * wp-color-picker (which needs jQuery + Iris) - if a richer picker with an
 * alpha channel or saved palette is needed later, swap render_input() for
 * one that enqueues wp-color-picker and initializes it in fieldsbox.js.
 */
class ColorField extends Field
{
    protected function render_input(mixed $value): string
    {
        $value = (string) $value !== '' ? (string) $value : '#000000';

        return sprintf(
            '<input type="color" id="%1$s" name="%2$s" value="%3$s"%4$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr($value),
            $this->render_attributes()
        );
    }

    public function sanitize(mixed $value): mixed
    {
        return sanitize_hex_color((string) $value) ?: '';
    }
}
