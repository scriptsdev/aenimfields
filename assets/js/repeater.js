/**
 * AenimFields — repeater fields: add/remove rows, collapsible row header.
 *
 * Clones the `<template class="aenimfields-repeater-template">` rendered by
 * templates/fields/repeater.php, substituting its `__INDEX__` and
 * `__ROW_NUMBER__` placeholders for a real, ever-increasing row index and
 * 1-based row number, and re-runs AenimFields's other modules (conditional
 * visibility, date pickers, media pickers) so fields in a newly added row
 * work immediately — each check is optional, since a repeater's sub-fields
 * might not use any of them.
 *
 * Each row's header (rendered by the template) doubles as a collapse
 * toggle and shows a title mirroring the row's `title_field` sub-field
 * value, kept in sync here as the user types.
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
				Repeater.bindRow( wrapper, row );
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
			var html      = template.innerHTML
				.split( '__INDEX__' ).join( String( nextIndex ) )
				.split( '__ROW_NUMBER__' ).join( String( nextIndex + 1 ) );

			rowsContainer.insertAdjacentHTML( 'beforeend', html );
			wrapper.setAttribute( 'data-aenimfields-repeater-next-index', String( nextIndex + 1 ) );

			var newRow = rowsContainer.lastElementChild;

			if ( newRow ) {
				Repeater.bindRow( wrapper, newRow );
			}

			Repeater.reinitFields();
			Repeater.updateButtons( wrapper );
		},

		/**
		 * Wire up one row: remove button, collapse toggle, title sync.
		 *
		 * @param {HTMLElement} wrapper
		 * @param {HTMLElement} row
		 */
		bindRow: function ( wrapper, row ) {
			Repeater.bindRemove( wrapper, row );
			Repeater.bindToggle( row );
			Repeater.bindTitleSync( row );
		},

		/**
		 * @param {HTMLElement} wrapper
		 * @param {HTMLElement} row
		 */
		bindRemove: function ( wrapper, row ) {

			var removeBtn = row.querySelector( ':scope > .aenimfields-repeater-row-header .aenimfields-repeater-remove-row' );

			if ( ! removeBtn ) {
				return;
			}

			removeBtn.addEventListener( 'click', function ( e ) {

				e.preventDefault();
				e.stopPropagation();

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
		 * Collapse/expand a row's body when its header is activated —
		 * uses the native `hidden` attribute so it works with no CSS from
		 * the consuming plugin; a `aenimfields-repeater-row--collapsed`
		 * class is toggled on the row alongside it for anything the
		 * consuming plugin wants to style (e.g. rotating the chevron).
		 *
		 * @param {HTMLElement} row
		 */
		bindToggle: function ( row ) {

			var header = row.querySelector( ':scope > .aenimfields-repeater-row-header' );
			var body   = row.querySelector( ':scope > .aenimfields-repeater-row-body' );

			if ( ! header || ! body ) {
				return;
			}

			var toggle = function () {
				var collapsed = header.getAttribute( 'aria-expanded' ) === 'true';

				header.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
				row.classList.toggle( 'aenimfields-repeater-row--collapsed', collapsed );

				if ( collapsed ) {
					body.setAttribute( 'hidden', '' );
				} else {
					body.removeAttribute( 'hidden' );
				}
			};

			header.addEventListener( 'click', toggle );

			header.addEventListener( 'keydown', function ( e ) {
				if ( 'Enter' === e.key || ' ' === e.key || 'Spacebar' === e.key ) {
					e.preventDefault();
					toggle();
				}
			} );
		},

		/**
		 * Mirror the row's `title_field` sub-field value into its header
		 * title as the user types, falling back to the server-rendered
		 * "Row N" text when the field is empty.
		 *
		 * @param {HTMLElement} row
		 */
		bindTitleSync: function ( row ) {

			var targetId = row.getAttribute( 'data-aenimfields-repeater-title-target' );
			var titleEl  = row.querySelector( ':scope > .aenimfields-repeater-row-header > .aenimfields-repeater-row-title' );

			if ( ! targetId || ! titleEl ) {
				return;
			}

			var input = document.getElementById( targetId );

			if ( ! input ) {
				return;
			}

			var fallback = row.getAttribute( 'data-aenimfields-repeater-title-fallback' ) || '';

			var sync = function () {
				var value = ( input.value || '' ).trim();
				titleEl.textContent = '' !== value ? value : fallback;
			};

			input.addEventListener( 'input', sync );
			input.addEventListener( 'change', sync );
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
				var removeBtn = row.querySelector( ':scope > .aenimfields-repeater-row-header .aenimfields-repeater-remove-row' );
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
