<?php

defined( 'ABSPATH' ) || exit;

use ScriptsDev\FieldsBox\Core\Helpers;

$extra = array(
	'type' => 'number',
);

if ( $field->get_arg( 'min' ) !== '' ) {
	$extra['min'] = $field->get_arg( 'min' );
}

if ( $field->get_arg( 'max' ) !== '' ) {
	$extra['max'] = $field->get_arg( 'max' );
}

if ( $field->get_arg( 'step' ) !== '' ) {
	$extra['step'] = $field->get_arg( 'step' );
}

$attributes = Helpers::input_attributes(
	$field,
	$extra
);

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>
