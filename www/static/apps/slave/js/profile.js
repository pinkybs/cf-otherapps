/**
 * profile(/slave/profile.js)
 * slave profile
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/23    zhangxin
 */

var CONST_DEFAULT_PAGE_SIZE = 1;

var objPane = {
	scrolledArea : '#newsFeed',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox1 ul li ul.actionList li a'
};

//jquery scroll bar
var _slaveUseStaticID = {
	scrollFrame : '#memberFrame',
	innaerFrame : '#innaerFrame',
	navLeft : '#navLeft',
	navRight : '#navRight',
	moveSetp : 66,
	minWidth : 0,
	maxWidth : 0
};

/**
 * jquery init move bar
 * @param  object array
 * @param  integer leftAdd
 * @param  integer rightAdd
 * @return void
 */
function initMoveBar(objStaticRank, leftAdd, rightAdd)
{	
	if (0 == leftAdd && 0 == rightAdd) {
		var listSize = $j(objStaticRank.innaerFrame).find('li').size()-1;
		objStaticRank.maxWidth = objStaticRank.moveSetp * (listSize-5) * (-1);
		objStaticRank.minWidth = 0;
		$j(objStaticRank.innaerFrame).css('left', '0');
	}
	else {
		objStaticRank.minWidth += leftAdd;
		objStaticRank.maxWidth -= rightAdd;		
	}
	
	$j(objStaticRank.innaerFrame).find('li').hover(
		function(){
			$j(this).addClass('active');
		},
		function(){
			if ($j(this).attr('class') != 'active select') {
				$j(this).removeClass('active');
			}
		}
	);
}

/*マイミクリストをスライド*/
function slide(direction,objStaticRank){
	if(direction == 'left'){
		$j(objStaticRank.innaerFrame).animate({left : '+=' + objStaticRank.moveSetp},300, function() {
			measurePosition(objStaticRank);
			//if (nowPosition > 0) {
			//		$j(objStaticRank.innaerFrame).css('left', '0px');
			//}
			$j(objStaticRank.navLeft).removeAttr("disabled");
		});
	} else if(direction == 'right'){
		$j(objStaticRank.innaerFrame).animate({left : '-=' + objStaticRank.moveSetp},300, function() {
			measurePosition(objStaticRank);
			//if (nowPosition < maxWidth) {
			//		$j(objStaticRank.innaerFrame).css('left',  maxWidth + 'px');
			//}
			$j(objStaticRank.navRight).removeAttr("disabled");
		});
	}
}

/**
 * jquery measure postion
 * @param  object objStaticRank
 * @return integer
 */
