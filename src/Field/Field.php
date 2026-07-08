<?php

namespace Fieldsbox\Field;

use Fieldsbox\Field\Types\CheckboxField;
use Fieldsbox\Field\Types\CodeField;
use Fieldsbox\Field\Types\ColorField;
use Fieldsbox\Field\Types\DateField;
use Fieldsbox\Field\Types\DateTimeField;
use Fieldsbox\Field\Types\EmailField;
use Fieldsbox\Field\Types\FileField;
use Fieldsbox\Field\Types\GalleryField;
use Fieldsbox\Field\Types\GoogleMapField;
use Fieldsbox\Field\Types\GroupField;
use Fieldsbox\Field\Types\HiddenField;
use Fieldsbox\Field\Types\HtmlField;
use Fieldsbox\Field\Types\ImageField;
use Fieldsbox\Field\Types\MapField;
use Fieldsbox\Field\Types\MultiSelectField;
use Fieldsbox\Field\Types\NumberField;
use Fieldsbox\Field\Types\RadioField;
use Fieldsbox\Field\Types\RepeaterField;
use Fieldsbox\Field\Types\SelectField;
use Fieldsbox\Field\Types\SeparatorField;
use Fieldsbox\Field\Types\TextareaField;
use Fieldsbox\Field\Types\TextField;
use Fieldsbox\Field\Types\TimeField;
use Fieldsbox\Field\Types\ToggleField;
use Fieldsbox\Field\Types\UrlField;
use Fieldsbox\Field\Types\WysiwygField;
use Fieldsbox\Fieldsbox;
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
abstract class Field {

	/**
	 * Maps a public field type string (as passed to Field::make()) to the
	 * concrete class that implements it. 'dropdown' is an alias for
	 * 'select' since both render a native <select>.
	 */
	protected static array $type_map = array(
		'text'        => TextField::class,
		'textarea'    => TextareaField::class,
		'radio'       => RadioField::class,
		'checkbox'    => CheckboxField::class,
		'select'      => SelectField::class,
		'dropdown'    => SelectField::class,
		'multiselect' => MultiSelectField::class,
		'email'       => EmailField::class,
		'url'         => UrlField::class,
		'hidden'      => HiddenField::class,
		'number'      => NumberField::class,
		'toggle'      => ToggleField::class,
		'color'       => ColorField::class,
		'code'        => CodeField::class,
		'wysiwyg'     => WysiwygField::class,
		'image'       => ImageField::class,
		'file'        => FileField::class,
		'gallery'     => GalleryField::class,
		'map'         => MapField::class,
		'google_map'  => GoogleMapField::class,
		'group'       => GroupField::class,
		'repeater'    => RepeaterField::class,
		'date'        => DateField::class,
		'datetime'    => DateTimeField::class,
		'time'        => TimeField::class,
		'separator'   => SeparatorField::class,
		'html'        => HtmlField::class,
	);

	/** Guarantees every rendered field gets a unique id, even repeated instances (see get_html_id()). */
	protected static int $id_sequence = 0;

	protected string $type;
	protected string $name;
	protected string $label;
	protected string $help_text    = '';
	protected mixed $default_value = '';

	/** Choices for radio/select/multiselect fields, as value => label. */
	protected array $options = array();

	/** Extra raw HTML attributes (e.g. placeholder, maxlength) merged onto the input. */
	protected array $attributes = array();

	/** Extra CSS classes added to the field's wrapper <div>. */
	protected array $classes = array();

	protected bool $required = false;

	/**
	 * Rule field names may carry one or more leading "parent." segments to
	 * reach past the immediate group/repeater row scope - see
	 * set_conditional_logic().
	 *
	 * @var array<int, array{field: string, value?: mixed, compare?: string}>|null
	 */
	protected ?array $conditional_logic = null;

	/** 'AND' (every rule must match) or 'OR' (any rule matches) - evaluated client-side. */
	protected string $conditional_relation = 'AND';

	/**
	 * Overrides the HTML "name" (and therefore "id") this field renders
	 * with, without changing its logical get_name(). Used by GroupField and
	 * RepeaterField to nest a shared field blueprint under a parent name,
	 * e.g. "team_members[3][role]" instead of just "role".
	 */
	protected ?string $html_name = null;

	/** Lazily computed, invalidated whenever set_html_name() changes context. */
	protected ?string $cached_html_id = null;

