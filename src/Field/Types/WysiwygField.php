<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Full TinyMCE editor, wrapping WordPress core's wp_editor().
 *
 * Not supported inside a repeater row: TinyMCE editors are bootstrapped by
 * id at page load, so instances added later by cloning a repeater template
 * would render as plain, non-rich textareas. Use it at the top level or
 * inside a (non-repeating) group instead.
 */
class WysiwygField extends Field
{
    /** @var array<string, mixed> */
    protected array $editor_settings = [
        'textarea_rows' => 10,
        'media_buttons' => false,
    ];

    /**
     * Merge extra options into the wp_editor() $settings array, e.g.
     * ['media_buttons' => true, 'tinymce' => false] for a plain-text/
     * quicktags-only editor.
     */
    public function set_editor_settings(array $settings): static
    {
        $this->editor_settings = array_merge($this->editor_settings, $settings);
        return $this;
    }

    protected function render_input(mixed $value): string
    {
        // wp_editor()'s id may only contain lowercase letters and underscores.
        $editor_id = strtolower((string) preg_replace('/[^a-z0-9_]/i', '_', 'fieldsbox_' . $this->name));

        // wp_editor() echoes directly rather than returning a string, so it
        // has to be captured the same way the rest of the package expects.
        ob_start();
        wp_editor((string) $value, $editor_id, array_merge($this->editor_settings, [
            'textarea_name' => $this->get_html_name(),
        ]));

        return (string) ob_get_clean();
    }

    public function sanitize(mixed $value): mixed
    {
        return wp_kses_post((string) $value);
    }
}
