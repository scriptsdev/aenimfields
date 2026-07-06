<?php

namespace Fieldsbox\Field\Types;

/**
 * Combined date + time picker, powered by Flatpickr.
 */
class DateTimeField extends AbstractFlatpickrField
{
    protected string $date_format = 'Y-m-d H:i';

    protected function flatpickr_mode(): string
    {
        return 'datetime';
    }
}
