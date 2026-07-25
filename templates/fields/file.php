<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Assets;
use FieldsBox\Core\Helpers;

Assets::enqueue_media();

$attachment_id = absint( $field->get_value() );
$file_url      = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
$file_name     = $attachment_id ? basename( (string) get_attached_file( $attachment_id ) ) : '';

$attributes = Helpers::input_attributes(
	$field,
	array(
		'type' => 'hidden',
	)
);

?>

<div
	class="fieldsbox-media-field fieldsbox-media-field-file"
	data-fieldsbox-media="file"
	data-fieldsbox-media-title="<?php echo esc_attr( $field->get_arg( 'title_text', __( 'Select File', 'fieldsbox' ) ) ); ?>"
>

	<input <?php echo Helpers::attributes( $attributes ); ?>>

	<p class="fieldsbox-media-preview" <?php echo $file_name ? '' : 'hidden'; ?>>
		<?php if ( $file_name ) : ?>
			<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer" class="fieldsbox-media-filename">
				<?php echo esc_html( $file_name ); ?>
			</a>
		<?php endif; ?>
	</p>

	<p class="fieldsbox-media-actions">
		<button type="button" class="button fieldsbox-media-select">
			<?php echo esc_html( $field->get_arg( 'select_text', __( 'Select File', 'fieldsbox' ) ) ); ?>
		</button>
		<button type="button" class="button fieldsbox-media-remove" <?php echo $file_name ? '' : 'hidden'; ?>>
			<?php esc_html_e( 'Remove', 'fieldsbox' ); ?>
		</button>
	</p>

</div>
