/**
 * list(/board/list.js)
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
    if ($('postArea'))
    {
        Event.observe('btnPost', 'click', addBoard);
    }

	if ($('boardList'))
	{ 
       
	   if ($F('openflag') == 0 && $F('count') != 0)
	   {   
			if ( null != getCookie('app_top_url_board') ) {
				top.location.href = getCookie('app_top_url_board') +  '#pagetop';            
			}
	       //location.href = '#top';
	       var nav = showPagerNavForList(Number($F('count')), Number($F('pageIndex')), CONST_DEFAULT_PAGE_SIZE);
	       $('navlist').innerHTML = nav;
	       
       }
       adjustHeight();
    }
});

/**
 * get the select page's messageboard list
 *
 * @param page int
 * @return void
 */
function changePageActionList(page)
{  
    
    $('pageIndex').value = page;
  
	var bownerId = $('bownerId').value;
    var url = UrlConfig.BaseUrl + '/ajax/board/getboardlist';

    new Ajax.Request(url, {
        method: 'post',
 
        parameters : {
            pageIndex : $F('pageIndex'),
            id : bownerId
        },
        onTimeout: function() {
            $('boardList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
     //   onCreate : getDataFromServer,
        onSuccess: renderResultsList});
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
function renderResultsList(response)
{   
    var responseObject = response.responseText.evalJSON();
    
    if ( responseObject.info == "")
    {
        $('boardList').innerHTML = '<div class="section"><p class="imgNull"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_null.png" style="height:80px"/></p></div><!--/.section-->';
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

        var html = showBoardView(responseObject.info, responseObject.uid);
        var nav = showPagerNavForList(responseObject.count,Number($('pageIndex').value),CONST_DEFAULT_PAGE_SIZE);
        
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
 * delete messageboard
 *
 * @param integer id
 * @return void
 */
function deleteBoard(id)
{

    var url = UrlConfig.BaseUrl + '/ajax/board/delete';

    new Ajax.Request(url, {
        method: 'get',

        parameters: {
            bownerId: $F('bownerId'),
            id: id,
            pageName: $F('pageName')
        },
        onTimeout: function() {
            $('boardList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
    //    onCreate : getDataFromServer_edit,
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
	
	var responseObject = response.responseText.evalJSON();
	var bownerId = responseObject.bownerId;
	var pageName = responseObject.pageName;

    if (pageName == 'history') {
        getHistoryList(bownerId);
    }
    else {
        gotoboard(bownerId);
    }

}

/**
 * add messageboard
 *
 * @param string content
 * @return void
 */
function addBoard()
{
    var txtContent = $F('txtContent');
    if ("" == txtContent) {
        $('alert').innerHTML = '<img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_alert.png" width="450" height="66" alt="何も書かれていない状態での投稿はできません" onclick="$(\'alert\').style.display = \'none\';"/>';
        $('alert').style.display = "block";
        return;
    }
        
    if (100 < txtContent.length) {
        $('alert').innerHTML = '<img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_alert_length.png" width="450" height="66" alt="コメントは100文字以内で入力してください" onclick="$(\'alert\').style.display = \'none\';"/>';
        $('alert').style.display = "block";
        return;  
    }   
    
    var url = UrlConfig.BaseUrl + '/ajax/board/new';

    new Ajax.Request(url, {
        method: 'post',
        parameters : {
            txtContent : $F('txtContent'),
            ownerId : $F('bownerId')
        },
        onTimeout: function() {
            $('boardList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        //onCreate : getDataFromServer_edit,
        onSuccess: renderResults_add});
}

/**
 * callback funtion when success
 *  set the callback data to the showArea of the page
 *
 * @param string response
 * @return void
 */
function renderResults_add(response)
{
    
    var array = response.responseText.evalJSON();
	//$('boardHead').scrollTo();
    if (array.status == "1"){
        $('txtContent').value = "";
    }
	
	addNewsFeed("txt", array.activity);

	gotoboard($F('bownerId'));

}

//add newsfeed
function addNewsFeed(type, msg) {
    if ("pic" == type) {
	    if ("" != msg) {
            //alert(msg);
            postActivity(msg);
	    }

	    var paint;
		if (1 == getOs()) {
			paint = thisMovie('paintIE');
		} else {
			paint = thisMovie('paint')
		}

	    new PeriodicalExecuter(function(pe) {
	        if (paint){
		        try{
		            //paint.forwardCallBack();
		            gotoboard($F('bownerId'));
		        }catch(e){}
	        }
	            pe.stop();
	        }, 1);
     } else if ("txt" == type) {
        if ("" != msg) {
            //alert(msg);
            postActivity(msg);
        }
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
	var bownerId = $('bownerId').value;
	if ( aryBoard ) {
        
		//set board list
		for (i = 0 ; i < aryBoard.length ; i++) {
            if (i%2 == 0) {
            html += '<div class="section">'
            } else {
            html += '<div class="section even">'
            }
            
            if (uid == aryBoard[i].comment_uid) {
            html += '<p class="pic"><a href="javascript:void(0);" onclick="gotoboard('+uid+')"><img src="' + aryBoard[i].thumbnailUrl + '" width="50" height="50" /></a></p>';
            html += '<p class="name"><a href="javascript:void(0);" onclick="gotoboard('+uid+')">' + aryBoard[i].displayName + '</a></p>';
            } else {
            html += '<p class="pic"><a href="javascript:void(0);" onclick="gotoboard('+aryBoard[i].comment_uid+')"><img src="' + aryBoard[i].thumbnailUrl + '" width="50" height="50" /></a></p>';
            html += '<p class="name"><a href="javascript:void(0);" onclick="gotoboard('+aryBoard[i].comment_uid+')">' + aryBoard[i].displayName + '</a></p>';
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
		     +  '<p class="imgNull"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_null.png" style="height:80px"/></p>'
             +  '</div><!--/.section-->';

	}

    return html;
}

function thisMovie(movieName) {
	if (navigator.appName.indexOf("Microsoft") != -1) {
		return window[movieName];
	}else{
		if(document[movieName].length != undefined){
			return document[movieName][1];
		}
		return document[movieName];
	}
}

function getOs()
{
    //IE,return 1
    if (navigator.userAgent.indexOf("MSIE")>0) {
       return 1;
    }
    //Firefox,return 2
    if (isFirefox=navigator.userAgent.indexOf("Firefox") > 0) {
       return 2;
    }
    //Safari,return 3
    if (isSafari=navigator.userAgent.indexOf("Safari") > 0) {
       return 3;
    }
    //Camino,return 4
    if (isCamino=navigator.userAgent.indexOf("Camino") > 0) {
       return 4;
    }
    //Gecko,return 5
    if (isMozilla=navigator.userAgent.indexOf("Gecko/") > 0) {
       return 5;
    }

    return 0;
}
/*******************************************  lp  add  *********************************/
function gotoboard(uid)
{   
    if (uid == $F('uid')) {
        var oldUid = $F("oldUid");
        if (oldUid != uid) {
            $('li'+oldUid+'').removeClassName('active');
        }
        $("oldUid").value = uid;
    }
    var url = UrlConfig.BaseUrl + '/ajax/board/gotoboard';
    new Ajax.Request(url, {
        parameters: {
            uid : uid
        },
        onTimeout: function() {
            $('mainColumn').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        
        onSuccess: showBoardPage});
}

function showBoardPage(response)
{   
    if (document.getElementById('postedb') != null) {
        document.getElementById('postedb').id = 'bbsb';
    }
    if (document.getElementById('editb') != null) {
        document.getElementById('editb').id = 'bbsb';
    }
    
    var responseObject = response.responseText.evalJSON();
    
    var info = responseObject.info;
    var bownerId = responseObject.bownerId;
    var count = responseObject.count;
    var listCount = responseObject.listCount;
    var uid = responseObject.uid;
    var fid = responseObject.fid;
    var pageIndex = responseObject.pageIndex;
    var openflag = responseObject.openflag;
    var allowComment = responseObject.allowComment;
    var thumbnailUrl = responseObject.thumbnailUrl;
    var title = responseObject.title;
    var introduce = responseObject.introduce;
    var userThumbnailUrl = responseObject.userThumbnailUrl;
    var headPic = responseObject.headPic;
    var picUrl = UrlConfig.StaticUrl + headPic;

    $('container').setStyle({
	  backgroundImage: 'url('+picUrl+')'
	 
	});
    
    var html = '';
    html += '<div id="hdr">'
         +  '<p class="pic">'
         +  '<img src="'+thumbnailUrl+'" width="60" height="60" /></p>'
         +  '<h1>'+title+'</h1>'
         +  '<p id="copy">'+introduce+'</p>'
         +  '</div><!--/#hdr-->'
         +  '<ul id="gNav">'
         +  '   <li id="bbs">';
    if (fid != null && fid != '' && fid != 'null') {
    html += '   <a href="javascript:void(0);" onclick="gotoboard('+fid+')">';
    }
    else {
    html += '   <a href="javascript:void(0);" onclick="gotoboard('+uid+')">';
    }
    html += '   あしあと帳</a></li>' 
         +  '   <li id="posted">';
    if (fid != null && fid != '' && fid != 'null') {
    html += '   <a href="javascript:void(0);" onclick="getHistoryList('+fid+')">';
    }
    else {
    html += '   <a href="javascript:void(0);" onclick="getHistoryList('+uid+')">';
    }
    html += '   投稿履歴</a></li>';
    if (fid == null || fid == '' || fid == 'null'){
    html += '<li id="edit"><a href="javascript:void(0);" onclick="getUserSetInfo();">設定変更</a></li>';
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
         +  '<input type="hidden" id="bownerId" name="bownerId" value="'+bownerId+'">'
         +  '<input type="hidden" id="uid" name="uid" value="'+uid+'">'
         +  '<input type="hidden" id="pageIndex" name="pageIndex" value="'+pageIndex+'">'
         +  '<input type="hidden" id="openflag" name="openflag" value="'+openflag+'">'
         +  '<input type="hidden" id="count" name="count" value="'+count+'">'
         +  '<input type="hidden" id="pageName" name="pageName" value="board">';

        if (!(allowComment == 1)) {
        html += '<div id="postArea">'
             +  '<p class="pic"><img src="'+userThumbnailUrl+'" width="50" height="50" alt="" /></p>'
             +  '<div id="paint" style="display:none">'
             +  '<div id="post">'
             +  '<ul id="postNav"><li id="navOekaki" class="active"><a href="javascript:void(0);">お絵かき</a></li><li id="navText"><a href="javascript:void(0);" onclick="paintOrWrite(1);">テキスト</a></li></ul>'
             +  '<div id="postBox">'
             +  '   <object id="paintIE" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="580" height="204" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0">'
             +  '   <param name="movie" value="'+UrlConfig.StaticUrl+'/apps/board/swf/paint.swf" />'
             +  '   <param name="wmode" value="Opaque" />'
             +  '   <param name="quality" value="high" />'
             +  '   <param name="bgcolor" value="#ffffff" />'
             +  '   <param name="name" value="paint" />'
             +  '   <param name="align" value="top" />'
             +  '   <param name="allowScriptAccess" value="always" />'
             +  '   <param name="allowFullScreen" value="false" />'
             +  '   <param name="type" value="application/x-shockwave-flash" />'
             +  '   <param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer" />'
             +  '   <param name="FlashVars" value="uuid='+bownerId+'&user='+uid+'&postUrl='+UrlConfig.BaseUrl+'/ajax/board/newimage&forwardUrl='+UrlConfig.BaseUrl+'/board?uid='+fid+'" />'
             +  '   <embed id="paint" src="'+UrlConfig.StaticUrl+'/apps/board/swf/paint.swf" wmode="Opaque" quality="high" bgcolor="#ffffff" width="580" height="204" name="paint" align="top"'
             +  '   allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash"'
             +  '   pluginspage="http://www.macromedia.com/go/getflashplayer"'
             +  '   FlashVars="uuid='+bownerId+'&user='+uid+'&postUrl='+UrlConfig.BaseUrl+'/ajax/board/newimage&forwardUrl='+UrlConfig.BaseUrl+'/board?uid='+fid+'" />'
             +  '   </object>'
             +  '</div>'
             +  '</div></div>'
             +  '<div id="write">'
             +  '<div id="post">'
             +  '   <ul id="postNav"><li id="navOekaki"><a href="javascript:void(0);" onclick="paintOrWrite(2)">お絵かき</a></li><li id="navText" class="active"><a href="javascript:void(0);">テキスト</a></li></ul>'
             +  '   <div id="postBox">'
             +  '       <form id="frmBoard" name="frmBoard" method="post" onsubmit="return false;">'
             +  '           <textarea id="txtContent" name="txtContent"></textarea>'
             +  '           <p class="note">※全角100文字以内</p>'
             +  '           <div id="alert"></div>'
             +  '           <input type="image" id="btnPost" name="btnPost" src="'+UrlConfig.StaticUrl+'/apps/board/img/btn_post.png" onclick="addBoard();"/>'
             +  '       </form>'
             +  '   </div>'
             +  '</div><!--/#post-->'
             +  '</div></div>';
         }
    html += '<div id="boardList">';
    
    var boardHtml = '';
    var nav = '';
    if (info != null && info != '') {
        
        if (listCount.startCount == listCount.endCount) {
            html += '<div class="pageNumber">' + count + '件中　' + listCount.startCount + '件を表示</div>';
        }
        else {
            html += '<div class="pageNumber">' + count + '件中　' + listCount.startCount + '-' + listCount.endCount + '件を表示</div>';
        }
        boardHtml = viewBoard(info, uid, bownerId);
        nav = showPagerNavForList(count, Number(pageIndex), CONST_DEFAULT_PAGE_SIZE);
        $('mainColumn').innerHTML = html + boardHtml + nav + '</div>';
        $('mainColumn').show();
        adjustHeight();
    }
    else {
       
        boardHtml += '<div class="section">'
                  +  '<p class="imgNull"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/txt_null.png" style="height:80px"/></p>'
                  +  '</div><!--/.section-->';
       
        $('mainColumn').innerHTML = html + boardHtml + '</div>';
        
        $('mainColumn').show();
        adjustHeight();
    }

    
    }
	if ( null != getCookie('app_top_url_board') ) {
		top.location.href = getCookie('app_top_url_board') +  '#pagetop';            
	}
   //location.href = '#top';

}

function viewBoard(aryBoard, uid, bownerId)
{   
    var html = '';

    if ( aryBoard ) {
        
        //set board list
        for (i = 0 ; i < aryBoard.length ; i++) {
            if (i%2 == 0) {
            html += '<div class="section">';
            } else {
            html += '<div class="section even">';
            }
            if (uid == aryBoard[i].comment_uid) {
                html += '<p class="pic"><a href="javascript:void(0);" onclick="gotoboard('+uid+')"><img src="' + aryBoard[i].thumbnailUrl + '" width="50" height="50" /></a></p>';
                html += '<p class="name"><a href="javascript:void(0);" onclick="gotoboard('+uid+')">' + aryBoard[i].displayName + '</a></p>';
            } 
            else {
                html += '<p class="pic"><a href="javascript:void(0);" onclick="gotoboard('+aryBoard[i].comment_uid+')"><img src="' + aryBoard[i].thumbnailUrl + '" width="50" height="50" /></a></p>';
                html += '<p class="name"><a href="javascript:void(0);" onclick="gotoboard('+aryBoard[i].comment_uid+')">' + aryBoard[i].displayName + '</a></p>';
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
                 +  '</p>'
                 +  '</div><!--/.section-->';
        }
    }
    
    return html;
}

function paintOrWrite(parm)
{
    if (parm == 1) {
        $('paint').hide();
        $('write').show();
    }
    else {
        $('paint').show();
        $('write').hide();
    }
    
}
