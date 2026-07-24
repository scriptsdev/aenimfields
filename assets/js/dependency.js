/**
 * FieldsBox — conditional field visibility ("depends_on").
 *
 * Reads `data-fieldsbox-depends-on-field` / `data-fieldsbox-depends-on-value`
 * attributes on `.fieldsbox-field` wrappers (written by templates/field-wrapper.php)
 * and shows/hides each dependent field based on the current value of the
 * field it depends on.
 *
 * Exposed as `window.FieldsBox.Dependency` so other code — e.g. a script
 * that injects new fields dynamically (a repeater row, an AJAX response) —
 * can call `FieldsBox.Dependency.evaluateAll()` to re-run the check on demand.
 */
( function ( window, document ) {
	'use strict';

	var Dependency = {

		/**
		 * Read the current value of a named field from the DOM.
		 *
		 * Supports text/select/textarea inputs, single and grouped checkboxes,
		 * radios, and multi-selects.
		 *
		 * @param {string} name Field name, without any `[]` suffix.
		 *
		 * @return {string|string[]|null} The field's current value, or null if
		 *                                no matching field exists on the page.
		 */
		getFieldValue: function ( name ) {

			var checked = document.querySelectorAll(
				'[name="' + name + '"]:checked, [name="' + name + '[]"]:checked'
			);

			if ( checked.length ) {

				if ( checked.length > 1 || checked[ 0 ].type === 'checkbox' ) {
					return Array.prototype.map.call( checked, function ( el ) {
						return el.value;
					} );
				}

				return checked[ 0 ].value;
			}

			var field = document.querySelector( '[name="' + name + '"], [name="' + name + '[]"]' );

			if ( ! field ) {
				return null;
			}

			if ( field.tagName === 'SELECT' && field.multiple ) {
				return Array.prototype.map.call( field.selectedOptions, function ( option ) {
					return option.value;
				} );
			}

			if ( ( field.type === 'checkbox' || field.type === 'radio' ) && ! field.checked ) {
				return '';
			}

			return field.value;
		},

		/**
		 * Determine whether a current value satisfies an expected value.
		 *
		 * Both sides may be a single value or an array of acceptable values;
		 * comparison is done as strings.
		 *
		 * @param {*} current
		 * @param {*} expected
		 *
		 * @return {boolean}
		 */
		matches: function ( current, expected ) {

			var expectedValues = Array.isArray( expected ) ? expected : [ expected ];
			var currentValues  = Array.isArray( current ) ? current : [ current ];

			return currentValues.some( function ( value ) {
				return expectedValues.some( function ( target ) {
					return String( value ) === String( target );
				} );
			} );
		},

		/**
		 * Show or hide a single dependent field wrapper.
		 *
		 * @param {HTMLElement} wrapper
		 */
		evaluate: function ( wrapper ) {

			var name = wrapper.getAttribute( 'data-fieldsbox-depends-on-field' );

			if ( ! name ) {
				return;
			}

			var expected = wrapper.getAttribute( 'data-fieldsbox-depends-on-value' );

			try {
				expected = JSON.parse( expected );
			} catch ( e ) {
				// Leave as the raw attribute string.
			}

			wrapper.hidden = ! Dependency.matches( Dependency.getFieldValue( name ), expected );
		},

		/**
		 * Re-evaluate every dependent field on the page.
		 */
		evaluateAll: function () {
			document
				.querySelectorAll( '[data-fieldsbox-depends-on-field]' )
				.forEach( Dependency.evaluate );
		},

		/**
		 * Bind the events that keep dependent fields in sync.
		 */
		init: function () {
			document.addEventListener( 'DOMContentLoaded', Dependency.evaluateAll );
			document.addEventListener( 'change', Dependency.evaluateAll );
			document.addEventListener( 'input', Dependency.evaluateAll );
		},
	};

	window.FieldsBox = window.FieldsBox || {};
	window.FieldsBox.Dependency = Dependency;

	Dependency.init();
} )( window, document );
