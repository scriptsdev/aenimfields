<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Helpers;

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type' => 'color',
	)
);

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>
