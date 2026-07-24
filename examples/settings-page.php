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
			'type'     => 'text',
			'name'     => 'site_tagline',
			'label'    => __( 'Site Tagline', 'fieldsbox' ),
			'required' => true,
		),
		array(
			'type'  => 'url',
			'name'  => 'support_url',
			'label' => __( 'Support URL', 'fieldsbox' ),
		),
		array(
			'type'        => 'password',
			'name'        => 'api_key',
			'label'       => __( 'API Key', 'fieldsbox' ),
			'description' => __( 'Used to authenticate with the remote service. Leave blank to keep the current key.', 'fieldsbox' ),
		),
		array(
			'type'    => 'color',
			'name'    => 'brand_color',
			'label'   => __( 'Brand Color', 'fieldsbox' ),
			'default' => '#2563eb',
		),
		array(
			'type'    => 'checkbox',
			'name'    => 'enabled_features',
			'label'   => __( 'Enabled Features', 'fieldsbox' ),
			'options' => array(
				'comments' => __( 'Comments', 'fieldsbox' ),
				'ratings'  => __( 'Ratings', 'fieldsbox' ),
				'sharing'  => __( 'Social Sharing', 'fieldsbox' ),
			),
		),
		array(
			'type'    => 'multiselect',
			'name'    => 'allowed_roles',
			'label'   => __( 'Allowed Roles', 'fieldsbox' ),
			'options' => array(
				'administrator' => __( 'Administrator', 'fieldsbox' ),
				'editor'        => __( 'Editor', 'fieldsbox' ),
				'author'        => __( 'Author', 'fieldsbox' ),
			),
		),
		array(
			'type'  => 'text',
			'name'  => 'contact_phone',
			'label' => __( 'Contact Phone', 'fieldsbox' ),
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
