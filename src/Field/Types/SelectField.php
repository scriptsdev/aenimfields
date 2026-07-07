<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Native single-choice dropdown (<select>), built from set_options().
 * Also used for the 'dropdown' alias registered in Field::$type_map.
 */
class SelectField extends Field
{
    protected function render_input(mixed $value): string
    {
        $html = sprintf(
            '<select id="%1$s" name="%2$s"%3$s%4$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            $this->required ? ' required' : '',
            $this->render_attributes()
        );

        foreach ($this->options as $key => $label) {
            $html .= sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr((string) $key),
                selected((string) $value, (string) $key, false),
                esc_html($label)
            );
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Only accept a value that's actually one of this field's set_options()
     * choices - guards against a forged direct POST bypassing the rendered
     * <select>.
     */
    public function sanitize(mixed $value): mixed
    {
        $value = sanitize_text_field((string) $value);

        return in_array($value, $this->get_option_keys(), true) ? $value : '';
    }
}
