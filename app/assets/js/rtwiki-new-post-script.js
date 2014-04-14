/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function ($) {
    if ( getUrlVars()["rtpost_title"] ) {
        $('#title').val(getUrlVars()["rtpost_title"]);
    }
    if ( getUrlVars()["rtpost_parent"] ) {
        $('#parent_id').val(getUrlVars()["rtpost_parent"]);
    }
});

function getUrlVars() {
    var vars = [],
        hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}