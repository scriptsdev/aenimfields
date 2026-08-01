/**
 * AenimFields — date/datetime fields, powered by flatpickr.
 *
 * Initializes flatpickr (assets/vendor/js/flatpickr.min.js) on every
 * input carrying `data-aenimfields-datepicker`, reading its date format
 * and enable-time option from the data attributes written by
 * templates/fields/date.php and templates/fields/datetime.php.
 *
 * Exposed as `window.AenimFields.Datepicker` so other code — e.g. a script
 * that injects new fields dynamically (a repeater row, an AJAX response) —
 * can call `AenimFields.Datepicker.init()` to pick up newly-added fields.
 */
( function ( window, document ) {
	'use strict';

	var Datepicker = {

		/**
		 * Initialize flatpickr on every not-yet-initialized datepicker field.
		 *
		 * Safe to call repeatedly; already-initialized fields are skipped.
		 */
		init: function () {

			if ( typeof window.flatpickr !== 'function' ) {
				return;
			}

			var fields = document.querySelectorAll(
				'[data-aenimfields-datepicker]:not([data-aenimfields-datepicker-ready])'
			);

			fields.forEach( function ( field ) {

				field.setAttribute( 'data-aenimfields-datepicker-ready', '' );

				window.flatpickr( field, {
					dateFormat: field.getAttribute( 'data-aenimfields-date-format' ) || 'Y-m-d',
					enableTime: 'true' === field.getAttribute( 'data-aenimfields-enable-time' ),
					allowInput: true,
					minDate: field.getAttribute( 'data-aenimfields-min-date' ) || undefined,
					maxDate: field.getAttribute( 'data-aenimfields-max-date' ) || undefined,
				} );
			} );
		},
	};

	window.AenimFields = window.AenimFields || {};
	window.AenimFields.Datepicker = Datepicker;

	document.addEventListener( 'DOMContentLoaded', Datepicker.init );
} )( window, document );
