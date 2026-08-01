<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Core;

use AenimTech\AenimFields\Contracts\FieldInterface;

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

		$template = AENIMFIELDS_DIR . '/templates/field-wrapper.php';

		if ( ! file_exists( $template ) ) {
			return '';
		}

		ob_start();

		include $template;

		return (string) ob_get_clean();
	}
}
