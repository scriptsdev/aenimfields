<?php

defined( 'ABSPATH' ) || exit;

use FieldsBox\Core\Assets;
use FieldsBox\Core\Helpers;

$provider = (string) $field->get_arg( 'provider', 'osm' );
$value    = (array) $field->get_value();

if ( 'google' === $provider && '' === Assets::get_google_maps_api_key() ) :
	?>

	<p class="fieldsbox-map-notice">
		<?php
		esc_html_e(
			'Google Maps API key not configured. Call FieldsBox\Core\Assets::set_google_maps_api_key() from your plugin, or set this field\'s "provider" option to "osm".',
			'fieldsbox'
		);
		?>
	</p>

	<?php
	return;
endif;

$wrapper_attributes = array(
	'class'                           => 'fieldsbox-map',
	'data-fieldsbox-map'              => $provider,
	'data-fieldsbox-map-lat'          => $value['lat'],
	'data-fieldsbox-map-lng'          => $value['lng'],
	'data-fieldsbox-map-zoom'         => $value['zoom'] ?: $field->get_arg( 'zoom', 14 ),
	'data-fieldsbox-map-default-lat'  => $field->get_arg( 'default_lat', '' ),
	'data-fieldsbox-map-default-lng'  => $field->get_arg( 'default_lng', '' ),
	'data-fieldsbox-map-min-zoom'     => $field->get_arg( 'min_zoom', 2 ),
	'data-fieldsbox-map-max-zoom'     => $field->get_arg( 'max_zoom', 20 ),
	'data-fieldsbox-map-draggable'    => $field->get_arg( 'marker_draggable', true ) ? 'true' : 'false',
	'data-fieldsbox-map-type'         => $field->get_arg( 'map_type', 'roadmap' ),
);

?>

<div <?php echo Helpers::attributes( $wrapper_attributes ); ?>>

	<?php if ( $field->get_arg( 'show_address', true ) ) : ?>
		<input
			type="text"
			class="fieldsbox-map-address regular-text"
			placeholder="<?php echo esc_attr( $field->get_arg( 'search_placeholder', __( 'Search address…', 'fieldsbox' ) ) ); ?>"
			value="<?php echo esc_attr( $value['address'] ); ?>"
			name="<?php echo esc_attr( $field->get_name() . '[address]' ); ?>"
			autocomplete="off"
		>
	<?php endif; ?>

	<div class="fieldsbox-map-canvas" style="height: <?php echo esc_attr( (int) $field->get_arg( 'height', 400 ) ); ?>px;"></div>

	<div class="fieldsbox-map-coordinates">

		<label>
			<?php esc_html_e( 'Latitude', 'fieldsbox' ); ?>
			<input
				type="number"
				step="any"
				class="fieldsbox-map-lat"
				name="<?php echo esc_attr( $field->get_name() . '[lat]' ); ?>"
				value="<?php echo esc_attr( $value['lat'] ); ?>"
			>
		</label>

		<label>
			<?php esc_html_e( 'Longitude', 'fieldsbox' ); ?>
			<input
				type="number"
				step="any"
				class="fieldsbox-map-lng"
				name="<?php echo esc_attr( $field->get_name() . '[lng]' ); ?>"
				value="<?php echo esc_attr( $value['lng'] ); ?>"
			>
		</label>

	</div>

	<input
		type="hidden"
		class="fieldsbox-map-zoom"
		name="<?php echo esc_attr( $field->get_name() . '[zoom]' ); ?>"
		value="<?php echo esc_attr( $value['zoom'] ); ?>"
	>

</div>
