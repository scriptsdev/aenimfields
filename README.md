# AenimFields

A lightweight PHP field framework for WordPress admin and frontend forms — render fields from plain array definitions, then sanitize and validate submissions with a small, consistent API.

AenimFields is a **Composer library only**. It has no plugin entry point of its own and cannot be activated as a WordPress plugin — install it as a dependency of a plugin or theme.

## Requirements

- PHP 8.0+
- `ext-mbstring`
- WordPress (the field templates and sanitize/validate helpers call core WordPress functions such as `esc_html()`, `sanitize_text_field()`, `wp_kses_post()`, etc.)

## Installation

```bash
composer require aenimtech/aenimfields
```

Then, from your plugin or theme, construct the application wherever you render fields or process a submission:

```php
$app = new \AenimTech\AenimFields\Core\Application();
```

Constructing `Application` is cheap and safe to call more than once per request (e.g. once from your main setup and again inside a metabox callback) — field registration, asset registration, and the `AENIMFIELDS_*` constants are all idempotent.

## Rendering fields

`Application::render()` accepts either a single field's args, or a list of fields:

```php
echo $app->render( [
    'type'     => 'text',
    'name'     => 'site_tagline',
    'label'    => __( 'Site Tagline', 'your-textdomain' ),
    'required' => true,
] );

echo $app->render( [
    [
        'type'  => 'email',
        'name'  => 'contact_email',
        'label' => __( 'Contact Email', 'your-textdomain' ),
    ],
    [
        'type'    => 'select',
        'name'    => 'status',
        'label'   => __( 'Status', 'your-textdomain' ),
        'options' => [ 'draft' => 'Draft', 'active' => 'Active' ],
    ],
] );
```

See [`examples/`](examples/) for complete, working patterns: an admin metabox, a settings page, and a public-facing frontend form, each covering rendering, sanitizing, and validating a submission.

### Fluent builder (`Field::make()`)

`AenimTech\AenimFields\Core\Field` is a Carbon Fields-style fluent alternative to the array API above — same engine underneath, just chained method calls instead of an args array:

```php
use AenimTech\AenimFields\Core\Field;

echo Field::make( 'text', 'site_tagline', __( 'Site Tagline', 'your-textdomain' ) )
    ->set_required()
    ->set_description( __( 'Shown under the site title.', 'your-textdomain' ) )
    ->render();
```

