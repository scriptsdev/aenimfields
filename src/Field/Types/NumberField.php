<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Numeric input (<input type="number">). Use set_attribute('min', ...),
 * set_attribute('max', ...), or set_attribute('step', ...) for constraints -
 * no dedicated methods are needed since those are just passthrough HTML attributes.
 */
class NumberField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<input type="number" id="%1$s" name="%2$s" value="%3$s"%4$s%5$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr((string) $value),
            $this->required ? ' required' : '',
            $this->render_attributes()
        );
    }

    /**
     * Non-numeric submissions are dropped rather than coerced, so the
     * container treats them the same as "field left empty".
     */
    public function sanitize(mixed $value): mixed
    {
        return is_numeric($value) ? $value + 0 : '';
    }
}
