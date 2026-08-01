<?php

declare(strict_types=1);

namespace AenimTech\AenimFields\Core;

use AenimTech\AenimFields\Contracts\FieldInterface;
use AenimTech\AenimFields\Validation\Rules\Email;
use AenimTech\AenimFields\Validation\Rules\Max;
use AenimTech\AenimFields\Validation\Rules\Min;
use AenimTech\AenimFields\Validation\Rules\Numeric;
use AenimTech\AenimFields\Validation\Rules\Regex;
use AenimTech\AenimFields\Validation\Rules\Required;
use AenimTech\AenimFields\Validation\Rules\Url;
use AenimTech\AenimFields\Validation\ValidationResult;

/**
 * Validator class.
 *
 * Runs a field's own validate() method, then any additional rules
 * declared in its `validate` argument, e.g. `'validate' => ['required', 'min:3']`.
 *
 * @since 1.0.0
 */
class Validator {

	/**
	 * Rule name to rule class map.
	 *
	 * @var array<string, string>
	 */
	protected static array $rules = array(
		'required' => Required::class,
		'email'    => Email::class,
		'url'      => Url::class,
		'numeric'  => Numeric::class,
		'min'      => Min::class,
		'max'      => Max::class,
		'regex'    => Regex::class,
	);

	/**
	 * Validate a field's value.
	 *
	 * @since 1.0.0
	 *
	 * @param FieldInterface $field
	 * @param mixed          $value
	 *
	 * @return ValidationResult
	 */
	public static function validate( FieldInterface $field, $value ): ValidationResult {

		$result = $field->validate( $value );

		if ( true !== $result ) {
			return ValidationResult::failure( (string) $result );
		}

		$label = (string) $field->get_arg( 'label', $field->get_name() );

		foreach ( (array) $field->get_arg( 'validate' ) as $rule ) {

			[ $name, $parameter ] = self::parse_rule( (string) $rule );

			if ( ! isset( self::$rules[ $name ] ) ) {
				continue;
			}

			$class  = self::$rules[ $name ];
			$result = $class::validate( $value, $parameter, $label );

			if ( true !== $result ) {
				return ValidationResult::failure( (string) $result );
			}
		}

		return ValidationResult::success();
	}

	/**
	 * Parse a "rule:parameter" string into its parts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $rule
	 *
	 * @return array{0: string, 1: string|null}
	 */
	protected static function parse_rule( string $rule ): array {
		[ $name, $parameter ] = array_pad( explode( ':', $rule, 2 ), 2, null );

		return array( strtolower( trim( (string) $name ) ), $parameter );
	}
}
