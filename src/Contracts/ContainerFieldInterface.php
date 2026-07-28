<?php

declare(strict_types=1);

namespace ScriptsDev\FieldsBox\Contracts;

/**
 * Defines the contract for fields that can contain child fields.
 *
 * Example:
 * - Repeater
 * - Group
 *
 * @since 1.0.0
 */
interface ContainerFieldInterface {

	/**
	 * Returns all child fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_fields(): array;

	/**
	 * Set child fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 *
	 * @return static
	 */
	public function set_fields( array $fields );

	/**
	 * Add a child field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field
	 *
	 * @return static
	 */
	public function add_field( array $field );

	/**
	 * Determine whether the container has child fields.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fields(): bool;
}
