<?php

namespace Fieldsbox\Field\Types;

/**
 * Date-only picker (no time component), powered by Flatpickr.
 */
class DateField extends AbstractFlatpickrField
{
    protected string $date_format = 'Y-m-d';

    protected function flatpickr_mode(): string
    {
        return 'date';
    }
}
