<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Assets;
use FieldsBox\Core\Helpers;

Assets::enqueue_media();

$ids = array_filter( array_map( 'absint', (array) $field->get_value() ) );

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type'  => 'hidden',
		'value' => implode( ',', $ids ),
	)
);

?>

<div
	class="fieldsbox-media-field fieldsbox-media-field-gallery"
	data-fieldsbox-media="gallery"
	data-fieldsbox-media-title="<?php echo esc_attr( $field->get_arg( 'title_text', __( 'Add Images', 'fieldsbox' ) ) ); ?>"
>

	<input <?php echo Helpers::attributes( $attributes ); ?>>

	<ul class="fieldsbox-media-gallery-preview">
		<?php foreach ( $ids as $id ) : ?>
			<li data-fieldsbox-media-id="<?php echo esc_attr( $id ); ?>">
				<?php echo wp_get_attachment_image( $id, $field->get_arg( 'preview_size', 'thumbnail' ) ); ?>
				<button type="button" class="fieldsbox-media-gallery-remove-item" aria-label="<?php esc_attr_e( 'Remove image', 'fieldsbox' ); ?>">&times;</button>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="fieldsbox-media-actions">
		<button type="button" class="button fieldsbox-media-select">
			<?php echo esc_html( $field->get_arg( 'select_text', __( 'Add Images', 'fieldsbox' ) ) ); ?>
		</button>
	</p>

</div>
