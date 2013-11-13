jQuery(document).ready(function() {
       
   jQuery("#public").click(function () {
       
jQuery('.case , .all_na , .all_r , .all_w').prop('checked', this.checked);
});

jQuery(".case , all_na , .all_r , .all_w").click(function(){
 
        if(jQuery(".case").length == jQuery(".case:checked").length) {
            jQuery("#public").attr("checked", "checked");
        } else {
            jQuery("#public").removeAttr("checked");
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
            jQuery('.all_w , #public').removeAttr("checked");
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






});


