jQuery(document).ready(function() {
       
   jQuery('#public').click(function () {
       
jQuery(' .case#r , .all_r').prop('checked', this.checked);
jQuery('.case#na ,.all_na').attr('disabled',true);
jQuery('.case#na ,.all_na').removeAttr('checked');

});

jQuery('.all_r').click(function(){
 
        if(jQuery('.all_r').length == jQuery('.all_r:checked').length) {
            jQuery('#public').attr('checked', 'checked');
        } else {
            jQuery('#public').removeAttr('checked');
        }
 
    });




jQuery('.all_na').click(function()
{
 jQuery('.case#na').prop('checked', this.checked);
});

jQuery('.all_r').click(function()
{
 jQuery('.case#r').prop('checked', this.checked);
});

jQuery('.all_w').click(function()
{
 jQuery('.case#w').prop('checked', this.checked);
});

jQuery('.case#w').click(function(){
 
        if(jQuery('.case#w').length == jQuery(".case#w:checked").length) {
            jQuery(".all_w").attr("checked", "checked");
        } else {
            jQuery('.all_w').removeAttr("checked");
        }
 
    });
    
 jQuery('.case#r').click(function(){
 
        if(jQuery('.case#r').length == jQuery(".case#r:checked").length) {
            jQuery(".all_r").attr("checked", "checked");
        } else {
            jQuery('.all_r, #public').removeAttr("checked");
        }
 
    });
    
  jQuery('.case#na').click(function(){
 
        if(jQuery('.case#na').length == jQuery(".case#na:checked").length) {
            jQuery(".all_na").attr("checked", "checked");
        } else {
            jQuery('.all_na , #public').removeAttr("checked");
        }
 
    });  


jQuery('#reset').on('click',function()
{
    jQuery('input[type="radio"] , #public').removeAttr('checked');
    jQuery('input[type="radio"], #public').attr('disabled',false);
    
    
});

jQuery('.all_na').click(function()
{
 jQuery('.all_r , .all_w , .case#r , .case#w , #public').attr('disabled',true);
  
});

jQuery('.all_w').click(function()
{
 jQuery('.all_r , .all_na , .case#r , .case#na , #public').attr('disabled',true);
});

jQuery('.all_r ').click(function()
{
 jQuery('.all_w , .all_na , .case#w , .case#na').attr('disabled',true);
});



});


