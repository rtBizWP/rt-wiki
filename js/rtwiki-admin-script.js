/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function uncheckAll(arg) {
    if (arg == 'na' || arg == 'r' || arg == 'w') {
        jQuery('.rtwiki_all_na').prop('checked', false);
        jQuery('.rtwiki_all_w').prop('checked', false);
    }
    if (arg == 'na' || arg == 'r') {
        jQuery('.rtwiki_all_r').prop('checked', false);
    }
}

function uncheckAllGroup(arg) {
    if (arg == 'na' || arg == 'r' || arg == 'w') {
        jQuery('.case_na').prop('checked', false);
        jQuery('.case_r').prop('checked', false);
    }
    if (arg == 'na' || arg == 'w' ) {
        jQuery('.case_w').prop('checked', false);
    }
}

