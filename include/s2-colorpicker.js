// Version 1.0 - original version
// Version 1.1 - Update for Subscribe2 9.0 to remove unecessary code now WordPress 3.3 is minimum requirement
// Version 1.2 - Initialise the colour fields on page load so they are the correct colour
// Version 1.3 - eslinted
// Version 1.4 - Update to wpColorPicker
// Version 1.5 - Ensure 'widget' is defined in onFormChange

( function( jQuery ){
	function initColorPicker( widget ) {
		widget.find( '.colorpickerField' ).not( '[id*="__i__"]' ).wpColorPicker( {
			change: function( event, ui ) {
				jQuery( event.target ).val( ui.color.toString() );
				jQuery( event.target ).trigger( 'change' );
				// cannot rely on jQuery to trigger event due to experimental block widget editor
				event.target.dispatchEvent( new Event( 'change', { 'bubbles': true, 'cancelable': false } ) );
			},
			clear: function( event ) {
				jQuery( event.target ).trigger( 'change' );
				// cannot rely on jQuery to trigger event due to experimental block widget editor
				event.target.dispatchEvent( new Event( 'change', { 'bubbles': true, 'cancelable': false } ) );
			}
		} );
	}

	function onFormUpdate( event, widget ) {
		if ( undefined !== widget ) {
			initColorPicker( widget );
		}
	}

	jQuery( document ).on( 'widget-added widget-updated', onFormUpdate );

	jQuery( document ).ready( function() {
		jQuery( '.widget-inside:has(.colorpickerField)' ).each( function () {
			initColorPicker( jQuery( this ) );
		} );
	} );

} )( jQuery );
