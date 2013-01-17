/*
----------------------------------------------
Script Editor archives JavaScript

Created Date: 2009/05/20
Author: liz

----------------------------------------------
*/

(function($) {

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 5;

/**
 * windows load function
 *  register funcion
 */
$().ready(function() {
    var archivesCount = $j('#archivesCount').val();
    var nav = showPagerNav(archivesCount, Number($j('#pageIndex').val()), CONST_DEFAULT_PAGE_SIZE, 10, '$j.archives.changePageAction');
    $j('#nav').html(nav); 
    
    var arrayCount = $j('#arrayCount').val();
    $.archives.getHeight(arrayCount);
});


$.archives = {
    /**
    * change page
    *
    * @param integer page current page index
    */
    changePageAction : function(page)
    {
        $j.loadPage();
        $j('input#pageIndex').val(page);
        var uid = $j("input#txtUid").val();
        var page = $j('input#pageIndex').val();
        var type = $j('input#type').val();
        var language = $j('input#language').val();
        
        var url = UrlConfig.BaseUrl + '/ajax/scripteditor/getarchives';
        
        //("#entryList").ajaxStart(function(){$j(this).html();});
        $j.ajax({
             type: "POST",
             url: url,
             data: {uid : uid,
                    pageIndex : page,
                    language : language,
                    type : type},
             success: function(msg){ $.archives.renderResults(msg); } 
        });
        
        
    },
    
    /**
    * process data from server
    *
    * @param string response
    */
    renderResults : function(response)
    {
        //var responseObject = response.evalJSON();
        var responseObject = $j.evalJSON(response);
        
        // no entry data
        if ( responseObject.info == "") {
            $j('div#entryList').html('<p class="null"><img src="' + UrlConfig.StaticUrl + '/apps/scripteditor/img/btn_null.png" width="310" height="50" alt="まだPHPみたいです。" /></p>'); 
        }
        // format the entry list data
        else {
            var html = $.archives.showEntryList(responseObject.info, responseObject.count, responseObject.langInfo);
    
            var nav = showPagerNav(responseObject.count, Number($j('input#pageIndex').val()), CONST_DEFAULT_PAGE_SIZE, 10, '$j.archives.changePageAction');

            $j('div#entryList').html(html + nav); 
                        
            $.archives.getHeight(responseObject.count);
            
            /*リストにホバーで'.hover'を追加*/
            var entryId;
            $j(chatUseID.listID).hover(
                function(){
                    $j(this).addClass('hover')
                    entryId = $j(this).attr("id"); 
                },
                function(){
                    $j(this).removeClass('hover')
                }
            ).click(
                function(){
                    location.href = UrlConfig.BaseUrl + '/scripteditor/entry/eid/' + entryId;
                }
            );
        }
    },
    
    
    /**
     * show entry list
     *
     */
    showEntryList : function(array, count, langInfo)
    {
        //get the start number and the end number
        var page = $j('input#pageIndex').val();
        
        var startNm = (page-1)*CONST_DEFAULT_PAGE_SIZE + 1;
        
        if (count > page*CONST_DEFAULT_PAGE_SIZE ) {
            var endNm = page*CONST_DEFAULT_PAGE_SIZE;
        }
        else {
            var endNm = count;
        }
        //count message
        var countMsg = count + '件中 ' + startNm + '-' + endNm + '件を表示';
        
        var html = '<h3>' + langInfo.language_name + '</h3>'
                 + '<p class="total">' + countMsg + '</p>'
                 + '<div class="entry">';
        
        for (i = 0 ; i < array.length ; i++)
        {
            var followCount = "";
            if ( array[i].follow_count ) {
                followCount = '(' + array[i].follow_count + ')';
            }
            else {
                followCount = '(0)';
            }
            
            if ( array[i].nickname ) {
                var nickname = array[i].nickname;
            }
            else {
                var nickname = array[i].displayName;
            }
                  
            html += '<div class="follow">'
                  + '   <div class="hdr">'
                  + '       <h4><a href="' + UrlConfig.BaseUrl + '/scripteditor/entry/eid/' + array[i].eid + '">' + $j.truncateString(array[i].title, 20, '…') + followCount + '</a></h4>'
                  + '       <ul class="status"><!--'
                  + '           --><li class="name">' + nickname + '</li><!--'
                  + '           --><li class="date">' + $j.formatToDate(array[i].create_time, 'yy/MM/dd hh:mm') + '</li><!--'
                  + '       --></ul>'
                  + '   </div><!--/.hdr-->'
                  + '   <textarea name="code" class="html" rows="6">' + array[i].content + '</textarea>'
                  + '</div><!--/.follow-->';
        }
        
        html += "</div><!--/.entry-->";
    
        return html;
    },
    
    getHeight : function(count)
    {
        dp.SyntaxHighlighter.HighlightAll('code');
        adjustHeight();
    }
};
    
})(jQuery);


