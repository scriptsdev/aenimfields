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

use Fieldsbox\Container\PostMetaContainer;
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
			Field::make( 'file', 'spec_sheet', __( 'Spec Sheet (PDF)', 'my-plugin' ) ),
			Field::make( 'gallery', 'product_gallery', __( 'Product Gallery', 'my-plugin' ) ),
			Field::make( 'separator', 'location_separator', __( 'Location & Structure', 'my-plugin' ) ),
			// Leaflet + OpenStreetMap, no API key - drag the pin or click the
			// map to set lat/lng.
			Field::make( 'map', 'store_location', __( 'Store Location', 'my-plugin' ) )
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

// Theme options page under Settings, storing its values as one option row.
ThemeOptionsContainer::make( __( 'My Plugin Settings', 'my-plugin' ) )
	->set_menu( __( 'My Plugin', 'my-plugin' ), null, 'manage_options' )
	->add_fields(
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
	);
