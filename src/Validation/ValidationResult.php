<?php

declare(strict_types=1);

namespace FieldsBox\Validation;

/**
 * Validation result.
 *
 * Immutable value object returned by Core\Validator::validate().
 *
 * @since 1.0.0
 */
class ValidationResult {

	/**
	 * Whether validation passed.
	 *
	 * @var bool
	 */
	protected bool $passed;

	/**
	 * Error message when validation failed.
	 *
	 * @var string
	 */
	protected string $message;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $passed
	 * @param string $message
	 */
	protected function __construct( bool $passed, string $message = '' ) {
		$this->passed  = $passed;
		$this->message = $message;
	}

	/**
	 * Create a passing result.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public static function success(): self {
		return new self( true );
	}

	/**
	 * Create a failing result.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 *
	 * @return self
	 */
	public static function failure( string $message ): self {
		return new self( false, $message );
	}

	/**
	 * Whether validation passed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function passed(): bool {
		return $this->passed;
	}

	/**
	 * Get the error message.
	 *
	 * Empty string when validation passed.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function message(): string {
		return $this->message;
	}
}
