<?php
/**
 * Example only - not loaded automatically. Copy the relevant parts into your
 * own plugin's bootstrap once Fieldsbox is required.
 *
 * User-facing strings (labels, help text, option labels, tab/container
 * titles) are wrapped in __() with your plugin's own text domain - swap
 * 'my-plugin' below for whatever your plugin actually uses. Field names
 * (the machine keys, e.g. 'subtitle', 'availability') and IDs are not
 * translatable and are left as plain strings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the shared package once; safe to repeat this guard in every plugin.
if ( ! class_exists( 'Fieldsbox\Fieldsbox' ) ) {
	require_once WP_PLUGIN_DIR . '/fieldsbox/fieldsbox.php';
}

// Only needed if you use the 'google_map' field type below - the 'map'
// field (Leaflet/OpenStreetMap) needs no key at all. Omit this call
// entirely if you don't need Google's geocoding/autocomplete.
Fieldsbox\Fieldsbox::set_google_maps_api_key( 'your-google-maps-api-key' );

use Fieldsbox\Container\PostMetaContainer;
use Fieldsbox\Container\TermMetaContainer;
use Fieldsbox\Container\ThemeOptionsContainer;
use Fieldsbox\Field\Field;

// Post meta box with tabs and a conditionally shown field.
PostMetaContainer::make( __( 'Product Details', 'my-plugin' ) )
	->show_on_post_type( 'product' )
	->add_tab(
		__( 'General', 'my-plugin' ),
		array(
			Field::make( 'text', 'subtitle', __( 'Subtitle', 'my-plugin' ) )
				->set_help_text( __( 'Shown under the product title.', 'my-plugin' ) )
				// add_class() adds to the field's wrapper <div> (for styling hooks);
				// set_id() overrides the auto-generated id (e.g. for a JS selector
				// or anchor link) - without it, an id like "fieldsbox-subtitle-3" is
				// generated automatically.
				->add_class( 'fieldsbox-subtitle-field' )
				->set_id( 'product-subtitle' ),
			Field::make( 'select', 'availability', __( 'Availability', 'my-plugin' ) )
			->set_options(
				array(
					'in_stock' => __( 'In stock', 'my-plugin' ),
					'preorder' => __( 'Preorder', 'my-plugin' ),
				)
			)
			->set_default_value( 'in_stock' ),
			// Shown when availability is "preorder" OR shipping_method is
			// "express" - evaluated live in the browser by assets/js/fieldsbox.js.
			// Rule shape mirrors Carbon Fields: relation/compare are optional and
			// default to 'AND'/'=' - see Field::set_conditional_logic() for the
			// full list of compare operators (=, <, >, <=, >=, IN, NOT IN,
			// INCLUDES, EXCLUDES) and the "parent." prefix for reaching outside
			// a group/repeater row.
			Field::make( 'text', 'release_date', __( 'Release date', 'my-plugin' ) )
				->set_conditional_logic(
					array(
						'relation' => 'OR',
						array(
							'field'   => 'availability',
							'value'   => 'preorder',
							'compare' => '=',
						),
						array(
							'field'   => 'shipping_method',
							'value'   => array( 'express' ),
							'compare' => 'IN',
						),
					)
				),
		)
	)
	->add_tab(
		__( 'Options', 'my-plugin' ),
		array(
			Field::make( 'checkbox', 'is_featured', __( 'Feature this product', 'my-plugin' ) ),
			Field::make( 'multiselect', 'tags', __( 'Tags', 'my-plugin' ) )
			->set_options(
				array(
					'sale'    => __( 'Sale', 'my-plugin' ),
					'new'     => __( 'New', 'my-plugin' ),
					'limited' => __( 'Limited edition', 'my-plugin' ),
				)
			),
			Field::make( 'textarea', 'internal_notes', __( 'Internal notes', 'my-plugin' ) ),
		)
	)
	->add_tab(
		__( 'Field Type Reference', 'my-plugin' ),
		array(
			// A separator renders no input and stores no value - just a heading
			// used to break this long reference list into sections.
			Field::make( 'separator', 'basic_inputs_separator', __( 'Basic Inputs', 'my-plugin' ) ),
			// 'dropdown' is just an alias for 'select' - same native <select> markup.
			Field::make( 'dropdown', 'shipping_method', __( 'Shipping Method', 'my-plugin' ) )
			->set_options(
				array(
					'standard' => __( 'Standard', 'my-plugin' ),
					'express'  => __( 'Express', 'my-plugin' ),
				)
			),
			Field::make( 'email', 'contact_email', __( 'Contact Email', 'my-plugin' ) ),
			Field::make( 'url', 'product_url', __( 'Product URL', 'my-plugin' ) ),
			// Hidden fields carry a value without any visible control or label.
			Field::make( 'hidden', 'internal_ref' )->set_default_value( 'ref-000' ),
			Field::make( 'number', 'stock_quantity', __( 'Stock Quantity', 'my-plugin' ) )
				->set_attribute( 'min', '0' )
				->set_attribute( 'step', '1' ),
			Field::make( 'toggle', 'is_digital', __( 'Digital Product', 'my-plugin' ) ),
			Field::make( 'color', 'accent_color', __( 'Accent Color', 'my-plugin' ) ),
			Field::make( 'separator', 'content_separator', __( 'Content', 'my-plugin' ) ),
			// Raw markup/scripts - only users with the unfiltered_html capability
			// get it verbatim, see CodeField::sanitize().
			Field::make( 'code', 'custom_html', __( 'Custom HTML', 'my-plugin' ) ),
			Field::make( 'wysiwyg', 'full_description', __( 'Full Description', 'my-plugin' ) ),
			Field::make( 'separator', 'media_separator', __( 'Media', 'my-plugin' ) ),
			Field::make( 'image', 'featured_image', __( 'Featured Image', 'my-plugin' ) ),
			// set_mime_types() restricts both the media library picker and
			// what sanitize() will accept - 'application/pdf' here is an
			// exact mime type; 'video'/'image'/'audio' below are WordPress'
			// generic type buckets (matches any subtype, e.g. video/mp4).
			Field::make( 'file', 'spec_sheet', __( 'Spec Sheet (PDF)', 'my-plugin' ) )
				->set_mime_types( 'application/pdf' ),
			Field::make( 'file', 'promo_video', __( 'Promo Video', 'my-plugin' ) )
				->set_mime_types( 'video' ),
			Field::make( 'gallery', 'product_gallery', __( 'Product Gallery', 'my-plugin' ) ),
			Field::make( 'separator', 'location_separator', __( 'Location & Structure', 'my-plugin' ) ),
			// Leaflet + OpenStreetMap, no API key - drag the pin or click the
			// map to set lat/lng.
			Field::make( 'map', 'store_location', __( 'Store Location', 'my-plugin' ) )
				->set_default_position( 51.5074, -0.1278, 12 ),
			// Same {lat, lng, address} storage shape as 'map', but backed by
			// Google Maps + Places Autocomplete - requires the API key set
			// above via Fieldsbox::set_google_maps_api_key().
			Field::make( 'google_map', 'warehouse_location', __( 'Warehouse Location', 'my-plugin' ) )
				->set_default_position( 51.5074, -0.1278, 12 ),
			// A fixed (non-repeating) set of sub-fields, stored as one associative array.
			Field::make( 'group', 'manufacturer', __( 'Manufacturer', 'my-plugin' ) )
				->set_fields(
					array(
						Field::make( 'text', 'name', __( 'Name', 'my-plugin' ) ),
						Field::make( 'text', 'country', __( 'Country', 'my-plugin' ) ),
					)
				),
			// A repeatable list of rows sharing the same sub-field set.
			Field::make( 'repeater', 'variants', __( 'Variants', 'my-plugin' ) )
				->set_fields(
					array(
						Field::make( 'text', 'sku', __( 'SKU', 'my-plugin' ) ),
						Field::make( 'number', 'price', __( 'Price', 'my-plugin' ) ),
						// "parent." reaches past this repeater row to the container's
						// own fields - here, the "Feature this product" checkbox from
						// the Options tab (tabs aren't a scoping boundary, only
						// group/repeater nesting is). Two levels deep would be
						// "parent.parent.field_name".
						Field::make( 'number', 'discount_price', __( 'Discount Price', 'my-plugin' ) )
							->set_conditional_logic(
								array(
									array(
										'field' => 'parent.is_featured',
										'value' => true,
									),
								)
							),
					)
				)
				->set_button_label( __( 'Add Variant', 'my-plugin' ) )
				->set_max_rows( 10 ),
			Field::make( 'separator', 'date_time_separator', __( 'Date & Time', 'my-plugin' ) ),
			// Flatpickr-powered date/date-time/time pickers.
			Field::make( 'date', 'launch_date', __( 'Launch Date', 'my-plugin' ) ),
			Field::make( 'datetime', 'sale_starts', __( 'Sale Starts', 'my-plugin' ) ),
			Field::make( 'time', 'daily_deal_time', __( 'Daily Deal Time', 'my-plugin' ) ),
		)
	);

// Theme options page nested under Settings (pass null instead of
// 'options-general.php' for a top-level menu item), storing its values as
// one option row. add_tab() works the same way here as on
// PostMetaContainer above - tabs aren't tied to a specific container type.
ThemeOptionsContainer::make( __( 'My Plugin Settings', 'my-plugin' ) )
	->set_menu( __( 'My Plugin', 'my-plugin' ), 'options-general.php', 'manage_options' )
	->add_tab(
		__( 'General', 'my-plugin' ),
		array(
			Field::make( 'text', 'api_key', __( 'API Key', 'my-plugin' ) )->set_required(),
			Field::make( 'radio', 'mode', __( 'Mode', 'my-plugin' ) )
			->set_options(
				array(
					'live' => __( 'Live', 'my-plugin' ),
					'test' => __( 'Test', 'my-plugin' ),
				)
			)
			->set_default_value( 'test' ),
		)
	)
	->add_tab(
		__( 'Advanced', 'my-plugin' ),
		array(
			Field::make( 'number', 'request_timeout', __( 'Request Timeout (seconds)', 'my-plugin' ) )
				->set_default_value( 30 ),
			Field::make( 'toggle', 'debug_mode', __( 'Debug Mode', 'my-plugin' ) ),
		)
	);

// A second, minimal PostMetaContainer - meta saving is entirely automatic:
// PostMetaContainer::make() wires both add_meta_boxes and save_post
// internally (see PostMetaContainer::boot()), so nothing else is needed
// for "venue"/"capacity" post meta to be written whenever an "event"
// post is saved - no custom save_post handler, no nonce field to add
// by hand. "event_date" is stored under "_event_date" instead - see
// set_meta_key() below - the leading underscore is WordPress's
// convention for hiding a field from the default Custom Fields meta
// box / REST API output, while the field itself (form name,
// sanitize, conditional logic) still refers to it as "event_date".
PostMetaContainer::make( __( 'Event Details', 'my-plugin' ) )
	->show_on_post_type( 'event' )
	->add_fields(
		array(
			Field::make( 'date', 'event_date', __( 'Event Date', 'my-plugin' ) )
				->set_meta_key( '_event_date' ),
			Field::make( 'text', 'venue', __( 'Venue', 'my-plugin' ) ),
			Field::make( 'number', 'capacity', __( 'Capacity', 'my-plugin' ) )
				->set_attribute( 'min', '0' ),
		)
	);

/**
 * Reading the saved values back out - e.g. in a single-event.php template
 * or a REST callback. fieldsbox doesn't wrap storage in anything special:
 * each field's meta key is just its own field name, so this is plain
 * get_post_meta() - no fieldsbox API needed to read values back.
 *
 * @param int $post_id
 */
