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

use ScriptsDev\FieldsBox\Core\Application;
use ScriptsDev\FieldsBox\Core\FieldFactory;
use ScriptsDev\FieldsBox\Core\Sanitizer;
use ScriptsDev\FieldsBox\Core\Validator;

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
			'type'              => 'text',
			'name'              => 'fieldsbox_subtitle',
			'label'             => __( 'Subtitle', 'fieldsbox' ),
			'label_description' => __( 'A short line shown under the title.', 'fieldsbox' ),
			'description'       => __( 'Shown below the post title.', 'fieldsbox' ),
			'help'              => __( 'Keep it under 60 characters for best display.', 'fieldsbox' ),
			'placeholder'       => __( 'e.g. A beginner-friendly guide', 'fieldsbox' ),
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-subtitle',
			'wrapper_class'     => 'fieldsbox-example-subtitle-wrapper',
			'autocomplete'      => 'off',
			'validate'          => array( 'max:60' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'subtitle' ),
			'label_attr'        => array( 'data-fieldsbox-example' => 'subtitle-label' ),
			'wrapper_attr'      => array( 'data-fieldsbox-example' => 'subtitle-wrapper' ),
		),
		array(
			'type'              => 'textarea',
			'name'              => 'fieldsbox_summary',
			'label'             => __( 'Summary', 'fieldsbox' ),
			'label_description' => __( 'A short overview, not the full content.', 'fieldsbox' ),
			'description'       => __( 'Used in list views and social previews.', 'fieldsbox' ),
			'help'              => __( 'Aim for one or two sentences.', 'fieldsbox' ),
			'placeholder'       => __( 'Write a brief summary…', 'fieldsbox' ),
			'default'           => '',
			'rows'              => 4,
			'cols'              => 50,
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-summary',
			'wrapper_class'     => 'fieldsbox-example-summary-wrapper',
			'validate'          => array( 'max:300' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'summary' ),
		),
		array(
			'type'              => 'number',
			'name'              => 'fieldsbox_price',
			'label'             => __( 'Price', 'fieldsbox' ),
			'label_description' => __( 'The amount charged for this item.', 'fieldsbox' ),
			'description'       => __( 'Shown on the frontend price display.', 'fieldsbox' ),
			'help'              => __( 'Use 0 for free items.', 'fieldsbox' ),
			'placeholder'       => '0.00',
			'default'           => 0,
			'min'               => 0,
			'max'               => 100000,
			'step'              => '0.01',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-price',
			'wrapper_class'     => 'fieldsbox-example-price-wrapper',
			'prefix'            => '$',
			'suffix'            => __( 'USD', 'fieldsbox' ),
			'validate'          => array( 'min:0', 'max:100000' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'price' ),
		),
		array(
			'type'              => 'email',
			'name'              => 'fieldsbox_contact_email',
			'label'             => __( 'Contact Email', 'fieldsbox' ),
			'label_description' => __( 'Used for inquiries about this content.', 'fieldsbox' ),
			'description'       => __( 'Displayed publicly on the post.', 'fieldsbox' ),
			'help'              => __( 'Double-check for typos.', 'fieldsbox' ),
			'placeholder'       => 'name@example.com',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-email',
			'wrapper_class'     => 'fieldsbox-example-email-wrapper',
			'autocomplete'      => 'email',
			'validate'          => array( 'email' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'contact-email' ),
		),
		array(
			'type'              => 'select',
			'name'              => 'fieldsbox_status',
			'label'             => __( 'Status', 'fieldsbox' ),
			'label_description' => __( 'Controls where this content appears.', 'fieldsbox' ),
			'description'       => __( 'Archived items are hidden from listings.', 'fieldsbox' ),
			'help'              => __( 'Draft items are visible only to editors.', 'fieldsbox' ),
			'placeholder'       => __( '— Select a status —', 'fieldsbox' ),
			'options'           => array(
				'draft'    => __( 'Draft', 'fieldsbox' ),
				'active'   => __( 'Active', 'fieldsbox' ),
				'archived' => __( 'Archived', 'fieldsbox' ),
			),
			'default'           => 'draft',
			'required'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-status',
			'wrapper_class'     => 'fieldsbox-example-status-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'status' ),
		),
		array(
			'type'              => 'toggle',
			'name'              => 'fieldsbox_featured',
			'label'             => __( 'Featured', 'fieldsbox' ),
			'label_description' => __( 'Highlight this content in featured listings.', 'fieldsbox' ),
			'description'       => __( 'Featured items appear on the homepage.', 'fieldsbox' ),
			'help'              => __( 'Limit to a handful of items at a time.', 'fieldsbox' ),
			'text'              => __( 'Show in featured listings', 'fieldsbox' ),
			'default'           => 0,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-featured',
			'wrapper_class'     => 'fieldsbox-example-featured-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'featured' ),
		),
		array(
			'type'              => 'date',
			'name'              => 'fieldsbox_event_date',
			'label'             => __( 'Event Date', 'fieldsbox' ),
			'label_description' => __( 'When this content goes live or takes place.', 'fieldsbox' ),
			'description'       => __( 'Optional date this content is associated with.', 'fieldsbox' ),
			'help'              => __( 'Shown to visitors on the frontend.', 'fieldsbox' ),
			'placeholder'       => __( 'YYYY-MM-DD', 'fieldsbox' ),
			'default'           => '',
			'date_format'       => 'Y-m-d',
			'min_date'          => 'today',
			'max_date'          => '2027-12-31',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-event-date',
			'wrapper_class'     => 'fieldsbox-example-event-date-wrapper',
			// Only shown once the status above is set to "Active".
			'depends_on'        => array(
				'field' => 'fieldsbox_status',
				'value' => 'active',
			),
			'input_attr'        => array( 'data-fieldsbox-example' => 'event-date' ),
		),
		array(
			'type'              => 'wysiwyg',
			'name'              => 'fieldsbox_long_description',
			'label'             => __( 'Long Description', 'fieldsbox' ),
			'label_description' => __( 'The full, detailed content for this item.', 'fieldsbox' ),
			'description'       => __( 'Supports rich text formatting, links, and embedded media.', 'fieldsbox' ),
			'help'              => __( 'Shown below the summary on the single view.', 'fieldsbox' ),
			'default'           => '',
			'rows'              => 8,
			'media_buttons'     => true,
			'teeny'             => false,
			'tinymce'           => true,
			'quicktags'         => true,
			'required'          => false,
			'class'             => 'fieldsbox-example-long-description',
			'wrapper_class'     => 'fieldsbox-example-long-description-wrapper',
		),
		array(
			'type'              => 'image',
			'name'              => 'fieldsbox_hero_image',
			'label'             => __( 'Hero Image', 'fieldsbox' ),
			'label_description' => __( 'The large image shown at the top of this content.', 'fieldsbox' ),
			'description'       => __( 'Recommended size: 1600×900px.', 'fieldsbox' ),
			'help'              => __( 'Falls back to the featured image if left empty.', 'fieldsbox' ),
			'default'           => '',
			'preview_size'      => 'medium',
			'select_text'       => __( 'Select Hero Image', 'fieldsbox' ),
			'title_text'        => __( 'Choose a Hero Image', 'fieldsbox' ),
			'required'          => false,
			'class'             => 'fieldsbox-example-hero-image',
			'wrapper_class'     => 'fieldsbox-example-hero-image-wrapper',
		),
		array(
			'type'              => 'gallery',
			'name'              => 'fieldsbox_photo_gallery',
			'label'             => __( 'Photo Gallery', 'fieldsbox' ),
			'label_description' => __( 'Additional photos shown in a gallery below the content.', 'fieldsbox' ),
			'description'       => __( 'Images display in the order they were added.', 'fieldsbox' ),
			'help'              => __( 'Add as many images as you like.', 'fieldsbox' ),
			'default'           => array(),
			'preview_size'      => 'thumbnail',
			'select_text'       => __( 'Add Photos', 'fieldsbox' ),
			'title_text'        => __( 'Choose Gallery Images', 'fieldsbox' ),
			'required'          => false,
			'class'             => 'fieldsbox-example-gallery',
			'wrapper_class'     => 'fieldsbox-example-gallery-wrapper',
		),
		array(
			'type'              => 'file',
			'name'              => 'fieldsbox_attachment',
			'label'             => __( 'Attachment', 'fieldsbox' ),
			'label_description' => __( 'A downloadable file related to this content.', 'fieldsbox' ),
			'description'       => __( 'e.g. a spec sheet, brochure, or dataset.', 'fieldsbox' ),
			'help'              => __( 'Linked as a download button on the frontend.', 'fieldsbox' ),
			'default'           => '',
			'select_text'       => __( 'Select Attachment', 'fieldsbox' ),
			'title_text'        => __( 'Choose a File', 'fieldsbox' ),
			'required'          => false,
			'class'             => 'fieldsbox-example-attachment',
			'wrapper_class'     => 'fieldsbox-example-attachment-wrapper',
		),
		array(
			'type'              => 'map',
			'name'              => 'fieldsbox_location',
			'label'             => __( 'Location', 'fieldsbox' ),
			'label_description' => __( 'Where this post\'s content takes place.', 'fieldsbox' ),
			'description'       => __( 'Search an address, or drag the pin to fine-tune it.', 'fieldsbox' ),
			'help'              => __( 'Uses the free OpenStreetMap provider — no API key needed.', 'fieldsbox' ),
			'default'           => array(
				'address' => '',
				'lat'     => '',
				'lng'     => '',
				'zoom'    => 14,
			),
			'provider'          => 'osm', // Or 'google' — see Core\Assets::set_google_maps_api_key().
			'height'            => 350,
			'required'          => false,
			'class'             => 'fieldsbox-example-location',
			'wrapper_class'     => 'fieldsbox-example-location-wrapper',
		),
		array(
			'type'              => 'group',
			'name'              => 'fieldsbox_seo',
			'label'             => __( 'SEO Settings', 'fieldsbox' ),
			'label_description' => __( 'Overrides the default title and description search engines see.', 'fieldsbox' ),
			'description'       => __( 'Leave blank to use the post title and excerpt.', 'fieldsbox' ),
			'help'              => __( 'Recommended: title under 60 characters, description under 160.', 'fieldsbox' ),
			'default'           => array(),
			'required'          => false,
			'class'             => 'fieldsbox-example-seo',
			'wrapper_class'     => 'fieldsbox-example-seo-wrapper',
			'fields'            => array(
				array(
					'type'        => 'text',
					'name'        => 'meta_title',
					'label'       => __( 'Meta Title', 'fieldsbox' ),
					'placeholder' => __( 'e.g. The Ultimate Guide to…', 'fieldsbox' ),
					'validate'    => array( 'max:60' ),
				),
				array(
					'type'        => 'textarea',
					'name'        => 'meta_description',
					'label'       => __( 'Meta Description', 'fieldsbox' ),
					'rows'        => 3,
					'placeholder' => __( 'A short, compelling summary for search results.', 'fieldsbox' ),
					'validate'    => array( 'max:160' ),
				),
			),
		),
		array(
			'type'              => 'repeater',
			'name'              => 'fieldsbox_links',
			'label'             => __( 'Additional Links', 'fieldsbox' ),
			'label_description' => __( 'Related links shown at the end of the content.', 'fieldsbox' ),
			'description'       => __( 'e.g. sources, further reading, or related products.', 'fieldsbox' ),
			'help'              => __( 'Displayed in the order added.', 'fieldsbox' ),
			'default'           => array(),
			'min_rows'          => 0,
			'max_rows'          => 10,
			'add_button_text'   => __( 'Add Link', 'fieldsbox' ),
			'required'          => false,
			'class'             => 'fieldsbox-example-links',
			'wrapper_class'     => 'fieldsbox-example-links-wrapper',
			'fields'            => array(
				array(
					'type'     => 'text',
					'name'     => 'link_label',
					'label'    => __( 'Label', 'fieldsbox' ),
					'required' => true,
				),
				array(
					'type'     => 'url',
					'name'     => 'link_url',
					'label'    => __( 'URL', 'fieldsbox' ),
					'required' => true,
					'validate' => array( 'url' ),
				),
			),
		),
		array(
			'type'              => 'text',
			'name'              => 'fieldsbox_internal_note',
			'label'             => __( 'Internal Note', 'fieldsbox' ),
			'label_description' => __( 'Editorial use only.', 'fieldsbox' ),
			'description'       => __( 'Not shown publicly; for editorial reference only.', 'fieldsbox' ),
			'help'              => __( 'Visible only to users who can edit this post.', 'fieldsbox' ),
			'placeholder'       => __( 'e.g. Needs a second review before publishing', 'fieldsbox' ),
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-note',
			'wrapper_class'     => 'fieldsbox-example-note-wrapper',
			'before'            => '<!-- fieldsbox_internal_note start -->',
			'after'             => '<!-- fieldsbox_internal_note end -->',
			'validate'          => array( 'max:200' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'internal-note' ),
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
