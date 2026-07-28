<?php

declare(strict_types=1);

/**
 * Example: Repeater + Dependency
 *
 * Pending. This example is meant to demonstrate:
 *
 * - A `repeater` field (ScriptsDev\FieldsBox\Fields\Repeater) rendering a
 *   dynamic, add/remove list of field groups.
 * - Conditional fields via the `dependency` argument, evaluated by
 *   ScriptsDev\FieldsBox\Core\DependencyEngine and the ScriptsDev\FieldsBox\Dependency\*
 *   classes, e.g. showing a field only when another field has a
 *   given value.
 *
 * Neither is implemented yet:
 *
 * - src/Fields/Repeater.php and src/Fields/Group.php are empty stubs.
 *   Both types are registered in Application::register_fields(), but
 *   have no class body, so instantiating either currently throws.
 * - src/Core/DependencyEngine.php and src/Dependency/{Condition,
 *   GroupCondition,Operators}.php are empty. The `dependency` argument
 *   already exists in BaseField::$defaults, but nothing reads it yet.
 * - assets/js/repeater.js and assets/js/dependency.js are empty, so
 *   there is no client-side add/remove or show/hide behavior either.
 *
 * See examples/metabox.php, examples/settings-page.php, and
 * examples/frontend-form.php for working examples using the field
 * types, sanitization, and validation that are implemented today.
 */

defined( 'ABSPATH' ) || exit;
