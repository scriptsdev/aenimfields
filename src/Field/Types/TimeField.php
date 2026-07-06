<?php

namespace Fieldsbox\Field\Types;

/**
 * Time-only picker (no calendar), powered by Flatpickr.
 */
class TimeField extends AbstractFlatpickrField
{
    protected string $date_format = 'H:i';

    protected function flatpickr_mode(): string
    {
        return 'time';
    }
}
