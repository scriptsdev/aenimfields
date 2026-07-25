/**
 * FieldsBox — repeater fields: add/remove rows.
 *
 * Clones the `<template class="fieldsbox-repeater-template">` rendered by
 * templates/fields/repeater.php, substituting its `__INDEX__` placeholder
 * for a real, ever-increasing row index, and re-runs FieldsBox's other
 * modules (conditional visibility, date pickers, media pickers) so fields
 * in a newly added row work immediately — each check is optional, since a
 * repeater's sub-fields might not use any of them.
 *
 * Exposed as `window.FieldsBox.Repeater` so other code can call
 * `FieldsBox.Repeater.init()` to pick up dynamically-added repeaters.
 */
( function ( window, document ) {
	'use strict';

	var Repeater = {

		/**
		 * Wire up every not-yet-initialized repeater on the page.
		 */
		init: function () {
			document
				.querySelectorAll( '[data-fieldsbox-repeater]:not([data-fieldsbox-repeater-ready])' )
				.forEach( Repeater.setup );
		},

		/**
		 * @param {HTMLElement} wrapper
		 */
		setup: function ( wrapper ) {

			wrapper.setAttribute( 'data-fieldsbox-repeater-ready', '' );

			var addBtn = wrapper.querySelector( ':scope > .fieldsbox-repeater-add-row' );

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
			var container = wrapper.querySelector( ':scope > .fieldsbox-repeater-rows' );
			return container ? Array.prototype.slice.call( container.children ) : [];
		},

		/**
		 * Clone the row template and append it.
		 *
		 * @param {HTMLElement} wrapper
		 */
		addRow: function ( wrapper ) {

			var max = parseInt( wrapper.getAttribute( 'data-fieldsbox-repeater-max' ) || '0', 10 );

			if ( max > 0 && Repeater.getRows( wrapper ).length >= max ) {
				return;
			}

			var template      = wrapper.querySelector( ':scope > .fieldsbox-repeater-template' );
			var rowsContainer = wrapper.querySelector( ':scope > .fieldsbox-repeater-rows' );

			if ( ! template || ! rowsContainer ) {
				return;
			}

			var nextIndex = parseInt( wrapper.getAttribute( 'data-fieldsbox-repeater-next-index' ) || '0', 10 );
			var html      = template.innerHTML.split( '__INDEX__' ).join( String( nextIndex ) );

			rowsContainer.insertAdjacentHTML( 'beforeend', html );
			wrapper.setAttribute( 'data-fieldsbox-repeater-next-index', String( nextIndex + 1 ) );

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

			var removeBtn = row.querySelector( ':scope > .fieldsbox-repeater-remove-row' );

			if ( ! removeBtn ) {
				return;
			}

			removeBtn.addEventListener( 'click', function ( e ) {

				e.preventDefault();

				var min = parseInt( wrapper.getAttribute( 'data-fieldsbox-repeater-min' ) || '0', 10 );

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

			var min    = parseInt( wrapper.getAttribute( 'data-fieldsbox-repeater-min' ) || '0', 10 );
			var max    = parseInt( wrapper.getAttribute( 'data-fieldsbox-repeater-max' ) || '0', 10 );
			var count  = Repeater.getRows( wrapper ).length;
			var addBtn = wrapper.querySelector( ':scope > .fieldsbox-repeater-add-row' );

			if ( addBtn ) {
				addBtn.disabled = max > 0 && count >= max;
			}

			Repeater.getRows( wrapper ).forEach( function ( row ) {
				var removeBtn = row.querySelector( ':scope > .fieldsbox-repeater-remove-row' );
				if ( removeBtn ) {
					removeBtn.disabled = min > 0 && count <= min;
				}
			} );
		},

		/**
		 * Re-run other FieldsBox modules so fields in a newly added row —
		 * conditional visibility, date pickers, media pickers — become
		 * interactive. Each is optional; only loaded if actually used by
		 * at least one field on the page.
		 */
		reinitFields: function () {

			if ( window.FieldsBox && window.FieldsBox.Dependency ) {
				window.FieldsBox.Dependency.evaluateAll();
			}

			if ( window.FieldsBox && window.FieldsBox.Datepicker ) {
				window.FieldsBox.Datepicker.init();
			}

			if ( window.FieldsBox && window.FieldsBox.Media ) {
				window.FieldsBox.Media.init();
			}
		},
	};

	window.FieldsBox = window.FieldsBox || {};
	window.FieldsBox.Repeater = Repeater;

	document.addEventListener( 'DOMContentLoaded', Repeater.init );
} )( window, document );
