<?php

declare(strict_types=1);

/**
 * Example: Metabox
 *
 * Demonstrates rendering FieldsBox fields inside a post edit-screen
 * metabox, and sanitizing + validating submitted values on save.
 *
 * This file is not loaded automatically by FieldsBox; copy the parts
 * you need into your own plugin or theme.
 */

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Application;
use FieldsBox\Core\FieldFactory;
use FieldsBox\Core\Sanitizer;
use FieldsBox\Core\Validator;

/**
 * Field definitions for the metabox.
 *
 * Shared between the render and save callbacks so both stay in sync.
 *
 * @return array
 */
function fieldsbox_example_metabox_fields(): array {
	return array(
		array(
			'type'        => 'text',
			'name'        => 'fieldsbox_subtitle',
			'label'       => __( 'Subtitle', 'fieldsbox' ),
			'description' => __( 'Shown below the post title.', 'fieldsbox' ),
		),
		array(
			'type'  => 'textarea',
			'name'  => 'fieldsbox_summary',
			'label' => __( 'Summary', 'fieldsbox' ),
			'rows'  => 4,
		),
		array(
			'type'     => 'number',
			'name'     => 'fieldsbox_price',
			'label'    => __( 'Price', 'fieldsbox' ),
			'min'      => 0,
			'step'     => '0.01',
			'required' => true,
			'validate' => array( 'min:0' ),
		),
		array(
			'type'  => 'email',
			'name'  => 'fieldsbox_contact_email',
			'label' => __( 'Contact Email', 'fieldsbox' ),
		),
		array(
			'type'    => 'select',
			'name'    => 'fieldsbox_status',
			'label'   => __( 'Status', 'fieldsbox' ),
			'options' => array(
				'draft'    => __( 'Draft', 'fieldsbox' ),
				'active'   => __( 'Active', 'fieldsbox' ),
				'archived' => __( 'Archived', 'fieldsbox' ),
			),
			'default' => 'draft',
		),
		array(
			'type'  => 'toggle',
			'name'  => 'fieldsbox_featured',
			'label' => __( 'Featured', 'fieldsbox' ),
		),
		array(
			'type'        => 'text',
			'name'        => 'fieldsbox_internal_note',
			'label'       => __( 'Internal Note', 'fieldsbox' ),
			'description' => __( 'Not shown publicly; for editorial reference only.', 'fieldsbox' ),
		),
	);
}

add_action( 'add_meta_boxes', 'fieldsbox_example_register_metabox' );

/**
 * Register the metabox.
 *
 * @return void
 */
function fieldsbox_example_register_metabox(): void {
	add_meta_box(
		'fieldsbox_example',
		__( 'FieldsBox Example', 'fieldsbox' ),
		'fieldsbox_example_render_metabox',
		'post',
		'normal',
		'default'
	);
}

/**
 * Render the metabox.
 *
 * @param WP_Post $post
 *
 * @return void
 */
function fieldsbox_example_render_metabox( WP_Post $post ): void {

	wp_nonce_field( 'fieldsbox_example_save', 'fieldsbox_example_nonce' );

	$app    = new Application();
	$fields = fieldsbox_example_metabox_fields();

	foreach ( $fields as &$field ) {
		$field['value'] = get_post_meta( $post->ID, $field['name'], true );
	}
	unset( $field );

	// Pull the last field out to render it on its own, demonstrating that
	// Application::render() also accepts a single field's args directly
	// (no outer array) — not only a list of fields.
	$note = array_pop( $fields ); // fieldsbox_internal_note, defined last above.

	echo $app->render( $fields );
	echo $app->render( $note );
}

add_action( 'save_post', 'fieldsbox_example_save_metabox' );

/**
 * Sanitize, validate, and persist the submitted metabox values.
 *
 * @param int $post_id
 *
 * @return void
 */
function fieldsbox_example_save_metabox( int $post_id ): void {

	if ( ! isset( $_POST['fieldsbox_example_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fieldsbox_example_nonce'] ) ), 'fieldsbox_example_save' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$factory = new FieldFactory();

	foreach ( fieldsbox_example_metabox_fields() as $args ) {

		$field  = $factory->make( $args );
		$raw    = wp_unslash( $_POST[ $field->get_name() ] ?? '' );
		$clean  = Sanitizer::sanitize( $field, $raw );
		$result = Validator::validate( $field, $clean );

		if ( ! $result->passed() ) {
			continue; // Skip invalid values; extend this to surface errors to the user.
		}

		update_post_meta( $post_id, $field->get_name(), $clean );
	}
}
