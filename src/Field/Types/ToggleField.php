<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * A boolean checkbox styled as an on/off switch instead of a square box.
 * Behaves identically to 'checkbox' underneath (stores '1' or '') - purely
 * a presentational alternative, see .fieldsbox-toggle in fieldsbox.css.
 */
class ToggleField extends Field
{
    protected function render_input(mixed $value): string
    {
        return sprintf(
            '<label class="fieldsbox-toggle"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s%4$s><span class="fieldsbox-toggle-slider"></span></label>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            checked((string) $value, '1', false),
            $this->render_attributes()
        );
    }

    public function sanitize(mixed $value): mixed
    {
        return $value ? '1' : '';
    }
}
