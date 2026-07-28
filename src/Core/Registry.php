<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Core;

use InvalidArgumentException;

/**
 * Registry class.
 *
 * Maps field type names to their handling class.
 *
 * @since 1.0.0
 */
class Registry {

	/**
	 * Registered field types.
	 *
	 * @var array<string, string>
	 */
	protected static array $fields = array();

	/**
	 * Register a field type.
	 *
	 * Example:
	 * Registry::register('text', Text::class);
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 * @param string $class
	 *
	 * @return void
	 */
	public static function register( string $type, string $class ): void {
		self::$fields[ strtolower( $type ) ] = $class;
	}

	/**
	 * Determine whether a field type is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function has( string $type ): bool {
		return isset( self::$fields[ strtolower( $type ) ] );
	}

	/**
	 * Get the field class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 *
	 * @return string
	 *
	 * @throws InvalidArgumentException
	 */
	public static function get( string $type ): string {
		$type = strtolower( $type );

		if ( ! self::has( $type ) ) {
			throw new InvalidArgumentException(
				sprintf(
					__( 'Field type "%s" is not registered.', 'fieldsbox' ),
					$type
				)
			);
		}

		return self::$fields[ $type ];
	}

	/**
	 * Get all registered field types.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string>
	 */
	public static function all(): array {
		return self::$fields;
	}

	/**
	 * Remove a registered field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 *
	 * @return void
	 */
	public static function unregister( string $type ): void {
		unset( self::$fields[ strtolower( $type ) ] );
	}
}
