<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single generic file picker (any mime type) backed by the WordPress media
 * library. Same attachment-id storage and JS wiring as ImageField, but
 * previews as a filename link instead of a thumbnail - see
 * initMediaField() in fieldsbox.js.
 */
class FileField extends Field
{
    protected function render_input(mixed $value): string
    {
        $attachment_id = absint($value);
        $file_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
        $filename = $file_url ? basename($file_url) : '';

        $html = '<div class="fieldsbox-media-field" data-media-type="file">';
        $html .= sprintf(
            '<input type="hidden" id="%1$s" name="%2$s" value="%3$s" class="fieldsbox-media-value">',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr((string) $attachment_id)
        );
        $html .= sprintf('<div class="fieldsbox-media-preview"%s>', $filename ? '' : ' style="display:none"');
        $html .= $filename ? sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($file_url), esc_html($filename)) : '';
        $html .= '</div>';
        $html .= '<button type="button" class="button fieldsbox-media-select">' . esc_html('Select File') . '</button>';
        $html .= sprintf(
            '<button type="button" class="button fieldsbox-media-remove"%s>%s</button>',
            $attachment_id ? '' : ' style="display:none"',
            esc_html('Remove')
        );
        $html .= '</div>';

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        return absint($value) ?: '';
    }
}
