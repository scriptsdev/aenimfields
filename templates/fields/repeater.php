<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Application;
use AenimTech\AenimFields\Core\Helpers;

$app  = new Application();
$rows = array_values( (array) $field->get_value() );

$min_rows = (int) $field->get_arg( 'min_rows', 0 );
$max_rows = (int) $field->get_arg( 'max_rows', 0 );

$wrapper_attrs = array(
	'class'                                 => 'aenimfields-repeater',
	'data-aenimfields-repeater'             => true,
	'data-aenimfields-repeater-next-index'  => count( $rows ),
);

if ( $min_rows > 0 ) {
	$wrapper_attrs['data-aenimfields-repeater-min'] = $min_rows;
}

if ( $max_rows > 0 ) {
	$wrapper_attrs['data-aenimfields-repeater-max'] = $max_rows;
}

/**
 * Render one row: a collapsible header (drag handle, live title, remove
 * button, collapse toggle) above the row's own fields.
 *
 * @param int|string $index      Row index, or the JS template placeholder.
 * @param array      $row_value  Stored values for this row.
 * @param int|string $row_number 1-based row number, or a placeholder
 *                                token for the JS template.
 */
$render_row = static function ( $index, array $row_value, $row_number ) use ( $field, $app ) {

	$body_id      = $field->body_id( $index );
	$title_target = $field->title_target_id( $index );
	$row_title    = $field->row_title( $row_value, $row_number );

	$row_attrs = array(
		'class'                                    => 'aenimfields-repeater-row',
		'data-aenimfields-repeater-index'          => $index,
		'data-aenimfields-repeater-title-target'   => $title_target,
		'data-aenimfields-repeater-title-fallback' => $row_title,
	);

	$header_attrs = array(
		'class'          => 'aenimfields-repeater-row-header',
		'role'           => 'button',
		'tabindex'       => '0',
		'aria-expanded'  => 'true',
		'aria-controls'  => $body_id,
	);

	?>

	<div <?php echo Helpers::attributes( $row_attrs ); ?>>

		<div <?php echo Helpers::attributes( $header_attrs ); ?>>

			<span class="aenimfields-repeater-drag-handle" aria-hidden="true">
				<span class="dashicons dashicons-move aenimfields-repeater-icon aenimfields-repeater-icon-drag"></span>
			</span>

			<span class="aenimfields-repeater-row-title"><?php echo esc_html( $row_title ); ?></span>

			<span class="aenimfields-repeater-row-actions">

				<button type="button" class="aenimfields-repeater-remove-row" aria-label="<?php esc_attr_e( 'Remove row', 'aenimfields' ); ?>">
					<span class="dashicons dashicons-trash aenimfields-repeater-icon aenimfields-repeater-icon-remove"></span>
				</button>

				<span class="aenimfields-repeater-toggle-icon" aria-hidden="true">
					<span class="dashicons dashicons-arrow-down-alt2 aenimfields-repeater-icon aenimfields-repeater-icon-toggle"></span>
				</span>

			</span>

		</div>

		<div class="aenimfields-repeater-row-body" id="<?php echo esc_attr( $body_id ); ?>">
			<div class="aenimfields-repeater-row-fields">
				<?php echo $app->render( $field->build_row_field_args( $index, $row_value ) ); ?>
			</div>
		</div>

	</div>

	<?php
};

?>

<div <?php echo Helpers::attributes( $wrapper_attrs ); ?>>

	<div class="aenimfields-repeater-rows">

		<?php foreach ( $rows as $index => $row ) : ?>
			<?php $render_row( $index, (array) $row, $index + 1 ); ?>
		<?php endforeach; ?>

	</div>

	<button type="button" class="button aenimfields-repeater-add-row">
		<?php echo esc_html( $field->get_arg( 'add_button_text', __( 'Add Row', 'aenimfields' ) ) ); ?>
	</button>

	<template class="aenimfields-repeater-template">
		<?php $render_row( '__INDEX__', array(), '__ROW_NUMBER__' ); ?>
	</template>

</div>
