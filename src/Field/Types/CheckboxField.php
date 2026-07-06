<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single boolean on/off checkbox (e.g. "Feature this product").
 *
 * For a group of independent checkboxes producing multiple values, use the
 * 'multiselect' field type instead - this type only ever stores '1' or ''.
 */
class CheckboxField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s%4$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            checked((string) $value, '1', false),
            $this->render_attributes()
        );
    }

    /**
     * Unchecked checkboxes are omitted from $_POST entirely, so any
     * truthy/falsy submitted value is normalized to '1' or ''.
     */
    public function sanitize(mixed $value): mixed
    {
        return $value ? '1' : '';
    }
}
