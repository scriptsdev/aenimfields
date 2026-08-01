<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type'  => 'checkbox',
		'value' => 1,
	)
);

if ( Helpers::is_checked( $field->get_value(), 1 ) ) {
	$attributes['checked'] = true;
}

?>

<label class="aenimfields-toggle">

	<input <?php echo Helpers::attributes( $attributes ); ?>>

	<span class="aenimfields-toggle-slider"></span>

	<?php if ( $field->get_arg( 'text' ) ) : ?>

		<span class="aenimfields-toggle-label">
			<?php echo esc_html( $field->get_arg( 'text' ) ); ?>
		</span>

	<?php endif; ?>

</label>
