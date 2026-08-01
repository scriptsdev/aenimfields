/**
 * AenimFields — repeater fields: add/remove rows.
 *
 * Clones the `<template class="aenimfields-repeater-template">` rendered by
 * templates/fields/repeater.php, substituting its `__INDEX__` placeholder
 * for a real, ever-increasing row index, and re-runs AenimFields's other
 * modules (conditional visibility, date pickers, media pickers) so fields
 * in a newly added row work immediately — each check is optional, since a
 * repeater's sub-fields might not use any of them.
 *
 * Exposed as `window.AenimFields.Repeater` so other code can call
 * `AenimFields.Repeater.init()` to pick up dynamically-added repeaters.
 */
( function ( window, document ) {
	'use strict';

	var Repeater = {

		/**
		 * Wire up every not-yet-initialized repeater on the page.
		 */
		init: function () {
			document
				.querySelectorAll( '[data-aenimfields-repeater]:not([data-aenimfields-repeater-ready])' )
				.forEach( Repeater.setup );
		},

		/**
		 * @param {HTMLElement} wrapper
		 */
		setup: function ( wrapper ) {

			wrapper.setAttribute( 'data-aenimfields-repeater-ready', '' );

			var addBtn = wrapper.querySelector( ':scope > .aenimfields-repeater-add-row' );

			if ( addBtn ) {
				addBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					Repeater.addRow( wrapper );
				} );
			}

			Repeater.getRows( wrapper ).forEach( function ( row ) {
				Repeater.bindRemove( wrapper, row );
			} );

			Repeater.updateButtons( wrapper );
		},

		/**
		 * @param {HTMLElement} wrapper
		 *
		 * @return {HTMLElement[]}
		 */
		getRows: function ( wrapper ) {
			var container = wrapper.querySelector( ':scope > .aenimfields-repeater-rows' );
			return container ? Array.prototype.slice.call( container.children ) : [];
		},

		/**
		 * Clone the row template and append it.
		 *
		 * @param {HTMLElement} wrapper
		 */
		addRow: function ( wrapper ) {

			var max = parseInt( wrapper.getAttribute( 'data-aenimfields-repeater-max' ) || '0', 10 );

			if ( max > 0 && Repeater.getRows( wrapper ).length >= max ) {
				return;
			}

			var template      = wrapper.querySelector( ':scope > .aenimfields-repeater-template' );
			var rowsContainer = wrapper.querySelector( ':scope > .aenimfields-repeater-rows' );

			if ( ! template || ! rowsContainer ) {
				return;
			}

			var nextIndex = parseInt( wrapper.getAttribute( 'data-aenimfields-repeater-next-index' ) || '0', 10 );
			var html      = template.innerHTML.split( '__INDEX__' ).join( String( nextIndex ) );

			rowsContainer.insertAdjacentHTML( 'beforeend', html );
			wrapper.setAttribute( 'data-aenimfields-repeater-next-index', String( nextIndex + 1 ) );

			var newRow = rowsContainer.lastElementChild;

			if ( newRow ) {
				Repeater.bindRemove( wrapper, newRow );
			}

			Repeater.reinitFields();
			Repeater.updateButtons( wrapper );
		},

		/**
		 * @param {HTMLElement} wrapper
		 * @param {HTMLElement} row
		 */
		bindRemove: function ( wrapper, row ) {

			var removeBtn = row.querySelector( ':scope > .aenimfields-repeater-remove-row' );

			if ( ! removeBtn ) {
				return;
			}

			removeBtn.addEventListener( 'click', function ( e ) {

				e.preventDefault();

				var min = parseInt( wrapper.getAttribute( 'data-aenimfields-repeater-min' ) || '0', 10 );

				if ( min > 0 && Repeater.getRows( wrapper ).length <= min ) {
					return;
				}

				if ( row.parentNode ) {
					row.parentNode.removeChild( row );
				}

				Repeater.updateButtons( wrapper );
			} );
		},

		/**
		 * Disable "Add" past max_rows and "Remove" at-or-below min_rows.
		 *
		 * @param {HTMLElement} wrapper
		 */
		updateButtons: function ( wrapper ) {

			var min    = parseInt( wrapper.getAttribute( 'data-aenimfields-repeater-min' ) || '0', 10 );
			var max    = parseInt( wrapper.getAttribute( 'data-aenimfields-repeater-max' ) || '0', 10 );
			var count  = Repeater.getRows( wrapper ).length;
			var addBtn = wrapper.querySelector( ':scope > .aenimfields-repeater-add-row' );

			if ( addBtn ) {
				addBtn.disabled = max > 0 && count >= max;
			}

			Repeater.getRows( wrapper ).forEach( function ( row ) {
				var removeBtn = row.querySelector( ':scope > .aenimfields-repeater-remove-row' );
				if ( removeBtn ) {
					removeBtn.disabled = min > 0 && count <= min;
				}
			} );
		},

		/**
		 * Re-run other AenimFields modules so fields in a newly added row —
		 * conditional visibility, date pickers, media pickers, maps —
		 * become interactive. Each is optional; only loaded if actually
		 * used by at least one field on the page.
		 */
		reinitFields: function () {

			if ( window.AenimFields && window.AenimFields.Dependency ) {
				window.AenimFields.Dependency.evaluateAll();
			}

			if ( window.AenimFields && window.AenimFields.Datepicker ) {
				window.AenimFields.Datepicker.init();
			}

			if ( window.AenimFields && window.AenimFields.Media ) {
				window.AenimFields.Media.init();
			}

			if ( window.AenimFields && window.AenimFields.MapOsm ) {
				window.AenimFields.MapOsm.init();
			}

			if ( window.AenimFields && window.AenimFields.MapGoogle ) {
				window.AenimFields.MapGoogle.init();
			}
		},
	};

	window.AenimFields = window.AenimFields || {};
	window.AenimFields.Repeater = Repeater;

	document.addEventListener( 'DOMContentLoaded', Repeater.init );
} )( window, document );
