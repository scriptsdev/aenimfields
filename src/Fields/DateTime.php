<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Fields;

/**
 * DateTime field.
 *
 * Same as Date, but defaults `date_format` to include time.
 *
 * @since 1.0.0
 */
class DateTime extends Date {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( array $args = array() ) {
		$args = array_merge( array( 'date_format' => 'Y-m-d H:i' ), $args );

		parent::__construct( $args );
	}
}
