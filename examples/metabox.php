<?php

declare(strict_types=1);

/**
 * Example: Metabox
 *
 * Demonstrates rendering AenimFields fields inside a post edit-screen
 * metabox, and sanitizing + validating submitted values on save.
 *
 * This file is not loaded automatically by AenimFields; copy the parts
 * you need into your own plugin or theme.
 */

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Application;
use AenimTech\AenimFields\Core\FieldFactory;
use AenimTech\AenimFields\Core\Sanitizer;
use AenimTech\AenimFields\Core\Validator;

/**
 * Field definitions for the metabox.
 *
 * Shared between the render and save callbacks so both stay in sync.
 *
 * @return array
 */
function aenimfields_example_metabox_fields(): array {
	return array(
		array(
			'type'              => 'text',
			'name'              => 'aenimfields_subtitle',
			'label'             => __( 'Subtitle', 'aenimfields' ),
			'label_description' => __( 'A short line shown under the title.', 'aenimfields' ),
			'description'       => __( 'Shown below the post title.', 'aenimfields' ),
			'help'              => __( 'Keep it under 60 characters for best display.', 'aenimfields' ),
			'placeholder'       => __( 'e.g. A beginner-friendly guide', 'aenimfields' ),
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-subtitle',
			'wrapper_class'     => 'aenimfields-example-subtitle-wrapper',
			'autocomplete'      => 'off',
			'validate'          => array( 'max:60' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'subtitle' ),
			'label_attr'        => array( 'data-aenimfields-example' => 'subtitle-label' ),
			'wrapper_attr'      => array( 'data-aenimfields-example' => 'subtitle-wrapper' ),
		),
		array(
			'type'              => 'textarea',
			'name'              => 'aenimfields_summary',
			'label'             => __( 'Summary', 'aenimfields' ),
			'label_description' => __( 'A short overview, not the full content.', 'aenimfields' ),
			'description'       => __( 'Used in list views and social previews.', 'aenimfields' ),
			'help'              => __( 'Aim for one or two sentences.', 'aenimfields' ),
			'placeholder'       => __( 'Write a brief summary…', 'aenimfields' ),
			'default'           => '',
			'rows'              => 4,
			'cols'              => 50,
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-summary',
			'wrapper_class'     => 'aenimfields-example-summary-wrapper',
			'validate'          => array( 'max:300' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'summary' ),
		),
		array(
			'type'              => 'number',
			'name'              => 'aenimfields_price',
			'label'             => __( 'Price', 'aenimfields' ),
			'label_description' => __( 'The amount charged for this item.', 'aenimfields' ),
			'description'       => __( 'Shown on the frontend price display.', 'aenimfields' ),
			'help'              => __( 'Use 0 for free items.', 'aenimfields' ),
			'placeholder'       => '0.00',
			'default'           => 0,
			'min'               => 0,
			'max'               => 100000,
			'step'              => '0.01',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-price',
			'wrapper_class'     => 'aenimfields-example-price-wrapper',
			'prefix'            => '$',
			'suffix'            => __( 'USD', 'aenimfields' ),
			'validate'          => array( 'min:0', 'max:100000' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'price' ),
		),
		array(
			'type'              => 'email',
			'name'              => 'aenimfields_contact_email',
			'label'             => __( 'Contact Email', 'aenimfields' ),
			'label_description' => __( 'Used for inquiries about this content.', 'aenimfields' ),
			'description'       => __( 'Displayed publicly on the post.', 'aenimfields' ),
			'help'              => __( 'Double-check for typos.', 'aenimfields' ),
			'placeholder'       => 'name@example.com',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-email',
			'wrapper_class'     => 'aenimfields-example-email-wrapper',
			'autocomplete'      => 'email',
			'validate'          => array( 'email' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'contact-email' ),
		),
		array(
			'type'              => 'select',
			'name'              => 'aenimfields_status',
			'label'             => __( 'Status', 'aenimfields' ),
			'label_description' => __( 'Controls where this content appears.', 'aenimfields' ),
			'description'       => __( 'Archived items are hidden from listings.', 'aenimfields' ),
			'help'              => __( 'Draft items are visible only to editors.', 'aenimfields' ),
			'placeholder'       => __( '— Select a status —', 'aenimfields' ),
			'options'           => array(
				'draft'    => __( 'Draft', 'aenimfields' ),
				'active'   => __( 'Active', 'aenimfields' ),
				'archived' => __( 'Archived', 'aenimfields' ),
			),
			'default'           => 'draft',
			'required'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-status',
			'wrapper_class'     => 'aenimfields-example-status-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'status' ),
		),
		array(
			'type'              => 'toggle',
			'name'              => 'aenimfields_featured',
			'label'             => __( 'Featured', 'aenimfields' ),
			'label_description' => __( 'Highlight this content in featured listings.', 'aenimfields' ),
			'description'       => __( 'Featured items appear on the homepage.', 'aenimfields' ),
			'help'              => __( 'Limit to a handful of items at a time.', 'aenimfields' ),
			'text'              => __( 'Show in featured listings', 'aenimfields' ),
			'default'           => 0,
			'disabled'          => false,
			'class'             => 'aenimfields-example-featured',
			'wrapper_class'     => 'aenimfields-example-featured-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'featured' ),
		),
		array(
			'type'              => 'date',
			'name'              => 'aenimfields_event_date',
			'label'             => __( 'Event Date', 'aenimfields' ),
			'label_description' => __( 'When this content goes live or takes place.', 'aenimfields' ),
			'description'       => __( 'Optional date this content is associated with.', 'aenimfields' ),
			'help'              => __( 'Shown to visitors on the frontend.', 'aenimfields' ),
			'placeholder'       => __( 'YYYY-MM-DD', 'aenimfields' ),
			'default'           => '',
			'date_format'       => 'Y-m-d',
			'min_date'          => 'today',
			'max_date'          => '2027-12-31',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-event-date',
			'wrapper_class'     => 'aenimfields-example-event-date-wrapper',
			// Only shown once the status above is set to "Active".
			'depends_on'        => array(
				'field' => 'aenimfields_status',
				'value' => 'active',
			),
			'input_attr'        => array( 'data-aenimfields-example' => 'event-date' ),
		),
		array(
			'type'              => 'wysiwyg',
			'name'              => 'aenimfields_long_description',
			'label'             => __( 'Long Description', 'aenimfields' ),
			'label_description' => __( 'The full, detailed content for this item.', 'aenimfields' ),
			'description'       => __( 'Supports rich text formatting, links, and embedded media.', 'aenimfields' ),
			'help'              => __( 'Shown below the summary on the single view.', 'aenimfields' ),
			'default'           => '',
			'rows'              => 8,
			'media_buttons'     => true,
			'teeny'             => false,
			'tinymce'           => true,
			'quicktags'         => true,
			'required'          => false,
			'class'             => 'aenimfields-example-long-description',
			'wrapper_class'     => 'aenimfields-example-long-description-wrapper',
		),
		array(
			'type'              => 'image',
			'name'              => 'aenimfields_hero_image',
			'label'             => __( 'Hero Image', 'aenimfields' ),
			'label_description' => __( 'The large image shown at the top of this content.', 'aenimfields' ),
			'description'       => __( 'Recommended size: 1600×900px.', 'aenimfields' ),
			'help'              => __( 'Falls back to the featured image if left empty.', 'aenimfields' ),
			'default'           => '',
			'preview_size'      => 'medium',
			'select_text'       => __( 'Select Hero Image', 'aenimfields' ),
			'title_text'        => __( 'Choose a Hero Image', 'aenimfields' ),
			'required'          => false,
			'class'             => 'aenimfields-example-hero-image',
			'wrapper_class'     => 'aenimfields-example-hero-image-wrapper',
		),
		array(
			'type'              => 'gallery',
			'name'              => 'aenimfields_photo_gallery',
			'label'             => __( 'Photo Gallery', 'aenimfields' ),
			'label_description' => __( 'Additional photos shown in a gallery below the content.', 'aenimfields' ),
			'description'       => __( 'Images display in the order they were added.', 'aenimfields' ),
			'help'              => __( 'Add as many images as you like.', 'aenimfields' ),
			'default'           => array(),
			'preview_size'      => 'thumbnail',
			'select_text'       => __( 'Add Photos', 'aenimfields' ),
			'title_text'        => __( 'Choose Gallery Images', 'aenimfields' ),
			'required'          => false,
			'class'             => 'aenimfields-example-gallery',
			'wrapper_class'     => 'aenimfields-example-gallery-wrapper',
		),
		array(
			'type'              => 'file',
			'name'              => 'aenimfields_attachment',
			'label'             => __( 'Attachment', 'aenimfields' ),
			'label_description' => __( 'A downloadable file related to this content.', 'aenimfields' ),
			'description'       => __( 'e.g. a spec sheet, brochure, or dataset.', 'aenimfields' ),
			'help'              => __( 'Linked as a download button on the frontend.', 'aenimfields' ),
			'default'           => '',
			'select_text'       => __( 'Select Attachment', 'aenimfields' ),
			'title_text'        => __( 'Choose a File', 'aenimfields' ),
			'required'          => false,
			'class'             => 'aenimfields-example-attachment',
			'wrapper_class'     => 'aenimfields-example-attachment-wrapper',
		),
		array(
			'type'              => 'map',
			'name'              => 'aenimfields_location',
			'label'             => __( 'Location', 'aenimfields' ),
			'label_description' => __( 'Where this post\'s content takes place.', 'aenimfields' ),
			'description'       => __( 'Search an address, or drag the pin to fine-tune it.', 'aenimfields' ),
			'help'              => __( 'Uses the free OpenStreetMap provider — no API key needed.', 'aenimfields' ),
			'default'           => array(
				'address' => '',
				'lat'     => '',
				'lng'     => '',
				'zoom'    => 14,
			),
			'provider'          => 'osm', // Or 'google' — see Core\Assets::set_google_maps_api_key().
			'height'            => 350,
			'required'          => false,
			'class'             => 'aenimfields-example-location',
			'wrapper_class'     => 'aenimfields-example-location-wrapper',
		),
		array(
			'type'              => 'group',
			'name'              => 'aenimfields_seo',
			'label'             => __( 'SEO Settings', 'aenimfields' ),
			'label_description' => __( 'Overrides the default title and description search engines see.', 'aenimfields' ),
			'description'       => __( 'Leave blank to use the post title and excerpt.', 'aenimfields' ),
			'help'              => __( 'Recommended: title under 60 characters, description under 160.', 'aenimfields' ),
			'default'           => array(),
			'required'          => false,
			'class'             => 'aenimfields-example-seo',
			'wrapper_class'     => 'aenimfields-example-seo-wrapper',
			'fields'            => array(
				array(
					'type'        => 'text',
					'name'        => 'meta_title',
					'label'       => __( 'Meta Title', 'aenimfields' ),
					'placeholder' => __( 'e.g. The Ultimate Guide to…', 'aenimfields' ),
					'validate'    => array( 'max:60' ),
				),
				array(
					'type'        => 'textarea',
					'name'        => 'meta_description',
					'label'       => __( 'Meta Description', 'aenimfields' ),
					'rows'        => 3,
					'placeholder' => __( 'A short, compelling summary for search results.', 'aenimfields' ),
					'validate'    => array( 'max:160' ),
				),
			),
		),
		array(
			'type'              => 'repeater',
			'name'              => 'aenimfields_links',
			'label'             => __( 'Additional Links', 'aenimfields' ),
			'label_description' => __( 'Related links shown at the end of the content.', 'aenimfields' ),
			'description'       => __( 'e.g. sources, further reading, or related products.', 'aenimfields' ),
			'help'              => __( 'Displayed in the order added.', 'aenimfields' ),
			'default'           => array(),
			'min_rows'          => 0,
			'max_rows'          => 10,
			'add_button_text'   => __( 'Add Link', 'aenimfields' ),
			'required'          => false,
			'class'             => 'aenimfields-example-links',
			'wrapper_class'     => 'aenimfields-example-links-wrapper',
			'fields'            => array(
				array(
					'type'     => 'text',
					'name'     => 'link_label',
					'label'    => __( 'Label', 'aenimfields' ),
					'required' => true,
				),
				array(
					'type'     => 'url',
					'name'     => 'link_url',
					'label'    => __( 'URL', 'aenimfields' ),
					'required' => true,
					'validate' => array( 'url' ),
				),
			),
		),
		array(
			'type'              => 'text',
			'name'              => 'aenimfields_internal_note',
			'label'             => __( 'Internal Note', 'aenimfields' ),
			'label_description' => __( 'Editorial use only.', 'aenimfields' ),
			'description'       => __( 'Not shown publicly; for editorial reference only.', 'aenimfields' ),
			'help'              => __( 'Visible only to users who can edit this post.', 'aenimfields' ),
			'placeholder'       => __( 'e.g. Needs a second review before publishing', 'aenimfields' ),
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-note',
			'wrapper_class'     => 'aenimfields-example-note-wrapper',
			'before'            => '<!-- aenimfields_internal_note start -->',
			'after'             => '<!-- aenimfields_internal_note end -->',
			'validate'          => array( 'max:200' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'internal-note' ),
		),
	);
}

