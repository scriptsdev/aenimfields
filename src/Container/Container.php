<?php

namespace Fieldsbox\Container;

use Fieldsbox\Field\Field;
use Fieldsbox\Fieldsbox;

/**
 * Base class for anything that groups fields together and persists their
 * values somewhere (post meta, a theme options page, ...).
 *
 * Subclasses only need to implement boot() (register the WordPress hooks
 * that render/save the container) and get_value() (read back a single
 * field's stored value). Field grouping, tab rendering, and HTML wrapper
 * markup are shared here.
 */
abstract class Container
{
    /**
     * Every container created this request, shared across all subclasses.
     * Walked by Fieldsbox::enqueue_assets() to work out what's needed.
     *
     * @var Container[]
     */
    protected static array $registry = [];

    /** Unique per-instance id, used for meta box id / menu slug / option name / DOM ids. */
    protected string $id;

    protected string $title;

    /** Fields shown directly in the container, outside of any tab. */
    /** @var array<string, Field> */
    protected array $fields = [];

    /** Tabs in add_tab() call order, keyed by label.
     * @var array<string, array{icon: string, description: string, fields: array<string, Field>}> */
    protected array $tabs = [];

    /** Extra CSS classes added to the container's wrapper <div>. */
    protected array $classes = [];

    /**
     * Not called directly - use the static make() factory.
     */
    protected function __construct(string $title)
    {
        $this->title = $title;
        // Derived from the title, not random, so it's stable between the
        // request that renders the form and the request that submits it -
        // the nonce, menu slug, and option name all depend on it matching.
        $this->id = sanitize_key($title);
    }

    /**
     * Create a container and defer boot()'s hook registration until
     * wp_loaded.
     *
     * This is deferred - rather than called synchronously here, right after
     * construction - because a subclass's boot() can bake a configurable
     * property directly into a hook name (TermMetaContainer registers
     * "{$taxonomy}_add_form_fields", derived from $this->taxonomies).
     * Calling boot() immediately would lock in whatever that property
     * defaults to, before the rest of the fluent chain
     * (->show_on_taxonomy(), ->add_fields(), ...) - which runs synchronously
     * right after make() returns, in the same call stack - has a chance to
     * configure it.
     *
     * wp_loaded specifically, not init: it fires exactly once, strictly
     * after every plugin's own init callbacks have already completed - so
     * this stays correct even when a consuming plugin calls
     * Container::make() from inside its own init hook (a common pattern
     * alongside register_taxonomy()). Re-adding a callback to init at an
     * earlier priority than one already executing would silently never
     * fire again that request - WP_Hook does not rewind to a priority it
     * has already passed. wp_loaded can't have "already passed" in that
     * scenario, since it only fires after init (all priorities) finishes.
     * The did_action() guard below covers the rarer case of make() being
     * called even later than that (e.g. from an admin_menu callback) - by
     * then the fluent chain has necessarily already finished too, so
     * booting immediately is just as safe as deferring would have been.
     */
    public static function make(string $title): static
    {
        // Ensures Fieldsbox's own hooks are registered even though
        // consuming plugins normally only ever touch Container/Field, never
        // Fieldsbox itself directly. Idempotent.
        Fieldsbox::init();

        $container = new static($title);

        if (did_action('wp_loaded')) {
            $container->boot();
        } else {
            add_action('wp_loaded', [$container, 'boot']);
        }

        self::$registry[] = $container;

        return $container;
    }

    /**
     * @return Container[]
     */
    public static function get_registry(): array
    {
        return self::$registry;
    }

    /**
     * Add fields to the container's top-level (non-tabbed) area.
     *
     * @param Field[] $fields
     */
    public function add_fields(array $fields): static
    {
        foreach ($fields as $field) {
            $this->fields[$field->get_name()] = $field;
        }

        return $this;
    }

    /**
     * Add a tab containing the given fields. Tabs render as a nav strip
     * with the first tab active by default; see assets/js/fieldsbox.js for
     * the click-to-switch behaviour.
     *
     * $icon is an arbitrary icon font class rendered as <i class="$icon">
     * before the title (e.g. a dashicon like 'dashicons dashicons-admin-generic',
     * or any icon font class your own plugin already loads) - fieldsbox
     * doesn't ship or assume any particular icon font. $description renders
     * as a second line under the title.
     *
     * @param Field[] $fields
     */
    public function add_tab(string $label, array $fields, string $icon = '', string $description = ''): static
    {
        $indexed = [];

        foreach ($fields as $field) {
            $indexed[$field->get_name()] = $field;
        }

        $this->tabs[$label] = [
            'icon' => $icon,
            'description' => $description,
            'fields' => $indexed,
        ];

        return $this;
    }