`set_required()` (or any `set_<arg>( $value )` call) sets the matching arg from the [common field args](#common-field-args) or a field-specific one (`set_options( [...] )`, `set_min_rows( 1 )`, `set_date_format( 'Y-m-d' )`, `set_provider( 'google' )`, etc.) — there's no fixed list of setters to keep in sync, any registered arg name works. Calling it with no argument (`set_required()`) sets `true`.

`repeater`/`group` sub-fields (`set_fields( [...] )`) can be `Field` builders themselves, mixed with raw arrays:

```php
echo Field::make( 'repeater', 'team_members', __( 'Team Members', 'your-textdomain' ) )
    ->set_min_rows( 1 )
    ->set_fields( [
        Field::make( 'text', 'name', __( 'Name', 'your-textdomain' ) )->set_required(),
        Field::make( 'image', 'photo', __( 'Photo', 'your-textdomain' ) ),
    ] )
    ->render();
```

`set_conditional_logic( $field, $value )` is a named shortcut for `depends_on` (see [Conditional fields](#conditional-fields-depends_on) below). `sanitize( $value )` / `validate( $value )` mirror `Core\Sanitizer`/`Core\Validator`, and `to_array()` returns the plain args array if you need to hand a built field to code that expects the array API (e.g. nest it under a raw `repeater` `fields` array).

## Field types

Implemented: `text`, `textarea`, `number`, `email`, `url`, `password`, `hidden`, `checkbox`, `radio`, `toggle`, `select`, `multiselect`, `color`, `date`, `datetime`, `wysiwyg`, `image`, `gallery`, `file`, `map`, `repeater`, `group`, `separator` (a display-only section divider), `heading` (a display-only label/sub-text block).

Every registered field type is now implemented.

### Date / DateTime fields

`date` and `datetime` render as a text input progressively enhanced by [flatpickr](https://flatpickr.js.org/) (bundled locally under `assets/libraries/flatpickr/`, no CDN). Both accept a `date_format` arg using PHP's `date()` tokens — default `Y-m-d` for `date`, `Y-m-d H:i` for `datetime`. Stick to tokens both PHP and flatpickr understand (`Y`, `m`, `d`, `H`, `i`, `s`) if you customize it.

```php
[
    'type'        => 'datetime',
    'name'        => 'event_start',
    'label'       => __( 'Event Start', 'your-textdomain' ),
    'date_format' => 'Y-m-d H:i:s',
],
```

flatpickr's CSS/JS only load on pages that actually render a `date` or `datetime` field — rendering any other field type does not pull it in.

Restrict the selectable range with `min_date` / `max_date` — each accepts a literal date string in `date_format`, or the keyword `'today'` (resolved server-side using the site's configured timezone via `wp_timezone()`, so the browser and server always agree). Enforced both in the browser (flatpickr) and in `validate()` — never rely on the browser restriction alone.

```php
[
    'type'     => 'date',
    'name'     => 'event_date',
    'label'    => __( 'Event Date', 'your-textdomain' ),
    'min_date' => 'today', // can't pick a date in the past
    'max_date' => '2026-12-31',
],
```

For `datetime`, `'today'` resolves to the exact current moment rather than midnight — so `min_date => 'today'` means "not in the past," not just "not before today's date."

### Wysiwyg field

`wysiwyg` renders WordPress's native rich text editor (`wp_editor()`) and sanitizes with `wp_kses_post()` instead of `sanitize_text_field()`, since its value is post-level HTML, not plain text.

```php
[
    'type'  => 'wysiwyg',
    'name'  => 'page_content',
    'label' => __( 'Page Content', 'your-textdomain' ),
],
```

The field's `name` (and therefore its `id`, unless you set one explicitly) must be lowercase with only letters, numbers, and underscores — no hyphens — since `wp_editor()` requires this for the DOM id it assigns to the editor. This is normalized automatically if violated, but the wrapper's `<label for="...">` won't match the normalized id in that case, so stick to underscores.

Accepts `rows` (default `10`, taller than the usual default of `5`), plus `media_buttons`, `teeny`, `tinymce`, and `quicktags` — all passed straight through to `wp_editor()`'s settings array and all default to matching `wp_editor()`'s own defaults except `rows`.

### Image / Gallery / File fields

`image`, `gallery`, and `file` are WordPress media library pickers built on `wp.media()` — designed for **wp-admin use**, since a frontend upload flow needs different handling (capability checks, chunked uploads, etc.) that these don't provide. `image`/`file` store a single attachment ID; `gallery` stores an array of attachment IDs. None of them store a URL — resolve the actual file (URL, alt text, sizes) from the ID wherever the value is used, e.g. `wp_get_attachment_image( $id, 'medium' )`.

```php
[
    'type'  => 'image',
    'name'  => 'hero_image',
    'label' => __( 'Hero Image', 'your-textdomain' ),
],
[
    'type'  => 'gallery',
    'name'  => 'photos',
    'label' => __( 'Photos', 'your-textdomain' ),
],
[
    'type'  => 'file',
    'name'  => 'brochure',
    'label' => __( 'Brochure', 'your-textdomain' ),
],
```

`image` and `gallery` restrict the media library to images and reject non-image attachment IDs in `validate()`; `file` accepts any attachment type. `preview_size` (default `'thumbnail'`) controls the image size shown for `image`/`gallery`. `select_text` and `title_text` customize the button label and media-modal title.

`wp_enqueue_media()` and this plugin's `assets/js/media.js` only load on pages that actually render one of these three field types — same selective-loading approach as the datepicker.

### Map field

`map` is a location picker: an address search box, a draggable-pin map, and latitude/longitude inputs, all kept in sync. Stores `['address' => string, 'lat' => float|string, 'lng' => float|string, 'zoom' => int]` — `lat`/`lng` are `''` until a location has actually been picked.

```php
[
    'type'  => 'map',
    'name'  => 'business_location',
    'label' => __( 'Business Location', 'your-textdomain' ),
],
```

Two providers, chosen with `provider`:

- **`'osm'` (default)** — [Leaflet](https://leafletjs.com/) + OpenStreetMap tiles, bundled locally under `assets/libraries/leaflet/`. Address search uses [Nominatim](https://nominatim.org/), OSM's free geocoding service — no API key, but it's rate-limited; a site with heavy traffic on this field should self-host Nominatim or switch to a paid geocoder.
- **`'google'`** — the Google Maps JavaScript API (map, marker, Places Autocomplete, reverse geocoding). Requires an API key.

```php
[
    'type'     => 'map',
    'name'     => 'business_location',
    'label'    => __( 'Business Location', 'your-textdomain' ),
    'provider' => 'google',
],
```

#### Google Maps API key

The key is a site-wide credential, not a per-field value — set it once from your plugin's own bootstrap, wherever your plugin stores its own settings:

```php
$app = new \AenimTech\AenimFields\Core\Application();
\AenimTech\AenimFields\Core\Assets::set_google_maps_api_key( 'AIza...' );
```

If a `map` field with `provider => 'google'` renders before a key has been set, it shows an inline notice instead of a broken map. Because the key is used in a client-side `<script src="...">` URL, it's inherently visible in the page source — that's normal for the Google Maps JS API. Restrict it in the Google Cloud Console (HTTP referrer restriction, limited to the Maps JavaScript API / Places API / Geocoding API) rather than relying on it staying secret.

#### Options

`default_lat` / `default_lng` (where the map centers before a location is picked), `zoom` (default `14`), `min_zoom` / `max_zoom` (default `2` / `20`), `height` (canvas height in px, default `400`), `show_address` (default `true` — set `false` for a lat/lng-only picker with no search box), `marker_draggable` (default `true`), `map_type` (Google only: `roadmap`/`satellite`/`hybrid`/`terrain`), `search_placeholder`.

Both providers' JS/CSS only load on pages that actually render a `map` field, and only the provider actually in use — a page with an `osm` map never loads the Google Maps script, and vice versa.

### Repeater field

`repeater` renders an add/remove list of rows, each containing the sub-fields declared in its `fields` arg — any registered field type, including `date`, `image`, `gallery`, etc. The stored value is an array of rows, each row an associative array of `sub_field_name => value`:

```php
[
    'type'  => 'repeater',
    'name'  => 'team_members',
    'label' => __( 'Team Members', 'your-textdomain' ),
    'min_rows' => 1,
    'max_rows' => 10,
    'fields' => [
        [ 'type' => 'text', 'name' => 'name', 'label' => __( 'Name', 'your-textdomain' ), 'required' => true ],
        [ 'type' => 'image', 'name' => 'photo', 'label' => __( 'Photo', 'your-textdomain' ) ],
        [ 'type' => 'textarea', 'name' => 'bio', 'label' => __( 'Bio', 'your-textdomain' ) ],
    ],
],
```

Each sub-field's `name`/`id` is automatically qualified per row (e.g. `team_members[0][name]`), so `sanitize()`/`validate()` work exactly like any other field — no repeater-specific glue code needed on your end:

```php
$field  = $factory->make( $repeater_args );
$clean  = Sanitizer::sanitize( $field, $_POST['team_members'] ?? [] ); // sanitizes every sub-field in every row
$result = Validator::validate( $field, $clean );                       // "Row 2: The "Name" field is required."
```

A sub-field's `depends_on.field` can reference a sibling sub-field **in the same row** by its plain name (e.g. `'field' => 'show_photo'`) — it's automatically rewritten to that row's qualified name at render time, so conditional fields work inside a repeater the same way they do anywhere else.

`min_rows`/`max_rows` (both optional) are enforced both in the browser (disabling Add/Remove buttons) and in `validate()`. Nesting a repeater inside another repeater is untested and not currently supported.

Each row renders as a collapsible card: a header (drag handle, a title, a remove button, a collapse toggle) above the row's own fields. This package ships the markup and the interaction (collapse/expand, remove) — it ships **no CSS** for it (the collapsed body is hidden with the plain `hidden` attribute, so collapsing works with zero stylesheet); a consuming plugin/theme is expected to style it. Hooks to style against:

- `.aenimfields-repeater-row` — one row/card; gets `.aenimfields-repeater-row--collapsed` while collapsed
- `.aenimfields-repeater-row-header` — the whole clickable toggle area (`role="button"`, `aria-expanded`)
- `.aenimfields-repeater-drag-handle` — a decorative grip icon; **not currently wired up to actual drag-to-reorder**, it's markup only
- `.aenimfields-repeater-row-title` — the live row title (see `title_field` below)
- `.aenimfields-repeater-row-actions`, `.aenimfields-repeater-remove-row`, `.aenimfields-repeater-toggle-icon` — right-aligned remove button and collapse chevron. Icons (drag handle, remove, toggle) render as WP core [Dashicons](https://developer.wordpress.org/resource/dashicons/) (`dashicons-move`, `dashicons-trash`, `dashicons-arrow-down-alt2`) via `<span class="dashicons dashicons-{name} aenimfields-repeater-icon aenimfields-repeater-icon-{drag,remove,toggle}">` — always available in wp-admin; this package enqueues Dashicons' style explicitly whenever a `repeater` renders, so it also works on the frontend. Override the glyph by targeting the `aenimfields-repeater-icon-*` class with your own icon font/CSS if you don't want Dashicons.
- `.aenimfields-repeater-row-body` — the collapsible container for the row's fields; carries the native `hidden` attribute when collapsed

`title_field` names which sub-field's value drives the header title (defaults to the first declared sub-field). It updates live as the user types — no page reload needed:

```php
[
    'type'        => 'repeater',
    'name'        => 'team_members',
    'label'       => __( 'Team Members', 'your-textdomain' ),
    'title_field' => 'name', // header shows this sub-field's value
    'fields'      => [
        [ 'type' => 'text', 'name' => 'name', 'label' => __( 'Full Name', 'your-textdomain' ) ],
        [ 'type' => 'text', 'name' => 'role', 'label' => __( 'Job Role', 'your-textdomain' ) ],
    ],
],
```

An empty title field falls back to "Row 1", "Row 2", etc.

### Group field

`group` is a **fixed** set of sub-fields nested under one field — unlike `repeater`, there is exactly one instance, not a repeatable list. The stored value is a single associative array of `sub_field_name => value`:

```php
[
    'type'   => 'group',
    'name'   => 'address',
    'label'  => __( 'Address', 'your-textdomain' ),
    'fields' => [
        [ 'type' => 'text', 'name' => 'street', 'label' => __( 'Street', 'your-textdomain' ), 'required' => true ],
        [ 'type' => 'text', 'name' => 'city', 'label' => __( 'City', 'your-textdomain' ) ],
    ],
],
```

Sub-field `name`/`id` qualification, `sanitize()`/`validate()` delegation, and same-group `depends_on.field` rewriting all work exactly like `repeater` (see above), just without the per-row index. A `group` can be used as a sub-field of a `repeater` (or another `group`) — its own field name is already qualified by its parent by the time it renders, so nesting composes without any special handling; verified with a `group` inside a `repeater` row resolving correctly to e.g. `locations[0][address][street]`.

## Common field args

Every field accepts `type`, `name`, `label`, `label_description` (a sub-line rendered directly under the label), `description` (rendered below the input), `help`, `required`, `readonly`, `disabled`, `default`, `value`, `class`, `wrapper_class`, `placeholder`, `prefix`/`suffix`, `before`/`after`, and `validate` (see below). Field-specific args like `options`, `rows`, `min`/`max`/`step` apply where relevant.

## Sanitizing and validating a submission

```php
use AenimTech\AenimFields\Core\FieldFactory;
use AenimTech\AenimFields\Core\Sanitizer;
use AenimTech\AenimFields\Core\Validator;

$factory = new FieldFactory();
$field   = $factory->make( [
    'type'     => 'text',
    'name'     => 'message',
    'required' => true,
    'validate' => [ 'min:10' ],
] );

$clean  = Sanitizer::sanitize( $field, $_POST['message'] ?? '' );
$result = Validator::validate( $field, $clean );

if ( ! $result->passed() ) {
    // $result->message()
}
```

Available `validate` rules: `required`, `email`, `url`, `numeric`, `min:N`, `max:N`, `regex:/pattern/`. Each field type also runs its own built-in checks (e.g. an `email` field always validates as an email) regardless of what's listed in `validate`.

## Conditional fields (`depends_on`)

Show or hide a field based on another field's current value:

```php
[
    'type'       => 'select',
    'name'       => 'registration_mode',
    'options'    => [ 'value1' => 'Option A', 'value2' => 'Option B' ],
    'depends_on' => [
        'field' => 'disable_registration',
        'value' => '1',
    ],
],
```

This is handled client-side by `assets/js/dependency.js`, exposed as `window.AenimFields.Dependency`. The script is only enqueued on pages that actually render a field — it does not load unconditionally.

## License

GPL-2.0-or-later. See [`LICENSE`](LICENSE).