	/** Explicit id override set via set_id(), takes priority over the auto-generated one. */
	protected ?string $custom_id = null;

	/** Storage-key override set via set_meta_key(); see get_meta_key(). */
	protected ?string $meta_key = null;

	/**
	 * Constructor is not called directly - use the static make() factory so
	 * the correct field-type subclass gets instantiated.
	 */
	protected function __construct( string $type, string $name, string $label ) {
		$this->type = $type;
		$this->name = $name;
		// Auto-generate a human label from the field name when none is given.
		$this->label = $label !== '' ? $label : ucwords( str_replace( array( '_', '-' ), ' ', $name ) );
	}

	/**
	 * Create a field of the given type.
	 *
	 * @throws InvalidArgumentException if $type isn't registered in $type_map.
	 */
	public static function make( string $type, string $name, string $label = '' ): static {
		// Mirrors the same call in Container::make(), in case a field is
		// ever built before any container. Idempotent.
		Fieldsbox::init();

		$class = self::$type_map[ $type ] ?? null;

		if ( ! $class ) {
			throw new InvalidArgumentException( "Fieldsbox: unknown field type \"{$type}\"." );
		}

		return new $class( $type, $name, $label );
	}

	public function set_label( string $label ): static {
		$this->label = $label;
		return $this;
	}

	public function set_default_value( mixed $value ): static {
		$this->default_value = $value;
		return $this;
	}

	public function set_help_text( string $text ): static {
		$this->help_text = $text;
		return $this;
	}

	public function set_required( bool $required = true ): static {
		$this->required = $required;
		return $this;
	}

	/**
	 * Set the value => label choices for radio/select/multiselect fields.
	 */
	public function set_options( array $options ): static {
		$this->options = $options;
		return $this;
	}

	/**
	 * The configured set_options() keys as strings - used by sanitize() in
	 * SelectField/RadioField/MultiSelectField to reject values outside the
	 * field's own choices.
	 *
	 * @return string[]
	 */
	protected function get_option_keys(): array {
		return array_map( 'strval', array_keys( $this->options ) );
	}

	/**
	 * Add a CSS class to the field's wrapper <div> (not the <input> itself -
	 * use set_attribute('class', ...) for that). Can be called more than
	 * once to add several classes.
	 */
	public function add_class( string $class ): static {
		$this->classes[] = $class;
		return $this;
	}

	/**
	 * Override the auto-generated "id" this field renders with (and the
	 * matching <label for>) - see get_html_id().
	 */
	public function set_id( string $id ): static {
		$this->custom_id = $id;
		return $this;
	}

	/**
	 * Attach an arbitrary HTML attribute (e.g. placeholder, maxlength,
	 * data-* ) to the rendered input element.
	 */
	public function set_attribute( string $key, string $value ): static {
		$this->attributes[ $key ] = $value;
		return $this;
	}

	/**
	 * Show/hide this field based on other fields' values. Evaluated
	 * client-side (assets/js/fieldsbox.js) via a data-conditional JSON
	 * attribute; storage here is just the rule set.
	 *
	 *   Field::make('text', 'crb_facebook', 'Facebook URL')
	 *       ->set_conditional_logic([
	 *           'relation' => 'AND', // or 'OR', defaults to 'AND'
	 *           [
	 *               'field' => 'crb_show_socials',
	 *               'value' => 'yes',  // defaults to ''
	 *               'compare' => '=',  // defaults to '='
	 *           ],
	 *       ]);
	 *
	 * `field` is scoped to the current field's siblings by default; prefix
	 * with "parent." per level to reach an enclosing group/repeater scope.
	 * `compare` is one of =, <, >, <=, >=, IN, NOT IN, INCLUDES, EXCLUDES
	 * (IN/NOT IN for single-value fields, INCLUDES/EXCLUDES for multi-value
	 * ones like multiselect).
	 *
	 * @param array<int|string, mixed> $conditions Optionally keyed 'relation', plus 0-indexed rule arrays.
	 */
	public function set_conditional_logic( array $conditions ): static {
		$relation = $conditions['relation'] ?? 'AND';
		unset( $conditions['relation'] );

		$this->conditional_relation = strtoupper( (string) $relation ) === 'OR' ? 'OR' : 'AND';
		$this->conditional_logic    = array_values( $conditions );

		return $this;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Override the key this field's value is stored under (post meta key,
	 * or the array key in a theme options page) without changing its
	 * logical get_name(). Useful for WordPress's "protected meta"
	 * convention, e.g. ->set_meta_key('_event_date') to hide it from the
	 * default Custom Fields box while the form itself still uses "event_date".
	 */
	public function set_meta_key( string $meta_key ): static {
		$this->meta_key = $meta_key;
		return $this;
	}