    /**
     * Add a CSS class to the container's wrapper <div>. Can be called more
     * than once to add several classes.
     */
    public function add_class(string $class): static
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     * Flatten top-level and tabbed fields into a single list, e.g. for
     * looping over every field when saving submitted values.
     *
     * @return Field[]
     */
    protected function get_all_fields(): array
    {
        $all = $this->fields;

        foreach ($this->tabs as $tab) {
            $all += $tab['fields'];
        }

        return $all;
    }

    /**
     * Register whatever WordPress hooks are needed to render and save this
     * container (e.g. add_meta_boxes/save_post, or admin_menu/admin_init).
     * Public rather than protected: make() registers it as a wp_loaded
     * callback (or calls it directly), both from outside the class.
     */
    abstract public function boot(): void;

    /**
     * Read back the currently stored value for a field, or null if unset.
     * Takes the Field itself so implementations can honor a per-field
     * set_meta_key() storage-key override.
     */
    abstract public function get_value(Field $field): mixed;

    /**
     * Whether this container renders on the current screen - used by
     * Fieldsbox::enqueue_assets() to work out which assets are needed.
     *
     * @param string|false|null $hook_suffix The $hook_suffix admin_enqueue_scripts was called with.
     */
    abstract public function matches_screen(string|false|null $hook_suffix): bool;

    /**
     * Whether any field in this container - including sub-fields nested
     * inside a group/repeater - is of the given type (e.g. 'map', 'image').
     */
    public function uses_field_type(string $type): bool
    {
        return self::fields_use_type($this->get_all_fields(), $type);
    }

    /**
     * @param Field[] $fields
     */
    private static function fields_use_type(array $fields, string $type): bool
    {
        foreach ($fields as $field) {
            if ($field->get_type() === $type || self::fields_use_type($field->get_sub_fields(), $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render a set of fields, resolving each one's current value via
     * get_value() as it goes.
     *
     * @param Field[] $fields
     */
    protected function render_fields(array $fields): string
    {
        $html = '';

        foreach ($fields as $field) {
            $html .= $field->render($this->get_value($field));
        }

        return $html;
    }

    /**
     * Render the full container: top-level fields first, then (if any
     * tabs were added) a tab nav strip and one panel per tab.
     */
    protected function render(): string
    {
        $wrapper_classes = array_merge(['fieldsbox-container'], $this->classes);

        $html = sprintf(
            '<div class="%1$s" id="fieldsbox-container-%2$s">',
            esc_attr(implode(' ', $wrapper_classes)),
            esc_attr($this->id)
        );

        if ($this->fields) {
            $html .= $this->render_fields($this->fields);
        }

        if ($this->tabs) {
            $html .= '<div class="fieldsbox-tabs">';
            $html .= '<nav class="fieldsbox-tabs-nav">';

            // First pass: one nav button per tab, first one marked active.
            $index = 0;
            foreach ($this->tabs as $label => $tab) {
                $html .= sprintf(
                    '<button type="button" class="fieldsbox-tab-link%s" data-tab="%s-%d">',
                    $index === 0 ? ' is-active' : '',
                    esc_attr($this->id),
                    $index
                );
                if ($tab['icon'] !== '') {
                    $html .= sprintf('<i class="fieldsbox-tab-icon %s"></i>', esc_attr($tab['icon']));
                }
                $html .= '<span class="fieldsbox-tab-content">';
                $html .= sprintf('<span class="fieldsbox-tab-title">%s</span>', esc_html($label));
                if ($tab['description'] !== '') {
                    $html .= sprintf('<span class="fieldsbox-tab-description">%s</span>', esc_html($tab['description']));
                }
                $html .= '</span>';
                $html .= '</button>';
                $index++;
            }

            $html .= '</nav>';

            // Second pass: matching panel per tab, keyed by the same
            // "{container-id}-{index}" used in data-tab above so the JS
            // can wire nav clicks to the right panel.
            $index = 0;
            foreach ($this->tabs as $label => $tab) {
                $html .= sprintf(
                    '<div class="fieldsbox-tab-panel%s" data-tab-panel="%s-%d">',
                    $index === 0 ? ' is-active' : '',
                    esc_attr($this->id),
                    $index
                );
                $html .= $this->render_fields($tab['fields']);
                $html .= '</div>';
                $index++;
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