add_action( 'add_meta_boxes', 'aenimfields_example_register_metabox' );

/**
 * Register the metabox.
 *
 * @return void
 */
function aenimfields_example_register_metabox(): void {
	add_meta_box(
		'aenimfields_example',
		__( 'AenimFields Example', 'aenimfields' ),
		'aenimfields_example_render_metabox',
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
function aenimfields_example_render_metabox( WP_Post $post ): void {

	wp_nonce_field( 'aenimfields_example_save', 'aenimfields_example_nonce' );

	$app    = new Application();
	$fields = aenimfields_example_metabox_fields();

	foreach ( $fields as &$field ) {
		$field['value'] = get_post_meta( $post->ID, $field['name'], true );
	}
	unset( $field );

	// Pull the last field out to render it on its own, demonstrating that
	// Application::render() also accepts a single field's args directly
	// (no outer array) — not only a list of fields.
	$note = array_pop( $fields ); // aenimfields_internal_note, defined last above.

	echo $app->render( $fields );
	echo $app->render( $note );
}

add_action( 'save_post', 'aenimfields_example_save_metabox' );

/**
 * Sanitize, validate, and persist the submitted metabox values.
 *
 * @param int $post_id
 *
 * @return void
 */
function aenimfields_example_save_metabox( int $post_id ): void {

	if ( ! isset( $_POST['aenimfields_example_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aenimfields_example_nonce'] ) ), 'aenimfields_example_save' )
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

	foreach ( aenimfields_example_metabox_fields() as $args ) {

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
