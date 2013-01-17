/*
----------------------------------------------
Millionminds top JavaScript

Created Date: 2009/07/29
Author: huch

----------------------------------------------
*/

(function($) {
    
$().ready(function() {
    //show first show pager nav
    $('#divArticle').append(jQuery.archive.showPageNav($('#archiveCount').val(), jQuery.archive.pageIndex, jQuery.archive.pageSize, 30));
});

$.archive = {
    type : 0,
    field : 1,
    countOrder : 1,
    dateOrder : 1,
    pageIndex : 1,
    pageSize  : 30,
    archiveType : ['all','character','politics','life','entertainment','hobby'],
    archiveOrder : ['','descend','ascend'],
    
    changeTab : function(type) 
    {
        jQuery.archive.type = type;
        jQuery.archive.changePage(1);
        jQuery.archive.setBannerClass();
    },
    
    changeOrder : function(field, order)
    {
        if (field == 1) {
            jQuery.archive.countOrder = order;
        }
        else {
            jQuery.archive.dateOrder = order;
        }
        
        jQuery.archive.field = field;
        
        jQuery.archive.changePage(1);
    },
    
    showMoreArchive : function()
    {
        jQuery.archive.type = 0;
        jQuery.archive.setBannerClass();
        jQuery.archive.changeOrder(1,1);
    },
    
    /**
     * change page
     *
     */
    changePage : function(pageIndex)
    {        
        jQuery.archive.pageIndex = pageIndex;
        
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/millionminds/getarchive',
            dataType: "json",
            data : {
                    type : jQuery.archive.type,
                    field : jQuery.archive.field,
                    order : jQuery.archive.field==1 ? jQuery.archive.countOrder : jQuery.archive.dateOrder,
                    pageIndex : pageIndex
                   },
            timeout : 10000,
            success : function(response) {
                $('#divArticle').children('.list').remove().end().children('.pager').remove().end().find('p').remove().end()
                                .append(jQuery.archive.showArchive(response.archive))
                                .append(jQuery.archive.showPageNav(response.count, jQuery.archive.pageIndex, jQuery.archive.pageSize, 30));
                gotoTop();
                adjustHeight(); 
            },
            error : function(request, settings) {
                $('#divArticle').children('.list').remove().end().children('.pager').remove().end().find('p').remove();
                
                if (settings == 'timeout') {
                    error = '<p>通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください。<p>';
                }
                else {
                    error = '<p>システムエラー。<p>';
                }
                
                $('#divArticle').append(error);
            }
        });
    },
    
    showPageNav : function(count, pageIndex, pageSize, pageCount)
    {    
        if (!pageCount) {
            pageCount = 10;
        }
        
        if (count <= pageSize) {
            return '';
        }
    
        var nav = '';
        var forward = '';
        var pagerLeft = '';
        var pagerCurrent = '';
        var pagerRight = '';
        var next = '';        
        var maxPage = Math.ceil(count/pageSize);
    
        if (pageIndex > 1) {
            forward += '<li><a href="javascript:void(0)" onclick="jQuery.archive.changePage(' + (pageIndex - 1) + ')">&laquo; 前へ</a></li>';
        }
        else {
            forward += '<li><span>&laquo; 前へ</span></li>';
        }
    
        if (maxPage > pageIndex) {
            next = '<li><a href="javascript:void(0)" onclick="jQuery.archive.changePage(' + (pageIndex + 1) + ')">次へ &raquo;</a></li>';
        }
        else {
            next = '<li><span>次へ &raquo;</span></li>';
        }
    
        var page = Math.ceil(pageCount/2);
    
        //all page count
        var i = 1;
    
        //left nav
        var left = 0;
        for (left = pageIndex - 1; left > 0 && left > pageIndex - page; left--) {
            i++;
            pagerLeft = '<li><a href="javascript:void(0)" onclick="jQuery.archive.changePage(' + left + ')">' + left + '</a></li>' + pagerLeft;
        }
    
        //current nav number
        pagerCurrent = '<li><strong>' + pageIndex + '</strong></li>';
    
        //right nva
        var right = 0;
        for (right = pageIndex + 1; right <= maxPage && right < pageIndex + page ; right++) {
            i++;
            pagerRight = pagerRight + '<li><a href="javascript:void(0)" onclick="jQuery.archive.changePage(' + right + ');">' + right + '</a></li>';
        }
    
        //If right side is not enough, show the page number for left until the page number number is up to 1
        if (i < pageCount && left >= 1) {
            for (; left > 0 && i < pageCount; left--,i++) {
                pagerLeft = '<li><a href="javascript:void(0)" onclick="javascript:jQuery.archive.changePage(' + left + ');">' + left + '</a></li>' + pagerLeft;
            }
        }
    
        //If left side is not enough, showthe page number for right until the page number number is up to max
        if (i < pageCount && right <= maxPage) {
            for (; right <= maxPage && i < pageCount; right++,i++) {
                pagerRight = pagerRight + '<li><a href="javascript:void(0)" onclick="javascript:jQuery.archive.changePage(' + right + ');">' + right + '</a></li>';
            }
        }
    
        nav = '<ul class="pager">' + forward + pagerLeft + pagerCurrent + pagerRight + next + '</ul>';
    
        return nav;
    },
    
    showArchive : function(array)
    {
        var l = array.length;
        
        if (l > 0) {
            var countOrderChange = jQuery.archive.countOrder==1 ? 2 : 1;
            var dateOrderChange = jQuery.archive.dateOrder==1 ? 2 : 1;
            
            var html = '';
            
            html += '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="list">'
    			  + '	<tr>'
    			  + '		<th class="title"></th>'
    			  + '		<th class="count ' + jQuery.archive.archiveOrder[jQuery.archive.countOrder] + '"><a href="javascript:void(0)" onclick="jQuery.archive.changeOrder(1,' + countOrderChange + ');">回答数</a></th>'
    			  + '		<th class="date ' + jQuery.archive.archiveOrder[jQuery.archive.dateOrder] + '"><a href="javascript:void(0)" onclick="jQuery.archive.changeOrder(2,' + dateOrderChange + ');">作成日</a></th>'
    			  + '	</tr>';
    			  
    	    for (i = 0; i < l; i++) {
    	       html += '<tr class="section">'
    	             + '   <td class="title stringCut">'
    	             + '       <a href="' + UrlConfig.BaseUrl + '/millionminds/question?qid=' + array[i].qid + '" class="ico">' + array[i].question.escapeHTML();

    	       if (array[i].hasAnswered == 0) {
    	           html += '<img src="' + UrlConfig.StaticUrl + '/apps/millionminds/img/noreply.gif" width="31" height="11" alt="" style="display: inline;" />';
    	       }
    	       
    	       html += '       </a>'
    	             + '   </td>'
					 + '   <td class="count">' + array[i].answer + '</td>'
					 + '   <td class="date">' + array[i].create_time + '</td>'
					 + '</tr>';
    	    }
    	   
    	    html += '</table>';
    	   
    	    return html;
        }
        else {
            return '<p class="null"><a href="' + UrlConfig.BaseUrl + '/millionminds/newquestion">クエスチョンを作成する</a></p>';
        }
    },
    
    setBannerClass : function()
    {
        $('#divArticle').find('ul.cat > li > a.active').removeClass().end()
                        .find('ul.cat > li.' + jQuery.archive.archiveType[jQuery.archive.type] + ' > a').addClass("active");
    }
};

})(jQuery);