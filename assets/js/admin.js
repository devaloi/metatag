/**
 * MetaTag Admin Scripts
 *
 * Character counters for SEO title and description fields.
 *
 * @package MetaTag
 */
( function () {
	'use strict';

	function updateCounter( input ) {
		var targetId = input.getAttribute( 'data-target' );
		var limit = parseInt( input.getAttribute( 'data-limit' ), 10 );
		var counter = document.getElementById( targetId );

		if ( ! counter ) {
			return;
		}

		var length = input.value.length;
		counter.textContent = length;

		if ( length > limit ) {
			counter.classList.add( 'over-limit' );
		} else {
			counter.classList.remove( 'over-limit' );
		}
	}

	function init() {
		var fields = document.querySelectorAll( '.metatag-char-count' );

		fields.forEach( function ( field ) {
			updateCounter( field );

			field.addEventListener( 'input', function () {
				updateCounter( field );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
