<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Multi-line text input (<textarea>).
 */
class TextareaField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<textarea id="%1$s" name="%2$s"%3$s%4$s>%5$s</textarea>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            $this->required ? ' required' : '',
            $this->render_attributes(),
            esc_textarea((string) $value)
        );
    }

    /**
     * Use sanitize_textarea_field() instead of the single-line default so
     * line breaks in the submitted value are preserved.
     */
    public function sanitize(mixed $value): mixed
    {
        return sanitize_textarea_field((string) $value);
    }
}
