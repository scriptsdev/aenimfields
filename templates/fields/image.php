<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Helpers;

$attachment_id = absint( $field->get_value() );
$preview_url   = $attachment_id
	? wp_get_attachment_image_url( $attachment_id, $field->get_arg( 'preview_size', 'thumbnail' ) )
	: '';

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type' => 'hidden',
	)
);

?>

<div
	class="fieldsbox-media-field fieldsbox-media-field-image"
	data-fieldsbox-media="image"
	data-fieldsbox-media-title="<?php echo esc_attr( $field->get_arg( 'title_text', __( 'Select Image', 'fieldsbox' ) ) ); ?>"
>

	<input <?php echo Helpers::attributes( $attributes ); ?>>

	<div class="fieldsbox-media-preview" <?php echo $preview_url ? '' : 'hidden'; ?>>
		<?php if ( $preview_url ) : ?>
			<img src="<?php echo esc_url( $preview_url ); ?>" alt="">
		<?php endif; ?>
	</div>

	<p class="fieldsbox-media-actions">
		<button type="button" class="button fieldsbox-media-select">
			<?php echo esc_html( $field->get_arg( 'select_text', __( 'Select Image', 'fieldsbox' ) ) ); ?>
		</button>
		<button type="button" class="button fieldsbox-media-remove" <?php echo $preview_url ? '' : 'hidden'; ?>>
			<?php esc_html_e( 'Remove', 'fieldsbox' ); ?>
		</button>
	</p>

</div>
