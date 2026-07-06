<?php
/**
 * Example only - not loaded automatically. Copy the relevant parts into your
 * own plugin's bootstrap once Fieldsbox is required.
 */

if (! defined('ABSPATH')) {
    exit;
}

// Load the shared package once; safe to repeat this guard in every plugin.
if (! class_exists('Fieldsbox\Fieldsbox')) {
    require_once WP_PLUGIN_DIR . '/fieldsbox/fieldsbox.php';
}

use Fieldsbox\Container\PostMetaContainer;
use Fieldsbox\Container\ThemeOptionsContainer;
use Fieldsbox\Field\Field;

// Post meta box with tabs and a conditionally shown field.
PostMetaContainer::make('Product Details')
    ->show_on_post_type('product')
    ->add_tab('General', [
        Field::make('text', 'subtitle', 'Subtitle')
            ->set_help_text('Shown under the product title.'),
        Field::make('select', 'availability', 'Availability')
            ->set_options([
                'in_stock' => 'In stock',
                'preorder' => 'Preorder',
            ])
            ->set_default_value('in_stock'),
        // Only shown once "Availability" above is set to "preorder" -
        // evaluated live in the browser by assets/js/fieldsbox.js.
        Field::make('text', 'release_date', 'Release date')
            ->set_conditional_logic([
                ['field' => 'availability', 'value' => 'preorder'],
            ]),
    ])
    ->add_tab('Options', [
        Field::make('checkbox', 'is_featured', 'Feature this product'),
        Field::make('multiselect', 'tags', 'Tags')
            ->set_options([
                'sale' => 'Sale',
                'new' => 'New',
                'limited' => 'Limited edition',
            ]),
        Field::make('textarea', 'internal_notes', 'Internal notes'),
    ]);

// Theme options page under Settings, storing its values as one option row.
ThemeOptionsContainer::make('My Plugin Settings')
    ->set_menu('My Plugin', null, 'manage_options')
    ->add_fields([
        Field::make('text', 'api_key', 'API Key')->set_required(),
        Field::make('radio', 'mode', 'Mode')
            ->set_options(['live' => 'Live', 'test' => 'Test'])
            ->set_default_value('test'),
    ]);
