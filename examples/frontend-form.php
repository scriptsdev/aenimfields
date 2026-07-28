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

use ScriptsDev\FieldsBox\Core\Application;
use ScriptsDev\FieldsBox\Core\FieldFactory;
use ScriptsDev\FieldsBox\Core\Sanitizer;
use ScriptsDev\FieldsBox\Core\Validator;

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
			'type'              => 'text',
			'name'              => 'name',
			'label'             => __( 'Name', 'fieldsbox' ),
			'label_description' => __( 'How should we address you?', 'fieldsbox' ),
			'description'       => __( 'Your full name.', 'fieldsbox' ),
			'placeholder'       => __( 'Jane Doe', 'fieldsbox' ),
			'default'           => '',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-name',
			'wrapper_class'     => 'fieldsbox-example-name-wrapper',
			'autocomplete'      => 'name',
			'validate'          => array( 'max:100' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'name' ),
		),
		array(
			'type'              => 'email',
			'name'              => 'email',
			'label'             => __( 'Email', 'fieldsbox' ),
			'label_description' => __( "We'll reply here.", 'fieldsbox' ),
			'description'       => __( 'Never shared with anyone else.', 'fieldsbox' ),
			'placeholder'       => 'you@example.com',
			'default'           => '',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-email',
			'wrapper_class'     => 'fieldsbox-example-email-wrapper',
			'autocomplete'      => 'email',
			'validate'          => array( 'email' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'email' ),
		),
		array(
			'type'              => 'select',
			'name'              => 'subject',
			'label'             => __( 'Subject', 'fieldsbox' ),
			'label_description' => __( 'What is this regarding?', 'fieldsbox' ),
			'description'       => __( 'Helps us route your message to the right person.', 'fieldsbox' ),
			'placeholder'       => __( '— Choose a subject —', 'fieldsbox' ),
			'options'           => fieldsbox_example_contact_subjects(),
			'default'           => 'general',
			'required'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-subject',
			'wrapper_class'     => 'fieldsbox-example-subject-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'subject' ),
		),
		array(
			'type'              => 'textarea',
			'name'              => 'message',
			'label'             => __( 'Message', 'fieldsbox' ),
			'label_description' => __( 'At least 10 characters.', 'fieldsbox' ),
			'description'       => __( 'Include any details that will help us help you.', 'fieldsbox' ),
			'placeholder'       => __( 'How can we help?', 'fieldsbox' ),
			'default'           => '',
			'rows'              => 6,
			'cols'              => 50,
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-message',
			'wrapper_class'     => 'fieldsbox-example-message-wrapper',
			'validate'          => array( 'min:10' ),
			'input_attr'        => array( 'data-fieldsbox-example' => 'message' ),
		),
		array(
			'type'              => 'date',
			'name'              => 'preferred_contact_date',
			'label'             => __( 'Preferred Contact Date (optional)', 'fieldsbox' ),
			'label_description' => __( "The earliest day we'll reach out.", 'fieldsbox' ),
			'description'       => __( "We'll try to call within a day of this date.", 'fieldsbox' ),
			'placeholder'       => __( 'YYYY-MM-DD', 'fieldsbox' ),
			'default'           => '',
			'date_format'       => 'Y-m-d',
			'min_date'          => 'today',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-contact-date',
			'wrapper_class'     => 'fieldsbox-example-contact-date-wrapper',
			'input_attr'        => array( 'data-fieldsbox-example' => 'preferred-contact-date' ),
		),
		array(
			'type'              => 'text',
			'name'              => 'phone',
			'label'             => __( 'Phone (optional)', 'fieldsbox' ),
			'label_description' => __( 'For a faster response.', 'fieldsbox' ),
			'description'       => __( "We'll only call if you provide a preferred contact date above.", 'fieldsbox' ),
			'placeholder'       => '+1 555 123 4567',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'fieldsbox-example-phone',
			'wrapper_class'     => 'fieldsbox-example-phone-wrapper',
			'autocomplete'      => 'tel',
			'pattern'           => '^\+?[0-9 ]{7,}$',
			'input_attr'        => array( 'data-fieldsbox-example' => 'phone' ),
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

				if ( '' !== $submitted['preferred_contact_date'] ) {
					$body = sprintf( "Preferred contact date: %s\n\n%s", $submitted['preferred_contact_date'], $body );
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
