jQuery(document).ready(function() {

    jQuery('#public').click(function() {

        jQuery(' .case#r , .rtwiki_all_r').prop('checked', this.checked);


    });

    jQuery('.rtwiki_all_r').click(function() {

        if (jQuery('.all_r').length === jQuery('.all_r:checked').length) {
            jQuery('#public').attr('checked', 'checked');
        } else {
            jQuery('#public').removeAttr('checked');
        }

    });

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


