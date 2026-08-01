<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Core;

use AenimTech\AenimFields\Contracts\FieldInterface;
use InvalidArgumentException;

/**
 * Field factory.
 *
 * Resolves a field type to its registered class and instantiates it.
 *
 * @since 1.0.0
 */
class FieldFactory {

	/**
	 * Create a field instance.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 *
	 * @return FieldInterface
	 *
	 * @throws InvalidArgumentException
	 */
	public function make( array $args ): FieldInterface {
		if ( empty( $args['type'] ) ) {
			throw new InvalidArgumentException(
				__( 'Field type is required.', 'aenimfields' )
			);
		}

		$class = Registry::get( $args['type'] );

		if ( ! class_exists( $class ) ) {
			throw new InvalidArgumentException(
				sprintf(
					__( 'Field type "%s" is not registered.', 'aenimfields' ),
					$args['type']
				)
			);
		}

		$field = new $class( $args );

		if ( ! $field instanceof FieldInterface ) {
			throw new InvalidArgumentException(
				sprintf(
					__( '"%s" must implement FieldInterface.', 'aenimfields' ),
					$class
				)
			);
		}

		return $field;
	}
}
