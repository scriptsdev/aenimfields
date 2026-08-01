<?php

defined( 'ABSPATH' ) || exit;

use AenimTech\AenimFields\Core\Assets;
use AenimTech\AenimFields\Core\Helpers;

$provider = (string) $field->get_arg( 'provider', 'osm' );
$value    = (array) $field->get_value();

if ( 'google' === $provider && '' === Assets::get_google_maps_api_key() ) :
	?>

	<p class="aenimfields-map-notice">
		<?php
		esc_html_e(
			'Google Maps API key not configured. Call AenimTech\AenimFields\Core\Assets::set_google_maps_api_key() from your plugin, or set this field\'s "provider" option to "osm".',
			'aenimfields'
		);
		?>
	</p>

	<?php
	return;
endif;

$wrapper_attributes = array(
	'class'                           => 'aenimfields-map',
	'data-aenimfields-map'              => $provider,
	'data-aenimfields-map-lat'          => $value['lat'],
	'data-aenimfields-map-lng'          => $value['lng'],
	'data-aenimfields-map-zoom'         => $value['zoom'] ?: $field->get_arg( 'zoom', 14 ),
	'data-aenimfields-map-default-lat'  => $field->get_arg( 'default_lat', '' ),
	'data-aenimfields-map-default-lng'  => $field->get_arg( 'default_lng', '' ),
	'data-aenimfields-map-min-zoom'     => $field->get_arg( 'min_zoom', 2 ),
	'data-aenimfields-map-max-zoom'     => $field->get_arg( 'max_zoom', 20 ),
	'data-aenimfields-map-draggable'    => $field->get_arg( 'marker_draggable', true ) ? 'true' : 'false',
	'data-aenimfields-map-type'         => $field->get_arg( 'map_type', 'roadmap' ),
);

?>

<div <?php echo Helpers::attributes( $wrapper_attributes ); ?>>

	<?php if ( $field->get_arg( 'show_address', true ) ) : ?>
		<input
			type="text"
			class="aenimfields-map-address regular-text"
			placeholder="<?php echo esc_attr( $field->get_arg( 'search_placeholder', __( 'Search address…', 'aenimfields' ) ) ); ?>"
			value="<?php echo esc_attr( $value['address'] ); ?>"
			name="<?php echo esc_attr( $field->get_name() . '[address]' ); ?>"
			autocomplete="off"
		>
	<?php endif; ?>

	<div class="aenimfields-map-canvas" style="height: <?php echo esc_attr( (int) $field->get_arg( 'height', 400 ) ); ?>px;"></div>

	<div class="aenimfields-map-coordinates">

		<label>
			<?php esc_html_e( 'Latitude', 'aenimfields' ); ?>
			<input
				type="number"
				step="any"
				class="aenimfields-map-lat"
				name="<?php echo esc_attr( $field->get_name() . '[lat]' ); ?>"
				value="<?php echo esc_attr( $value['lat'] ); ?>"
			>
		</label>

		<label>
			<?php esc_html_e( 'Longitude', 'aenimfields' ); ?>
			<input
				type="number"
				step="any"
				class="aenimfields-map-lng"
				name="<?php echo esc_attr( $field->get_name() . '[lng]' ); ?>"
				value="<?php echo esc_attr( $value['lng'] ); ?>"
			>
		</label>

	</div>

	<input
		type="hidden"
		class="aenimfields-map-zoom"
		name="<?php echo esc_attr( $field->get_name() . '[zoom]' ); ?>"
		value="<?php echo esc_attr( $value['zoom'] ); ?>"
	>

</div>
