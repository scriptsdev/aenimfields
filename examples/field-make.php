<?php

declare(strict_types=1);

/**
 * Example: Fluent Field::make() builder
 *
 * Same metabox pattern as examples/metabox.php — register, render,
 * sanitize, validate, save — but built with the fluent
 * `AenimTech\AenimFields\Core\Field::make()` API instead of raw arg
 * arrays. Both APIs use the same underlying engine and can be mixed
 * freely, as shown below (the `aenimfields_fluent_internal_note` field
 * is a plain array passed alongside `Field` builders).
 *
 * This file is not loaded automatically by AenimFields; copy the parts
 * you need into your own plugin or theme.
 */

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Application;
use AenimTech\AenimFields\Core\Field;
use AenimTech\AenimFields\Core\FieldFactory;
use AenimTech\AenimFields\Core\Sanitizer;
use AenimTech\AenimFields\Core\Validator;

/**
 * Field definitions for the metabox.
 *
 * Shared between the render and save callbacks so both stay in sync.
 * Returns a mix of `Field` builders and, deliberately, one plain args
 * array — both are valid items in the list `Application::render()`
 * (and this file's own render/save loops) accept.
 *
 * @return array
 */
function aenimfields_example_fluent_fields(): array {
	return array(
		Field::make( 'text', 'aenimfields_fluent_subtitle', __( 'Subtitle', 'aenimfields' ) )
			->set_label_description( __( 'A short line shown under the title.', 'aenimfields' ) )
			->set_description( __( 'Shown below the post title.', 'aenimfields' ) )
			->set_placeholder( __( 'e.g. A beginner-friendly guide', 'aenimfields' ) )
			->set_validate( array( 'max:60' ) ),

		Field::make( 'select', 'aenimfields_fluent_status', __( 'Status', 'aenimfields' ) )
			->set_label_description( __( 'Controls where this content appears.', 'aenimfields' ) )
			->set_options(
				array(
					'draft'    => __( 'Draft', 'aenimfields' ),
					'active'   => __( 'Active', 'aenimfields' ),
					'archived' => __( 'Archived', 'aenimfields' ),
				)
			)
			->set_default( 'draft' ),

		// Only shown once the status field above is set to "Active" —
		// set_conditional_logic() is the fluent shortcut for `depends_on`.
		Field::make( 'date', 'aenimfields_fluent_event_date', __( 'Event Date', 'aenimfields' ) )
			->set_description( __( 'Optional date this content is associated with.', 'aenimfields' ) )
			->set_min_date( 'today' )
			->set_conditional_logic( 'aenimfields_fluent_status', 'active' ),

		Field::make( 'toggle', 'aenimfields_fluent_featured', __( 'Featured', 'aenimfields' ) )
			->set_label_description( __( 'Highlight this content in featured listings.', 'aenimfields' ) )
			->set_text( __( 'Show in featured listings', 'aenimfields' ) ),

		// A repeater's sub-fields (`set_fields()`) can be `Field` builders,
		// plain arrays, or a mix of both — same rule as the top-level list.
		Field::make( 'repeater', 'aenimfields_fluent_links', __( 'Additional Links', 'aenimfields' ) )
			->set_label_description( __( 'Related links shown at the end of the content.', 'aenimfields' ) )
			->set_min_rows( 0 )
			->set_max_rows( 10 )
			->set_add_button_text( __( 'Add Link', 'aenimfields' ) )
			->set_fields(
				array(
					Field::make( 'text', 'link_label', __( 'Label', 'aenimfields' ) )->set_required(),
					array(
						'type'     => 'url',
						'name'     => 'link_url',
						'label'    => __( 'URL', 'aenimfields' ),
						'required' => true,
						'validate' => array( 'url' ),
					),
				)
			),

		array(
			'type'        => 'text',
			'name'        => 'aenimfields_fluent_internal_note',
			'label'       => __( 'Internal Note', 'aenimfields' ),
			'description' => __( 'Not shown publicly; for editorial reference only.', 'aenimfields' ),
			'validate'    => array( 'max:200' ),
		),
	);
}

add_action( 'add_meta_boxes', 'aenimfields_example_fluent_register_metabox' );

/**
 * Register the metabox.
 *
 * @return void
 */
function aenimfields_example_fluent_register_metabox(): void {
	add_meta_box(
		'aenimfields_example_fluent',
		__( 'AenimFields Fluent Example', 'aenimfields' ),
		'aenimfields_example_fluent_render_metabox',
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
function aenimfields_example_fluent_render_metabox( WP_Post $post ): void {

	wp_nonce_field( 'aenimfields_example_fluent_save', 'aenimfields_example_fluent_nonce' );

	$app = new Application();

	foreach ( aenimfields_example_fluent_fields() as $field ) {

		if ( $field instanceof Field ) {
			$name = $field->get_field()->get_name();
			echo $field->set_value( get_post_meta( $post->ID, $name, true ) )->render();
			continue;
		}

		// Plain array item — same shape Application::render() accepts.
		$field['value'] = get_post_meta( $post->ID, $field['name'], true );
		echo $app->render( $field );
	}
}

add_action( 'save_post', 'aenimfields_example_fluent_save_metabox' );

/**
 * Sanitize, validate, and persist the submitted metabox values.
 *
 * @param int $post_id
 *
 * @return void
 */
function aenimfields_example_fluent_save_metabox( int $post_id ): void {

	if ( ! isset( $_POST['aenimfields_example_fluent_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aenimfields_example_fluent_nonce'] ) ), 'aenimfields_example_fluent_save' )
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

	foreach ( aenimfields_example_fluent_fields() as $field ) {

		// `Field::make()`'s sanitize()/validate() wrap Core\Sanitizer and
		// Core\Validator exactly like the array API's static calls do —
		// pick whichever matches the item's own type.
		if ( $field instanceof Field ) {
			$name   = $field->get_field()->get_name();
			$raw    = wp_unslash( $_POST[ $name ] ?? '' );
			$clean  = $field->sanitize( $raw );
			$result = $field->validate( $clean );
		} else {
			$instance = $factory->make( $field );
			$name     = $instance->get_name();
			$raw      = wp_unslash( $_POST[ $name ] ?? '' );
			$clean    = Sanitizer::sanitize( $instance, $raw );
			$result   = Validator::validate( $instance, $clean );
		}

		if ( ! $result->passed() ) {
			continue; // Skip invalid values; extend this to surface errors to the user.
		}

		update_post_meta( $post_id, $name, $clean );
	}
}
