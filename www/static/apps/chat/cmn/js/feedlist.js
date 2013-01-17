/**
 * feedlist(/chat/feedlist.js)
 * chat feedlist
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/12    zhangxin
 */

var CONST_DEFAULT_PAGE_SIZE = 30;

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	if (null != cm_getCookie('app_top_url') && null != $j("#pageIndex").val()) {
		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	}
	
	changePageAction(1);
});

/**
 * jquery get the feed list
 *
 * @return void
 */
function changePageAction(page)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/getfeedlist';
    if (null != $j("#pageIndex").val()) {
		$j("#pageIndex").val(page);
	}
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "pageIndex=" + page + "&pageSize=" + CONST_DEFAULT_PAGE_SIZE,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var strHtml = showFeedInfo(responseObject.info);
	            	if (null != $j("#pageIndex").val()) {
	            		var nav = showPagerNav(responseObject.count, parseInt($j("#pageIndex").val()), CONST_DEFAULT_PAGE_SIZE);
	            		strHtml += nav;
	            	}
	            	else {	            
	            		strHtml += '<div class="morelink"><a href="' + UrlConfig.BaseUrl + '/chat/feedlist">＞お知らせ一覧</a></div>';
	            	}
	            	$j('#newslist').html(strHtml);

	            	adjustHeight();
	            }
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
		    	//alert(textStatus);
		    }
		});
	}
	catch (e) {
		//alert(e);
	}
}

/**
 * jquery show Feed list
 * @param  object array
 * @return string
 */
function showFeedInfo(array)
{
    //concat html tags to array data
    var html = '';      
    if (null == array ||0 == array.length) {
    	html += '<p><img src="' +  UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/content/txt_info_null.png" width="630" height="100" alt="まだお知らせはありません" /></p>';
    	return html;
    }
      
	html += '<table class="innerTable">';
	html += '  <tr>';
	html += '    <th scope="col">お知らせの内容</th>';
	html += '    <th scope="col">お知らせの日時</th>';
	html += '  </tr>';
	
    //for each row data    
    for (var i = 0 ; i < array.length ; i++) {
    	var cssClass = '';
        if (1 == i % 2) {
        	cssClass = ' class="odd" ';
        }
        html += '  <tr' + cssClass + '>';
        html += '    <td class="contents"><a onclick="delFeed(' + array[i].id + ');" href="' + UrlConfig.BaseUrl + array[i].link + '">' + (array[i].message) + '</a></td>';
        html += '    <td class="date">' + array[i].format_time + '</td>';
        html += '  </tr>';   
    }
    html += '</table><!--/.innerTable-->';
    return html;
}

function delFeed(id) 
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/delfeed';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "id=" + id,
		    dataType: "text",
		    success: function(response) {
	            
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
		    	//alert(textStatus);
		    }
		});
	}
	catch (e) {
		//alert(e);
	}
}

/**
 * show page nav
 *
 * @param integer count
 * @param integer pageindex
 * @param integer pagesize
 * @param integer pagecount
 * @return string
 */
function showPagerNav(count,pageindex,pagesize,pagecount,action)
{
    if (!pagecount) {
        pagecount = 10;
    }

    if (!action) {
        action = 'changePageAction';
    }

    if (count <= pagesize) {
        return '';
    }

    var nav = '';

    var forward = '';
    var pagerleft = '';
    var pagercurrent = '';
    var pagerright = '';
    var next = '';
    var maxpage = Math.ceil(count/pagesize);
    var classA = 'border:1px solid #8AD84D;display:block;text-decoration:none;text-align:center;';
    var classAActive = classA + 'background-color:#8AD84D;color:#FFFFFF;';
    var classLi = 'float:left;margin-right:5px;display:inline;width:20px;';
    var classUl = 'clear:both;';

    if (pageindex > 1) {
        forward += '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + (pageindex - 1) + ');">&lt;&lt;</a></li>';
    }

    if (maxpage > pageindex) {
        next = '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + (pageindex + 1) + ');">&gt;&gt;</a></li>';
    }

    var page = Math.ceil(pagecount/2);

    //all page count
    var i = 1;

    //left nav
    var left = 0;
    for (left = pageindex - 1; left > 0 && left > pageindex - page; left--) {
        i++;
        pagerleft = '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
    }

    //current nav number
    pagercurrent = '<li style="' + classLi + '"><a style="' + classAActive + '" href="javascript:' + action + '(' + pageindex + ');" class="active">' + pageindex + '</a></li>';

    //right nva
    var right = 0;
    for (right = pageindex + 1; right <= maxpage && right < pageindex + page ; right++) {
        i++;
        pagerright = pagerright + '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
    }

    //If right side is not enough, show the page number for left until the page number number is up to 1
    if (i < pagecount && left >= 1) {
        for (; left > 0 && i < pagecount; left--,i++) {
            pagerleft = '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
        }
    }

    //If left side is not enough, showthe page number for right until the page number number is up to max
    if (i < pagecount && right <= maxpage) {
        for (; right <= maxpage && i < pagecount; right++,i++) {
            pagerright = pagerright + '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
        }
    }

    nav = '<ul style="' + classUl + '">' + forward + pagerleft + pagercurrent + pagerright + next + '</ul>';

    return nav;
}