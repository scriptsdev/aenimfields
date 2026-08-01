<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

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
	class="aenimfields-media-field aenimfields-media-field-gallery"
	data-aenimfields-media="gallery"
	data-aenimfields-media-title="<?php echo esc_attr( $field->get_arg( 'title_text', __( 'Add Images', 'aenimfields' ) ) ); ?>"
>

	<input <?php echo Helpers::attributes( $attributes ); ?>>

	<ul class="aenimfields-media-gallery-preview">
		<?php foreach ( $ids as $id ) : ?>
			<li data-aenimfields-media-id="<?php echo esc_attr( $id ); ?>">
				<?php echo wp_get_attachment_image( $id, $field->get_arg( 'preview_size', 'thumbnail' ) ); ?>
				<button type="button" class="aenimfields-media-gallery-remove-item" aria-label="<?php esc_attr_e( 'Remove image', 'aenimfields' ); ?>">&times;</button>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="aenimfields-media-actions">
		<button type="button" class="button aenimfields-media-select">
			<?php echo esc_html( $field->get_arg( 'select_text', __( 'Add Images', 'aenimfields' ) ) ); ?>
		</button>
	</p>

</div>
