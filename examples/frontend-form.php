<?php

declare(strict_types=1);

/**
 * Example: Frontend Form
 *
 * Demonstrates rendering AenimFields fields on the site's public
 * frontend via a shortcode, and sanitizing + validating a submission
 * before acting on it (here: sending an email).
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
 * Subject options, shared between the field definition and the sent email.
 *
 * @return array
 */
function aenimfields_example_contact_subjects(): array {
	return array(
		'general' => __( 'General Question', 'aenimfields' ),
		'support' => __( 'Support', 'aenimfields' ),
		'sales'   => __( 'Sales', 'aenimfields' ),
	);
}

/**
 * Field definitions for the contact form.
 *
 * @return array
 */
function aenimfields_example_contact_fields(): array {
	return array(
		array(
			'type'              => 'text',
			'name'              => 'name',
			'label'             => __( 'Name', 'aenimfields' ),
			'label_description' => __( 'How should we address you?', 'aenimfields' ),
			'description'       => __( 'Your full name.', 'aenimfields' ),
			'placeholder'       => __( 'Jane Doe', 'aenimfields' ),
			'default'           => '',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-name',
			'wrapper_class'     => 'aenimfields-example-name-wrapper',
			'autocomplete'      => 'name',
			'validate'          => array( 'max:100' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'name' ),
		),
		array(
			'type'              => 'email',
			'name'              => 'email',
			'label'             => __( 'Email', 'aenimfields' ),
			'label_description' => __( "We'll reply here.", 'aenimfields' ),
			'description'       => __( 'Never shared with anyone else.', 'aenimfields' ),
			'placeholder'       => 'you@example.com',
			'default'           => '',
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-email',
			'wrapper_class'     => 'aenimfields-example-email-wrapper',
			'autocomplete'      => 'email',
			'validate'          => array( 'email' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'email' ),
		),
		array(
			'type'              => 'select',
			'name'              => 'subject',
			'label'             => __( 'Subject', 'aenimfields' ),
			'label_description' => __( 'What is this regarding?', 'aenimfields' ),
			'description'       => __( 'Helps us route your message to the right person.', 'aenimfields' ),
			'placeholder'       => __( '— Choose a subject —', 'aenimfields' ),
			'options'           => aenimfields_example_contact_subjects(),
			'default'           => 'general',
			'required'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-subject',
			'wrapper_class'     => 'aenimfields-example-subject-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'subject' ),
		),
		array(
			'type'              => 'textarea',
			'name'              => 'message',
			'label'             => __( 'Message', 'aenimfields' ),
			'label_description' => __( 'At least 10 characters.', 'aenimfields' ),
			'description'       => __( 'Include any details that will help us help you.', 'aenimfields' ),
			'placeholder'       => __( 'How can we help?', 'aenimfields' ),
			'default'           => '',
			'rows'              => 6,
			'cols'              => 50,
			'required'          => true,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-message',
			'wrapper_class'     => 'aenimfields-example-message-wrapper',
			'validate'          => array( 'min:10' ),
			'input_attr'        => array( 'data-aenimfields-example' => 'message' ),
		),
		array(
			'type'              => 'date',
			'name'              => 'preferred_contact_date',
			'label'             => __( 'Preferred Contact Date (optional)', 'aenimfields' ),
			'label_description' => __( "The earliest day we'll reach out.", 'aenimfields' ),
			'description'       => __( "We'll try to call within a day of this date.", 'aenimfields' ),
			'placeholder'       => __( 'YYYY-MM-DD', 'aenimfields' ),
			'default'           => '',
			'date_format'       => 'Y-m-d',
			'min_date'          => 'today',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-contact-date',
			'wrapper_class'     => 'aenimfields-example-contact-date-wrapper',
			'input_attr'        => array( 'data-aenimfields-example' => 'preferred-contact-date' ),
		),
		array(
			'type'              => 'text',
			'name'              => 'phone',
			'label'             => __( 'Phone (optional)', 'aenimfields' ),
			'label_description' => __( 'For a faster response.', 'aenimfields' ),
			'description'       => __( "We'll only call if you provide a preferred contact date above.", 'aenimfields' ),
			'placeholder'       => '+1 555 123 4567',
			'default'           => '',
			'required'          => false,
			'readonly'          => false,
			'disabled'          => false,
			'class'             => 'aenimfields-example-phone',
			'wrapper_class'     => 'aenimfields-example-phone-wrapper',
			'autocomplete'      => 'tel',
			'pattern'           => '^\+?[0-9 ]{7,}$',
			'input_attr'        => array( 'data-aenimfields-example' => 'phone' ),
		),
	);
}

add_shortcode( 'aenimfields_example_form', 'aenimfields_example_render_form' );

/**
 * Render (and process) the contact form.
 *
 * @return string
 */
function aenimfields_example_render_form(): string {

	$fields    = aenimfields_example_contact_fields();
	$submitted = array();
	$errors    = array();
	$notice    = '';

	if ( isset( $_POST['aenimfields_example_form_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aenimfields_example_form_nonce'] ) ), 'aenimfields_example_submit_form' )
	) {

		// Honeypot: real visitors never see or fill this field; bots often do.
		$honeypot = wp_unslash( $_POST['website'] ?? '' );

		if ( '' !== $honeypot ) {

			// Pretend success without actually sending anything.
			$notice = __( 'Thanks, your message has been sent.', 'aenimfields' );

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

				$subjects = aenimfields_example_contact_subjects();
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

				$notice    = __( 'Thanks, your message has been sent.', 'aenimfields' );
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
		<p class="aenimfields-form-notice"><?php echo esc_html( $notice ); ?></p>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'aenimfields_example_submit_form', 'aenimfields_example_form_nonce' ); ?>

		<?php echo $app->render( $fields ); ?>

		<p class="aenimfields-form-hint"><?php esc_html_e( 'Optional details:', 'aenimfields' ); ?></p>
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

		<button type="submit"><?php esc_html_e( 'Send', 'aenimfields' ); ?></button>
	</form>

	<?php
	return (string) ob_get_clean();
}
