<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single-line text input (<input type="text">).
 */
class TextField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<input type="text" id="fieldsbox-%1$s" name="%1$s" value="%2$s"%3$s%4$s>',
            esc_attr($this->name),
            esc_attr((string) $value),
            $this->required ? ' required' : '',
            $this->render_attributes()
        );
    }
}
