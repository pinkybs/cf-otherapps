/*
----------------------------------------------
Script Editor archives JavaScript

Created Date: 2009/05/20
Author: liz

----------------------------------------------
*/

(function($) {

$().ready(function() {
    //jQuery.editor.checkValue();
    //window.clipboardData.getData("code").replace(/\t/g,   "         ");
});

$.editor = {
    
    /**
     * add entry ajax request
     * @param  null
     * @return void
     */
    newEntry : function()
    {
        var title = $j('input#txtTitle').val();
        var tag = $j('input#txtTag').val();
        var language = $j('input#dllLanguage').val();
        var content = $j('textarea#textCode').val();
        var followId = $j('input#txtFollowId').val();
        var eid = $j('input#txtEid').val();
        
        var url = UrlConfig.BaseUrl + '/ajax/scripteditor/newentry';
    
        $j.ajax({
             type: "POST",
             url: url,
             data: {title : title,
                    tag : tag,
                    language : language,
                    content : content,
                    followId : followId,
                    eid : eid},
             success: function(msg){ $.editor.renderResults(msg); } 
        });
    },
    
    /**
    * process data from server
    *
    * @param string response
    */
    renderResults : function(response)
    {
        var responseObject = $j.evalJSON(response);
        
        if ( responseObject.entryId == "") {
            $j.loadPage();
            $j('div#wrap').html('新規に失敗しました。しばらく待ってから再度おためしください。'); 
        }
        else {
            $j.loadPage();        
            var nextUrl = UrlConfig.BaseUrl + '/scripteditor/entry/eid/' + responseObject.entryId;
            countDown(1, nextUrl);
        }
    },
    
    /**
     * save entry ajax request
     * @param  null
     * @return void
     */
    saveEntry : function()
    {
        $.editor.checkCanSubmit();
        
        var title = $j('input#txtTitle').val();
        var tag = $j('input#txtTag').val();
        var language = $j('input#dllLanguage').val();
        var content = $j('textarea#textCode').val();
        var followId = $j('input#txtFollowId').val();
        var eid = $j('input#txtEid').val();
        
        if ( !title || !content ) {
            return;
        }
        
        var url = UrlConfig.BaseUrl + '/ajax/scripteditor/saveentry';
    
        $j.ajax({
             type: "POST",
             url: url,
             data: {title : title,
                    tag : tag,
                    language : language,
                    content : content,
                    followId : followId,
                    eid : eid},
             success: function(msg){ $.editor.renderResultsSaveEntry(msg); } 
        });
    },
    
    /**
    * process data from server
    *
    * @param string response
    */
    renderResultsSaveEntry : function(response)
    {
        var responseObject = $j.evalJSON(response);
        
        if ( responseObject.entryId == "") {
            $j.loadPage();
            $j('div#wrap').html('保存に失敗しました。しばらく待ってから再度おためしください。'); 
        }
        else {
            $j.loadPage();
            //var nextUrl = UrlConfig.BaseUrl + '/scripteditor/entry/' + responseObject.entryId;
            var nextUrl = UrlConfig.BaseUrl + '/scripteditor/profile?submit=2';
            countDown(1, nextUrl);
        }
    },
    /*
    checkValue : function(divId, errDivId)
    {
        var divContent = $j( divId ).val();
        //clear 'space'
        divContent = jQuery.cTrim(divContent, 0);
        
        if ( !divContent ) {
            $j( errDivId ).css({ display: "" });
        }
        else {
            $j( errDivId ).css({ display: "none" });
        }
        $.editor.checkCanSubmit();
    },
    checktextCode : function()
    {
        var divContent = $j('#textCode').val();
        //clear 'space'
        divContent = jQuery.cTrim(divContent, 0);
        
        if ( !divContent ) {
            $j('#btnPreview').attr("class","disable");
            $j('#aPreview').attr("onclick","");
        }
        else {
            $j('#btnPreview').attr("class","");
            $j('#aPreview').attr("onclick","runCode();");
        }
        $.editor.checkCanSubmit();
    },*/
    
    checkValue : function()
    {
        var txtTitle = $j('#txtTitle').val();
        //clear 'space'
        txtTitle = jQuery.cTrim(txtTitle, 0);
        
        var divContent = $j('#textCode').val();
        var isChanged = (divContent != initCode);
        
        //clear 'space'
        divContent = jQuery.cTrim(divContent, 0);

        if ( !txtTitle || !divContent) {
            $j('#btnDruft').attr("class","disable");
            //$j('a#aDruft').attr("onclick","");
            $j("#aDruft").unbind('click').removeAttr('onclick').click(function(){});
        }
        else {
            $j('#btnDruft').attr("class","");
            //$j('a#aDruft').attr("onclick","jQuery.editor.saveEntry()");
            $j("#aDruft").unbind('click').removeAttr('onclick').click(function(){jQuery.editor.saveEntry();});
        }
        
        if ( !txtTitle || !divContent || !isChanged) {
            $j('#btnPreview').attr("class","disable");
            //$j('a#aPreview').attr("onclick","");
            $j("#aPreview").unbind('click').removeAttr('onclick').click(function(){});
        }
        else {
            $j('#btnPreview').attr("class","");
            //$j('a#aPreview').attr("onclick","runCode()");
            $j("#aPreview").unbind('click').removeAttr('onclick').click(function(){runCode();});
        }
        $.editor.checkCanSubmit();
    },
    
    checkCanSubmit : function()
    {
        var title = $j('input#txtTitle').val();
        var language = $j('input#dllLanguage').val();
        var content = $j('textarea#textCode').val();
        var isChanged = (content != initCode);
        
        if ( !title || !language || !content || !isChanged) {
            $j('a#btnSubmit').css({ display: "none" });
            $j('a#btnSubmit2').css({ display: "" });
            return;
        }
        
        //get check value
        var isAgree = $('input:checked[name="agreement"]').val();

        if ( isAgree != '1' ) {
            $j('a#btnSubmit').css({ display: "none" });
            $j('a#btnSubmit2').css({ display: "" });
        }
        else {
            $j('a#btnSubmit').css({ display: "" });
            $j('a#btnSubmit2').css({ display: "none" });
        }
    }    
};
    
})(jQuery);
    


