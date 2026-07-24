<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Helpers;

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type'  => 'password',
		// Never prefill passwords.
		'value' => '',
	)
);

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>
