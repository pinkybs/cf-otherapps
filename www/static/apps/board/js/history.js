/**
 * history(/board/history.js)
 *  get|delete board list
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/03/11    Liz
 */

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 10;

/**
 * get the select page's messageboard list
 *
 * @param page int
 * @return void
 */
function changePageAction(page)
{   
    
    $('pageIndex').value = page;
	var bownerId = $('bownerId').value;
    
    var url = UrlConfig.BaseUrl + '/ajax/board/gethistorylist';

    new Ajax.Request(url, {
        method: 'post',
 
        parameters:{
            page: page,
            pageSize: CONST_DEFAULT_PAGE_SIZE,
            id: bownerId
        },
        onTimeout: function() {
            $('boardList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
      //  onCreate : getDataFromServer,
        onSuccess: renderResults});
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
        $('boardList').innerHTML = '<div class="section"><p><img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_null2.png" style="height:100px"/></p></div><!--/.section-->';

    }
    else
    {
		if (responseObject.info)
		{
			if (responseObject.listCount.startCount == responseObject.listCount.endCount)
			{
				var listStatus = '<div class="pageNumber">' + responseObject.count + '件中　' + responseObject.listCount.startCount + '件を表示</div>';
			}
			else {
				var listStatus = '<div class="pageNumber">' + responseObject.count + '件中　' + responseObject.listCount.startCount + '-' + responseObject.listCount.endCount + '件を表示</div>';
			}
		}

        var html = showBoardViewForHistory(responseObject.info, responseObject.uid);
        var nav = showPagerNav(responseObject.count,Number($('pageIndex').value),CONST_DEFAULT_PAGE_SIZE);

        $('boardList').innerHTML = listStatus + html + nav;
        $('boardList').show();

    }
	if ( null != getCookie('app_top_url_board') ) {
		top.location.href = getCookie('app_top_url_board') +  '#pagetop';            
	}
   //location.href = '#top';

    adjustHeight();
}
/**
 * show board info list
 *
 * @param array aryBoard board info
 * @return string
 */
function showBoardViewForHistory(aryBoard, uid)
{   

    var html = '';
    var bownerId = $('bownerId').value;
    if ( aryBoard ) {
       
        //set board list
        for (i = 0 ; i < aryBoard.length ; i++) {
            if (i%2 == 0) {
            html += '<div class="section">'
            } else {
            html += '<div class="section even">'
            }
            html += '<p class="pic"><img src="' + aryBoard[i].thumbnailUrl + '" width="50" height="50" /></p>'
                  + '<p class="name">' + aryBoard[i].displayName + '</p>';
                  
            if (aryBoard[i].uid != aryBoard[i].comment_uid) {
                html += '<p class="name"> >> <a href="javascript:void(0)" onclick="gotoboard('+aryBoard[i].uid+')">'+aryBoard[i].targetName+'</p></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            
            html += '<p class="date">' + aryBoard[i].create_time.formatToDate('MM月dd日 hh:mm') + '</p>';
            if (uid == aryBoard[i].comment_uid || uid == bownerId ) {
            html += '<p class="btnDelete"><a href="javascript:void(0);" onclick="deleteBoard(' + aryBoard[i].bid + ');return false;"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_delete.png" width="39" height="11" alt="削除" /></a></p>';
            }
            html += '<p class="comment">'
                  + '  <span class="inner">'; 
            if ("0" == aryBoard[i].type) {
                html += aryBoard[i].content.escapeHTML().parseBR();
            }
            else {
                html += '<img src="' + UrlConfig.PhotoUrl + '/apps/board/flash' + aryBoard[i].pic_url + '" style="height:136px"/>';
               
            } 
            html += '</span>'
                  + '</p>'
                  + '</div><!--/.section-->';
            }
    }
    else {
        html += '<div class="section">'
              + '<p><img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_null2.png" style="height:100px"/></p>'
              + '</div><!--/.section-->';
       
    }
    return html;
}
/**
 * delete messageboard
 *
 * @param integer id
 * @return void
 */
function deleteBoard(id)
{
    var requestObject = new Object();
    requestObject.id = id;
    var jsonRequest = Object.toJSON(requestObject);

    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/board/delete';

    new Ajax.Request(url, {
        method: 'get',
        parameters: 'request='+escape(jsonRequest)+'&r='+escape(rand),
        onTimeout: function() {
            $('boardList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
      //  onCreate : getDataFromServer_edit,
        onSuccess: renderResults_delete});
}

/**
 * show processing info
 */
function getDataFromServer_edit()
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
function renderResults_delete(response)
{

    changePageAction(1);
}



/****************************************** lp add ***************************************/
function getHistoryList(uid)
{
    var url = UrlConfig.BaseUrl + '/ajax/board/showhistory';
    new Ajax.Request(url, {
        parameters: {
            uid : uid
        },
        onTimeout: function() {
            $('mainColumn').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        
        onSuccess: showHistoryPage});
}
function showHistoryPage(response)
{   
    if (document.getElementById('bbsb') != null) {
        document.getElementById('bbsb').id = 'postedb';
    }
    if (document.getElementById('editb') != null) {
        document.getElementById('editb').id = 'postedb';
    }
    
    var responseObject = response.responseText.evalJSON();
    
    var info = responseObject.info;
    var count = responseObject.count;
    var listCount = responseObject.listCount;
    var uid = responseObject.uid;
    var bownerId = responseObject.bownerId;
    var fid = responseObject.fid == 'null'? null:responseObject.fid;
    var openflag = responseObject.openflag;
    var thumbnailUrl = responseObject.thumbnailUrl;
    var title = responseObject.title;
    var introduce = responseObject.introduce;
    var pageIndex = responseObject.pageIndex;

    
    html = '';
    html += '<div id="hdr">'
         +  '<p class="pic">'
         +  '<img src="'+thumbnailUrl+'" width="60" height="60" /></p>'
         +  '<h1>'+title+'</h1>'
         +  '<p id="copy">'+introduce+'</p>'
         +  '</div><!--/#hdr-->'
         +  '<ul id="gNav">'
         +  '   <li id="bbs">';
    if (fid != null && fid != '') {
    html += '   <a href="javascript:void(0);" onclick="gotoboard('+fid+');">';
    }
    else {
    html += '   <a href="javascript:void(0);" onclick="gotoboard('+uid+');">';
    }
    html += '   あしあと帳</a></li>' 
         +  '   <li id="posted">';
    if (fid != null && fid != '') {
    html += '   <a href="javascript:void(0);" onclick="getHistoryList('+fid+');">';
    }
    else {
    html += '   <a href="javascript:void(0);" onclick="getHistoryList('+uid+');">';
    }
    html += '   投稿履歴</a></li>' ;
    if (fid == null || fid == ''){
    html +=  '<li id="edit"><a href="javascript:void(0);" onclick="getUserSetInfo();">設定変更</a></li>';
    }
    html += '</ul>';
    if (openflag == 3) {
    html += '<div id="content"><div class="inner">このあしあと帳は非公開です。</div></div>';
    }
    else if (openflag == 2) {
    html += '<div id="content"><div class="inner">友人まで公開のため閲覧することが出来ません。</div></div>';
    }
    else if (openflag == 1) {
    html += '<div id="content"><div class="inner">友達の友達迄公開のため閲覧することが出来ません。</div></div>';
    }
    else {
    html += '<div id="content"><div class="inner">'
         +  '<input type="hidden" name="bownerId" id="bownerId" value="'+bownerId+'">'
         +  '<input type="hidden" id="uid" name="uid" value="'+uid+'">'
         +  '<input type="hidden" id="pageIndex" name="pageIndex" value="1">'
         +  '<input type="hidden" id="pageName" name="pageName" value="history">'
         +  '<div id="boardList">';
    
    var boardHtml = '';
    var nav = '';
    if (info != null && info != '') {
        
        if (listCount.startCount == listCount.endCount) {
            html += '<div class="pageNumber">' + count + '件中　' + listCount.startCount + '件を表示</div>';
        }
        else {
            html += '<div class="pageNumber">' + count + '件中　' + listCount.startCount + '-' + listCount.endCount + '件を表示</div>';
        }
        boardHtml = viewBoardForHistory(info, uid, bownerId);
        nav = showPagerNav(count, Number(pageIndex), CONST_DEFAULT_PAGE_SIZE);
       
        $('mainColumn').innerHTML = html + boardHtml + nav + '</div><!--/.boardList-->';

    }
    else {
       
        boardHtml += '<div class="section">'
                  +  '<p class="imgNull history"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_null.png" style="height:80px"/></p>'
                  +  '</div><!--/.section-->';
        
        $('mainColumn').innerHTML = html + boardHtml + '</div><!--/.boardList-->';
  
    }
    $('mainColumn').show();

    adjustHeight();
    
   }
         
}

function viewBoardForHistory(aryBoard, uid, bownerId)
{   
   
    var html = '';

    if ( aryBoard ) {
        
        //set board list
        for (i = 0 ; i < aryBoard.length ; i++) {
            if (i%2 == 0) {
            html += '<div class="section">';
            } 
            else {
            html += '<div class="section even">';
            }
            html += '<p class="pic"><img src="' + aryBoard[i].thumbnailUrl + '" width="50" height="50" /></p>';
            
            html += '<p class="name">'+aryBoard[i].displayName+'</p>';
            
            if (aryBoard[i].uid != aryBoard[i].comment_uid) {
                html += '<p class="name"> >> <a href="javascript:void(0)" onclick="gotoboard('+aryBoard[i].uid+')">'+aryBoard[i].targetName+'</p></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            
            html += '<p class="date">' + aryBoard[i].create_time.formatToDate('MM月dd日 hh:mm') + '</p>';
            if (uid == aryBoard[i].comment_uid || uid == bownerId ) {
            html += '<p class="btnDelete"><a href="javascript:void(0);" onclick="deleteBoard(' + aryBoard[i].bid + ');return false;"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_delete.png" width="39" height="11" alt="削除" /></a></p>';
            }
            html += '<p class="comment">'
                  + '  <span class="inner">'; 
            if ("0" == aryBoard[i].type) {
                html += aryBoard[i].content.escapeHTML().parseBR();
                
            }
            else {
                html += '<img src="' + UrlConfig.PhotoUrl + '/apps/board/flash' + aryBoard[i].pic_url + '" style="height:136px"/>';
                
            } 
            html += '</span>'
                  + '</p>'
                  + '</div><!--/.section-->';
        }
        
    }
    
    return html;
}