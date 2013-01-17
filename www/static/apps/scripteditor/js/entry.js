
(function($) {

$.entry = {
    download : function() {
    
        var id = $('input#txtId').val();
        
        location.href = UrlConfig.BaseUrl + '/ajax/scripteditor/download/id/' + id;
        
    }
};

})(jQuery);


