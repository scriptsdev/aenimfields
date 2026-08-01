<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

$options = $field->get_arg( 'options' );
$value   = $field->get_value();

/**
 * ----------------------------------------------------
 * Multiple Checkboxes
 * ----------------------------------------------------
 */
if ( ! empty( $options ) ) :

	foreach ( $options as $key => $label ) :

		$attributes = Helpers::input_attributes(
			$field,
			array(
				'type'  => 'checkbox',
				'name'  => $field->get_name() . '[]',
				'value' => $key,
			)
		);

		if ( is_array( $value ) && in_array( $key, $value, true ) ) {
			$attributes['checked'] = true;
		}

		?>

		<label class="aenimfields-checkbox">

			<input <?php echo Helpers::attributes( $attributes ); ?>>

			<?php echo esc_html( $label ); ?>

		</label>

		<?php

	endforeach;

else :

	/**
	 * ----------------------------------------------------
	 * Single Checkbox
	 * ----------------------------------------------------
	 */

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

	<label class="aenimfields-checkbox">

		<input <?php echo Helpers::attributes( $attributes ); ?>>

		<?php echo esc_html( $field->get_arg( 'text' ) ); ?>

	</label>

<?php endif; ?>
