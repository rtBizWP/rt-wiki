/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function uncheckAll(element,arg) {
	if (element.checked) {
	    if (arg == 'na' || arg == 'r' || arg == 'w') {
	        jQuery('.rtwiki_all_na').prop('checked', false);
	        jQuery('.rtwiki_all_w').prop('checked', false);
		    jQuery('.rtwiki_all_r').prop('checked', false);
	    }
	}
	jQuery(".rtwiki_pub").prop("checked", false);
	jQuery(".rtwiki_na").prop("disabled", false);
}

function uncheckAllGroup(element,arg) {
	if (element.checked) {
	    if (arg == 'na') {
	        jQuery('.rtwiki_na').prop('checked', true);
	        jQuery('.rtwiki_r').prop('checked', false);
	        jQuery('.rtwiki_w').prop('checked', false);
	    } else if (arg == 'r') {
	        jQuery('.rtwiki_na').prop('checked', false);
	        jQuery('.rtwiki_r').prop('checked', true);
	    } else if (arg == 'w') {
	        jQuery('.rtwiki_na').prop('checked', false);
	        jQuery('.rtwiki_w').prop('checked', true);
	    }
	}
	jQuery(".rtwiki_pub").prop("checked", false);
	jQuery(".rtwiki_na").prop("disabled", false);
}

