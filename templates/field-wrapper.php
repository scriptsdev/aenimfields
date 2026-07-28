<?php

/**
 * Field Wrapper Template
 *
 * @var \ScriptsDev\FieldsBox\Contracts\FieldInterface $field
 */

defined( 'ABSPATH' ) || exit;

use ScriptsDev\FieldsBox\Core\Helpers;

$field_template = FIELDSBOX_DIR . '/templates/fields/' . strtolower( $field->get_type() ) . '.php';

$wrapper_class = array(
	'fieldsbox-field',
	'fieldsbox-field-' . $field->get_type(),
	$field->get_arg( 'wrapper_class' ),
);

if ( $field->get_arg( 'error' ) ) {
	$wrapper_class[] = 'fieldsbox-field-error';
}

$wrapper_class = implode( ' ', array_filter( $wrapper_class ) );

$depends_on = $field->get_arg( 'depends_on' );

$wrapper_attributes = array_merge(
	array(
		'id'    => $field->get_id() . '-wrapper',
		'class' => $wrapper_class,
	),
	! empty( $depends_on['field'] ) ? array(
		// Hidden by default; dependency.js reveals the field once its
		// condition is met, so there is no flash of a field that should
		// stay hidden.
		'hidden'                          => true,
		'data-fieldsbox-depends-on-field' => $depends_on['field'],
		'data-fieldsbox-depends-on-value' => wp_json_encode( $depends_on['value'] ?? '' ),
	) : array(),
	$field->get_arg( 'wrapper_attr' )
);

?>

<?php echo $field->get_arg( 'before' ); ?>

<div <?php echo Helpers::attributes( $wrapper_attributes ); ?>>

	<?php if ( $field->get_arg( 'label' ) ) : ?>

		<?php
		$label_attributes = array_merge(
			array(
				'for'   => $field->get_id(),
				'class' => 'fieldsbox-label',
			),
			$field->get_arg( 'label_attr' )
		);
		?>

		<label <?php echo Helpers::attributes( $label_attributes ); ?>>

			<?php echo esc_html( $field->get_arg( 'label' ) ); ?>

			<?php if ( $field->is_required() ) : ?>
				<span class="fieldsbox-required">*</span>
			<?php endif; ?>

		</label>

		<?php if ( $field->get_arg( 'label_description' ) ) : ?>

			<p class="fieldsbox-label-description">

				<?php echo wp_kses_post( $field->get_arg( 'label_description' ) ); ?>

			</p>

		<?php endif; ?>

	<?php endif; ?>

	<div class="fieldsbox-field-control">

		<?php

		if ( $field->get_arg( 'prefix' ) ) :

			?>

			<span class="fieldsbox-prefix">

				<?php echo wp_kses_post( $field->get_arg( 'prefix' ) ); ?>

			</span>

			<?php

		endif;

		if ( file_exists( $field_template ) ) {
			include $field_template;
		}

		if ( $field->get_arg( 'suffix' ) ) :

			?>

			<span class="fieldsbox-suffix">

				<?php echo wp_kses_post( $field->get_arg( 'suffix' ) ); ?>

			</span>

			<?php

		endif;

		?>

	</div>

	<?php if ( $field->get_arg( 'error' ) ) : ?>

	<p class="fieldsbox-error">

		<?php echo esc_html( $field->get_arg( 'error' ) ); ?>

	</p>

<?php endif; ?>

	<?php if ( $field->get_arg( 'description' ) ) : ?>

		<p class="fieldsbox-description">

			<?php echo wp_kses_post( $field->get_arg( 'description' ) ); ?>

		</p>

	<?php endif; ?>

	<?php if ( $field->get_arg( 'help' ) ) : ?>

		<p class="fieldsbox-help">

			<?php echo wp_kses_post( $field->get_arg( 'help' ) ); ?>

		</p>

	<?php endif; ?>

</div>

<?php echo $field->get_arg( 'after' ); ?>
