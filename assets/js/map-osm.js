/**
 * AenimFields — map field, free provider (Leaflet + OpenStreetMap).
 *
 * Initializes a Leaflet map with a draggable marker on every
 * `[data-aenimfields-map="osm"]` wrapper, wires it to the field's
 * `.aenimfields-map-lat` / `-lng` / `-zoom` inputs, and — if an
 * `.aenimfields-map-address` input is present — a debounced live search
 * against Nominatim (OpenStreetMap's free geocoding API; no key
 * required, but rate-limited, so heavy production traffic should
 * self-host Nominatim or switch to a paid geocoder instead).
 *
 * Exposed as `window.AenimFields.MapOsm` so other code — e.g. a repeater
 * adding a new row — can call `AenimFields.MapOsm.init()` to pick up
 * newly-added fields.
 */
( function ( window, document ) {
	'use strict';

	var MapOsm = {

		/**
		 * Initialize every not-yet-initialized OSM map field on the page.
		 *
		 * Safe to call repeatedly; already-initialized fields are skipped.
		 */
		init: function () {

			if ( typeof window.L === 'undefined' ) {
				return;
			}

			document
				.querySelectorAll( '[data-aenimfields-map="osm"]:not([data-aenimfields-map-ready])' )
				.forEach( MapOsm.setup );
		},

		/**
		 * @param {HTMLElement} wrapper
		 */
		setup: function ( wrapper ) {

			wrapper.setAttribute( 'data-aenimfields-map-ready', '' );

			var canvas       = wrapper.querySelector( '.aenimfields-map-canvas' );
			var latInput     = wrapper.querySelector( '.aenimfields-map-lat' );
			var lngInput     = wrapper.querySelector( '.aenimfields-map-lng' );
			var zoomInput    = wrapper.querySelector( '.aenimfields-map-zoom' );
			var addressInput = wrapper.querySelector( '.aenimfields-map-address' );

			if ( ! canvas || ! latInput || ! lngInput ) {
				return;
			}

			var lat         = parseFloat( latInput.value || wrapper.getAttribute( 'data-aenimfields-map-default-lat' ) ) || 0;
			var lng         = parseFloat( lngInput.value || wrapper.getAttribute( 'data-aenimfields-map-default-lng' ) ) || 0;
			var zoom        = parseInt( ( zoomInput && zoomInput.value ) || wrapper.getAttribute( 'data-aenimfields-map-zoom' ), 10 ) || 14;
			var draggable   = 'false' !== wrapper.getAttribute( 'data-aenimfields-map-draggable' );
			var hasLocation = !! ( latInput.value && lngInput.value );

			var map = window.L.map( canvas, {
				center: [ lat, lng ],
				zoom: zoom,
				minZoom: parseInt( wrapper.getAttribute( 'data-aenimfields-map-min-zoom' ), 10 ) || 2,
				maxZoom: parseInt( wrapper.getAttribute( 'data-aenimfields-map-max-zoom' ), 10 ) || 20,
			} );

			window.L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors',
			} ).addTo( map );

			var marker = window.L.marker( [ lat, lng ], { draggable: draggable } );

			if ( hasLocation ) {
				marker.addTo( map );
			}

			/**
			 * @param {number}  newLat
			 * @param {number}  newLng
			 * @param {boolean} updateAddress Reverse-geocode and fill the
			 *                                address input.
			 */
			function setLocation( newLat, newLng, updateAddress ) {

				latInput.value = newLat.toFixed( 6 );
				lngInput.value = newLng.toFixed( 6 );

				if ( ! map.hasLayer( marker ) ) {
					marker.addTo( map );
				}

				marker.setLatLng( [ newLat, newLng ] );
				map.panTo( [ newLat, newLng ] );

				latInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );

				if ( updateAddress && addressInput ) {
					MapOsm.reverseGeocode( newLat, newLng, addressInput );
				}
			}

			marker.on( 'dragend', function () {
				var pos = marker.getLatLng();
				setLocation( pos.lat, pos.lng, true );
			} );

			map.on( 'click', function ( e ) {

				if ( ! draggable ) {
					return;
				}

				setLocation( e.latlng.lat, e.latlng.lng, true );
			} );

			if ( addressInput ) {
				MapOsm.bindSearch( addressInput, function ( result ) {
					map.setZoom( 16 );
					setLocation( parseFloat( result.lat ), parseFloat( result.lon ), false );
					addressInput.value = result.display_name;
				} );
			}

			// A map initialized while its container is hidden (an inactive
			// tab, a collapsed conditional field) renders at the wrong
			// size until told to recheck — give the surrounding layout a
			// moment to settle first.
			window.setTimeout( function () {
				map.invalidateSize();
			}, 200 );
		},

		/**
		 * Wire a debounced, Nominatim-backed live search to an address
		 * input, rendering results in a simple dropdown list.
		 *
		 * @param {HTMLElement} input
		 * @param {Function}    onSelect Called with the chosen Nominatim result.
		 */
		bindSearch: function ( input, onSelect ) {

			var timer = null;
			var list  = document.createElement( 'ul' );

			list.className = 'aenimfields-map-suggestions';
			list.hidden    = true;

			input.insertAdjacentElement( 'afterend', list );

			input.addEventListener( 'input', function () {

				window.clearTimeout( timer );

				var query = input.value.trim();

				if ( query.length < 3 ) {
					list.hidden   = true;
					list.innerHTML = '';
					return;
				}

				timer = window.setTimeout( function () {
					MapOsm.search( query, function ( results ) {

						list.innerHTML = '';

						if ( ! results.length ) {
							list.hidden = true;
							return;
						}

						results.forEach( function ( result ) {

							var item = document.createElement( 'li' );
							item.textContent = result.display_name;

							item.addEventListener( 'click', function () {
								onSelect( result );
								list.hidden = true;
							} );

							list.appendChild( item );
						} );

						list.hidden = false;
					} );
				}, 400 );
			} );

			document.addEventListener( 'click', function ( e ) {
				if ( e.target !== input && ! list.contains( e.target ) ) {
					list.hidden = true;
				}
			} );
		},

		/**
		 * @param {string}   query
		 * @param {Function} callback Called with an array of Nominatim results.
		 */
		search: function ( query, callback ) {
			window.fetch( 'https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent( query ) )
				.then( function ( response ) {
					return response.json();
				} )
				.then( callback )
				.catch( function () {
					callback( [] );
				} );
		},

		/**
		 * @param {number}      lat
		 * @param {number}      lng
		 * @param {HTMLElement} addressInput
		 */
		reverseGeocode: function ( lat, lng, addressInput ) {
			window.fetch( 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( result ) {
					if ( result && result.display_name ) {
						addressInput.value = result.display_name;
					}
				} )
				.catch( function () {} );
		},
	};

	window.AenimFields = window.AenimFields || {};
	window.AenimFields.MapOsm = MapOsm;

	document.addEventListener( 'DOMContentLoaded', MapOsm.init );
} )( window, document );
