<?php

namespace Fieldsbox\Field\Types;

use DateTime;
use Fieldsbox\Field\Field;

/**
 * Shared rendering/sanitizing for the date/date-time/time fields, all of
 * which are a plain text input progressively enhanced by the Flatpickr JS
 * library (see initFlatpickr() in fieldsbox.js).
 *
 * $date_format uses PHP's date()/DateTime::createFromFormat() tokens
 * (Y, m, d, H, i, s, ...) - Flatpickr's own dateFormat option accepts the
 * same token set, so one format string configures both sides without
 * translation.
 */
abstract class AbstractFlatpickrField extends Field
{
    protected string $date_format;

    /** @var array<string, mixed> Extra Flatpickr options (minDate, maxDate, ...), merged client-side. */
    protected array $flatpickr_options = [];

    /**
     * Override the default PHP/Flatpickr format token string for this field.
     */
    public function set_format(string $format): static
    {
        $this->date_format = $format;
        return $this;
    }

    /**
     * Merge extra Flatpickr constructor options, e.g.
     * ->set_flatpickr_options(['minDate' => 'today', 'maxDate' => '2026-12-31']).
     * Passed through to the JS side as a JSON data attribute.
     */
    public function set_flatpickr_options(array $options): static
    {
        $this->flatpickr_options = array_merge($this->flatpickr_options, $options);
        return $this;
    }

    /**
     * 'date', 'datetime', or 'time' - read by fieldsbox.js to decide which
     * Flatpickr options (enableTime, noCalendar) to switch on.
     */
    abstract protected function flatpickr_mode(): string;

    protected function render_input(mixed $value): string
    {
        $extra_options = $this->flatpickr_options
            ? sprintf(' data-flatpickr-options="%s"', esc_attr((string) wp_json_encode($this->flatpickr_options)))
            : '';

        return sprintf(
            '<input type="text" id="%1$s" name="%2$s" value="%3$s" class="fieldsbox-flatpickr" autocomplete="off" data-flatpickr-mode="%4$s" data-flatpickr-format="%5$s"%6$s%7$s%8$s>',
            esc_attr($this->get_html_id()),
            esc_attr($this->get_html_name()),
            esc_attr((string) $value),
            esc_attr($this->flatpickr_mode()),
            esc_attr($this->date_format),
            $extra_options,
            $this->required ? ' required' : '',
            $this->render_attributes()
        );
    }

    /**
     * Reject anything that doesn't parse as this field's format instead of
     * storing free-typed garbage - a direct POST can bypass the Flatpickr
     * UI entirely.
     */
    public function sanitize(mixed $value): mixed
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $parsed = DateTime::createFromFormat($this->date_format, $value);

        return $parsed ? $parsed->format($this->date_format) : '';
    }
}
