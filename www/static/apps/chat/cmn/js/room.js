/**
 * room(/chat/room.js)
 * chat room
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/05    zhangxin
 */

var CONST_DEFAULT_RELOAD_SECONDS = 20;//10;
var CONST_DEFAULT_RELOAD_SECONDS_SYS = 13;//32;
var CONST_DEFAULT_RELOAD_SECONDS_EXTEND = 311;//301;

var globe_is_exit = 0;

var chatUseID = {
	scrollArea : '#timeLine',
	membersArea : '#members',
	alertArea : '#alertBox'
};

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	if (null != cm_getCookie('app_top_url')) {
		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	}

	//activity
   	if (null != $j('#activity') && '' != $j('#activity').val()) {
   		postActivity($j('#activity').val());
   	}
   	
   	setTimeout('walkclock()', 1000);
        	
    //send 
    $j('#btnSend').click(send);
	//exit chat
	$j('#btnExit').click(exitConfirm);
	//send message
	$j('#txtContent').bind('keydown', 'return', send);
	$j('#txtContent').bind('keydown', 'Ctrl+return', function() {
		$j('#txtContent').val($j('#txtContent').val() + "\n");
		$j('#txtContent').focus();
	});
	
	//get member list
	getMemberList();
	
	//get alert msg
	getSysContent();
	//get sended message
	setTimeout("getContent()", 2000);

	setHeight();//高さ取得・変更
        
    //is owner
    if ('1' == $j('#isOwner').val()) {
	    isTimeOut();
	}    
	
	adjustHeight();
	
});

window.onbeforeunload = function(event)   
{    
	exitRoom(1);
}

function walkclock()
{
	$j('#curDate').val(parseInt($j('#curDate').val()) + 1);
	setTimeout('walkclock()', 1000);
}

/*高さを自動取得・変更*/
function setHeight() {
	var setHeightID = {
		mainColumn : [{
			hdr : 72,
			comment : 100
		}],
		subColumn : [{
			hdr : 31,
			ad : 246,
			btn : 50
		}]
	};

	var windowHeight = $j(window).height();//現在の高さの取得
//alert(windowHeight);	
	var ajustHeight = windowHeight - setHeightID.mainColumn[0].hdr - setHeightID.mainColumn[0].comment - 20;
	var mainColumnHeight = ajustHeight;
	var subColumnHeight = windowHeight - setHeightID.subColumn[0].hdr - setHeightID.subColumn[0].ad - setHeightID.subColumn[0].btn - 20;
//alert(mainColumnHeight);
	
	$j(chatUseID.scrollArea).css({height:mainColumnHeight});
	$j(chatUseID.membersArea).css({height:subColumnHeight});
	
	$j.extend($j.fn.jScrollPane.defaults, {
		showArrows:true,
		arrowSize: 16,
		dragMinHeight: 16,
		dragMaxHeight: 800,
		reinitialiseOnImageLoad: true,
		animateTo: true,
		animateInterval:50,
		animateStep:3
	});
	$j(chatUseID.scrollArea).jScrollPane({
		scrollbarWidth: 19
	});
	$j(chatUseID.membersArea).jScrollPane({
		scrollbarWidth: 20
	});
	$j('#jScrollPaneContainer').css({height:mainColumnHeight});
}


/**
 * jquery get the member list
 *
 * @return void
 */
function getMemberList()
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/getmemberlist';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val(),
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var strHtml = showMemberInfo(responseObject.info);	            	
	            	$j('#members').html(strHtml);
	            	/*参加メンバーロールオーバー*/
	            	$j('#members').find('ul li').hover(function(){
						$j(this).addClass('hover');
					},function(){
						$j(this).removeClass('hover');
					});
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
	
  	setTimeout("getMemberList()", CONST_DEFAULT_RELOAD_SECONDS_SYS*1000);
}

/**
 * jquery get the system content list
 *
 * @return void
 */
