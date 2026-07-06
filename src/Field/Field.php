<?php

namespace Fieldsbox\Field;

use Fieldsbox\Field\Types\CheckboxField;
use Fieldsbox\Field\Types\MultiSelectField;
use Fieldsbox\Field\Types\RadioField;
use Fieldsbox\Field\Types\SelectField;
use Fieldsbox\Field\Types\TextareaField;
use Fieldsbox\Field\Types\TextField;
use InvalidArgumentException;

/**
 * Base class for every field type (text, select, radio, ...).
 *
 * Concrete field types only need to implement render_input() to draw their
 * own control; everything else (label, wrapper markup, help text,
 * conditional-logic data attribute, sanitization default) is handled here
 * so new field types stay tiny. Fields are configured through a fluent
 * builder API, e.g.:
 *
 *   Field::make('text', 'subtitle', 'Subtitle')
 *       ->set_required()
 *       ->set_help_text('Shown under the title.');
 */
abstract class Field
{
    /**
     * Maps a public field type string (as passed to Field::make()) to the
     * concrete class that implements it. 'dropdown' is an alias for
     * 'select' since both render a native <select>.
     */
    protected static array $type_map = [
        'text' => TextField::class,
        'textarea' => TextareaField::class,
        'radio' => RadioField::class,
        'checkbox' => CheckboxField::class,
        'select' => SelectField::class,
        'dropdown' => SelectField::class,
        'multiselect' => MultiSelectField::class,
    ];

    protected string $type;
    protected string $name;
    protected string $label;
    protected string $help_text = '';
    protected mixed $default_value = '';

    /** Choices for radio/select/multiselect fields, as value => label. */
    protected array $options = [];

    /** Extra raw HTML attributes (e.g. placeholder, maxlength) merged onto the input. */
    protected array $attributes = [];

    /** Extra CSS classes added to the field's wrapper <div>. */
    protected array $classes = [];

    protected bool $required = false;

    /** @var array<int, array{field: string, value: mixed, operator?: string}>|null */
    protected ?array $conditional_logic = null;

    /** 'AND' (every rule must match) or 'OR' (any rule matches) - evaluated client-side. */
    protected string $conditional_relation = 'AND';

    /**
     * Constructor is not called directly - use the static make() factory so
     * the correct field-type subclass gets instantiated.
     */
    protected function __construct(string $type, string $name, string $label)
    {
        $this->type = $type;
        $this->name = $name;
        // Auto-generate a human label from the field name when none is given.
        $this->label = $label !== '' ? $label : ucwords(str_replace(['_', '-'], ' ', $name));
    }

    /**
     * Create a field of the given type.
     *
     * @throws InvalidArgumentException if $type isn't registered in $type_map.
     */
    public static function make(string $type, string $name, string $label = ''): static
    {
        $class = self::$type_map[$type] ?? null;

        if (! $class) {
            throw new InvalidArgumentException("Fieldsbox: unknown field type \"{$type}\".");
        }

        return new $class($type, $name, $label);
    }

    public function set_label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function set_default_value(mixed $value): static
    {
        $this->default_value = $value;
        return $this;
    }

    public function set_help_text(string $text): static
    {
        $this->help_text = $text;
        return $this;
    }

    public function set_required(bool $required = true): static
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set the value => label choices for radio/select/multiselect fields.
     */
    public function set_options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function add_class(string $class): static
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     * Attach an arbitrary HTML attribute (e.g. placeholder, maxlength,
     * data-* ) to the rendered input element.
     */
    public function set_attribute(string $key, string $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Show/hide this field in the browser based on the value of other
     * fields in the same container. Evaluated entirely client-side by
     * assets/js/fieldsbox.js - this only stores the rule set as a
     * data-conditional JSON attribute on the field wrapper.
     *
     * @param array<int, array{field: string, value: mixed, operator?: string}> $rules
     * @param string $relation 'AND' or 'OR'
     */
    public function set_conditional_logic(array $rules, string $relation = 'AND'): static
    {
        $this->conditional_logic = $rules;
        $this->conditional_relation = $relation;
        return $this;
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_type(): string
    {
        return $this->type;
    }

    /**
     * Clean a raw submitted value before it is persisted. Field types that
     * store something other than plain text (textarea, multiselect, ...)
     * override this with the appropriate WordPress sanitizer.
     */
    public function sanitize(mixed $value): mixed
    {
        return sanitize_text_field((string) $value);
    }

    /**
     * Render just the <input>/<select>/... control itself (no label/wrapper).
     * Implemented by each concrete field type.
     */
    abstract protected function render_input(mixed $value): string;

    /**
     * Render the full field markup: wrapper div (carrying the conditional-
     * logic data attribute), label, the control from render_input(), and
     * optional help text.
     */
    public function render(mixed $value): string
    {
        // Fall back to the configured default when there's no stored value yet.
        if ($value === null || $value === '') {
            $value = $this->default_value;
        }

        $wrapper_classes = array_merge(['fieldsbox-field', 'fieldsbox-field-' . $this->type], $this->classes);

        // Encode the conditional-logic rules as JSON so the JS engine can
        // read them straight off the DOM node without another data source.
        $data_conditional = '';
        if ($this->conditional_logic) {
            $data_conditional = sprintf(
                ' data-conditional="%s"',
                esc_attr((string) wp_json_encode([
                    'relation' => $this->conditional_relation,
                    'rules' => $this->conditional_logic,
                ]))
            );
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>"<?php echo $data_conditional; ?> data-field-name="<?php echo esc_attr($this->name); ?>">
            <label class="fieldsbox-label" for="fieldsbox-<?php echo esc_attr($this->name); ?>">
                <?php echo esc_html($this->label); ?>
                <?php if ($this->required): ?><span class="fieldsbox-required">*</span><?php endif; ?>
            </label>
            <div class="fieldsbox-input">
                <?php echo $this->render_input($value); ?>
                <?php if ($this->help_text !== ''): ?>
                    <p class="fieldsbox-help"><?php echo esc_html($this->help_text); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Turn the $attributes array into an HTML attribute string (with a
     * leading space before each pair) for concrete field types to splice
     * into their <input>/<select> tag.
     */
    protected function render_attributes(): string
    {
        $html = '';

        foreach ($this->attributes as $key => $value) {
            $html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }

        return $html;
    }
}
