<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single image picker backed by the WordPress media library.
 *
 * Stores the attachment ID (not a URL) so the image can be re-resolved to
 * any size later. The hidden input holds the id; fieldsbox.js wires the
 * "Select"/"Remove" buttons up to wp.media - see initMediaField().
 */
class ImageField extends Field
{
    protected function render_input(mixed $value): string
    {
        $attachment_id = absint($value);
        $image_url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'medium') : '';

        $html = '<div class="fieldsbox-media-field" data-media-type="image">';
        $html .= sprintf(
            '<input type="hidden" id="%1$s" name="%2$s" value="%3$s" class="fieldsbox-media-value">',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr((string) $attachment_id)
        );
        $html .= sprintf('<div class="fieldsbox-media-preview"%s>', $image_url ? '' : ' style="display:none"');
        $html .= $image_url ? sprintf('<img src="%s" alt="">', esc_url($image_url)) : '';
        $html .= '</div>';
        $html .= '<button type="button" class="button fieldsbox-media-select">' . esc_html('Select Image') . '</button>';
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