function measurePosition(objStaticRank)
{
	var nowPosition = $j(objStaticRank.innaerFrame).css('left');
	nowPosition = nowPosition.replace('px','');
	return nowPosition;
}

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	//scroll bar	
	$j('#navLeft').click(function() {
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntPos').val()) <= 6) {
			return;
		}
		var pos = measurePosition(_slaveUseStaticID);
		if (pos < _slaveUseStaticID.minWidth) {
			$j(this).attr("disabled","disabled");
			slide('left',_slaveUseStaticID);
		}
		else {
			if (parseInt($j('#hidPosprev').val()) > 1) {
				$j(this).attr("disabled","disabled");
				var start = (parseInt($j('#hidPosprev').val()) - 6);
				var cntSize = start<1 ? (parseInt($j('#hidPosprev').val()) - 1) : 6;
				start = start > 1 ? start : 1;
				var end = $j('#hidPosnext').val();
				listFriend(start, end, start, cntSize, 'leftStep');
			}			
		}
		return false;
		/*
		if ('disabled' == $j(this).attr("disabled")) {
			return;
		}
		measurePosition(_slaveUseStaticID);
		if (nowPosition < 0) {
			$j(this).attr("disabled","disabled");
			slide('left',_slaveUseStaticID);
		}
		return false;
		*/
	});
	$j('#navRight').click(function() {
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntPos').val()) <= 6) {
			return;
		}
		var pos = measurePosition(_slaveUseStaticID);
		if (pos > _slaveUseStaticID.maxWidth) {			
			$j(this).attr("disabled","disabled");
			slide('right',_slaveUseStaticID);
		}
		else {
			var cntMax = parseInt($j('#hidCntPos').val());
			if (parseInt($j('#hidPosnext').val()) < cntMax) {
				$j(this).attr("disabled","disabled");
				var end = (parseInt($j('#hidPosnext').val()) + 6);
				var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidPosnext').val())) : 6;
				var start = parseInt($j('#hidPosnext').val()) + 1;
				var end = end > cntMax ? cntMax : end;
				listFriend($j('#hidPosprev').val(), end, start, cntSize, 'rightStep');		
			}
		}
		return false;
		/*
		if ('disabled' == $j(this).attr("disabled")) {
			return;
		}
		measurePosition(_slaveUseStaticID);		
		if (nowPosition > maxWidth) {			
			$j(this).attr("disabled","disabled");
			slide('right',_slaveUseStaticID);
		}
		else {
			if (parseInt($j('#hidCurFriendPage').val())*6 < parseInt($j('#hidFriendCnt').val())) {
				$j(this).attr("disabled","disabled");
				$j('#hidCurFriendPage').val(parseInt($j('#hidCurFriendPage').val()) + 1);
				listFriend($j('#hidCurFriendPage').val());
				slide('right',_slaveUseStaticID);
			}
		}
		
		return false;
		*/
	});	
	
	initMoveBar(_slaveUseStaticID, 0, 0);
	//get friend list
	//listFriend(1);
	cm_initScrollPane(objPane);

	//get feed list
	//listFeed($j('#hidProfileUid').val(), 1);	
	
	//gift list
	if (0 < parseInt($j("#hidCntGift").val())) {	
		$j("#btnLeft").click(function() {
			var nextPage = (parseInt($j("#pageIndex").val()) - 1 < 1) ? $j("#hidCntGift").val() : parseInt($j("#pageIndex").val()) - 1; 
	    	giftNavAction(nextPage);
	    	return false;
	    });
	    
	    $j("#btnRight").click(function() {
	    	var nextPage = (parseInt($j("#pageIndex").val()) + 1 > parseInt($j("#hidCntGift").val())) ? 1 : parseInt($j("#pageIndex").val()) + 1; 
	    	giftNavAction(nextPage);
	    	return false;
	    });
	    
		//giftNavAction(1);
	}
	adjustHeight();
	
	//activity
	/*if (null != $j('#activity') && $j('#activity').val()!=undefined && ''!= $j('#activity').val()) {
		var aryAct = $j('#activity').val().split('|');
		for (var i=0; i<aryAct.length; i++) {
			if ('' != aryAct[i]) {
//alert(aryAct[i] + '||' + $j('#activityPic').val());			
				postActivityWithPic(aryAct[i], $j('#activityPic').val());
			}
		}
		//tar act
		var aryActTar = $j('#activityNew').val().split('|');
		for (var i=0; i<aryActTar.length; i++) {
			if ('' != aryActTar[i]) {
				var aryData = aryActTar[i].split('&');
//alert(aryData[0] + '||' + aryData[1]);					
				postActivityWithPic(aryData[0], $j('#activityPic').val(), '', aryData[1]);
			}
		}
	}*/
});


//to profile page
function lnkProfile(elem, uid) {
	
	var x = 0;
	while(elem){
		x = x + elem.offsetLeft;
		elem = elem.offsetParent;
	}
	var pos = 1;
	pos = Math.floor(Math.abs(x-260)/66) + 1;
	window.location.href = UrlConfig.BaseUrl + '/slave/profile?uid=' + uid + '&pos=' + pos;
}
	
/**
 * jquery get the friend list
 *
 * @param integer page
 * @return void
 */
