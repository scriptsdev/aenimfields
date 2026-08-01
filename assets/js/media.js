/**
 * AenimFields — image/gallery/file fields, powered by wp.media().
 *
 * Wires up the "Select"/"Remove" buttons rendered by
 * templates/fields/{image,gallery,file}.php to WordPress's native media
 * modal, and keeps each field's hidden input (the actual submitted
 * value — an attachment ID, or a comma-separated list for galleries) in
 * sync with what's selected.
 *
 * Exposed as `window.AenimFields.Media` so other code — e.g. a script
 * that injects new fields dynamically — can call
 * `AenimFields.Media.init()` to pick up newly-added fields.
 */
( function ( window, document ) {
	'use strict';

	var Media = {

		/**
		 * Wire up every not-yet-initialized media field on the page.
		 *
		 * Safe to call repeatedly; already-initialized fields are skipped.
		 */
		init: function () {
			document
				.querySelectorAll( '[data-aenimfields-media]:not([data-aenimfields-media-ready])' )
				.forEach( Media.setup );
		},

		/**
		 * @param {HTMLElement} wrapper
		 */
		setup: function ( wrapper ) {

			wrapper.setAttribute( 'data-aenimfields-media-ready', '' );

			var type      = wrapper.getAttribute( 'data-aenimfields-media' );
			var input     = wrapper.querySelector( 'input[type="hidden"]' );
			var selectBtn = wrapper.querySelector( '.aenimfields-media-select' );
			var removeBtn = wrapper.querySelector( '.aenimfields-media-remove' );

			if ( ! input ) {
				return;
			}

			if ( selectBtn ) {
				selectBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					Media.openFrame( wrapper, type, input );
				} );
			}

			if ( removeBtn ) {
				removeBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					Media.clear( wrapper, input );
				} );
			}

			wrapper.querySelectorAll( '.aenimfields-media-gallery-remove-item' ).forEach( function ( button ) {
				Media.bindGalleryRemove( button, input );
			} );
		},

		/**
		 * Open the wp.media() frame and handle its selection.
		 *
		 * @param {HTMLElement} wrapper
		 * @param {string}      type    'image', 'gallery', or 'file'.
		 * @param {HTMLElement} input
		 */
		openFrame: function ( wrapper, type, input ) {

			if ( ! window.wp || ! window.wp.media ) {
				return;
			}

			var isGallery = 'gallery' === type;
			var isImage   = 'image' === type || isGallery;

			var frame = window.wp.media( {
				title: wrapper.getAttribute( 'data-aenimfields-media-title' ) || '',
				library: isImage ? { type: 'image' } : {},
				multiple: isGallery,
			} );

			frame.on( 'select', function () {

				var selection = frame.state().get( 'selection' ).toJSON();

				if ( isGallery ) {
					Media.appendGalleryItems( wrapper, input, selection );
				} else {
					Media.setSingle( wrapper, input, selection[ 0 ], isImage );
				}
			} );

			frame.open();
		},

		/**
		 * Apply a single selected attachment (image or file field).
		 *
		 * @param {HTMLElement} wrapper
		 * @param {HTMLElement} input
		 * @param {Object}      attachment
		 * @param {boolean}     isImage
		 */
		setSingle: function ( wrapper, input, attachment, isImage ) {

			if ( ! attachment ) {
				return;
			}

			input.value = attachment.id;

			var preview   = wrapper.querySelector( '.aenimfields-media-preview' );
			var removeBtn = wrapper.querySelector( '.aenimfields-media-remove' );

			if ( preview ) {

				if ( isImage ) {
					var size = ( attachment.sizes && ( attachment.sizes.thumbnail || attachment.sizes.full ) ) || { url: attachment.url };
					preview.innerHTML = '<img src="' + size.url + '" alt="">';
				} else {
					preview.innerHTML =
						'<a href="' + attachment.url + '" target="_blank" rel="noopener noreferrer" class="aenimfields-media-filename">' +
						attachment.filename +
						'</a>';
				}

				preview.hidden = false;
			}

			if ( removeBtn ) {
				removeBtn.hidden = false;
			}

			Media.dispatchChange( input );
		},

		/**
		 * Clear a single image/file field.
		 *
		 * @param {HTMLElement} wrapper
		 * @param {HTMLElement} input
		 */
		clear: function ( wrapper, input ) {

			input.value = '';

			var preview   = wrapper.querySelector( '.aenimfields-media-preview' );
			var removeBtn = wrapper.querySelector( '.aenimfields-media-remove' );

			if ( preview ) {
				preview.innerHTML = '';
				preview.hidden = true;
			}

			if ( removeBtn ) {
				removeBtn.hidden = true;
			}

			Media.dispatchChange( input );
		},

		/**
		 * Append newly selected images to a gallery field.
		 *
		 * @param {HTMLElement} wrapper
		 * @param {HTMLElement} input
		 * @param {Object[]}    selection
		 */
		appendGalleryItems: function ( wrapper, input, selection ) {

			var list = wrapper.querySelector( '.aenimfields-media-gallery-preview' );
			var ids  = input.value ? input.value.split( ',' ) : [];

			selection.forEach( function ( attachment ) {

				if ( ids.indexOf( String( attachment.id ) ) !== -1 ) {
					return;
				}

				ids.push( String( attachment.id ) );

				if ( ! list ) {
					return;
				}

				var size = ( attachment.sizes && ( attachment.sizes.thumbnail || attachment.sizes.full ) ) || { url: attachment.url };

				var li = document.createElement( 'li' );
				li.setAttribute( 'data-aenimfields-media-id', attachment.id );

				var img = document.createElement( 'img' );
				img.src = size.url;
				li.appendChild( img );

				var removeBtn = document.createElement( 'button' );
				removeBtn.type = 'button';
				removeBtn.className = 'aenimfields-media-gallery-remove-item';
				removeBtn.setAttribute( 'aria-label', 'Remove image' );
				removeBtn.innerHTML = '&times;';
				li.appendChild( removeBtn );

				list.appendChild( li );

				Media.bindGalleryRemove( removeBtn, input );
			} );

			input.value = ids.join( ',' );

			Media.dispatchChange( input );
		},

		/**
		 * Bind a gallery item's remove button.
		 *
		 * @param {HTMLElement} button
		 * @param {HTMLElement} input
		 */
		bindGalleryRemove: function ( button, input ) {

			button.addEventListener( 'click', function ( e ) {

				e.preventDefault();

				var li = button.closest( 'li' );
				var id = li ? li.getAttribute( 'data-aenimfields-media-id' ) : null;

				if ( id ) {
					var ids = ( input.value ? input.value.split( ',' ) : [] ).filter( function ( existing ) {
						return existing !== id;
					} );
					input.value = ids.join( ',' );
				}

				if ( li && li.parentNode ) {
					li.parentNode.removeChild( li );
				}

				Media.dispatchChange( input );
			} );
		},

		/**
		 * Fire a native change event so other code (e.g. AenimFields.Dependency)
		 * reacts to the field's new value.
		 *
		 * @param {HTMLElement} input
		 */
		dispatchChange: function ( input ) {
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		},
	};

	window.AenimFields = window.AenimFields || {};
	window.AenimFields.Media = Media;

	document.addEventListener( 'DOMContentLoaded', Media.init );
} )( window, document );
