/**
 * wish(/shopping/wish.js)
 * shopping wish
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/10    zhangxin
 */
 
var CONST_DEFAULT_PAGE_SIZE = 10;

//jquery scroll bar
var _shoppingUseStaticID = {
	scrollFrame : '#memberFrame',
	innaerFrame : '#innaerFrame',
	navLeft : '#navLeft',
	navRight : '#navRight',
	moveSetp : 81,
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
		objStaticRank.maxWidth = objStaticRank.moveSetp * (listSize-6) * (-1);
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
	
	if (null != cm_getCookie('app_top_url')) {
		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	}						
	
	//scroll bar	
	$j('#navLeft').click(function() {
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntPos').val()) <= 7) {
			return;
		}
		var pos = measurePosition(_shoppingUseStaticID);
		if (pos < _shoppingUseStaticID.minWidth) {
			$j(this).attr("disabled","disabled");
			slide('left',_shoppingUseStaticID);
		}
		else {
			if (parseInt($j('#hidPosprev').val()) > 1) {
				$j(this).attr("disabled","disabled");
				var start = (parseInt($j('#hidPosprev').val()) - 7);
				var cntSize = start<1 ? (parseInt($j('#hidPosprev').val()) - 1) : 7;
				start = start > 1 ? start : 1;
				var end = $j('#hidPosnext').val();
				listFriend(start, end, start, cntSize, 'leftStep');
			}			
		}
		return false;		
	});
	
	$j('#navRight').click(function() {
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntPos').val()) <= 7) {
			return;
		}
		var pos = measurePosition(_shoppingUseStaticID);
		if (pos > _shoppingUseStaticID.maxWidth) {			
			$j(this).attr("disabled","disabled");
			slide('right',_shoppingUseStaticID);
		}
		else {
			var cntMax = parseInt($j('#hidCntPos').val());
			if (parseInt($j('#hidPosnext').val()) < cntMax) {
				$j(this).attr("disabled","disabled");
				var end = (parseInt($j('#hidPosnext').val()) + 7);
				var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidPosnext').val())) : 7;
				var start = parseInt($j('#hidPosnext').val()) + 1;
				var end = end > cntMax ? cntMax : end;
				listFriend($j('#hidPosprev').val(), end, start, cntSize, 'rightStep');		
			}
		}
		return false;		
	});	
	
	initMoveBar(_shoppingUseStaticID, 0, 0);
	adjustHeight();
});


//to profile page
function lnkProfile(elem, uid) {
	
	var x = 0;
	while(elem){
		x = x + elem.offsetLeft;
		elem = elem.offsetParent;
	}
	//alert(x);return;
	var pos = 1;
	pos = Math.floor(Math.abs(x-57)/_shoppingUseStaticID.moveSetp) + 1;
	window.location.href = UrlConfig.BaseUrl + '/shopping/wish?uid=' + uid + '&pos=' + pos;
}
	
/**
 * jquery get the friend list
 *
 * @param integer page
 * @return void
 */
