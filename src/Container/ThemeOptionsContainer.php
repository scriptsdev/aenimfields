<?php

namespace Fieldsbox\Container;

use Fieldsbox\Field\Field;

/**
 * Fields shown on a dedicated wp-admin settings page, persisted as a single
 * serialized option.
 *
 * Usage:
 *   ThemeOptionsContainer::make('My Plugin Settings')
 *       ->set_menu('My Plugin')
 *       ->add_fields([...]);
 */
class ThemeOptionsContainer extends Container
{
    protected string $menu_title;

    /** wp-admin menu slug and option name prefix; derived from the container id. */
    protected string $menu_slug;

    protected string $capability = 'manage_options';

    /** Parent menu slug to nest under, or null for a top-level menu item. */
    protected ?string $parent_slug = null;

    protected string $icon = 'dashicons-admin-generic';

    protected int $position = 100;

    /**
     * The hook suffix WordPress assigns this settings page, captured from
     * add_menu_page()/add_submenu_page()'s return value so matches_screen()
     * can recognize it later.
     */
    protected string|false|null $hook_suffix = null;

    /** Cached option values, lazily loaded by load_values(). */
    protected array $values = [];
    protected bool $values_loaded = false;

    /**
     * Register the WordPress hooks that add the settings page and persist it.
     */
    protected function boot(): void
    {
        $this->menu_title = $this->title;
        $this->menu_slug = $this->id;

        add_action('admin_menu', [$this, 'register_page']);
        add_action('admin_init', [$this, 'maybe_save']);
    }

    /**
     * Configure where the settings page appears in wp-admin.
     *
     * @param string|null $parent_slug Pass an existing admin menu slug (e.g. 'options-general.php')
     *                                 to nest this as a submenu instead of a top-level menu item.
     */
    public function set_menu(string $menu_title, ?string $parent_slug = null, string $capability = 'manage_options'): static
    {
        $this->menu_title = $menu_title;
        $this->parent_slug = $parent_slug;
        $this->capability = $capability;

        return $this;
    }

    public function set_icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function set_position(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    /**
     * admin_menu callback - registers either a top-level or submenu page
     * depending on whether a parent slug was set via set_menu().
     */
    public function register_page(): void
    {
        if ($this->parent_slug) {
            $this->hook_suffix = add_submenu_page($this->parent_slug, $this->title, $this->menu_title, $this->capability, $this->menu_slug, [$this, 'render_page']);
        } else {
            $this->hook_suffix = add_menu_page($this->title, $this->menu_title, $this->capability, $this->menu_slug, [$this, 'render_page'], $this->icon, $this->position);
        }
    }

    /**
     * Settings page render callback: wraps the container's field markup in
     * a standard settings form with a nonce and submit button.
     */
    public function render_page(): void
    {
        ?>
        <div class="wrap fieldsbox-theme-options">
            <h1><?php echo esc_html($this->title); ?></h1>
            <form method="post">
                <?php wp_nonce_field('fieldsbox_' . $this->id, 'fieldsbox_nonce_' . $this->id); ?>
                <?php echo $this->render(); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * admin_init callback. Runs on every wp-admin request, but only writes
     * anything when this container's own nonce is present and valid - so
     * it's safe alongside other plugins' admin_init handlers.
     */
    public function maybe_save(): void
    {
        $nonce_key = 'fieldsbox_nonce_' . $this->id;

        if (! isset($_POST[$nonce_key]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])), 'fieldsbox_' . $this->id)) {
            return;
        }

        if (! current_user_can($this->capability)) {
            return;
        }

        $values = [];

        foreach ($this->get_all_fields() as $field) {
            $name = $field->get_name();
            // Multiselect fields submit nothing when empty, so default to [] rather than ''.
            $raw = $_POST[$name] ?? ($field->get_type() === 'multiselect' ? [] : '');
            // Storage key can differ from the submitted form field name -
            // see Field::set_meta_key().
            $values[$field->get_meta_key()] = $field->sanitize(wp_unslash($raw));
        }

        update_option($this->menu_slug . '_options', $values);

        // Update the in-memory cache too, so get_value() reflects the just-saved
        // values immediately when render_page() runs later in the same request.
        $this->values = $values;
        $this->values_loaded = true;
    }

    /**
     * Load the stored option array into memory once per request.
     */
    protected function load_values(): void
    {
        if (! $this->values_loaded) {
            $this->values = get_option($this->menu_slug . '_options', []);
            $this->values_loaded = true;
        }
    }

    public function get_value(Field $field): mixed
    {
        $this->load_values();

        return $this->values[$field->get_meta_key()] ?? null;
    }

    /**
     * This settings page only ever renders on its own admin page - admin_menu
     * (where $hook_suffix is captured) always fires before admin_enqueue_scripts,
     * so it's already set by the time this runs.
     */
    public function matches_screen(string|false|null $hook_suffix): bool
    {
        return $hook_suffix !== null && $hook_suffix !== false && $hook_suffix === $this->hook_suffix;
    }
}
