<?php

defined( 'ABSPATH' ) || exit;

use ScriptsDev\FieldsBox\Core\Application;
use ScriptsDev\FieldsBox\Core\Helpers;

$app  = new Application();
$rows = array_values( (array) $field->get_value() );

$min_rows = (int) $field->get_arg( 'min_rows', 0 );
$max_rows = (int) $field->get_arg( 'max_rows', 0 );

$wrapper_attrs = array(
	'class'                               => 'fieldsbox-repeater',
	'data-fieldsbox-repeater'             => true,
	'data-fieldsbox-repeater-next-index'  => count( $rows ),
);

if ( $min_rows > 0 ) {
	$wrapper_attrs['data-fieldsbox-repeater-min'] = $min_rows;
}

if ( $max_rows > 0 ) {
	$wrapper_attrs['data-fieldsbox-repeater-max'] = $max_rows;
}

?>

<div <?php echo Helpers::attributes( $wrapper_attrs ); ?>>

	<div class="fieldsbox-repeater-rows">

		<?php foreach ( $rows as $index => $row ) : ?>

			<div class="fieldsbox-repeater-row" data-fieldsbox-repeater-index="<?php echo esc_attr( $index ); ?>">

				<div class="fieldsbox-repeater-row-fields">
					<?php echo $app->render( $field->build_row_field_args( $index, (array) $row ) ); ?>
				</div>

				<button type="button" class="button fieldsbox-repeater-remove-row">
					<?php esc_html_e( 'Remove', 'fieldsbox' ); ?>
				</button>

			</div>

		<?php endforeach; ?>

	</div>

	<button type="button" class="button fieldsbox-repeater-add-row">
		<?php echo esc_html( $field->get_arg( 'add_button_text', __( 'Add Row', 'fieldsbox' ) ) ); ?>
	</button>

	<template class="fieldsbox-repeater-template">
		<div class="fieldsbox-repeater-row" data-fieldsbox-repeater-index="__INDEX__">

			<div class="fieldsbox-repeater-row-fields">
				<?php echo $app->render( $field->build_row_field_args( '__INDEX__', array() ) ); ?>
			</div>

			<button type="button" class="button fieldsbox-repeater-remove-row">
				<?php esc_html_e( 'Remove', 'fieldsbox' ); ?>
			</button>

		</div>
	</template>

</div>
