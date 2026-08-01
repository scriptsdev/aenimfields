<?php

declare(strict_types=1);

/**
 * Example: Settings Page
 *
 * Demonstrates rendering AenimFields fields on a custom admin settings
 * page, and sanitizing + validating submitted values before saving
 * them as a single option.
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
 * Field definitions for the settings page.
 *
 * @return array
 */
function aenimfields_example_settings_fields(): array {
	return array(
		array(
			'type'              => 'text',
			'name'              => 'site_tagline',
			'label'             => __( 'Site Tagline', 'aenimfields' ),
			'label_description' => __( 'A short phrase describing your site.', 'aenimfields' ),
			'description'       => __( 'Used in the browser title and meta description.', 'aenimfields' ),
			'help'              => __( 'Keep it under 60 characters for search results.', 'aenimfields' ),
			'placeholder'       => __( 'e.g. Handmade goods, made to order', 'aenimfields' ),
			'default'           => '',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-tagline',
			'wrapper_class'     => 'aenimfields-example-tagline-wrapper',
			'autocomplete'      => 'off',
			'validate'          => array( 'max:60' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'tagline' ),
			'label_attr'        => array( 'data-aenimfields-example' => 'tagline-label' ),
			'wrapper_attr'      => array( 'data-aenimfields-example' => 'tagline-wrapper' ),
		),
		array(
			'type'              => 'url',
			'name'              => 'support_url',
			'label'             => __( 'Support URL', 'aenimfields' ),
			'label_description' => __( 'Where customers go for help.', 'aenimfields' ),
			'description'       => __( 'Linked from the footer and confirmation emails.', 'aenimfields' ),
			'help'              => __( 'Include the https:// prefix.', 'aenimfields' ),
			'placeholder'       => 'https://example.com/support',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-support-url',
			'wrapper_class'     => 'aenimfields-example-support-url-wrapper',
			'autocomplete'      => 'url',
			'validate'          => array( 'url' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'support-url' ),
		),
		array(
			'type'              => 'password',
			'name'              => 'api_key',
			'label'             => __( 'API Key', 'aenimfields' ),
			'label_description' => __( 'Issued by the remote service you connect to.', 'aenimfields' ),
			'description'       => __( 'Used to authenticate with the remote service. Leave blank to keep the current key.', 'aenimfields' ),
			'help'              => __( 'Treat this like a password; do not share it.', 'aenimfields' ),
			'placeholder'       => __( '••••••••', 'aenimfields' ),
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-api-key',
			'wrapper_class'     => 'aenimfields-example-api-key-wrapper',
			'autocomplete'      => 'off',
			'input_attr'        => array( 'data-aenimfields-example' => 'api-key' ),
		),
		array(
			'type'              => 'color',
			'name'              => 'brand_color',
			'label'             => __( 'Brand Color', 'aenimfields' ),
			'label_description' => __( 'Used for buttons and links on the frontend.', 'aenimfields' ),
			'description'       => __( 'Pick a color that matches your logo.', 'aenimfields' ),
			'help'              => __( 'Defaults to the theme color if left unset.', 'aenimfields' ),
			'default'           => '#2563eb',
			'required'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-brand-color',
			'wrapper_class'     => 'aenimfields-example-brand-color-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'brand-color' ),
		),
		array(
			'type'              => 'image',
			'name'              => 'site_logo',
			'label'             => __( 'Site Logo', 'aenimfields' ),
			'label_description' => __( 'Shown in the header and in emails.', 'aenimfields' ),
			'description'       => __( 'Recommended size: 300×80px, transparent PNG.', 'aenimfields' ),
			'help'              => __( 'Falls back to the site title if left empty.', 'aenimfields' ),
			'default'           => '',
			'preview_size'      => 'medium',
			'select_text'       => __( 'Select Logo', 'aenimfields' ),
			'title_text'        => __( 'Choose a Logo', 'aenimfields' ),
			'required'          => false,
			'class'             => 'aenimfields-example-logo',
			'wrapper_class'     => 'aenimfields-example-logo-wrapper',
		),
		array(
			'type'              => 'checkbox',
			'name'              => 'enabled_features',
			'label'             => __( 'Enabled Features', 'aenimfields' ),
			'label_description' => __( 'Toggle optional site features.', 'aenimfields' ),
			'description'       => __( 'Disabled features are hidden everywhere on the frontend.', 'aenimfields' ),
			'help'              => __( 'Changes apply immediately after saving.', 'aenimfields' ),
			'default'           => array( 'comments' ),
			'options'           => array(
				'comments' => __( 'Comments', 'aenimfields' ),
				'ratings'  => __( 'Ratings', 'aenimfields' ),
				'sharing'  => __( 'Social Sharing', 'aenimfields' ),
			),
			'required'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-features',
			'wrapper_class'     => 'aenimfields-example-features-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'enabled-features' ),
		),
		array(
			'type'              => 'multiselect',
			'name'              => 'allowed_roles',
			'label'             => __( 'Allowed Roles', 'aenimfields' ),
			'label_description' => __( 'Which user roles can access this feature.', 'aenimfields' ),
			'description'       => __( 'Administrators always have access regardless of this setting.', 'aenimfields' ),
			'help'              => __( 'Hold Ctrl/Cmd to select multiple.', 'aenimfields' ),
			'default'           => array( 'administrator' ),
			'options'           => array(
				'administrator' => __( 'Administrator', 'aenimfields' ),
				'editor'        => __( 'Editor', 'aenimfields' ),
				'author'        => __( 'Author', 'aenimfields' ),
			),
			'required'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-roles',
			'wrapper_class'     => 'aenimfields-example-roles-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'allowed-roles' ),
		),
		array(
			'type'              => 'datetime',
			'name'              => 'maintenance_until',
			'label'             => __( 'Maintenance Mode Until', 'aenimfields' ),
			'label_description' => __( 'Site shows a maintenance notice until this time.', 'aenimfields' ),
			'description'       => __( 'Leave blank to disable scheduled maintenance mode.', 'aenimfields' ),
			'help'              => __( 'Times are shown in your site\'s configured timezone.', 'aenimfields' ),
			'placeholder'       => __( 'YYYY-MM-DD HH:MM', 'aenimfields' ),
			'default'           => '',
			'date_format'       => 'Y-m-d H:i',
			'min_date'          => 'today',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-maintenance',
			'wrapper_class'     => 'aenimfields-example-maintenance-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'maintenance-until' ),
		),
		array(
			'type'              => 'wysiwyg',
			'name'              => 'footer_text',
			'label'             => __( 'Footer Text', 'aenimfields' ),
			'label_description' => __( 'Shown at the bottom of every page.', 'aenimfields' ),
			'description'       => __( 'Supports links and basic formatting.', 'aenimfields' ),
			'help'              => __( 'Leave blank to use the theme default.', 'aenimfields' ),
			'default'           => '',
			'rows'              => 6,
			'media_buttons'     => false,
			'teeny'             => true,
			'quicktags'         => false,
			'required'          => false,
			'class'             => 'aenimfields-example-footer-text',
			'wrapper_class'     => 'aenimfields-example-footer-text-wrapper',
		),
		array(
			'type'              => 'group',
			'name'              => 'business_address',
			'label'             => __( 'Business Address', 'aenimfields' ),
			'label_description' => __( 'Used for invoices and structured data.', 'aenimfields' ),
			'description'       => __( 'Shown in the site footer if provided.', 'aenimfields' ),
			'help'              => __( 'Leave blank if you operate online only.', 'aenimfields' ),
			'default'           => array(),
			'required'          => false,
			'class'             => 'aenimfields-example-address',
			'wrapper_class'     => 'aenimfields-example-address-wrapper',
			'fields'            => array(
				array(
					'type'  => 'text',
					'name'  => 'street',
					'label' => __( 'Street', 'aenimfields' ),
				),
				array(
					'type'  => 'text',
					'name'  => 'city',
					'label' => __( 'City', 'aenimfields' ),
				),
				array(
					'type'     => 'text',
					'name'     => 'zip',
					'label'    => __( 'ZIP / Postal Code', 'aenimfields' ),
					'validate' => array( 'max:12' ),
				),
			),
		),
		array(
			'type'              => 'repeater',
			'name'              => 'social_links',
			'label'             => __( 'Social Media Links', 'aenimfields' ),
			'label_description' => __( 'Shown as icons in the site footer.', 'aenimfields' ),
			'description'       => __( 'Add one row per platform.', 'aenimfields' ),
			'help'              => __( 'Icons display in the order added.', 'aenimfields' ),
			'default'           => array(),
			'min_rows'          => 0,
			'max_rows'          => 8,
			'add_button_text'   => __( 'Add Social Link', 'aenimfields' ),
			'required'          => false,
			'class'             => 'aenimfields-example-social-links',
			'wrapper_class'     => 'aenimfields-example-social-links-wrapper',
			'fields'            => array(
				array(
					'type'    => 'select',
					'name'    => 'platform',
					'label'   => __( 'Platform', 'aenimfields' ),
					'options' => array(
						'twitter'   => __( 'Twitter / X', 'aenimfields' ),
						'facebook'  => __( 'Facebook', 'aenimfields' ),
						'instagram' => __( 'Instagram', 'aenimfields' ),
						'linkedin'  => __( 'LinkedIn', 'aenimfields' ),
					),
				),
				array(
					'type'     => 'url',
					'name'     => 'url',
					'label'    => __( 'Profile URL', 'aenimfields' ),
					'required' => true,
					'validate' => array( 'url' ),
				),
			),
		),
		array(
			'type'              => 'text',
			'name'              => 'contact_phone',
			'label'             => __( 'Contact Phone', 'aenimfields' ),
			'label_description' => __( 'Shown next to social sharing buttons.', 'aenimfields' ),
			'description'       => __( 'Only needed when Social Sharing is enabled above.', 'aenimfields' ),
			'help'              => __( 'Include your country code, e.g. +1.', 'aenimfields' ),
			'placeholder'       => '+1 555 123 4567',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-phone',
			'wrapper_class'     => 'aenimfields-example-phone-wrapper',
			'autocomplete'      => 'tel',
			'pattern'           => '^\+?[0-9 ]{7,}$',
			// Only shown once "Social Sharing" is checked above.
			'depends_on'        => array(
				'field' => 'enabled_features',
				'value' => 'sharing',
			),
			'input_attr'        => array( 'data-aenimfields-example' => 'contact-phone' ),
		),
	);
}

