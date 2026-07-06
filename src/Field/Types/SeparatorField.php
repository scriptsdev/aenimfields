<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * A presentational-only divider used to break a long list of fields into
 * visually distinct sections. Renders no input and stores no value - the
 * label (if any) is shown as a section heading. For a blank rule with no
 * heading text, chain ->set_label('') after Field::make().
 */
class SeparatorField extends Field
{
    protected function render_input(mixed $value): string
    {
        return '';
    }

    /**
     * Overrides the base render() entirely - a separator has no input,
     * required-state, or help text to lay out, just an optional heading.
     */
    public function render(mixed $value): string
    {
        $wrapper_classes = array_merge(['fieldsbox-field', 'fieldsbox-field-separator'], $this->classes);

        $heading = $this->label !== ''
            ? sprintf('<h3 class="fieldsbox-separator-heading">%s</h3>', esc_html($this->label))
            : '';

        return sprintf(
            '<div class="%1$s" id="%2$s">%3$s</div>',
            esc_attr(implode(' ', $wrapper_classes)),
            esc_attr($this->get_html_id()),
            $heading
        );
    }

    /**
     * Nothing is ever submitted for this field, so there is nothing to
     * store - PostMetaContainer treats '' as "no meta row" and
     * ThemeOptionsContainer stores it as a harmless empty entry.
     */
    public function sanitize(mixed $value): mixed
    {
        return '';
    }
}
