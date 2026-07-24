<?php

declare(strict_types=1);

/**
 * Example: Frontend Form
 *
 * Demonstrates rendering FieldsBox fields on the site's public
 * frontend via a shortcode, and sanitizing + validating a submission
 * before acting on it (here: sending an email).
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
 * Subject options, shared between the field definition and the sent email.
 *
 * @return array
 */
function fieldsbox_example_contact_subjects(): array {
	return array(
		'general' => __( 'General Question', 'fieldsbox' ),
		'support' => __( 'Support', 'fieldsbox' ),
		'sales'   => __( 'Sales', 'fieldsbox' ),
	);
}

/**
 * Field definitions for the contact form.
 *
 * @return array
 */
function fieldsbox_example_contact_fields(): array {
	return array(
		array(
			'type'     => 'text',
			'name'     => 'name',
			'label'    => __( 'Name', 'fieldsbox' ),
			'required' => true,
		),
		array(
			'type'     => 'email',
			'name'     => 'email',
			'label'    => __( 'Email', 'fieldsbox' ),
			'required' => true,
		),
		array(
			'type'    => 'select',
			'name'    => 'subject',
			'label'   => __( 'Subject', 'fieldsbox' ),
			'options' => fieldsbox_example_contact_subjects(),
			'default' => 'general',
		),
		array(
			'type'     => 'textarea',
			'name'     => 'message',
			'label'    => __( 'Message', 'fieldsbox' ),
			'rows'     => 6,
			'required' => true,
			'validate' => array( 'min:10' ),
		),
		array(
			'type'  => 'text',
			'name'  => 'phone',
			'label' => __( 'Phone (optional)', 'fieldsbox' ),
		),
	);
}

add_shortcode( 'fieldsbox_example_form', 'fieldsbox_example_render_form' );

/**
 * Render (and process) the contact form.
 *
 * @return string
 */
function fieldsbox_example_render_form(): string {

	$fields    = fieldsbox_example_contact_fields();
	$submitted = array();
	$errors    = array();
	$notice    = '';

	if ( isset( $_POST['fieldsbox_example_form_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fieldsbox_example_form_nonce'] ) ), 'fieldsbox_example_submit_form' )
	) {

		// Honeypot: real visitors never see or fill this field; bots often do.
		$honeypot = wp_unslash( $_POST['website'] ?? '' );

		if ( '' !== $honeypot ) {

			// Pretend success without actually sending anything.
			$notice = __( 'Thanks, your message has been sent.', 'fieldsbox' );

		} else {

			$factory    = new FieldFactory();
			$has_errors = false;

			foreach ( $fields as $args ) {

				$field = $factory->make( $args );
				$raw   = wp_unslash( $_POST[ $field->get_name() ] ?? '' );
				$value = Sanitizer::sanitize( $field, $raw );

				$submitted[ $field->get_name() ] = $value;

				$result = Validator::validate( $field, $value );

				if ( ! $result->passed() ) {
					$errors[ $field->get_name() ] = $result->message();
					$has_errors                   = true;
				}
			}

			if ( ! $has_errors ) {

				$subjects = fieldsbox_example_contact_subjects();
				$body     = $submitted['message'];

				if ( '' !== $submitted['phone'] ) {
					$body = sprintf( "Phone: %s\n\n%s", $submitted['phone'], $body );
				}

				wp_mail(
					get_option( 'admin_email' ),
					sprintf( '[%s] %s', get_bloginfo( 'name' ), $subjects[ $submitted['subject'] ] ?? $submitted['subject'] ),
					$body,
					array( sprintf( 'Reply-To: %1$s <%2$s>', $submitted['name'], $submitted['email'] ) )
				);

				$notice    = __( 'Thanks, your message has been sent.', 'fieldsbox' );
				$submitted = array();
			}
		}
	}

	foreach ( $fields as &$field ) {

		$field['value'] = $submitted[ $field['name'] ] ?? '';

		if ( isset( $errors[ $field['name'] ] ) ) {
			$field['error'] = $errors[ $field['name'] ];
		}
	}
	unset( $field );

	// Pull the last field out to render it on its own, demonstrating that
	// Application::render() also accepts a single field's args directly
	// (no outer array) — for example, to place it in a different part of
	// the form's layout, as done below.
	$phone = array_pop( $fields ); // phone, defined last above.

	$app = new Application();

	ob_start();
	?>

	<?php if ( $notice ) : ?>
		<p class="fieldsbox-form-notice"><?php echo esc_html( $notice ); ?></p>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'fieldsbox_example_submit_form', 'fieldsbox_example_form_nonce' ); ?>

		<?php echo $app->render( $fields ); ?>

		<p class="fieldsbox-form-hint"><?php esc_html_e( 'Optional details:', 'fieldsbox' ); ?></p>
		<?php echo $app->render( $phone ); ?>

		<?php
		// A single field can also be rendered on its own, without wrapping
		// it in a list — useful for a one-off field like this honeypot trap.
		echo $app->render(
			array(
				'type' => 'hidden',
				'name' => 'website',
			)
		);
		?>

		<button type="submit"><?php esc_html_e( 'Send', 'fieldsbox' ); ?></button>
	</form>

	<?php
	return (string) ob_get_clean();
}
