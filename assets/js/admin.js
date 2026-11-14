/* Degriz Hazard - PDF pickers in the product editor. */
( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.degriz-hz-pick', function ( event ) {
		event.preventDefault();
		var $row = $( this ).closest( '.degriz-hz-file' );
		var frame = wp.media( {
			title: window.degrizHazard.chooseTitle,
			button: { text: window.degrizHazard.chooseButton },
			library: { type: 'application/pdf' },
			multiple: false
		} );
		frame.on( 'select', function () {
			var file = frame.state().get( 'selection' ).first().toJSON();
			$row.find( 'input[type=hidden]' ).val( file.id );
			$row.find( '.degriz-hz-file-name' ).text( file.title || file.filename );
		} );
		frame.open();
	} );

	$( document ).on( 'click', '.degriz-hz-clear', function ( event ) {
		event.preventDefault();
		var $row = $( this ).closest( '.degriz-hz-file' );
		$row.find( 'input[type=hidden]' ).val( '' );
		$row.find( '.degriz-hz-file-name' ).text( '' );
	} );
}( jQuery ) );
