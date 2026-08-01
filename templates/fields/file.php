<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Helpers;

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
	class="aenimfields-media-field aenimfields-media-field-file"
	data-aenimfields-media="file"
	data-aenimfields-media-title="<?php echo esc_attr( $field->get_arg( 'title_text', __( 'Select File', 'aenimfields' ) ) ); ?>"
>

	<input <?php echo Helpers::attributes( $attributes ); ?>>

	<p class="aenimfields-media-preview" <?php echo $file_name ? '' : 'hidden'; ?>>
		<?php if ( $file_name ) : ?>
			<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer" class="aenimfields-media-filename">
				<?php echo esc_html( $file_name ); ?>
			</a>
		<?php endif; ?>
	</p>

	<p class="aenimfields-media-actions">
		<button type="button" class="button aenimfields-media-select">
			<?php echo esc_html( $field->get_arg( 'select_text', __( 'Select File', 'aenimfields' ) ) ); ?>
		</button>
		<button type="button" class="button aenimfields-media-remove" <?php echo $file_name ? '' : 'hidden'; ?>>
			<?php esc_html_e( 'Remove', 'aenimfields' ); ?>
		</button>
	</p>

</div>
