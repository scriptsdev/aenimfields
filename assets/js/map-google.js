/**
 * FieldsBox — map field, Google provider (Google Maps JavaScript API).
 *
 * Initializes a Google map with a draggable marker on every
 * `[data-fieldsbox-map="google"]` wrapper, wires it to the field's
 * `.fieldsbox-map-lat` / `-lng` / `-zoom` inputs, and — if an
 * `.fieldsbox-map-address` input is present — Google Places
 * Autocomplete, plus reverse geocoding on marker drag.
 *
 * Uses the classic `google.maps.Marker` (not `AdvancedMarkerElement`) to
 * avoid also requiring a Map ID; revisit if Google removes it.
 *
 * Exposed as `window.FieldsBox.MapGoogle` so other code — e.g. a
 * repeater adding a new row — can call `FieldsBox.MapGoogle.init()` to
 * pick up newly-added fields.
 */
( function ( window, document ) {
	'use strict';

	var MapGoogle = {

		/**
		 * Initialize every not-yet-initialized Google map field on the page.
		 *
		 * Safe to call repeatedly; already-initialized fields are skipped.
		 */
		init: function () {

			if ( ! window.google || ! window.google.maps ) {
				return;
			}

			document
				.querySelectorAll( '[data-fieldsbox-map="google"]:not([data-fieldsbox-map-ready])' )
				.forEach( MapGoogle.setup );
		},

		/**
		 * @param {HTMLElement} wrapper
		 */
		setup: function ( wrapper ) {

			wrapper.setAttribute( 'data-fieldsbox-map-ready', '' );

			var canvas       = wrapper.querySelector( '.fieldsbox-map-canvas' );
			var latInput     = wrapper.querySelector( '.fieldsbox-map-lat' );
			var lngInput     = wrapper.querySelector( '.fieldsbox-map-lng' );
			var zoomInput    = wrapper.querySelector( '.fieldsbox-map-zoom' );
			var addressInput = wrapper.querySelector( '.fieldsbox-map-address' );

			if ( ! canvas || ! latInput || ! lngInput ) {
				return;
			}

			var lat         = parseFloat( latInput.value || wrapper.getAttribute( 'data-fieldsbox-map-default-lat' ) ) || 0;
			var lng         = parseFloat( lngInput.value || wrapper.getAttribute( 'data-fieldsbox-map-default-lng' ) ) || 0;
			var zoom        = parseInt( ( zoomInput && zoomInput.value ) || wrapper.getAttribute( 'data-fieldsbox-map-zoom' ), 10 ) || 14;
			var draggable   = 'false' !== wrapper.getAttribute( 'data-fieldsbox-map-draggable' );
			var hasLocation = !! ( latInput.value && lngInput.value );

			var map = new window.google.maps.Map( canvas, {
				center: { lat: lat, lng: lng },
				zoom: zoom,
				minZoom: parseInt( wrapper.getAttribute( 'data-fieldsbox-map-min-zoom' ), 10 ) || 2,
				maxZoom: parseInt( wrapper.getAttribute( 'data-fieldsbox-map-max-zoom' ), 10 ) || 20,
				mapTypeId: wrapper.getAttribute( 'data-fieldsbox-map-type' ) || 'roadmap',
				streetViewControl: false,
			} );

			var marker = new window.google.maps.Marker( {
				map: hasLocation ? map : null,
				position: { lat: lat, lng: lng },
				draggable: draggable,
			} );

			var geocoder = new window.google.maps.Geocoder();

			/**
			 * @param {google.maps.LatLng} position
			 * @param {boolean}            updateAddress Reverse-geocode and
			 *                                            fill the address input.
			 */
			function setLocation( position, updateAddress ) {

				latInput.value = position.lat().toFixed( 6 );
				lngInput.value = position.lng().toFixed( 6 );

				marker.setPosition( position );
				marker.setMap( map );
				map.panTo( position );

				latInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );

				if ( updateAddress && addressInput ) {
					geocoder.geocode( { location: position }, function ( results, status ) {
						if ( 'OK' === status && results[ 0 ] ) {
							addressInput.value = results[ 0 ].formatted_address;
						}
					} );
				}
			}

			marker.addListener( 'dragend', function () {
				setLocation( marker.getPosition(), true );
			} );

			map.addListener( 'click', function ( e ) {

				if ( ! draggable ) {
					return;
				}

				setLocation( e.latLng, true );
			} );

			if ( addressInput && window.google.maps.places ) {

				var autocomplete = new window.google.maps.places.Autocomplete( addressInput );
				autocomplete.bindTo( 'bounds', map );

				autocomplete.addListener( 'place_changed', function () {

					var place = autocomplete.getPlace();

					if ( ! place.geometry || ! place.geometry.location ) {
						return;
					}

					map.setZoom( 16 );
					setLocation( place.geometry.location, false );
				} );
			}
		},
	};

	window.FieldsBox = window.FieldsBox || {};
	window.FieldsBox.MapGoogle = MapGoogle;

	document.addEventListener( 'DOMContentLoaded', MapGoogle.init );
} )( window, document );