	/**
	 * The key to store/read this field's value under. Defaults to
	 * get_name() unless overridden via set_meta_key().
	 */
	public function get_meta_key(): string {
		return $this->meta_key ?? $this->name;
	}

	/**
	 * Nested fields this field wraps (GroupField/RepeaterField only). Used
	 * by Container::uses_field_type() to detect e.g. a map nested inside a
	 * repeater, so its assets still get enqueued.
	 *
	 * @return Field[]
	 */
	public function get_sub_fields(): array {
		return array();
	}

	/**
	 * Used by GroupField/RepeaterField to namespace a shared sub-field's
	 * HTML name (e.g. "members[3][email]") while get_name() keeps
	 * returning its own short name ("email") for value lookups.
	 */
	public function set_html_name( string $html_name ): static {
		$this->html_name      = $html_name;
		$this->cached_html_id = null;
		return $this;
	}

	/**
	 * Clean a raw submitted value before it is persisted. Field types that
	 * store something other than plain text (textarea, multiselect, ...)
	 * override this with the appropriate WordPress sanitizer.
	 */
	public function sanitize( mixed $value ): mixed {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Render just the <input>/<select>/... control itself (no label/wrapper).
	 * Implemented by each concrete field type.
	 */
	abstract protected function render_input( mixed $value ): string;

	/**
	 * Render the full field markup: wrapper div (carrying the conditional-
	 * logic data attribute), label, the control from render_input(), and
	 * optional help text.
	 */
	public function render( mixed $value ): string {
		if ( $value === null || $value === '' ) {
			$value = $this->default_value;
		}

		$wrapper_classes = array_merge( array( 'fieldsbox-field', 'fieldsbox-field-' . $this->type ), $this->classes );

		$data_conditional = '';
		if ( $this->conditional_logic ) {
			$data_conditional = sprintf(
				' data-conditional="%s"',
				esc_attr(
					(string) wp_json_encode(
						array(
							'relation' => $this->conditional_relation,
							'rules'    => $this->conditional_logic,
						)
					)
				)
			);
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"<?php echo $data_conditional; ?> data-field-name="<?php echo esc_attr( $this->name ); ?>">
			<label class="fieldsbox-label" for="<?php echo esc_attr( $this->get_html_id() ); ?>">
				<?php echo esc_html( $this->label ); ?>
				<?php
				if ( $this->required ) :
					?>
					<span class="fieldsbox-required">*</span><?php endif; ?>
			</label>
			<div class="fieldsbox-input">
				<?php echo $this->render_input( $value ); ?>
			</div>
			<?php if ( $this->help_text !== '' ) : ?>
				<p class="fieldsbox-help"><?php echo esc_html( $this->help_text ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Turn the $attributes array into an HTML attribute string (with a
	 * leading space before each pair) for concrete field types to splice
	 * into their <input>/<select> tag.
	 */
	protected function render_attributes(): string {
		$html = '';

		foreach ( $this->attributes as $key => $value ) {
			$html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return $html;
	}

	/**
	 * The HTML "name" attribute concrete field types should render with:
	 * the namespaced override set by GroupField/RepeaterField if present,
	 * otherwise just the field's own name.
	 */
	protected function get_html_name(): string {
		return $this->html_name ?? $this->name;
	}

	/**
	 * A unique "id" for this render, shared by <label for> and the
	 * control's own id. Uses the explicit set_id() value if given,
	 * otherwise lazily generates and caches one - recomputed whenever
	 * set_html_name() puts the field in a new context (e.g. a repeater row).
	 *
	 * set_id() on a field reused across repeater rows would produce
	 * duplicate ids - only use it on fields that render once.
	 */
	protected function get_html_id(): string {
		if ( $this->custom_id !== null ) {
			return $this->custom_id;
		}

		if ( $this->cached_html_id === null ) {
			$this->cached_html_id = 'fieldsbox-' . sanitize_key( $this->get_html_name() ) . '-' . ( ++self::$id_sequence );
		}

		return $this->cached_html_id;
	}
}
