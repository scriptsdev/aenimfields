<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * A value carried in the form without any visible control - no label, help
 * text, or wrapper markup, unlike every other field type.
 */
class HiddenField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<input type="hidden" id="%1$s" name="%2$s" value="%3$s">',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr((string) $value)
        );
    }

    /**
     * Overrides the base render() entirely - a hidden field has nothing for
     * a label or help text to describe, so the usual wrapper would just be
     * empty chrome around an invisible input.
     */
    public function render(mixed $value): string
    {
        if ($value === null || $value === '') {
            $value = $this->default_value;
        }

        return $this->render_input($value);
    }
}
