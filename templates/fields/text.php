<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type' => 'text',
	)
);

?>

<input <?php echo Helpers::attributes( $attributes ); ?>>
