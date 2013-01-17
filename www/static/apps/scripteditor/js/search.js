/*
----------------------------------------------
Script Editor archives JavaScript

Created Date: 2009/05/20
Author: liz

----------------------------------------------
*/

(function($){

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 5;

/**
 * windows load function
 *  register funcion 
 */
$().ready(function() {
    $.search.changePageAction(1, 0);
});


$.search = { 

    /**
     * search submit
     *
     * @param string content
     * @return void
     */
    sumbitSearch : function(content)
    {
        //is from search page
        $.search.changePageAction(1, 1);
    },
    
    
    /**
    * change page
    *
    * @param integer page current page index
    */
    changePageAction : function(page, isFromSearch)
    {
        $j.loadPage();
        
        $j('input#pageIndex').val(page);
        var page = $j('input#pageIndex').val();
        
        var search = $j('input#searchInput').val();
        var langType = $j('input#langType').val();

        if ( isFromSearch == '1' ) {
            langType = '0';
        }
        
        search = jQuery.ToCDB(search);
        
        //send ajax request to send note
        var url = UrlConfig.BaseUrl + '/ajax/scripteditor/search';
        
        $j.ajax({
             type: "POST",
             url: url,
             data: {search : search,
                    page : page,
                    langType : langType,
                    pageSize : CONST_DEFAULT_PAGE_SIZE},
            success: function(msg){ $.search.renderResults(msg); } 
        });
    
    },
    
    /**
     * callback funtion when success
     *  set the callback data to the showArea of the page
     *
     * @param string response
     * @return void
     */
    renderResults : function(response)
    {
        var responseObject = $j.evalJSON(response);
        
        // no entry data
        if ( responseObject.info == "") {
            var entryHtml = $.search.showNoEntry(responseObject.search);
            var tagHtml = $.search.showTagList(responseObject.tagList);
            
            var heightFF = 880;
            var heightIE = 875;
            
            if ( !$j.browser.msie ) {
                adjustHeight(heightFF);
            }
            else {
                adjustHeight(heightIE);
            }
            
            $j('div#follows').html(entryHtml + tagHtml);
        }
        // format the circle list data
        else {
            var html = $.search.showEntryList(responseObject.info, responseObject.countInfo, responseObject.isSearchAll, responseObject.search);
    
            var nav = showPagerNav(responseObject.countInfo.allCount, Number($j('input#pageIndex').val()), CONST_DEFAULT_PAGE_SIZE, 10, 'jQuery.search.changePageAction' );
                    
            $j('div#follows').html(html + nav); 
            
            dp.SyntaxHighlighter.HighlightAll('code');
            adjustHeight();
        }
        
        
        if ( $j('input#isOnFocus').val() ) {
            $j("input#searchInput").focus();
        }
    },
    
    /**
     * show entry list
     *
     */
    showEntryList : function(array, countInfo, isSearchAll, search)
    {
        //page index value
        var page = $j('input#pageIndex').val();
        //start number
        var startNm = (page-1)*CONST_DEFAULT_PAGE_SIZE + 1;
        //all count
        var count = countInfo.allCount;
        //get search type
        if ( isSearchAll == '1' ) {
            var searchType = 'すべて';
            
            //get search info by language 
            var languageHtml = '<div>言語選択オプション：'
                             + '    <ul>';
            
            for ( j = 0; j < countInfo.languageCount; j++ ) {
                if ( countInfo[j].count > 0 ) {
                    languageHtml += '<li><a href="' + UrlConfig.BaseUrl + '/scripteditor/search?search=' + search + '&langType=' + countInfo[j].id + '">' + countInfo[j].language_name + '（' + countInfo[j].count + '）</a></li>';
                }
            }
            
            languageHtml += '   </ul>'
                          + '</div>';
        }
        else {
            var searchType = countInfo.language_name;
            var languageHtml = '';
        }
        //get end number
        if ( count > page*CONST_DEFAULT_PAGE_SIZE ) {
            var endNm = page*CONST_DEFAULT_PAGE_SIZE;
        }
        else {
            var endNm = count;
        }
      
        if ( search ) {
            var searchString = '<strong>' + search + '</strong>に対する<strong>';
        }
        else {
            var searchString = "";
        }
        var countMsg = searchString + searchType + '</strong>のエントリー　<strong>' + count + '</strong>件中 <strong>' + startNm + '</strong>-<strong>' + endNm + '</strong>件を表示</p>';
        
        var html = '<h3>検索結果</h3>'
                 + '<div class="total">'
                 + '    <p>' + countMsg + '</p>'
                 + languageHtml
                 + '</div>';
        
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
    
        return html;
    },
    
    /**
     * show no entry 
     *
     */
    showNoEntry : function(search)
    {
    
        var html = '<div class="section">'
                 + '    <h3>検索結果</h3>'
                 + '    <div class="total">'
                 + '        <p><strong>' + search + '</strong>に一致するエントリーは見つかりませんでした。</p>'
                 + '    </div>'
                 + '    <p class="null"><img src="' + UrlConfig.StaticUrl + '/apps/scripteditor/img/btn_null.png" width="310" height="50" alt="まだnullみたいです。" /></p>'
                 + '</div><!--/.section-->';
        
        return html;
    },
    
    /**
     * show tag list
     *
     */
    showTagList : function(tagList)
    {
        var html = '<div id="tagCloud" class="section">'
                 + '    <h3>タグクラウド</h3>'
                 + '    <ul><!--';
                 
        for ( i=0,count=tagList.length; i<count; i++ ) {
            html +='--><li class="tagCloudSize' + tagList[i].tagClass + '"><a href="' + UrlConfig.BaseUrl + '/scripteditor/search?search=' + tagList[i].tag + '">' + tagList[i].tag + '</a></li><!--';
        }
        
        html += '   --></ul>'
              + '</div>';
        
        return html;
    }
    
};

})(jQuery);