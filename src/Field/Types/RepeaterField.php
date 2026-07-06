<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * A repeatable list of rows, each containing the same sub-field set - e.g.
 * a "Team Members" repeater where each row has name/role/photo fields.
 * Stored as a list of associative arrays.
 *
 * Rows are namespaced by a real numeric index ("members[3][role]") rather
 * than PHP's "append" bracket syntax ("members[][role]"), because radio
 * button grouping is based on the HTML name attribute alone - two rows
 * sharing one name would make their radios fight over the same selection.
 * fieldsbox.js assigns each new row a fresh index by cloning the
 * <template> below and replacing the literal "__INDEX__" placeholder - see
 * initRepeater().
 */
class RepeaterField extends Field
{
    /** @var Field[] */
    protected array $sub_fields = [];

    protected string $button_label = 'Add Row';
    protected ?int $max_rows = null;

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

    public function set_button_label(string $label): static
    {
        $this->button_label = $label;
        return $this;
    }

    public function set_max_rows(?int $max_rows): static
    {
        $this->max_rows = $max_rows;
        return $this;
    }

    protected function render_input(mixed $value): string
    {
        $rows = is_array($value) ? $value : [];

        $html = sprintf(
            '<div class="fieldsbox-repeater"%s>',
            $this->max_rows ? ' data-max-rows="' . (int) $this->max_rows . '"' : ''
        );
        $html .= '<div class="fieldsbox-repeater-rows">';

        foreach ($rows as $index => $row) {
            $html .= $this->render_row((string) $index, is_array($row) ? $row : []);
        }

        $html .= '</div>';
        $html .= sprintf('<button type="button" class="button fieldsbox-repeater-add">%s</button>', esc_html($this->button_label));
        // Inert markup (never submitted, never runs embedded editors) cloned
        // by fieldsbox.js to add a fresh row - see the class doc comment.
        $html .= '<template class="fieldsbox-repeater-template">' . $this->render_row('__INDEX__', []) . '</template>';
        $html .= '</div>';

        return $html;
    }

    protected function render_row(string $index, array $row_value): string
    {
        $html = '<div class="fieldsbox-repeater-row">';
        $html .= '<div class="fieldsbox-repeater-row-fields">';

        foreach ($this->sub_fields as $sub_field) {
            $sub_field->set_html_name(sprintf('%s[%s][%s]', $this->get_html_name(), $index, $sub_field->get_name()));
            $html .= $sub_field->render($row_value[$sub_field->get_name()] ?? null);
        }

        $html .= '</div>';
        $html .= '<button type="button" class="button-link-delete fieldsbox-repeater-remove">' . esc_html('Remove') . '</button>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Rows arrive keyed by whatever index fieldsbox.js assigned them
     * client-side (not necessarily sequential) - array_values() below
     * renumbers them 0..n before storage.
     */
    public function sanitize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $clean_row = [];
            foreach ($this->sub_fields as $sub_field) {
                $raw = $row[$sub_field->get_name()] ?? ($sub_field->get_type() === 'multiselect' ? [] : '');
                $clean_row[$sub_field->get_name()] = $sub_field->sanitize($raw);
            }

            // Drop rows where every sub-field ended up empty (e.g. a row
            // added and immediately abandoned without input).
            $has_value = array_filter($clean_row, static fn ($v) => $v !== '' && $v !== []);
            if ($has_value) {
                $rows[] = $clean_row;
            }
        }

        return array_values($rows);
    }
}
