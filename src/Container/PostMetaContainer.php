<?php

namespace Fieldsbox\Container;

use Fieldsbox\Field\Field;
use WP_Post;

/**
 * Fields shown as a meta box on the post edit screen, persisted as post
 * meta on save.
 *
 * Usage:
 *   PostMetaContainer::make('Product Details')
 *       ->show_on_post_type('product')
 *       ->add_fields([...]);
 */
class PostMetaContainer extends Container
{
    /** Post types the meta box appears on. Defaults to 'post'. */
    /** @var string[] */
    protected array $post_types = ['post'];

    /** Meta box placement: 'normal', 'side', or 'advanced'. */
    protected string $context = 'normal';

    /** Meta box ordering within its context: 'high', 'core', 'default', or 'low'. */
    protected string $priority = 'default';

    /** Post currently being edited, set by render_meta_box() for get_value() to use. */
    protected ?WP_Post $current_post = null;

    public function show_on_post_type(string ...$post_types): static
    {
        $this->post_types = $post_types;
        return $this;
    }

    public function set_context(string $context): static
    {
        $this->context = $context;
        return $this;
    }

    public function set_priority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Register the WordPress hooks that draw the meta box and persist it.
     */
    public function boot(): void
    {
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post', [$this, 'save']);
    }

    /**
     * add_meta_boxes callback - registers one meta box per configured post type.
     */
    public function register_meta_box(): void
    {
        foreach ($this->post_types as $post_type) {
            add_meta_box($this->id, $this->title, [$this, 'render_meta_box'], $post_type, $this->context, $this->priority);
        }
    }

    /**
     * Meta box render callback. Outputs a nonce (checked in save()) plus
     * the container's field markup.
     */
    public function render_meta_box(WP_Post $post): void
    {
        // Stashed so get_value() knows which post to read meta from.
        $this->current_post = $post;

        wp_nonce_field('fieldsbox_' . $this->id, 'fieldsbox_nonce_' . $this->id);
        echo $this->render();
    }

    /**
     * save_post callback. Validates the request (nonce, autosave,
     * capability, matching post type) before writing each field's
     * sanitized value to post meta.
     */
    public function save(int $post_id): void
    {
        $nonce_key = 'fieldsbox_nonce_' . $this->id;

        if (! isset($_POST[$nonce_key]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_key])), 'fieldsbox_' . $this->id)) {
            return;
        }

        // Autosaves happen without the full form submission - skip them.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // save_post fires for every post type; only act on our own meta box's targets.
        if (! in_array(get_post_type($post_id), $this->post_types, true)) {
            return;
        }

        foreach ($this->get_all_fields() as $field) {
            $name = $field->get_name();
            // Multiselect fields submit nothing when empty, so default to [] rather than ''.
            $raw = $_POST[$name] ?? ($field->get_type() === 'multiselect' ? [] : '');
            $value = $field->sanitize(wp_unslash($raw));

            // Storage key can differ from the submitted form field name -
            // see Field::set_meta_key() (e.g. an underscore-prefixed,
            // "protected" post meta key).
            $meta_key = $field->get_meta_key();

            // Store an absent value as "no meta row" instead of an empty one.
            if ($value === '' || $value === []) {
                delete_post_meta($post_id, $meta_key);
            } else {
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }

    /**
     * Read a field's current value from post meta for the post being edited.
     */
    public function get_value(Field $field): mixed
    {
        if (! $this->current_post) {
            return null;
        }

        return get_post_meta($this->current_post->ID, $field->get_meta_key(), true);
    }

    /**
     * Meta boxes only ever render on the post editor screens, and only for
     * the post types this container was registered for.
     */
    public function matches_screen(string|false|null $hook_suffix): bool
    {
        if (! in_array($hook_suffix, ['post.php', 'post-new.php'], true)) {
            return false;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        return $screen && in_array($screen->post_type, $this->post_types, true);
    }
}
