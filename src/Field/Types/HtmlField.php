<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * A presentational-only field that renders arbitrary developer-supplied
 * markup between other fields (e.g. a section description). Renders no
 * input and stores no value.
 *
 * The set_html() content is trusted, developer-authored markup baked into
 * the consuming plugin's own PHP source - not user input - so it's output
 * unescaped, same as Carbon Fields' 'html' field. Never pass user-supplied
 * or database-sourced strings to set_html().
 */
class HtmlField extends Field
{
    protected string $html = '';

    public function set_html(string $html): static
    {
        $this->html = $html;
        return $this;
    }

    protected function render_input(mixed $value): string
    {
        return '';
    }

    /**
     * Overrides the base render() entirely - like SeparatorField, this has
     * no label, input, required-state, or help text to lay out.
     */
    public function render(mixed $value): string
    {
        $wrapper_classes = array_merge(['fieldsbox-field', 'fieldsbox-field-html'], $this->classes);

        return sprintf(
            '<div class="%1$s" id="%2$s">%3$s</div>',
            esc_attr(implode(' ', $wrapper_classes)),
            esc_attr($this->get_html_id()),
            $this->html
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
