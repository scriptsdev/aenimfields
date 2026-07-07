<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single-choice radio button group, rendered from set_options().
 */
class RadioField extends Field
{
    protected function render_input(mixed $value): string
    {
        $html = '';

        // Render one radio <input> per option, all sharing the field's name
        // so only one can be selected at a time.
        foreach ($this->options as $key => $label) {
            $id = sprintf('%s-%s', $this->get_html_id(), sanitize_key((string) $key));

            $html .= sprintf(
                '<label class="fieldsbox-radio-option"><input type="radio" id="%1$s" name="%2$s" value="%3$s"%4$s> %5$s</label>',
                esc_attr($id),
                esc_attr($this->get_html_name()),
                esc_attr((string) $key),
                checked((string) $value, (string) $key, false),
                esc_html($label)
            );
        }

        return $html;
    }

    /**
     * Only accept a value that's actually one of this field's set_options()
     * choices - guards against a forged direct POST bypassing the rendered
     * radio inputs.
     */
    public function sanitize(mixed $value): mixed
    {
        $value = sanitize_text_field((string) $value);

        return in_array($value, $this->get_option_keys(), true) ? $value : '';
    }
}
