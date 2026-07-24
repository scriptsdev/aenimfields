<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Helpers;

$options = (array) $field->get_arg( 'options' );

foreach ( $options as $key => $label ) :

	$attributes = Helpers::input_attributes(
		$field,
		array(
			'type'  => 'radio',
			'value' => $key,
		)
	);

	if ( Helpers::is_checked( $field->get_value(), $key ) ) {
		$attributes['checked'] = true;
	}

	?>

	<label class="fieldsbox-radio">

		<input <?php echo Helpers::attributes( $attributes ); ?>>

		<span><?php echo esc_html( $label ); ?></span>

	</label>

<?php endforeach; ?>
