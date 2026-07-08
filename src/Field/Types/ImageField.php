<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single image picker backed by the WordPress media library.
 *
 * Stores the attachment ID (not a URL) so the image can be re-resolved to
 * any size later. The hidden input holds the id; fieldsbox.js wires the
 * "Select" button and the cross-icon remove button (overlaid on the
 * thumbnail, same style as GalleryField's per-item remove icon) up to
 * wp.media - see initMediaField().
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
        if ($image_url) {
            $html .= sprintf('<img src="%s" alt="">', esc_url($image_url));
            $html .= sprintf(
                '<button type="button" class="fieldsbox-media-remove-icon" title="%1$s" aria-label="%1$s"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>',
                esc_attr('Remove')
            );
        }
        $html .= '</div>';
        $html .= '<button type="button" class="button fieldsbox-media-select">' . esc_html('Select Image') . '</button>';
        $html .= '</div>';

        return $html;
    }

    public function sanitize(mixed $value): mixed
    {
        return absint($value) ?: '';
    }
}
