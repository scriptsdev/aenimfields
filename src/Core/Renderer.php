<?php

declare(strict_types=1);

namespace FieldsBox\Core;

use FieldsBox\Contracts\FieldInterface;

/**
 * Renderer class.
 *
 * @since 1.0.0
 */
class Renderer {

	/**
	 * Render a field.
	 *
	 * @since 1.0.0
	 *
	 * @param FieldInterface $field
	 *
	 * @return string
	 */
	public function render( FieldInterface $field ): string {
		Assets::enqueue();

		foreach ( $field->assets() as $key ) {
			Assets::enqueue( $key );
		}

		$template = FIELDSBOX_DIR . '/templates/field-wrapper.php';

		if ( ! file_exists( $template ) ) {
			return '';
		}

		ob_start();

		include $template;

		return (string) ob_get_clean();
	}
}
