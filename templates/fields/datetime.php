<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Assets;
use FieldsBox\Core\Helpers;

Assets::enqueue_datepicker();

$extra = array(
	'type'                       => 'text',
	'autocomplete'               => 'off',
	'data-fieldsbox-datepicker'  => true,
	'data-fieldsbox-date-format' => $field->get_arg( 'date_format', 'Y-m-d H:i' ),
	'data-fieldsbox-enable-time' => 'true',
);

if ( '' !== $field->get_min_date() ) {
	$extra['data-fieldsbox-min-date'] = $field->get_min_date();
}

if ( '' !== $field->get_max_date() ) {
	$extra['data-fieldsbox-max-date'] = $field->get_max_date();
}

$attributes = Helpers::input_attributes( $field, $extra );

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>
