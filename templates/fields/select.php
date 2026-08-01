<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

$attributes = array(
	'id'   => $field->get_id(),
	'name' => $field->get_name(),
);

if ( $field->get_arg( 'class' ) !== '' ) {
	$attributes['class'] = $field->get_arg( 'class' );
}

if ( $field->get_arg( 'disabled' ) ) {
	$attributes['disabled'] = true;
}

if ( $field->is_required() ) {
	$attributes['required'] = true;
}

$attributes = Helpers::select_attributes( $field );

?>

<select <?php echo Helpers::attributes( $attributes ); ?>>

	<?php if ( $field->get_arg( 'placeholder' ) !== '' ) : ?>

		<option value="">
			<?php echo esc_html( $field->get_arg( 'placeholder' ) ); ?>
		</option>

	<?php endif; ?>

	<?php foreach ( (array) $field->get_arg( 'options' ) as $key => $label ) : ?>

		<option
			value="<?php echo esc_attr( $key ); ?>"
			<?php selected( Helpers::is_checked( $field->get_value(), $key ) ); ?>
		>
			<?php echo esc_html( $label ); ?>
		</option>

	<?php endforeach; ?>

</select>
