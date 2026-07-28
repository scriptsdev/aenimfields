<?php

defined( 'ABSPATH' ) || exit;

use ScriptsDev\FieldsBox\Core\Helpers;

$attributes = Helpers::select_attributes(
	$field,
	array(
		'name'     => $field->get_name() . '[]',
		'multiple' => true,
	)
);

?>

<select <?php echo Helpers::attributes( $attributes ); ?>>

	<?php foreach ( (array) $field->get_arg( 'options' ) as $key => $label ) : ?>

		<option
			value="<?php echo esc_attr( $key ); ?>"
			<?php selected( Helpers::is_checked( $field->get_value(), $key ) ); ?>>

			<?php echo esc_html( $label ); ?>

		</option>

	<?php endforeach; ?>

</select>
