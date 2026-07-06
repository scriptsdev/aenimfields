<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Multiple-image picker backed by the WordPress media library.
 *
 * Stores a comma-separated list of attachment IDs in a single hidden input
 * (rather than name="x[]") since the whole list is rewritten together by
 * fieldsbox.js on every add/remove - see initGalleryField().
 */
class GalleryField extends Field
{
    protected function render_input(mixed $value): string
    {
        $ids = array_values(array_filter(array_map('absint', is_array($value) ? $value : [])));

        $html = '<div class="fieldsbox-media-field fieldsbox-gallery-field" data-media-type="gallery">';
        $html .= sprintf(
            '<input type="hidden" id="%1$s" name="%2$s" value="%3$s" class="fieldsbox-media-value">',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr(implode(',', $ids))
        );
        $html .= '<div class="fieldsbox-gallery-items">';

        foreach ($ids as $id) {
            $thumb = wp_get_attachment_image_url($id, 'thumbnail');

            if (! $thumb) {
                continue;
            }

            $html .= sprintf(
                '<div class="fieldsbox-gallery-item" data-id="%1$d"><img src="%2$s" alt=""><button type="button" class="fieldsbox-gallery-remove">&times;</button></div>',
                $id,
                esc_url($thumb)
            );
        }

        $html .= '</div>';
        $html .= '<button type="button" class="button fieldsbox-gallery-add">' . esc_html('Add Images') . '</button>';
        $html .= '</div>';

        return $html;
    }

    /**
     * The stored value is a CSV string (matching the hidden input above),
     * not an array - this normalizes either form back to a clean id list.
     */
    public function sanitize(mixed $value): mixed
    {
        $ids = is_array($value) ? $value : explode(',', (string) $value);

        return array_values(array_filter(array_map('absint', $ids)));
    }
}
