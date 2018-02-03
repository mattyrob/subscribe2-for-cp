/* global s2_script_strings */
/* exported s2_bulk_action_check, submitHandler, bm_check, s2_script_strings */
// Version 1.0 - original version
// Version 1.1 - context specific delete warnings for registered or public subscribers
// Version 1.2 - single and plural string confirmation messages
// Version 1.3 - fix suppressed form processing for Toggle action, JavaScript binding only if elements exist & more effecient handling when no users selected
// Version 1.4 - improve Bulk Management user experience and functions

function s2_bulk_action_check() {
	if ( null !== document.getElementById( 'doaction' ) ) {
		document.getElementById( 'doaction' ).onclick = submitHandler;
		document.getElementById( 'doaction2' ).onclick = submitHandler;
	}
}
function submitHandler() {
	var location, action1, action2, agree, selected;
	location = document.getElementById( 's2_location' );
	action1 = document.getElementById( 'bulk-action-selector-top' );
	action2 = document.getElementById( 'bulk-action-selector-bottom' );
	agree = false;
	selected = document.querySelectorAll( 'input[name="subscriber[]"]:checked' ).length;
	if ( 0 === selected ) {
		return true;
	}
	if ( 'delete' === action1.value || 'delete' === action2.value ) {
		if ( 'registered' === location.value ) {
			if ( selected > 1 ) {
				agree = window.confirm( s2_script_strings.registered_confirm_plural );
			} else {
				agree = window.confirm( s2_script_strings.registered_confirm_single );
			}
		} else if ( 'public' === location.value ) {
			if ( selected > 1 ) {
				agree = window.confirm( s2_script_strings.public_confirm_plural );
			} else {
				agree = window.confirm( s2_script_strings.public_confirm_single );
			}
		}
	} else if ( 'toggle' === action1.value || 'toggle' === action2.value ) {
		agree = true;
	}
	return agree;
}
function bm_check() {
	var agree, selected;
	agree = false;
	selected = document.querySelectorAll( 'input[name="subscriber[]"]:checked' ).length;
	if ( 0 === selected ) {
		agree = window.confirm( s2_script_strings.bulk_manage_all );
	} else if ( selected > 1 ) {
		agree = window.confirm( s2_script_strings.bulk_manage_single );
	} else {
		agree = window.confirm( s2_script_strings.bulk_manage_plural );
	}
	return agree;
}
window.onload = s2_bulk_action_check;