<?php

declare(strict_types=1);

/**
 * Example: Settings Page
 *
 * Demonstrates rendering FieldsBox fields on a custom admin settings
 * page, and sanitizing + validating submitted values before saving
 * them as a single option.
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
 * Field definitions for the settings page.
 *
 * @return array
 */
function fieldsbox_example_settings_fields(): array {
	return array(
		array(
			'type'              => 'text',
			'name'              => 'site_tagline',
			'label'             => __( 'Site Tagline', 'fieldsbox' ),
			'label_description' => __( 'A short phrase describing your site.', 'fieldsbox' ),
			'description'       => __( 'Used in the browser title and meta description.', 'fieldsbox' ),
			'help'              => __( 'Keep it under 60 characters for search results.', 'fieldsbox' ),
			'placeholder'       => __( 'e.g. Handmade goods, made to order', 'fieldsbox' ),
			'default'           => '',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-tagline',
			'wrapper_class'     => 'fieldsbox-example-tagline-wrapper',
			'autocomplete'      => 'off',
			'validate'          => array( 'max:60' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'tagline' ),
			'label_attr'        => array( 'data-fieldsbox-example' => 'tagline-label' ),
			'wrapper_attr'      => array( 'data-fieldsbox-example' => 'tagline-wrapper' ),
		),
		array(
			'type'              => 'url',
			'name'              => 'support_url',
			'label'             => __( 'Support URL', 'fieldsbox' ),
			'label_description' => __( 'Where customers go for help.', 'fieldsbox' ),
			'description'       => __( 'Linked from the footer and confirmation emails.', 'fieldsbox' ),
			'help'              => __( 'Include the https:// prefix.', 'fieldsbox' ),
			'placeholder'       => 'https://example.com/support',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-support-url',
			'wrapper_class'     => 'fieldsbox-example-support-url-wrapper',
			'autocomplete'      => 'url',
			'validate'          => array( 'url' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'support-url' ),
		),
		array(
			'type'              => 'password',
			'name'              => 'api_key',
			'label'             => __( 'API Key', 'fieldsbox' ),
			'label_description' => __( 'Issued by the remote service you connect to.', 'fieldsbox' ),
			'description'       => __( 'Used to authenticate with the remote service. Leave blank to keep the current key.', 'fieldsbox' ),
			'help'              => __( 'Treat this like a password; do not share it.', 'fieldsbox' ),
			'placeholder'       => __( '••••••••', 'fieldsbox' ),
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-api-key',
			'wrapper_class'     => 'fieldsbox-example-api-key-wrapper',
			'autocomplete'      => 'off',
			'input_attr'        => array( 'data-fieldsbox-example' => 'api-key' ),
		),
		array(
			'type'              => 'color',
			'name'              => 'brand_color',
			'label'             => __( 'Brand Color', 'fieldsbox' ),
			'label_description' => __( 'Used for buttons and links on the frontend.', 'fieldsbox' ),
			'description'       => __( 'Pick a color that matches your logo.', 'fieldsbox' ),
			'help'              => __( 'Defaults to the theme color if left unset.', 'fieldsbox' ),
			'default'           => '#2563eb',
			'required'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-brand-color',
			'wrapper_class'     => 'fieldsbox-example-brand-color-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'brand-color' ),
		),
		array(
			'type'              => 'image',
			'name'              => 'site_logo',
			'label'             => __( 'Site Logo', 'fieldsbox' ),
			'label_description' => __( 'Shown in the header and in emails.', 'fieldsbox' ),
			'description'       => __( 'Recommended size: 300×80px, transparent PNG.', 'fieldsbox' ),
			'help'              => __( 'Falls back to the site title if left empty.', 'fieldsbox' ),
			'default'           => '',
			'preview_size'      => 'medium',
			'select_text'       => __( 'Select Logo', 'fieldsbox' ),
			'title_text'        => __( 'Choose a Logo', 'fieldsbox' ),
			'required'          => false,
			'class'             => 'fieldsbox-example-logo',
			'wrapper_class'     => 'fieldsbox-example-logo-wrapper',
		),
		array(
			'type'              => 'checkbox',
			'name'              => 'enabled_features',
			'label'             => __( 'Enabled Features', 'fieldsbox' ),
			'label_description' => __( 'Toggle optional site features.', 'fieldsbox' ),
			'description'       => __( 'Disabled features are hidden everywhere on the frontend.', 'fieldsbox' ),
			'help'              => __( 'Changes apply immediately after saving.', 'fieldsbox' ),
			'default'           => array( 'comments' ),
			'options'           => array(
				'comments' => __( 'Comments', 'fieldsbox' ),
				'ratings'  => __( 'Ratings', 'fieldsbox' ),
				'sharing'  => __( 'Social Sharing', 'fieldsbox' ),
			),
			'required'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-features',
			'wrapper_class'     => 'fieldsbox-example-features-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'enabled-features' ),
		),
		array(
			'type'              => 'multiselect',
			'name'              => 'allowed_roles',
			'label'             => __( 'Allowed Roles', 'fieldsbox' ),
			'label_description' => __( 'Which user roles can access this feature.', 'fieldsbox' ),
			'description'       => __( 'Administrators always have access regardless of this setting.', 'fieldsbox' ),
			'help'              => __( 'Hold Ctrl/Cmd to select multiple.', 'fieldsbox' ),
			'default'           => array( 'administrator' ),
			'options'           => array(
				'administrator' => __( 'Administrator', 'fieldsbox' ),
				'editor'        => __( 'Editor', 'fieldsbox' ),
				'author'        => __( 'Author', 'fieldsbox' ),
			),
			'required'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-roles',
			'wrapper_class'     => 'fieldsbox-example-roles-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'allowed-roles' ),
		),
		array(
			'type'              => 'datetime',
			'name'              => 'maintenance_until',
			'label'             => __( 'Maintenance Mode Until', 'fieldsbox' ),
			'label_description' => __( 'Site shows a maintenance notice until this time.', 'fieldsbox' ),
			'description'       => __( 'Leave blank to disable scheduled maintenance mode.', 'fieldsbox' ),
			'help'              => __( 'Times are shown in your site\'s configured timezone.', 'fieldsbox' ),
			'placeholder'       => __( 'YYYY-MM-DD HH:MM', 'fieldsbox' ),
			'default'           => '',
			'date_format'       => 'Y-m-d H:i',
			'min_date'          => 'today',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-maintenance',
			'wrapper_class'     => 'fieldsbox-example-maintenance-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'maintenance-until' ),
		),
		array(
			'type'              => 'wysiwyg',
			'name'              => 'footer_text',
			'label'             => __( 'Footer Text', 'fieldsbox' ),
			'label_description' => __( 'Shown at the bottom of every page.', 'fieldsbox' ),
			'description'       => __( 'Supports links and basic formatting.', 'fieldsbox' ),
			'help'              => __( 'Leave blank to use the theme default.', 'fieldsbox' ),
			'default'           => '',
			'rows'              => 6,
			'media_buttons'     => false,
			'teeny'             => true,
			'quicktags'         => false,
			'required'          => false,
			'class'             => 'fieldsbox-example-footer-text',
			'wrapper_class'     => 'fieldsbox-example-footer-text-wrapper',
		),
		array(
			'type'              => 'group',
			'name'              => 'business_address',
			'label'             => __( 'Business Address', 'fieldsbox' ),
			'label_description' => __( 'Used for invoices and structured data.', 'fieldsbox' ),
			'description'       => __( 'Shown in the site footer if provided.', 'fieldsbox' ),
			'help'              => __( 'Leave blank if you operate online only.', 'fieldsbox' ),
			'default'           => array(),
			'required'          => false,
			'class'             => 'fieldsbox-example-address',
			'wrapper_class'     => 'fieldsbox-example-address-wrapper',
			'fields'            => array(
				array(
					'type'  => 'text',
					'name'  => 'street',
					'label' => __( 'Street', 'fieldsbox' ),
				),
				array(
					'type'  => 'text',
					'name'  => 'city',
					'label' => __( 'City', 'fieldsbox' ),
				),
				array(
					'type'     => 'text',
					'name'     => 'zip',
					'label'    => __( 'ZIP / Postal Code', 'fieldsbox' ),
					'validate' => array( 'max:12' ),
				),
			),
		),
		array(
			'type'              => 'repeater',
			'name'              => 'social_links',
			'label'             => __( 'Social Media Links', 'fieldsbox' ),
			'label_description' => __( 'Shown as icons in the site footer.', 'fieldsbox' ),
			'description'       => __( 'Add one row per platform.', 'fieldsbox' ),
			'help'              => __( 'Icons display in the order added.', 'fieldsbox' ),
			'default'           => array(),
			'min_rows'          => 0,
			'max_rows'          => 8,
			'add_button_text'   => __( 'Add Social Link', 'fieldsbox' ),
			'required'          => false,
			'class'             => 'fieldsbox-example-social-links',
			'wrapper_class'     => 'fieldsbox-example-social-links-wrapper',
			'fields'            => array(
				array(
					'type'    => 'select',
					'name'    => 'platform',
					'label'   => __( 'Platform', 'fieldsbox' ),
					'options' => array(
						'twitter'   => __( 'Twitter / X', 'fieldsbox' ),
						'facebook'  => __( 'Facebook', 'fieldsbox' ),
						'instagram' => __( 'Instagram', 'fieldsbox' ),
						'linkedin'  => __( 'LinkedIn', 'fieldsbox' ),
					),
				),
				array(
					'type'     => 'url',
					'name'     => 'url',
					'label'    => __( 'Profile URL', 'fieldsbox' ),
					'required' => true,
					'validate' => array( 'url' ),
				),
			),
		),
		array(
			'type'              => 'text',
			'name'              => 'contact_phone',
			'label'             => __( 'Contact Phone', 'fieldsbox' ),
			'label_description' => __( 'Shown next to social sharing buttons.', 'fieldsbox' ),
			'description'       => __( 'Only needed when Social Sharing is enabled above.', 'fieldsbox' ),
			'help'              => __( 'Include your country code, e.g. +1.', 'fieldsbox' ),
			'placeholder'       => '+1 555 123 4567',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-phone',
			'wrapper_class'     => 'fieldsbox-example-phone-wrapper',
			'autocomplete'      => 'tel',
			'pattern'           => '^\+?[0-9 ]{7,}$',
			// Only shown once "Social Sharing" is checked above.
			'depends_on'        => array(
				'field' => 'enabled_features',
				'value' => 'sharing',
			),
			'input_attr'        => array( 'data-fieldsbox-example' => 'contact-phone' ),
		),
	);
}

