<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Helpers;

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type' => 'date',
	)
);

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>
