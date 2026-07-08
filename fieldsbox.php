<?php
/**
 * Fieldsbox - shared meta fields & frontend fields toolkit for custom plugins.
 *
 * This is not a standalone WordPress plugin. Require it once from any plugin
 * that needs fields:
 *
 *   if (! class_exists('Fieldsbox\Fieldsbox', false)) {
 *       require_once WP_PLUGIN_DIR . '/fieldsbox/fieldsbox.php';
 *   }
 *
 * Multiple plugins can safely require this file - only the first load runs.
 *
 * The ", false" matters if you also install this package via Composer
 * (e.g. a "path" repository pointing at a local checkout): class_exists()
 * autoloads by default, so a plain class_exists('Fieldsbox\Fieldsbox')
 * would let Composer's own PSR-4 autoloader silently satisfy the check
 * straight from src/Fieldsbox.php, skipping this file (and the require
 * below) entirely. src/Fieldsbox.php self-boots regardless (see its own
 * doc comment) specifically so that scenario still works, but requiring
 * this file explicitly is still the documented, reliable way to load
 * fieldsbox and is required for the autoloader below to work for the rest
 * of this package's classes when Composer isn't in the picture.
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

// Boot the shared runtime (asset registration, etc.). src/Fieldsbox.php
// also self-boots the moment it's loaded by any autoloader, so this call
// is what makes it happen promptly on the "no Composer" path rather than
// waiting for something else to first reference a Fieldsbox\* class.
\Fieldsbox\Fieldsbox::init();
