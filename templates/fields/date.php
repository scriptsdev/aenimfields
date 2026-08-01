<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

$extra = array(
	'type'                       => 'text',
	'autocomplete'               => 'off',
	'data-aenimfields-datepicker'  => true,
	'data-aenimfields-date-format' => $field->get_arg( 'date_format', 'Y-m-d' ),
	'data-aenimfields-enable-time' => 'false',
);

if ( '' !== $field->get_min_date() ) {
	$extra['data-aenimfields-min-date'] = $field->get_min_date();
}

if ( '' !== $field->get_max_date() ) {
	$extra['data-aenimfields-max-date'] = $field->get_max_date();
}

$attributes = Helpers::input_attributes( $field, $extra );

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>
