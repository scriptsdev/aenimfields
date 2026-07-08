<?php

namespace Fieldsbox\Field\Types;

use Fieldsbox\Field\Field;

/**
 * Single generic file picker backed by the WordPress media library. Same
 * attachment-id storage and JS wiring as ImageField, but previews as a
 * filename link instead of a thumbnail - see initMediaField() in
 * fieldsbox.js.
 *
 * Accepts any mime type by default; call set_mime_types() to restrict the
 * media library picker to one or more types, e.g. set_mime_types('video')
 * for videos only, or set_mime_types('application/pdf') for a single exact
 * type. Accepts either WordPress' generic type buckets ('image', 'video',
 * 'audio', 'application', 'text') or exact mime strings - both are also
 * re-checked server-side in sanitize() so the restriction can't be bypassed
 * by submitting an arbitrary attachment id for a disallowed type.
 */
class FileField extends Field
{
    /** @var string[] Empty means "any mime type", the historical default. */
    protected array $mime_types = [];

    /**
     * Restrict the media library picker (and sanitize()) to one or more
     * mime types/type buckets, e.g. set_mime_types('video') or
     * set_mime_types('image/png', 'image/jpeg').
     */
    public function set_mime_types(string ...$mime_types): static
    {
        $this->mime_types = $mime_types;

        return $this;
    }

    protected function render_input(mixed $value): string
    {
        $attachment_id = absint($value);
        $file_url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
        $filename = $file_url ? basename($file_url) : '';

        $html = sprintf(
            '<div class="fieldsbox-media-field" data-media-type="file"%s>',
            $this->mime_types ? sprintf(' data-media-mime-types="%s"', esc_attr(implode(',', $this->mime_types))) : ''
        );
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
        $attachment_id = absint($value);

        if (! $attachment_id) {
            return '';
        }

        if ($this->mime_types && ! $this->matches_allowed_mime_type($attachment_id)) {
            return '';
        }

        return $attachment_id;
    }

    /**
     * Whether the given attachment's mime type satisfies set_mime_types() -
     * matching either an exact mime string ("application/pdf") or a
     * WordPress type bucket ("video" matches "video/mp4", "video/webm", ...).
     */
    private function matches_allowed_mime_type(int $attachment_id): bool
    {
        $mime = get_post_mime_type($attachment_id);

        if (! $mime) {
            return false;
        }

        foreach ($this->mime_types as $allowed) {
            if ($allowed === $mime || str_starts_with($mime, $allowed . '/')) {
                return true;
            }
        }

        return false;
    }
}
