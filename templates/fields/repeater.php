<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Application;
use AenimTech\AenimFields\Core\Helpers;

$app  = new Application();
$rows = array_values( (array) $field->get_value() );

$min_rows = (int) $field->get_arg( 'min_rows', 0 );
$max_rows = (int) $field->get_arg( 'max_rows', 0 );

$wrapper_attrs = array(
	'class'                               => 'aenimfields-repeater',
	'data-aenimfields-repeater'             => true,
	'data-aenimfields-repeater-next-index'  => count( $rows ),
);

if ( $min_rows > 0 ) {
	$wrapper_attrs['data-aenimfields-repeater-min'] = $min_rows;
}

if ( $max_rows > 0 ) {
	$wrapper_attrs['data-aenimfields-repeater-max'] = $max_rows;
}

?>

<div <?php echo Helpers::attributes( $wrapper_attrs ); ?>>

	<div class="aenimfields-repeater-rows">

		<?php foreach ( $rows as $index => $row ) : ?>

			<div class="aenimfields-repeater-row" data-aenimfields-repeater-index="<?php echo esc_attr( $index ); ?>">

				<div class="aenimfields-repeater-row-fields">
					<?php echo $app->render( $field->build_row_field_args( $index, (array) $row ) ); ?>
				</div>

				<button type="button" class="button aenimfields-repeater-remove-row">
					<?php esc_html_e( 'Remove', 'aenimfields' ); ?>
				</button>

			</div>

		<?php endforeach; ?>

	</div>

	<button type="button" class="button aenimfields-repeater-add-row">
		<?php echo esc_html( $field->get_arg( 'add_button_text', __( 'Add Row', 'aenimfields' ) ) ); ?>
	</button>

	<template class="aenimfields-repeater-template">
		<div class="aenimfields-repeater-row" data-aenimfields-repeater-index="__INDEX__">

			<div class="aenimfields-repeater-row-fields">
				<?php echo $app->render( $field->build_row_field_args( '__INDEX__', array() ) ); ?>
			</div>

			<button type="button" class="button aenimfields-repeater-remove-row">
				<?php esc_html_e( 'Remove', 'aenimfields' ); ?>
			</button>

		</div>
	</template>

</div>
