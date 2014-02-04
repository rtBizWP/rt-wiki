jQuery(document).ready(function() {
//    jQuery('#rtwiki_public_r').click(function() {
//    jQuery(' .case#r').prop('checked', this.checked);
//   });
//   
  
   
    jQuery('.rtwiki_all_na').click(function()
    {
        jQuery('.case#na').prop('checked', this.checked);
    });

    jQuery('.rtwiki_all_r').click(function()
    {
        jQuery('.case#r').prop('checked', this.checked);
    });

    jQuery('.rtwiki_all_w').click(function()
    {
        jQuery('.case#w').prop('checked', this.checked);
    });

    jQuery('#reset').on('click', function()
    {
        jQuery('input[type="radio"] , #public').removeAttr('checked');
        jQuery('input[type="radio"], #public').attr('disabled', false);
    });
    
});

function uncheckAll() {
    jQuery('.rtwiki_all_na').prop('checked', false); 
    jQuery('.rtwiki_all_r').prop('checked', false); 
    jQuery('.rtwiki_all_w').prop('checked', false);
}
