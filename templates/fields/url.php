<?php

defined( 'ABSPATH' ) || exit;

use ScriptsDev\FieldsBox\Core\Helpers;

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type' => 'url',
	)
);

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>