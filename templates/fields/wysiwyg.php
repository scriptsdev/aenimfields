<?php

defined( 'ABSPATH' ) || exit;

// wp_editor() requires a lowercase, underscore-only id; normalize just in
// case, though a well-formed field id already satisfies this untouched.
$editor_id = strtolower( preg_replace( '/[^a-zA-Z0-9_]/', '_', $field->get_id() ) );

wp_editor(
	(string) $field->get_value(),
	$editor_id,
	array(
		'textarea_name' => $field->get_name(),
		'textarea_rows' => $field->get_arg( 'rows', 10 ),
		'media_buttons' => $field->get_arg( 'media_buttons', true ),
		'teeny'         => $field->get_arg( 'teeny', false ),
		'tinymce'       => $field->get_arg( 'tinymce', true ),
		'quicktags'     => $field->get_arg( 'quicktags', true ),
		'editor_class'  => $field->get_arg( 'class', '' ),
	)
);
