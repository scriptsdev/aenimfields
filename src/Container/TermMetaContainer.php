<?php

namespace Fieldsbox\Container;

use Fieldsbox\Field\Field;
use WP_Term;

/**
 * Fields shown on a taxonomy term's "Add New Term" and "Edit Term" screens,
 * persisted as term meta.
 *
 * Usage:
 *   TermMetaContainer::make('Category Details')
 *       ->show_on_taxonomy('category')
 *       ->add_fields([...]);
 *
 * WordPress renders those two screens completely differently - "Add New
 * Term" (on edit-tags.php) is a plain form, "Edit Term" (on term.php) is a
 * <table class="form-table"> - so the two render callbacks below wrap the
 * same field set in different markup. Quick Edit (the inline row editor in
 * the term list table) is a separate AJAX flow that doesn't fire either
 * hook, so fields registered here won't appear there - save() also simply
 * no-ops for it, since its request never carries our nonce field.
 */
class TermMetaContainer extends Container
{
    /** Taxonomies this container appears on. Defaults to 'category'. */
    /** @var string[] */
    protected array $taxonomies = ['category'];

    /** Term currently being edited, set by render_edit_fields() for get_value() to use. Null on the "Add New Term" screen. */
    protected ?int $current_term_id = null;

    public function show_on_taxonomy(string ...$taxonomies): static
    {
        $this->taxonomies = $taxonomies;
        return $this;
    }

    /**
     * Register the WordPress hooks that draw the add/edit form fields and
     * persist them. Each hook name is taxonomy-specific (e.g.
     * "category_add_form_fields"), so this loops over every configured
     * taxonomy rather than registering once.
     */
    protected function boot(): void
    {
        foreach ($this->taxonomies as $taxonomy) {
            add_action("{$taxonomy}_add_form_fields", [$this, 'render_add_fields']);
            add_action("{$taxonomy}_edit_form_fields", [$this, 'render_edit_fields']);
            // Both fire with the same (term_id, tt_id) signature - one
            // method handles saving for new and edited terms alike.
            add_action("created_{$taxonomy}", [$this, 'save']);
            add_action("edited_{$taxonomy}", [$this, 'save']);
        }
    }

    /**
     * "{taxonomy}_add_form_fields" callback - there's no term yet, so
     * fields always render with their configured defaults, in a plain
     * (non-table) layout matching WP core's own add-term fields.
     */
    public function render_add_fields(): void
    {
        $this->current_term_id = null;

        wp_nonce_field('fieldsbox_' . $this->id, 'fieldsbox_nonce_' . $this->id);
        echo $this->render();
    }

    /**
     * "{taxonomy}_edit_form_fields" callback. WordPress wraps this hook's
     * output in an existing <table class="form-table">, so the field
     * markup (which is <div>-based, like every other container) is wrapped
     * in one spanning <tr><td> to stay valid HTML, rather than rendering
     * our own <th>/<td> per field to match WP's native two-column look.
     */
    public function render_edit_fields(WP_Term $term): void
    {
        $this->current_term_id = $term->term_id;

        echo '<tr class="form-field"><td colspan="2">';
        wp_nonce_field('fieldsbox_' . $this->id, 'fieldsbox_nonce_' . $this->id);
        echo $this->render();
        echo '</td></tr>';
    }

    /**
     * "created_{taxonomy}"/"edited_{taxonomy}" callback. Validates the
     * request (nonce, capability) before writing each field's sanitized
     * value to term meta.
     */
    public function save(int $term_id): void
    {
        $nonce_key = 'fieldsbox_nonce_' . $this->id;

        if (! isset($_POST[$nonce_key]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])), 'fieldsbox_' . $this->id)) {
            return;
        }

        if (! current_user_can('edit_term', $term_id)) {
            return;
        }

        foreach ($this->get_all_fields() as $field) {
            $name = $field->get_name();
            // Multiselect fields submit nothing when empty, so default to [] rather than ''.
            $raw = $_POST[$name] ?? ($field->get_type() === 'multiselect' ? [] : '');
            $value = $field->sanitize(wp_unslash($raw));

            // Storage key can differ from the submitted form field name -
            // see Field::set_meta_key().
            $meta_key = $field->get_meta_key();

            // Store an absent value as "no meta row" instead of an empty one.
            if ($value === '' || $value === []) {
                delete_term_meta($term_id, $meta_key);
            } else {
                update_term_meta($term_id, $meta_key, $value);
            }
        }
    }

    /**
     * Read a field's current value from term meta for the term being
     * edited. Always null on the "Add New Term" screen (no term yet), so
     * fields fall back to their configured default.
     */
    public function get_value(Field $field): mixed
    {
        if (! $this->current_term_id) {
            return null;
        }

        return get_term_meta($this->current_term_id, $field->get_meta_key(), true);
    }

    /**
     * The term list screen (edit-tags.php, which hosts the "Add New Term"
     * form) and the single edit-term screen (term.php), for a taxonomy
     * this container was registered for.
     */
    public function matches_screen(string|false|null $hook_suffix): bool
    {
        if ($hook_suffix !== 'edit-tags.php' && $hook_suffix !== 'term.php') {
            return false;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        return $screen && in_array($screen->taxonomy, $this->taxonomies, true);
    }
}