function listFriend(rankStart, rankEnd, cntStart, cntSize, moveMethod)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listfriend';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "posStart=" + cntStart + "&fetchSize=" + cntSize,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var aryInfo = responseObject.info;
	            	//var cntInfo = $j('#hidFriendCnt').val();//responseObject.count;	     
	            	$j('#hidPosprev').val(rankStart);
	            	$j('#hidPosnext').val(rankEnd);	         	
	            	if (null != aryInfo && 0!=aryInfo.length) {
	            		var strHtml = '<!--';
	            		for (var i = 0 ; i < aryInfo.length ; i++) {
	            			if (0 == aryInfo[i].fid) {
	            				strHtml += '--><li><a href="javascript:void(0);"><img height="60px" width="60px" src="' + UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_s_4.png" alt="招待する" /></a></li><!--';
	            			}
	            			else {
	            				strHtml += '--><li><a href="javascript:void(0);" onclick="lnkProfile(this,\'' + aryInfo[i].fid + '\');return false;" style="background-image: url(' + aryInfo[i].thumbnailUrl + ');"><img height="60px" width="60px" src="' + UrlConfig.StaticUrl + '/apps/slave/img/dummy/spacer.gif" alt="' + aryInfo[i].displayName + '" /></a></li><!--';
	            			}
	            		}
	            		strHtml += '-->';	            		
	            		
	            		if ('rightStep' == moveMethod) {
	            			$j('#ulFriend').append(strHtml);
	            			initMoveBar(_slaveUseStaticID, 0, cntSize * _slaveUseStaticID.moveSetp);
							slide('right',_slaveUseStaticID);
	            		}
	            		else if ('leftStep' == moveMethod) {
	            			$j('#ulFriend').html(strHtml + $j('#ulFriend').html());
	            			$j(_slaveUseStaticID.innaerFrame).css('left', 0 + (-1) * cntSize * _slaveUseStaticID.moveSetp);
							initMoveBar(_slaveUseStaticID, 0, cntSize * _slaveUseStaticID.moveSetp);
							slide('left',_slaveUseStaticID);
	            		}
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
}

/**
 * jquery get the gift list
 *
 * @return void
 */
function giftNavAction(page)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/getgiftinfo';

	$j("#pageIndex").val(page);
	//$j('#divgift').fadeOut('slow');
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "pageIndex=" + page + "&pageSize=" + CONST_DEFAULT_PAGE_SIZE + '&uid=' + $j('#hidProfileUid').val(),
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var strHtml = showGiftInfo(responseObject.info);	           
	            	$j('#divgift').html(strHtml).fadeIn('fast');            	
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
 * jquery show gift info
 * @param  object array
 * @return string
 */
function showGiftInfo(array)
{

    //concat html tags to array data
    var html = '';          
    var img = UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_n_s.png';
    if (null != array.gift_big_pic && '' != array.gift_big_pic) {
		img = array.gift_big_pic;
	}
	//html += '<p class="pic"><a href="' + UrlConfig.BaseUrl + '/slave/torakuten?gid=' + encodeURIComponent(array.gid) + '" style="background-image:url(' + img + ')">' + cm_escapeHtml(array.name) + '</a></p>';
	html += '<p class="pic" style="cursor:default;"><span style="background-image:url(' + img + ')">' + cm_escapeHtml(array.name) + '</span></p>';
	html += '<p class="name">' + cm_escapeHtml(array.name) + '</p>';
	html += '<p class="price">価格：¥' + array.format_price + '</p>';
	//is my profile page gift
	if ($j('#hidUid').val() == $j('#hidProfileUid').val()) {
		html += '<ul class="action box"><!--';
		html += '--><li><a href="' + UrlConfig.BaseUrl + '/slave/presentgift?id=' + array.id + '">プレゼントする</a></li><!--';
		html += '--><li><a href="' + UrlConfig.BaseUrl + '/slave/sellgift?id=' + array.id + '">ギフト屋に売る</a></li><!--';
		html += '--></ul><!--/.action.box-->';
	}
	
	$j('#lnkToarkuten').attr('href', UrlConfig.BaseUrl + '/slave/torakuten?gid=' + encodeURIComponent(array.gid));
	
    return html;
}


/**
 * jquery get the feed list
 *
 * @param  string uid
 * @param  integer mode
 * @return void
 */
function listFeed(uid, mode)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listfeed';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "uid=" + uid + "&mode=" + mode,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var strHtml = showFeedInfo(responseObject.info);	           
	            	$j('#newsFeed').html(strHtml);	  
	            	//init scroll bar
					cm_initScrollPane(objPane);          	
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
 * jquery show feed info
 * @param  object array
 * @return string
 */
function showFeedInfo(array) 
{

	//concat html tags to array data
    var html = '';      
    if (null == array ||0 == array.length) {
    	html += '<p>まだ何もありません。</p>';
    	return html;
    }
      
	html += '<ul><!--';
	
    //for each row data    
    for (var i = 0 ; i < array.length ; i++) {
        html += '--><li>';
        html += '<p class="pic"><a class="noLink" style="background-image:url(' + array[i].pic_url + ');"></a></p>';
        html += '<p class="date">[' + array[i].format_time + ']</p>';
        html += '<p class="action">' + array[i].message + '</p>';       
        html += '</li><!--';        
    }
    html += '--></ul>';
    return html;
}
