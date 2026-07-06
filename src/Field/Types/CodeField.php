<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Raw code/HTML textarea (monospace styling via .fieldsbox-code) for markup
 * or snippets that shouldn't be run through the plain-text sanitizer.
 */
class CodeField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<textarea id="%1$s" name="%2$s" class="fieldsbox-code"%3$s%4$s>%5$s</textarea>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            $this->required ? ' required' : '',
            $this->render_attributes(),
            esc_textarea((string) $value)
        );
    }

    /**
     * Storing arbitrary markup/scripts verbatim is inherently risky, so
     * only users who can already publish unfiltered HTML get it as-is;
     * everyone else is limited to the tags wp_kses_post() allows in post
     * content.
     */
    public function sanitize(mixed $value): mixed
    {
        $value = (string) $value;

        return current_user_can('unfiltered_html') ? $value : wp_kses_post($value);
    }
}
