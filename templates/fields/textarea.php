<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

$attributes = array(
	'id'   => $field->get_id(),
	'name' => $field->get_name(),
);

if ( $field->get_arg( 'placeholder' ) !== '' ) {
	$attributes['placeholder'] = $field->get_arg( 'placeholder' );
}

if ( $field->get_arg( 'class' ) !== '' ) {
	$attributes['class'] = $field->get_arg( 'class' );
}

if ( $field->get_arg( 'readonly' ) ) {
	$attributes['readonly'] = true;
}

if ( $field->get_arg( 'disabled' ) ) {
	$attributes['disabled'] = true;
}

if ( $field->is_required() ) {
	$attributes['required'] = true;
}

// Optional rows
if ( $field->get_arg( 'rows' ) ) {
	$attributes['rows'] = (int) $field->get_arg( 'rows' );
}

// Optional cols
if ( $field->get_arg( 'cols' ) ) {
	$attributes['cols'] = (int) $field->get_arg( 'cols' );
}

$attributes = array_merge(
	$attributes,
	$field->get_arg( 'input_attr' )
);

?>

<textarea <?php echo Helpers::attributes( $attributes ); ?>><?php echo esc_textarea( $field->get_value() ); ?></textarea>
