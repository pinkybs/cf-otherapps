/*
----------------------------------------------
Grope Chat static JavaScript

Created Date: 2009/05/29
Author: Yu Uno
Last Up Date : 2009/05/29
Author: Yu Uno
----------------------------------------------
*/

var $j = jQuery.noConflict();

function initListMoveBar(chatUseStaticID) 
{
	var minusMove = -chatUseStaticID.frameWidth + 'px';
	var plusMove = chatUseStaticID.frameWidth + 'px';
	var nowPosition = 0;
	var listSize = $j(chatUseStaticID.innaerFrame).find('ul').size()-1;
	var maxWidth = chatUseStaticID.frameWidth * listSize * -1;

/*マイミクリストをスライド*/
function slide(direction,chatUseStaticID){
	if(direction == 'left'){		
		$j(chatUseStaticID.innaerFrame).animate({left : '+=' + chatUseStaticID.frameWidth},300, function(){
			measurePosition(chatUseStaticID);
			if (nowPosition > 0) {
					$j(chatUseStaticID.innaerFrame).css('left', '0px');
			}
		});
	} else if(direction == 'right'){
		$j(chatUseStaticID.innaerFrame).animate({left : '-=' + chatUseStaticID.frameWidth},300, function() {
			measurePosition(chatUseStaticID);
			if (nowPosition < maxWidth) {
					$j(chatUseStaticID.innaerFrame).css('left',  maxWidth + 'px');
			}
		});
	}
}
function measurePosition(chatUseStaticID){
	nowPosition = $j(chatUseStaticID.innaerFrame).css('left');
	nowPosition = nowPosition.replace('px','');
}	
	
	$j(chatUseStaticID.navLeft).unbind( "click" );
	$j(chatUseStaticID.navLeft).bind('click', function(){
		measurePosition(chatUseStaticID);
		if (nowPosition < 0) {
			slide('left',chatUseStaticID);
		}
		return false;
	});
	$j(chatUseStaticID.navRight).unbind( "click" );
	$j(chatUseStaticID.navRight).bind('click', function(){
		measurePosition(chatUseStaticID);
		if (nowPosition > maxWidth) {
			slide('right',chatUseStaticID);
		}
		return false;
	});
	
	/*チェックを入れた時に .active を付加*/
	$j(chatUseStaticID.innaerFrame).find('input[type=checkbox]').click(function(){
		checkedCheck(chatUseStaticID.innaerFrame);
	});
	checkedCheck(chatUseStaticID.innaerFrame);
	
	/*名前を表示*/
	$j(chatUseStaticID.innaerFrame).find('li').hover(
		function(){
			$j(this).find('span').show();
		},
		function(){
			$j(this).find('span').hide();
		}
	);
}

	
/*チェックを入れた時に .active を付加*/
function checkedCheck(containerID){
	$j(containerID)
		.find('input[type=checkbox]')
		.parent('li')
		.removeClass('active')
		.find('input:checked')
		.parent('li')
		.addClass('active');
}

//escape html tags
function cm_escapeHtml(strContent) 
{
    return strContent.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#039;");
}

function cm_nl2br(strContent) 
{
	return strContent.replace(/\r\n|\r|\n/g,'<br/>');
}

function cm_getCookie(name)
{
	name += '_chat';
    var result = null;
    var myCookie = document.cookie + ";";
    var searchName = name + "=";
    var startOfCookie = myCookie.indexOf(searchName);
    var endOfCookie;
    if (startOfCookie != -1) {
        startOfCookie += searchName.length;
        endOfCookie = myCookie.indexOf(";",startOfCookie);
        result = unescape(myCookie.substring(startOfCookie, endOfCookie));
    }
    return result;
}