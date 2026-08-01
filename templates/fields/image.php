<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

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
	class="aenimfields-media-field aenimfields-media-field-image"
	data-aenimfields-media="image"
	data-aenimfields-media-title="<?php echo esc_attr( $field->get_arg( 'title_text', __( 'Select Image', 'aenimfields' ) ) ); ?>"
>

	<input <?php echo Helpers::attributes( $attributes ); ?>>

	<div class="aenimfields-media-preview" <?php echo $preview_url ? '' : 'hidden'; ?>>
		<?php if ( $preview_url ) : ?>
			<img src="<?php echo esc_url( $preview_url ); ?>" alt="">
		<?php endif; ?>
	</div>

	<p class="aenimfields-media-actions">
		<button type="button" class="button aenimfields-media-select">
			<?php echo esc_html( $field->get_arg( 'select_text', __( 'Select Image', 'aenimfields' ) ) ); ?>
		</button>
		<button type="button" class="button aenimfields-media-remove" <?php echo $preview_url ? '' : 'hidden'; ?>>
			<?php esc_html_e( 'Remove', 'aenimfields' ); ?>
		</button>
	</p>

</div>