add_action( 'admin_menu', 'aenimfields_example_register_settings_page' );

/**
 * Register the settings page.
 *
 * @return void
 */
function aenimfields_example_register_settings_page(): void {
	add_options_page(
		__( 'AenimFields Example', 'aenimfields' ),
		__( 'AenimFields Example', 'aenimfields' ),
		'manage_options',
		'aenimfields-example',
		'aenimfields_example_render_settings_page'
	);
}

/**
 * Render the settings page and process a submission.
 *
 * @return void
 */
function aenimfields_example_render_settings_page(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved  = get_option( 'aenimfields_example_settings', array() );
	$errors = array();
	$notice = '';

	if ( isset( $_POST['aenimfields_example_settings_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aenimfields_example_settings_nonce'] ) ), 'aenimfields_example_save_settings' )
	) {

		$factory = new FieldFactory();
		$clean   = array();

		foreach ( aenimfields_example_settings_fields() as $args ) {

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
			update_option( 'aenimfields_example_settings', array_merge( $saved, $clean ) );
			$saved  = get_option( 'aenimfields_example_settings', array() );
			$notice = __( 'Settings saved.', 'aenimfields' );
		}
	}

	$app    = new Application();
	$fields = aenimfields_example_settings_fields();

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
		<h1><?php esc_html_e( 'AenimFields Example Settings', 'aenimfields' ); ?></h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'aenimfields_example_save_settings', 'aenimfields_example_settings_nonce' ); ?>
			<?php echo $app->render( $fields ); ?>

			<h2><?php esc_html_e( 'Additional Contact Info', 'aenimfields' ); ?></h2>
			<?php echo $app->render( $phone ); ?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
