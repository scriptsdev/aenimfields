<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * A fixed (non-repeating) set of sub-fields nested under one field, stored
 * as a single associative array - e.g. an "Address" group containing
 * street/city/zip.
 *
 * For a repeatable list of the same sub-field set, use 'repeater' instead.
 */
class GroupField extends Field
{
    /** @var Field[] */
    protected array $sub_fields = [];

    /**
     * @param Field[] $fields
     */
    public function set_fields(array $fields): static
    {
        $this->sub_fields = $fields;
        return $this;
    }

    public function get_sub_fields(): array
    {
        return $this->sub_fields;
    }

    protected function render_input(mixed $value): string
    {
        $value = is_array($value) ? $value : [];

        $html = '<div class="fieldsbox-group">';

        foreach ($this->sub_fields as $sub_field) {
            // Namespace the sub-field's HTML name under this group's name
            // (e.g. "address[city]") without touching its own get_name().
            $sub_field->set_html_name(sprintf('%s[%s]', $this->get_html_name(), $sub_field->get_name()));
            $html .= $sub_field->render($value[$sub_field->get_name()] ?? null);
        }

        $html .= '</div>';

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        $value = is_array($value) ? $value : [];

        $clean = [];
        foreach ($this->sub_fields as $sub_field) {
            $raw = $value[$sub_field->get_name()] ?? ($sub_field->get_type() === 'multiselect' ? [] : '');
            $clean[$sub_field->get_name()] = $sub_field->sanitize($raw);
        }

        return $clean;
    }
}
