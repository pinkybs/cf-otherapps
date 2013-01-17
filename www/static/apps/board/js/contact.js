/**
 * contact(/board/contact.js)
 *  get|delete board list
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/02/10    Liz
 */

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 10;

/**
 * windows load function
 * register funcion
 */
Event.observe(window, 'load', function()
{
    if ($('boardList'))
	{
		changePageAction(1);
	}
});

/**
 * get the select page's messageboard list
 *
 * @param page int
 * @return void
 */
function changePageAction(page)
{
    $('pageIndex').value = page;
	var ownerId = $('ownerId').value;
    var requestObject = new Object();
    requestObject.page = page;
    requestObject.pageSize = CONST_DEFAULT_PAGE_SIZE;
    requestObject.id = ownerId;      //get the user id
    
    var jsonRequest = Object.toJSON(requestObject);

    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/board/getcontactlist';

    new Ajax.Request(url, {
        method: 'get',
        parameters: 'request='+escape(jsonRequest)+'&r='+escape(rand),
        onTimeout: function() {
            $('boardList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        onCreate : getDataFromServer,
        onComplete: renderResults});
}

/**
 * show processing info
 */
function getDataFromServer()
{
    $('boardList').innerHTML = '<p class="loadingBar">' + '<img src="' + UrlConfig.StaticUrl + '/apps/board/img/loading.gif" alt="" />' + '</p>';
}

/**
 * callback funtion when success
 *  set the callback data to the showArea of the page
 *
 * @param string response
 * @return void
 */
function renderResults(response)
{
    var responseObject = response.responseText.evalJSON();
    
    if ( responseObject.info == "")
    {
        $('boardList').innerHTML = 'まだ伝言がありません';
    }
    else
    {
		if (responseObject.info)
		{
			if (responseObject.listCount.startCount == responseObject.listCount.endCount)
			{
				var listStatus = '<p>' + responseObject.count + '件中　' + responseObject.listCount.startCount + '件を表示</p>';
			}
			else {
				var listStatus = '<p>' + responseObject.count + '件中　' + responseObject.listCount.startCount + '-' + responseObject.listCount.endCount + '件を表示</p>';
			}
		}

        var html = showBoardView(responseObject.info, responseObject.uid);
        var nav = showPagerNav(responseObject.count,Number($('pageIndex').value),CONST_DEFAULT_PAGE_SIZE);

        $('boardList').innerHTML = listStatus + html + nav;
        $('boardList').show();
    }
}

/**
 * show board info list
 *
 * @param array aryBoard board info
 * @return string
 */
function showBoardView(aryBoard, uid)
{
	var html = '';
	var ownerId = $('ownerId').value;
	if ( aryBoard ) {
		//set board list
		for (i = 0 ; i < aryBoard.length ; i++) {
			html +='<table width="97%" style="background-color:#FFF5EE;margin-bottom:5px;">'
				  + '   <tr>'
				  + '       <td width="70" rowspan="2"><a href="' + UrlConfig.BaseUrl + '/board/list?uid=' + aryBoard[i].comment_uid + '"><img src="' + aryBoard[i].thumbnailUrl + '" alt="' + aryBoard[i].displayName + '" tile="' + aryBoard[i].displayName + '"></a></td>'
				  + '       <td style="padding:2px"><span class="text" style="margin-right:10px"><a href="' + UrlConfig.BaseUrl + '/board/list?uid=' + aryBoard[i].comment_uid + '">' + aryBoard[i].displayName + '</a></span><span style="font-size:11px">' + aryBoard[i].create_time.formatToDate('MM月dd日 hh:mm') + '</span></td>'
			      + '   </tr>'
				  + '   <tr>'
				  + '       <td colspan="3" style="padding:10px">' + aryBoard[i].content.escapeHTML() + '</td>'
				  + '   </tr>'
				  + '</table>';
			}
	}
	else {
		html = '<p>まだ伝言がありません！</p>';
	}

    return html;
}