function getSysContent()
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/getdetaillist';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val() + '&lastId=' + $j('#hidLastIdSys').val() + '&type=1',
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	if (responseObject.info && 'null' != responseObject.info) {
	            		var html = showAlertInfo(responseObject.info);	            	
	            		$j('#alertBox').append(html).fadeIn(1000);
					}
	            	//chat owner ended the chat
	            	if (1 == responseObject.ended) {
	            		var parm = '?cancel=90005&cname=' + encodeURI($j('#cname').val()) + '&uname=' + encodeURI($j('#uname').val());
	            		window.location.href = UrlConfig.BaseUrl + '/chat/add' + parm;
	            		return false;
	            	}
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
    
    setTimeout("getSysContent()", CONST_DEFAULT_RELOAD_SECONDS_SYS*1000);
}

/**
 * jquery get the content list
 *
 * @return void
 */
function getContent(mode)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/getdetaillist';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val() + '&lastId=' + $j('#hidLastId').val() + '&type=0',
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	showContentInfo(responseObject.info);	            		         	
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

	if (1 != mode) {
  		setTimeout("getContent()", CONST_DEFAULT_RELOAD_SECONDS*1000);
  	}
}


/**
 * jquery show member list
 * @param  object array
 * @return string
 */
function showMemberInfo(array)
{
    //concat html tags to array data
    var html = '';        
	html += '<ul>';
	
    //for each row data    
    for (var i = 0 ; i < array.length ; i++) {
    	if ($j('#ownerId').val() == array[i].uid) {
        	html += '<li class="own">';
        }
        else {
        	html += '<li class="friend">';
        }
                
        html += '<p class="pic"><img src="' + array[i].thumbnailUrl + '" width="30" height="30" class="userPic" alt="' + array[i].displayName + '" /><span class="frame"></span></p>';
        html += '<p class="name">' + array[i].displayName + '</p>';
        html += '</li>';
        
    }
    html += '</ul>';
    return html;
}

/**
 * jquery show alert info
 * @param  object array
 * @return string
 */
function showAlertInfo(array)
{
    //concat html tags to array data
    var html = '';        

    //for each row data
    var lastId = 0;
    for (var i = 0 ; i < array.length ; i++) {                
        html += '<li id="li_alart_' + array[i].did + '">';
        var linkClose = '<a class="alertclose" href="javascript:void(0);" onclick="removeSysContent(' + array[i].did + ');return false;"><img height="16" width="16" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/alert/btn_close.png"/></a>';
        var linkImage = '<img height="17" width="17" class="icon" alt="" src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/alert/icn_friend.png"/>';
        var linkContent = '<span>' + (array[i].content) + '</span>';
        
        html += linkClose + linkImage + linkContent;       
        html += '</li>';
        lastId = array[i].last_id;
    }
    
    $j('#hidLastIdSys').val(lastId);
    return html;
}

/**
 * jquery remove system content 
 *
 * @return void
 */
function removeSysContent(id)
{
	$j('#li_alart_' + id).fadeOut(500, function(){
		$j(this).remove();
	});		
}

/**
 * jquery show content info
 * @param  object array
 * @return string
 */
function showContentInfo(array)
{
    //concat html tags to array data
    var html = '';        
  
    //for each row data
    var lastId = 0;
    var autoScroll = true;
    for (var i = 0 ; i < array.length ; i++) {
    	var user = 'friend';
    	if ($j('#uid').val() == array[i].uid) {
    		user = 'own';
    	}

		autoScroll = $j('#timeLine').data('jScrollPanePosition') == $j('#timeLine').data('jScrollPaneMaxScroll');
		
		var strhtml = '';
		var linkImage = '<img height="50" width="50" alt="' + (array[i].displayName) + '" class="userPic" src="' + array[i].thumbnailUrl + '"/><img height="54" width="54" alt="" class="frame" src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/bg_pic_' + user + '.png"/>';
		var strContent = cm_escapeHtml(array[i].content);
		strContent = cm_nl2br(strContent);
		
		//var strDateTime = array[i].create_time + '|' + getDateTimeFormatJp(parseInt($j('#curDate').val()) - parseInt(array[i].last_id));
		var strDateTime = array[i].create_time;
        var linkContent = '<div class="comment"><div class="inner"><h2>' + (array[i].displayName) + '</h2><span class="time">' + strDateTime + '</span><p class="last">' + strContent + '</p><div class="lt"/><div class="rt"/><div class="lb"/><div class="rb"/></div></div>';        
        strhtml += '<p class="pic">' + linkImage + '</p>' + linkContent;

	 	$j("#contentBox").append('<li id="li_content_' + array[i].did + '" class="' + user + '" style="opacity:0"></li>')
		.find('li:last')
		.html(strhtml)
	 	.animate({opacity: 1}, 1000);
				 		 	
        lastId = array[i].last_id;
    }
    
    $j('#timeLine').jScrollPane({
			showArrows:true,
			scrollbarWidth: 19,
			arrowSize: 16,
			animateTo: true
		});			
    if (autoScroll){
		$j('#timeLine')[0].scrollTo($j('#timeLine').data('jScrollPaneMaxScroll'));
	}
    
    $j('#hidLastId').val(lastId);
    return $j("#contentBox").html();
}

