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
    if (arg == 'na') {
        jQuery('.case_na').prop('checked', true);
        jQuery('.case_r').prop('checked', false);
        jQuery('.case_w').prop('checked', false);
    } else if (arg == 'r') {
        jQuery('.case_na').prop('checked', false);
        jQuery('.case_r').prop('checked', true);
    } else if (arg == 'w') {
        jQuery('.case_na').prop('checked', false);
        jQuery('.case_w').prop('checked', true);
    }
}

