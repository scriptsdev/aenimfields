<?php

namespace Fieldsbox\Container;

use Fieldsbox\Field\Field;

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
     * Every container instance created this request - not redeclared in
     * PostMetaContainer/ThemeOptionsContainer, so this storage is shared
     * across both. Fieldsbox::enqueue_assets() walks this to work out
     * which of its assets the current screen actually needs.
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

    /** Fields grouped under a tab label, in add_tab() call order. */
    /** @var array<string, array<string, Field>> */
    protected array $tabs = [];

    /**
     * Not called directly - use the static make() factory.
     */
    protected function __construct(string $title)
    {
        $this->title = $title;
        // Must be deterministic across requests, NOT random per-request:
        // PHP re-runs the plugin's whole bootstrap (and therefore this
        // constructor) fresh on every request, so the id generated while
        // rendering the edit-post/settings page has to match the id
        // generated again when that form is submitted - it's what the
        // nonce action/field name, ThemeOptionsContainer's menu slug, and
        // its "{id}_options" option name are all built from. Two
        // containers sharing an identical title (e.g. across two
        // differently-scoped plugins) is the same "give it a unique
        // name" constraint every add_menu_page()/add_meta_box() call in
        // WordPress already has - not something to paper over with
        // per-request randomness.
        $this->id = sanitize_key($title);
    }

    /**
     * Create and boot a container. boot() is called immediately so the
     * container's WordPress hooks are registered as soon as it's declared.
     */
    public static function make(string $title): static
    {
        $container = new static($title);
        $container->boot();

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
     * @param Field[] $fields
     */
    public function add_tab(string $label, array $fields): static
    {
        $indexed = [];

        foreach ($fields as $field) {
            $indexed[$field->get_name()] = $field;
        }

        $this->tabs[$label] = $indexed;

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

        foreach ($this->tabs as $tab_fields) {
            $all += $tab_fields;
        }

        return $all;
    }

    /**
     * Register whatever WordPress hooks are needed to render and save this
     * container (e.g. add_meta_boxes/save_post, or admin_menu/admin_init).
     */
    abstract protected function boot(): void;

    /**
     * Read back the currently stored value for a single field, or null if
     * there isn't one yet (the field will then fall back to its default).
     * Takes the Field itself (not just its name) so implementations can
     * honor a per-field set_meta_key() storage-key override.
     */
    abstract public function get_value(Field $field): mixed;

    /**
     * Whether this container would actually render on the screen currently
     * being prepared - used by Fieldsbox::enqueue_assets() to decide
     * whether this container's field types should count towards this
     * request's asset needs. Implemented per container type:
     * PostMetaContainer checks the edited post's type, ThemeOptionsContainer
     * checks its own settings page.
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
        $html = sprintf('<div class="fieldsbox-container" id="fieldsbox-container-%s">', esc_attr($this->id));

        if ($this->fields) {
            $html .= $this->render_fields($this->fields);
        }

        if ($this->tabs) {
            $html .= '<div class="fieldsbox-tabs">';
            $html .= '<nav class="fieldsbox-tabs-nav">';

            // First pass: one nav button per tab, first one marked active.
            $index = 0;
            foreach ($this->tabs as $label => $tab_fields) {
                $html .= sprintf(
                    '<button type="button" class="fieldsbox-tab-link%s" data-tab="%s-%d">%s</button>',
                    $index === 0 ? ' is-active' : '',
                    esc_attr($this->id),
                    $index,
                    esc_html($label)
                );
                $index++;
            }

            $html .= '</nav>';

            // Second pass: matching panel per tab, keyed by the same
            // "{container-id}-{index}" used in data-tab above so the JS
            // can wire nav clicks to the right panel.
            $index = 0;
            foreach ($this->tabs as $label => $tab_fields) {
                $html .= sprintf(
                    '<div class="fieldsbox-tab-panel%s" data-tab-panel="%s-%d">',
                    $index === 0 ? ' is-active' : '',
                    esc_attr($this->id),
                    $index
                );
                $html .= $this->render_fields($tab_fields);
                $html .= '</div>';
                $index++;
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
