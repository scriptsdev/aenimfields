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
            '<select id="fieldsbox-%1$s" name="%1$s"%2$s%3$s>',
            esc_attr($this->name),
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
}
