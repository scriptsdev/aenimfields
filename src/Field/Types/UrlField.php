<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single-line URL input (<input type="url">).
 */
class UrlField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<input type="url" id="%1$s" name="%2$s" value="%3$s"%4$s%5$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr((string) $value),
            $this->required ? ' required' : '',
            $this->render_attributes()
        );
    }

    public function sanitize(mixed $value): mixed
    {
        return esc_url_raw((string) $value);
    }
}