/**
 * jquery send message
 *
 * @return void
 */
function send() 
{
	//message empty
	if ( null == $j('#txtContent').val || '' == $j.trim($j('#txtContent').val()) ) {
		return false; 
	}
	if ($j('#txtContent').val().length > 400) {
	    alert('400文字を越えました。文字数オーバーです。');
		return false;
	}
	
	try {
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/send';
		var strContent = $j('#txtContent').val();
		$j('#txtContent').attr('disabled','disabled');
	    $j.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val() + "&txtContent=" + strContent,
		    dataType: "text",
		    success: function(response) {		    	
		    	//$j('#txtContent').removeAttr('disabled').val('').focus();
		    	getContent(1);
		    	setHeight();
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
		    	//alert(textStatus);
		    }
		});
	}
	catch (e) {
		//alert(e);
	}
	$j('#txtContent').removeAttr('disabled').val('').focus();    
	return false;
}

/**
 * jquery is chat time out
 *
 * @return void
 */
function isTimeOut()
{		
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/getsystemtime';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val(),
		    dataType: "json",
		    success: function(responseObject) {	            
	            if (responseObject) {	            	
	                //is time out
	                if ((responseObject.systime - $j('#hidStartTime').val()) >= (3600*(parseInt(responseObject.extend_count)+1))) {						
						alertTimeOutConfirm();
	                }
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
    
    setTimeout("isTimeOut()", CONST_DEFAULT_RELOAD_SECONDS_EXTEND*1000);
}

/**
 * jquery alert chat time over
 *
 * @return void
 */
function alertTimeOutConfirm()
{		
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/timeoutalert';
	
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val(),
		    dataType: "text",
		    success: function(responseText) {	            
	            if (responseText == 'true') {      
	            //db alert flag already set
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
    
    if ('1' == $j('#flgAlerted').val()) {
    	return false;
    }
    
    $j('#flgAlerted').val('1');
        
    var strhtml = '';
    strhtml += '<div id="layer"></div>'
             + '  <div id="overAlert">'
             //+ '    <a id="alertClose" href="javascript:void(0);"><img alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/alert/btn_close.png" width="16" height="16"></a>'
             + '    <p>このチャットはあと5分で終了します。延長しますか？</p>'
             + '    <ul class="btnList"><!--'
             + '       --><li><a href="javascript:void(0;)" id="popExtend"><img src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/content/btn_extend_s.png" alt="延長する" width="120" height="26"></a></li><!--'
             + '       --><li><a href="javascript:void(0;)" id="popExit"><img src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/content/btn_finish_s.png" alt="終了" width="120" height="26"></a></li><!--'
             + '    --></ul>'
             + '</div>';
          
    $j("#overlay").html(strhtml).toggle('fast');   
    if (null != cm_getCookie('app_top_url')) {
		//top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	}
    /*
    $j('#alertClose').click(function() {
        $j("#overlay").toggle('slow');
        return false;
    });
    */
    
    $j('#popExtend').focus();
    $j('#popExit').click(function() {
        exitRoom();
        return false;
    });
    
    $j('#popExtend').click(function() {
        extendTime();
        return false;
    });
             
    return false;
}

/**
 * jquery extend chat 
 *
 * @return void
 */
function extendTime() 
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/extendtime';

	try {
		$j('#overAlert').html('<p><br />延長しています。。。<br /></p>');
	    $j.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val(),
		    dataType: "text",
		    success: function(response) {
		    	if ('true' == response) {
		    		$j('#overAlert').html('<p>延長しました。<br /></p>');
		    		var aryInfo = new Array();
		    		var d = new Date();
   					var obj = new Object();
   					obj.did = d.getTime();
   					obj.content = 'チャットを1時間延長しました。';
   					obj.last_id = $j('#hidLastIdSys').val();
   					aryInfo[0] = obj;
		    		var strHtml = showAlertInfo(aryInfo);
		    		$j('#alertBox').append(strHtml).fadeIn(1000);
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
	
  	setTimeout(function() {
    		$j("#overlay").toggle('slow');
    		$j('#flgAlerted').val('0');
        }, 
    	3000);
	    
    return false;
}

/**
 * jquery exit chat room confirm
 *
 * @return void
 */
function exitConfirm() 
{
	var strhtml = '';
    strhtml += '<div id="layer"></div>'
             + '  <div id="overAlert">'
             + '    <a id="alertClose" href="javascript:void(0);"><img alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/alert/btn_close.png" width="16" height="16"></a>'
             + '    <p>このチャットを終了します。よろしいですか？</p>'
             + '    <ul class="btnList"><!--'
             + '       --><li><a href="javascript:void(0);" id="popCancel"><img src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/content/btn_cancel_s.png" alt="キャンセル" width="120" height="26"></a></li><!--'
             + '       --><li><a href="javascript:void(0);" id="popExit"><img src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/content/btn_finish_s.png" alt="終了" width="120" height="26"></a></li><!--'
             + '    --></ul>'
             + '</div>';
         
    $j("#overlay").html(strhtml).toggle('fast');
    if (null != cm_getCookie('app_top_url')) {  
		//top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	}
    
    $j('#popCancel').focus();
    $j('#alertClose,#popCancel').click(function() {
        $j("#overlay").toggle('slow');
        return false;
    });
    
    $j('#popExit').click(function() {
        exitRoom();
        return false;
    });
    
    return false;
}

/**
 * jquery exit chat room
 *
 * @return void
 */
function exitRoom(mode) 
{
	if (1 == globe_is_exit) {
		return false;
	}
	
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/exitroom';

	try {
		globe_is_exit = 1;
		$j('#overAlert').html('<p><br />退室しています。。。<br /></p>');
	    $j.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: "cid=" + $j("#cid").val() + "&lastId=" + $j("#hidLastId").val() + "&lastIdSys=" + $j("#hidLastIdSys").val(),
		    dataType: "text",
		    success: function(response) {
		    	if ('true' == response) {
		    		$j('#overAlert').html('<p>退室しました。<br /></p>');		    		
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
	
	if (null == mode) {
	    setTimeout(function() {
	    		var parm = '?cancel=90003&cname=' + encodeURI($j('#cname').val());
	    		if ('1' == $j('#isOwner').val()) {
	    			parm = '?cancel=90004&cid=' + $j('#cid').val();
	    		}
	        	window.location.href = UrlConfig.BaseUrl + '/chat/add' + parm;
	        }, 
	    	3000);
	}
    return false;
}

/**
 * change time to ｎ秒前　ｎ分前　ｎ時前　ｎ日前
 *
 * @param  integer seconds
 * @return string
 */
function getDateTimeFormatJp(seconds) 
{
	var strReturn = '';
	if (seconds < 60) {
		strReturn = (seconds <= 0 ? '1' : seconds) + '秒前';
	}
	else if (seconds < 60*60) {
		strReturn = Math.ceil(seconds/60) + '分前';
	}
	else if (seconds < 60*60*24) {
		strReturn = Math.ceil(seconds/60/60) + '時前';
	}
	else {
		strReturn = Math.ceil(seconds/60/60/24) + '日前';
	}
	return strReturn;
}