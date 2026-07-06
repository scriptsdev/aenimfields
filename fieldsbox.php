<?php
/**
 * Fieldsbox - shared meta fields & frontend fields toolkit for custom plugins.
 *
 * This is not a standalone WordPress plugin. Require it once from any plugin
 * that needs fields:
 *
 *   if (! class_exists('Fieldsbox\Fieldsbox')) {
 *       require_once WP_PLUGIN_DIR . '/fieldsbox/fieldsbox.php';
 *   }
 *
 * Multiple plugins can safely require this file - only the first load runs.
 */

// Block direct access - this file must only run inside a WordPress request.
if (! defined('ABSPATH')) {
    exit;
}

// Guard against being required more than once (e.g. by several plugins).
if (defined('FIELDSBOX_LOADED')) {
    return;
}

define('FIELDSBOX_LOADED', true);
define('FIELDSBOX_DIR', __DIR__);
define('FIELDSBOX_URL', plugins_url('', __FILE__));
define('FIELDSBOX_VERSION', '0.1.0');

// Lightweight PSR-4-style autoloader so consuming plugins don't need to run
// `composer install` just to pull in this package's classes.
spl_autoload_register(function (string $class): void {
    $prefix = 'Fieldsbox\\';

    // Ignore any class outside our namespace.
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // Fieldsbox\Container\PostMetaContainer -> src/Container/PostMetaContainer.php
    $relative = substr($class, strlen($prefix));
    $path = FIELDSBOX_DIR . '/src/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($path)) {
        require $path;
    }
});

// Boot the shared runtime (asset registration, etc.).
\Fieldsbox\Fieldsbox::init();
