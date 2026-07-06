<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single-line email input (<input type="email">), giving the browser's own
 * format validation for free.
 */
class EmailField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<input type="email" id="%1$s" name="%2$s" value="%3$s"%4$s%5$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr((string) $value),
            $this->required ? ' required' : '',
            $this->render_attributes()
        );
    }

    public function sanitize(mixed $value): mixed
    {
        return sanitize_email((string) $value);
    }
}
