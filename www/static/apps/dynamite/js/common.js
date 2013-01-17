
(function($) {

$().ready(function() {
   
});

$.common = {
    
    close : function(parm)
    {   
        $('#overlay').html('');
        $('#overlay').hide();
        
        var itemId = $('#useCard').val();
        var useCardResult = $('#useCardResult').val();
        
        if (useCardResult == 1) {
            if (itemId >0 && itemId <= 10) {
                jQuery.dynamite.goUserDynamite($('txtUid').val());
            
                if (itemId == 5 || itemId == 7 || itemId == 8) {
                    jQuery.dynamite.updateBombBox($('txtUid').val());
                }
            }
        }
       
        $('#dynamiteBody').unbind('click');
    },
    
    removeClickEvent : function()
    {   
        $('#dynamiteBody').unbind('click');
    },
    
    addClickEvent : function(parm, type, status)
    {   
        $('#dynamiteBody').bind('click', function(){
            if (parm == 1) {
               // $.dynamite.updateBombBox(userId);
              //  $.dynamite.goUserDynamite(userDynamite.uid);
            
              //  clearTimeout(autoCloseTimeOut);
                $.dynamite.removeOverlay(type, status);
            }
            
            else {
                jQuery.common.close();
            }
        });
       
    },
    
    commonClose : function()
    {
        $('#overlay').html('');
        $('#overlay').hide();
    }

};

})(jQuery);