function my_plugin_render_event_details( int $post_id ): void {
	// Note the leading underscore - matches the set_meta_key( '_event_date' )
	// override above; 'venue' and 'capacity' were never overridden, so
	// they're stored under their own field name as usual.
	$event_date = get_post_meta( $post_id, '_event_date', true ); // e.g. "2026-08-14"
	$venue      = get_post_meta( $post_id, 'venue', true );
	$capacity   = get_post_meta( $post_id, 'capacity', true );

	printf(
		'<p>%1$s at %2$s (capacity: %3$s)</p>',
		esc_html( $event_date ),
		esc_html( $venue ),
		esc_html( $capacity )
	);

	// A repeater/group field stores one serialized array under its own
	// meta key - here, the "variants" repeater defined on the Product
	// Details container above.
	$variants = get_post_meta( $post_id, 'variants', true );

	foreach ( (array) $variants as $variant ) {
		printf(
			'<p>%1$s - $%2$s</p>',
			esc_html( $variant['sku'] ?? '' ),
			esc_html( $variant['price'] ?? '' )
		);
	}
}

// Fields on the "Add New Category" and "Edit Category" screens, persisted
// as term meta. WordPress renders those two screens with completely
// different markup (a plain form vs. a <table class="form-table">) -
// TermMetaContainer handles that difference internally, so this call looks
// exactly like the PostMetaContainer/ThemeOptionsContainer ones above.
TermMetaContainer::make( __( 'Category Details', 'my-plugin' ) )
	->show_on_taxonomy( 'category' )
	->add_fields(
		array(
			Field::make( 'text', 'hero_text', __( 'Hero Text', 'my-plugin' ) )
				->set_help_text( __( 'Shown at the top of this category\'s archive page.', 'my-plugin' ) ),
			Field::make( 'color', 'accent_color', __( 'Accent Color', 'my-plugin' ) ),
		)
	);

/**
 * Reading term meta back out - e.g. in a taxonomy archive template. Same
 * plain get_term_meta() as the post meta example above; fieldsbox doesn't
 * change how you read anything back.
 *
 * @param int $term_id
 */
function my_plugin_render_category_details( int $term_id ): void {
	$hero_text    = get_term_meta( $term_id, 'hero_text', true );
	$accent_color = get_term_meta( $term_id, 'accent_color', true );

	printf(
		'<div style="border-top-color: %2$s"><h2>%1$s</h2></div>',
		esc_html( $hero_text ),
		esc_attr( $accent_color )
	);
}