add_action( 'admin_menu', 'fieldsbox_example_register_settings_page' );

/**
 * Register the settings page.
 *
 * @return void
 */
function fieldsbox_example_register_settings_page(): void {
	add_options_page(
		__( 'FieldsBox Example', 'fieldsbox' ),
		__( 'FieldsBox Example', 'fieldsbox' ),
		'manage_options',
		'fieldsbox-example',
		'fieldsbox_example_render_settings_page'
	);
}

/**
 * Render the settings page and process a submission.
 *
 * @return void
 */
function fieldsbox_example_render_settings_page(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved  = get_option( 'fieldsbox_example_settings', array() );
	$errors = array();
	$notice = '';

	if ( isset( $_POST['fieldsbox_example_settings_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fieldsbox_example_settings_nonce'] ) ), 'fieldsbox_example_save_settings' )
	) {

		$factory = new FieldFactory();
		$clean   = array();

		foreach ( fieldsbox_example_settings_fields() as $args ) {

			$field = $factory->make( $args );
			$raw   = wp_unslash( $_POST[ $field->get_name() ] ?? '' );

			// A blank password field means "keep the current key", not "clear it".
			if ( 'password' === $field->get_type() && '' === $raw ) {
				continue;
			}

			$value  = Sanitizer::sanitize( $field, $raw );
			$result = Validator::validate( $field, $value );

			if ( ! $result->passed() ) {
				$errors[ $field->get_name() ] = $result->message();
				continue;
			}

			$clean[ $field->get_name() ] = $value;
		}

		if ( empty( $errors ) ) {
			update_option( 'fieldsbox_example_settings', array_merge( $saved, $clean ) );
			$saved  = get_option( 'fieldsbox_example_settings', array() );
			$notice = __( 'Settings saved.', 'fieldsbox' );
		}
	}

	$app    = new Application();
	$fields = fieldsbox_example_settings_fields();

	foreach ( $fields as &$field ) {

		// Never echo the stored secret back into the page.
		$field['value'] = 'password' === $field['type']
			? ''
			: ( $saved[ $field['name'] ] ?? '' );

		if ( isset( $errors[ $field['name'] ] ) ) {
			$field['error'] = $errors[ $field['name'] ];
		}
	}
	unset( $field );

	// Pull the last field out to render it on its own, demonstrating that
	// Application::render() also accepts a single field's args directly
	// (no outer array) — for example, to place it in a different part of
	// the page layout, as done below.
	$phone = array_pop( $fields ); // contact_phone, defined last above.

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FieldsBox Example Settings', 'fieldsbox' ); ?></h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'fieldsbox_example_save_settings', 'fieldsbox_example_settings_nonce' ); ?>
			<?php echo $app->render( $fields ); ?>

			<h2><?php esc_html_e( 'Additional Contact Info', 'fieldsbox' ); ?></h2>
			<?php echo $app->render( $phone ); ?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