function listFriend(rankStart, rankEnd, cntStart, cntSize, moveMethod)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/listfriend';

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
	            			if (0 == aryInfo[i].uid) {
	            				strHtml += '--><li><a href="javascript:void(0);" onclick="mixi_invite();return false;" style="background-image:url(' + UrlConfig.StaticUrl + '/apps/shopping/img/content/thum_invite.png)"><img src="' + UrlConfig.StaticUrl + '/apps/shopping/img/spacer.gif" width="76" height="76" alt="招待する" /></a></li><!--';
	            			}
	            			else {
	            				strHtml += '--><li><a href="javascript:void(0);" onclick="lnkProfile(this,\'' + aryInfo[i].uid + '\');return false;" style="background-image: url(' + aryInfo[i].thumbnailUrl + ');"><img height="76px" width="76px" src="' + UrlConfig.StaticUrl + '/apps/shopping/img/spacer.gif" alt="' + aryInfo[i].displayName + '" /></a></li><!--';
	            			}
	            		}
	            		strHtml += '-->';	            		
	            		
	            		if ('rightStep' == moveMethod) {
	            			$j('#ulFriend').append(strHtml);
	            			initMoveBar(_shoppingUseStaticID, 0, cntSize * _shoppingUseStaticID.moveSetp);
							slide('right',_shoppingUseStaticID);
	            		}
	            		else if ('leftStep' == moveMethod) {
	            			$j('#ulFriend').html(strHtml + $j('#ulFriend').html());
	            			$j(_shoppingUseStaticID.innaerFrame).css('left', 0 + (-1) * cntSize * _shoppingUseStaticID.moveSetp);
							initMoveBar(_shoppingUseStaticID, 0, cntSize * _shoppingUseStaticID.moveSetp);
							slide('left',_shoppingUseStaticID);
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

function changePageAction(page)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/listwishitem';
    
    $j("#pageIndex").val(page);
    try {
        $j.ajax({
            type: "GET",   
            url: ajaxUrl,
            dataType: "json",
            data: "pageIndex=" + page + "&pageSize=" + CONST_DEFAULT_PAGE_SIZE
                  + "&profileUid=" + $j("#hidProfileUid").val(),
            success: function(responseObject) {             
                if (responseObject) {          
                    //show response array data to list table
                    var strHtml = showItemInfo(responseObject.info);           
                    
                    $j('#maxCount').html(responseObject.count == null ? 0 : responseObject.count);
                    var numstart = (parseInt($j('#pageIndex').val()) - 1) * CONST_DEFAULT_PAGE_SIZE + 1;
                    var numend = (numstart + CONST_DEFAULT_PAGE_SIZE - 1) > parseInt(responseObject.count) ? responseObject.count : (numstart + CONST_DEFAULT_PAGE_SIZE - 1);
                    if (0 == responseObject.count) {
                        numstart = 0;
                        numend = 0;
                    }
                    $j('#lblNumS').html(numstart);
                    $j('#lblNumB').html(numend);
                    
                    $j('#lstItem').html('');
                    $j('#pageLeader').hide();
                    var nav = cm_showPagerNav(responseObject.count, parseInt($j("#pageIndex").val()), 10);
                    $j('#lstItem').html(strHtml + nav);

                    adjustHeight(); 
                    if (null != cm_getCookie('app_top_url')) {
                        top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
                    }
                }
                else {
                    $j('#message1').show();
                    $j('#message2').hide();
                    $j('#lstItem').hide();
                }
            }
        });
    }catch (e) {
        //alert(e);
    }
}

function showItemInfo(array)
{
    var html = '';     
    if (null == array ||0 == array.length) {
        return html;
    }
    
    var isMine = $j('#hidIsMine').val();
    
    //for each row data
    for (var i = 0 ; i < array.length ; i++) {
	   
	    htmlStr = '';
        if (isMine == 1){
	        htmlStr = '<a href="' + UrlConfig.BaseUrl + '/shopping/removeitem?iid=' + array[i].iid + '"><span>欲しいものリストからはずす</span></a>';
	    }
	    else {
	       if(array[i].model == 'remove'){
	           htmlStr = '<a href="' + UrlConfig.BaseUrl + '/shopping/removeitem?iid=' + array[i].iid + '"><span>欲しいものリストからはずす</span></a>';
	       }
	       else {
	           htmlStr = '<a href="' + UrlConfig.BaseUrl + '/shopping/additem?iid=' + array[i].iid + '"><span>欲しいものリストに追加する</span></a>';
	       }
	    }
	    
        html += '<div class="section">';        
        html += '<dl class="itemBlock clearfix"><!--';
        html += '--><dt class="pic"><a style="cursor:default;background-image:url(' + array[i].pic_small + ')"><img src="' + UrlConfig.StaticUrl + '/apps/shopping/img/spacer.gif" width="76"  height="76" alt="" /></a></dt><!--';
        html += '--><dd class="name">' + cm_escapeHtml(array[i].name) + '</dd><!--';
        html += '--><dd class="price">価格：￥' + array[i].format_price + '</dd><!--';
        html += '--></dl>';
        html += '<ul class="btnBlock clearfix"><!--';        
        html += '--><li class="remove">' + htmlStr + '</li><!--';
        html += '--><li class="rakuten"><a href="' + UrlConfig.BaseUrl + '/shopping/torakuten?iid=' + array[i].iid + '"><span>楽天市場で購入する</span></a></li><!--';
        html += '--></ul>';        
        html += '</div>';
    }

    return html;
}

