<?php

namespace Fieldsbox;

/**
 * Package entry point.
 *
 * Boots shared, one-time behaviour (currently just asset registration) that
 * every Field/Container needs, regardless of how many containers a
 * consuming plugin registers.
 */
final class Fieldsbox
{
    /**
     * Prevents re-registering hooks if init() is somehow called more than
     * once (e.g. multiple plugins requiring fieldsbox.php).
     */
    private static bool $booted = false;

    /**
     * Wire up the package's WordPress hooks. Safe to call multiple times.
     */
    public static function init(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;

        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    /**
     * Register and enqueue the shared admin CSS/JS.
     *
     * Loaded on every wp-admin screen rather than conditionally per-screen,
     * since containers can be registered by any number of plugins and there
     * is no single hook point to detect "a fieldsbox container is present
     * on this screen" before enqueue time.
     */
    public static function enqueue_assets(): void
    {
        wp_enqueue_style('fieldsbox', FIELDSBOX_URL . '/assets/css/fieldsbox.css', [], FIELDSBOX_VERSION);
        wp_enqueue_script('fieldsbox', FIELDSBOX_URL . '/assets/js/fieldsbox.js', [], FIELDSBOX_VERSION, true);
    }
}
