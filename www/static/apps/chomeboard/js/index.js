//IE,return 1 Firefox,return 2, Safari,return 3 ,Camino,return 4 Gecko,return 5
var uaType = getOs();
var canMove = 1;

Event.observe(window, 'load', function() {
    showChomeBoard($F('bownerId'));

	//addNewsFeed($F('friendsFeed'))
});

Event.observe(document, "keydown", function(event) {
    var e = Event.element(event);
    if (event.keyCode == 40) {
        keyDown();
    }
    if (event.keyCode == 38) {
        keyUp();
    }
});

function keyDown(){
    var nowSelectID = $F('curSortId');
    var maxLength = $F('boardCount') - 1;

    if (nowSelectID != 0) {
        nowSelectID--;

        toID = 'board' + nowSelectID;

        if(1 == canMove) {
			showBoardInfo($F('hidBid' + nowSelectID), $F('hidCommentUid' + nowSelectID), $F('hidDisplayName' + nowSelectID),
							$F('hidCreateTime' + nowSelectID), $F('hidContent' + nowSelectID), $F('hidSortId' + nowSelectID));
			showSliderBar($F('boardCount'), $F('hidSortId' + nowSelectID), "keydown");
		}
    }
    return false;
}

function keyUp(){
    var nowSelectID = $F('curSortId');
    var maxLength = $F('boardCount') - 1;

    if (nowSelectID != maxLength) {
        nowSelectID++;

        toID = 'board' + nowSelectID;

        if(1 == canMove) {
			showBoardInfo($F('hidBid' + nowSelectID), $F('hidCommentUid' + nowSelectID), $F('hidDisplayName' + nowSelectID),
							$F('hidCreateTime' + nowSelectID), $F('hidContent' + nowSelectID), $F('hidSortId' + nowSelectID));
			showSliderBar($F('boardCount'), $F('hidSortId' + nowSelectID), "keyup");
		}

    }
    return false;
}

/**
 * get the select page's messageboard list
 *
 * @param page int
 * @return void
 */
function showChomeBoard(bownerId)
{
    var requestObject = new Object();
    requestObject.viewerId = $F('viewerId');      //get the user id
    requestObject.bownerId = bownerId;      //get the user id

    var jsonRequest = Object.toJSON(requestObject);

    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/chomeboard/getboardinfo';

    new Ajax.Request(url, {
        method: 'post',
        parameters: 'request='+escape(jsonRequest)+'&r='+escape(rand),
        onTimeout: function() {
            $('boardColumn').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        //onCreate : getDataFromServer,
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
	var array = response.responseText.evalJSON();
	
	var responseObject = response.responseText.evalJSON();
	var lastChomeBoard = responseObject.lastChomeBoard;
	var aryBoardHistory = responseObject.aryBoardHistory;
	var lastChomeBoardId = responseObject.lastChomeBoardId;
	var chomeringFlag = responseObject.chomeringFlag;
	var ownerInfo = responseObject.ownerInfo;

	$('boardCount').value = aryBoardHistory.length;
	$('lastChomeBoardId').value = lastChomeBoardId;
	$('bownerId').value = lastChomeBoard.uid;
	$('chomeringFlag').value = chomeringFlag;
	$('curSortId').value = lastChomeBoard.sort_id;

	var html = showBoardList(lastChomeBoardId, aryBoardHistory);

	$('chomeHead').innerHTML = changeChomehead(ownerInfo)
	$('friendsColumn').innerHTML = html;
	$('boardColumn').innerHTML = showLastBoardInfo(lastChomeBoard);

	showSliderBar(aryBoardHistory.length, lastChomeBoard.sort_id, "init");
	if (aryBoardHistory.length > 4) {
		Event.observe('friendsColumn', 'mousewheel', scrollfunc);
		Event.observe('friendsColumn', 'DOMMouseScroll', scrollfunc);
	}
}

function showSliderBar(historyLength, sortId, type) {
    if (historyLength > 4) {
        var boardHeight = 105;
        var offset = 23;
        if (1 == uaType) {
            boardHeight = 109;
            offset = 40;
        }
        var activeBoardPos = 0;
		var curTop = Math.abs(parseInt($('list').getStyle('top')));
        var height = boardHeight * (historyLength - 5) + offset;
		if ("click" != type) {
			if ("init" == type) {
				if ( sortId <= 3) {
					activeBoardPos = boardHeight * (historyLength - 5) + offset;
				} else if ( sortId > 3 && (historyLength - 1 -sortId) > 3) {
					activeBoardPos = boardHeight * (historyLength - 1 - sortId);
				} else if ( (historyLength - 1 -sortId) <= 3) {
					activeBoardPos = 0;
				}

				$('list').setStyle({top: 0 - activeBoardPos + 'px'});
			}

			if ("keydown" == type || "keyup" == type) {
				if (sortId > 3) {
					activeBoardPos = boardHeight * (historyLength - 1 - sortId);
				} else {
					activeBoardPos = height;
				}
				var move = 0;
				if ("keydown" == type) {
					var moveArea = boardHeight * (historyLength - 5) + offset;
					if(moveArea < curTop) {
						move =  0;
					} else if((curTop + boardHeight) > moveArea){
						move = -(moveArea - curTop);
					} else {
						move = -boardHeight;
					}
				} else if ("keyup" == type) {
					if(boardHeight > curTop) {
						move =  curTop;
					} else {
						move = boardHeight;
					}
				}

				if (0 != move) {
					new Effect.Move ('list',
						{x: 0, y: move, duration: 0.3,
						beforeStart : function() { canMove = 2; },
						afterFinish:function() { canMove = 1; }
					});
				}
			}

			new Control.Slider($('divHandle'), $('divTrack'), {
				axis:'vertical',
				range: $R(0, height),
				sliderValue: activeBoardPos,
				onSlide: function(value) {
					$('list').setStyle({top: 0 - value + 'px',duration : 1});
				},
				onChange: function(value) {
					$('list').setStyle({top: 0 - value + 'px'});
				}
			});
		}
    }
}

/**
 * show board info list
 *
 * @param array aryBoardHistory
 * @return string
 */
function showBoardList(lastChomeBoardId, aryBoardHistory)
{
    var html = '';

    html +='<div id="container" class="jScrollPaneContainer" style="height: 502px; width: 211px;">';

    html +='<div id="list" class="inner" style="overflow: visible; height: auto; width: 191px; padding-left: 20px; position: absolute; top: 0px;">';
    html +='<ul id="ulBoards">';

    //set board list
    for (i = 0 ; i < aryBoardHistory.length ; i++) {
        //set active status
        if (aryBoardHistory[i].bid == lastChomeBoardId) {
            html +='<li id="board' + aryBoardHistory[i].sort_id  + '" class="active">';
        } else {
            html +='<li id="board' + aryBoardHistory[i].sort_id  + '">';
        }
        html +='<a href="javascript:void(0)" onclick="showBoard(' + aryBoardHistory[i].bid + ', \'' + aryBoardHistory[i].comment_uid + '\', \'' + aryBoardHistory[i].displayName.replace(/&/g,"&amp;") + '\', \'' + aryBoardHistory[i].create_time + '\', \'' + aryBoardHistory[i].content + '\',' + aryBoardHistory[i].sort_id + ',\'click\');">';
        html +='    <span style="background-image:url(' + aryBoardHistory[i].thumbnailUrl +')"></span>';
        html +='<input id="hidBid' + aryBoardHistory[i].sort_id  + '" type="hidden" value="' + aryBoardHistory[i].bid + '">';
        html +='<input id="hidCommentUid' + aryBoardHistory[i].sort_id  + '" type="hidden" value="' + aryBoardHistory[i].comment_uid + '">';
        html +='<input id="hidDisplayName' + aryBoardHistory[i].sort_id  + '" type="hidden" value="' + aryBoardHistory[i].displayName + '">';
        html +='<input id="hidCreateTime' + aryBoardHistory[i].sort_id  + '" type="hidden" value="' + aryBoardHistory[i].create_time + '">';
        html +='<input id="hidContent' + aryBoardHistory[i].sort_id  + '" type="hidden" value="' + aryBoardHistory[i].content + '">';
        html +='<input id="hidSortId' + aryBoardHistory[i].sort_id  + '" type="hidden" value="' + aryBoardHistory[i].sort_id + '">';
        html +='</a>';

    }
    html += '   </ul>';
    html += '</div>';

    if (aryBoardHistory.length > 4) {
        var percentView = Math.ceil((4/aryBoardHistory.length) * 100);
        html += '<div class="jScrollPaneTrack" style="width: 15px;" id="divTrack">'
        html += '   <div id="divHandle" class="jScrollPaneDrag" style="width: 15px; height: ' + percentView + '%; top: 0px;">';
        html += '       <div class="jScrollPaneDragTop" style="width: 15px;"></div>';
        html += '       <div class="jScrollPaneDragBottom" style="width: 15px;"></div>';
        html += '   </div>';
        html += '</div>';
    } else {
        html += '<div class="jScrollPaneTrack" style="width: 15px;" id="divTrack">'
        html += '</div>';
    }
    html += '</div>';

    return html;
}

/**
 * show last board info
 *
 * @param array lastChomeBoard
 * @return string
 */
function showLastBoardInfo(lastChomeBoard)
{
    var html = '';
    var bownerId = $F('bownerId');

    //set last board
    html +='<div id="alertBox">';
    html +='<a href="javascript:void(0)" id="chomering" onclick="showChomeBoard(\'' + lastChomeBoard.comment_uid + '\');">' + lastChomeBoard.displayName + '</a>のチョメチョメ　' + lastChomeBoard.create_time.formatToDate('yy/MM/dd hh:mm');

    //set active status
    if (1 == $F('chomeringFlag')) {
        html +='&nbsp;-&nbsp;<a href="javascript:void(0)" id="chomering" onclick="chomering('+ lastChomeBoard.bid +', \'' + $F('viewerId') + '\', \'' + $F('bownerId') + '\', \'' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + lastChomeBoard.content + '\');">チョメる</a>';
    }
    if (($F('bownerId') == $F('viewerId') || $F('viewerId') == lastChomeBoard.comment_uid) && lastChomeBoard.sort_id != 0) {
        html +='&nbsp;-&nbsp;<a href="javascript:void(0)" id="delete" onclick="deleteBoard(' + lastChomeBoard.bid + ', \'' + lastChomeBoard.uid + '\', \'' + lastChomeBoard.comment_uid + '\')">削除する</a>';
    }
    html +='</div><!--/#alertBox-->';

    if (1 == $F('chomeringFlag')) {
        html +='<div id="board" onclick="chomering('+ lastChomeBoard.bid +', \'' + $F('viewerId') + '\', \'' + $F('bownerId') + '\', \'' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + lastChomeBoard.content + '\');" style="background-image: url(' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + lastChomeBoard.content + ');">';
        html +='</div><!--/#board-->';
    } else {
        html +='<div id="board" style="background-image: url(' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + lastChomeBoard.content + ');">';
        html +='</div><!--/#board-->';
    }

    return html;
}

/**
 * change chome head
 *
 * @param array ownerInfo
 * @return string
 */
function changeChomehead(ownerInfo)
{
    var html = '';

    //change chome head
    html +='<p class="pic">';
    html +='<img height="25" width="25" alt="" src="' + ownerInfo.thumbnailUrl + '"/>';
    html +='</p>';
    html +='<h1>' + ownerInfo.displayName + 'さんのチョメチョメ★ボード</h1>';

    return html;
}

function showBoard(bid, comment_uid, name, create_time, content, sortId, type) {
	showBoardInfo(bid, comment_uid, name, create_time, content, sortId)
    showSliderBar($F('boardCount'), sortId, type);

}

/**
 * show board info
 *
 * @param bid int
 * @param comment_uid string
 * @param name string
 * @param create_time string
 * @param content string
 * @param sortId int
 * @return void
 */
function showBoardInfo(bid, comment_uid, name, create_time, content, sortId) {
    var html = "";
    var lastChomeBoardId = $F('lastChomeBoardId');
    var bownerId = $F('bownerId');
    var viewerId = $F('viewerId');

    //clear all active status
    for (i = 0; i <= lastChomeBoardId ; i++){
        if( $('board'+i) ) {
            $('board'+i).removeClassName("active");
        }
    }

    //set current board to active status
    $('board'+sortId).addClassName("active");

    var deleteLink = "";

    if ((bownerId == viewerId || comment_uid == viewerId) && sortId != 0){
        deleteLink = ' - <a href="javascript:void(0)" id="delete" onclick="deleteBoard(' + bid + ',\'' + bownerId + '\',\'' + comment_uid +'\')">削除する</a>';
    }

    var chomeringLink = "";
    var chomeringFlag = $F('chomeringFlag');

    if (chomeringFlag == 1){
        chomeringLink = ' - <a href="javascript:void(0)" id="chomering" onclick="chomering(' + bid + ',\'' + $F('viewerId') + '\', \'' + $F('bownerId') + '\',\'' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + content +'\');">チョメる</a>';
    }

    html += '<div id="alertBox">'
         + '      <a href="javascript:void(0)" id="chomering" onclick="showChomeBoard(\'' + comment_uid + '\');">' + name + '</a>のチョメチョメ　'
         +   create_time.formatToDate('yy/MM/dd hh:mm')
         + chomeringLink
         + deleteLink
         + '</div>';
    if (chomeringFlag == 1){
        html +='<div id="board" onclick="chomering(' + bid + ',\'' + $F('viewerId') + '\', \'' + $F('bownerId') + '\',\'' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + content +'\');" style="background-image: url(' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + content + ');">';
              + ' </div><!--/#board-->';
    } else {
        html +='<div id="board" style="background-image: url(' + UrlConfig.PhotoUrl + '/apps/chomeboard/' + content + ');">';
              + ' </div><!--/#board-->';
    }

    $('boardColumn').innerHTML = html;
    $('curSortId').value = sortId;
}

/**
 * show board info
 *
 * @param bid int
 * @param viewerId string
 * @param ownerId string
 * @param content string
 * @return void
 */
function chomering(bid, viewerId, ownerId, content) {
    $('alertBox').hide();

    var loadPic = 'src=' + content + '&fromUid=' + viewerId
                + '&postUrl=' + UrlConfig.HostUrl + '/ajax/chomeboard/newchomeboard'
                + '&forwardUrl=' + UrlConfig.HostUrl + '/chomeboard' + (viewerId != ownerId ? '?uid=' + ownerId : '')
                + '&bid=' + bid
                + '&uuid=' + ownerId ;
    var swfUrl = UrlConfig.StaticUrl + "/apps/chomeboard/swf/chomeBoard.swf";

    var html = '<div id="board">';
        html +='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="723" height="502" ' + (uaType == 1 ? 'id="chomeBoard"':'') +' align="middle">'
                    + '<param name="movie" value="' + swfUrl + '" />'
                    + '<param name="quality" value="high" />'
                    + '<param name="allowScriptAccess" value="always" />'
                    + '<param name="FlashVars" value="' + loadPic + '" />'
                    + '<embed id="chomeBoard" src="' + swfUrl + '" ' + (uaType == 2 ? '': 'wmode="opaque"') + 'quality="high" value="transparent" width="723" height="502" name="' + swfUrl + '" align="top" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" FlashVars="' + loadPic + '"></embed>'
                + '</object>';
        html += '</div>';

    $('boardColumn').innerHTML = html;
}

/**
 * delete messageboard
 *
 * @param integer bid
 * @return void
 */
function deleteBoard(bid, uid, comment_uid)
{
    if(window.confirm('本当に削除してよろしいですか？')){
        var requestObject = new Object();
        requestObject.bid = bid;
        requestObject.uid = uid;
        requestObject.comment_uid = comment_uid;
        var jsonRequest = Object.toJSON(requestObject);

        var rand=Math.random();
        var url = UrlConfig.BaseUrl + '/ajax/chomeboard/delete';

        new Ajax.Request(url, {
            method: 'get',
            parameters: 'request='+escape(jsonRequest)+'&r='+escape(rand),
            onTimeout: function() {
                $('boardColumn').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
            },
            onComplete: renderResults_delete});
    }
    else{
        return false;
    }
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
    var bownerId = $F('bownerId');
    var viewerId = $F('viewerId');
    var redirectUrl = "";

    if (bownerId != viewerId){
        redirectUrl = UrlConfig.BaseUrl + '/chomeboard?uid=' + bownerId;
    } else {
        redirectUrl = UrlConfig.BaseUrl + '/chomeboard';
    }

    window.location = redirectUrl;
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

function scrollfunc (event) {
        var boardHeight = 105;
        var offset = 23;
        if (1 == uaType) {
            boardHeight = 109;
            offset = 40;
        }
        //IE是event.wheelDelta，Firefox是event.detail
        //IE向上 > 0，Firefox向下 > 0
        var direct = 0;
        if (event.wheelDelta) {
            direct = event.wheelDelta > 0 ? 1 : -1;
        } else if (event.detail) {
            direct = event.detail < 0 ? 1 : -1;
        }
        var curTopPos = $('list').getStyle('top');
        var height = boardHeight * ($F('boardCount') - 5) + offset;

        var posListY = parseInt(curTopPos) + (parseInt(direct) * 50);

        if (posListY >= 0) {
            posListY = 0;
        } else if (posListY <= -(height)) {
            posListY = -(height);
        }

        $('list').setStyle({top: posListY + 'px'});
        new Control.Slider($('divHandle'), $('divTrack'), {
            axis:'vertical',
            range: $R(0, height),
            sliderValue: Math.abs(posListY),
            onSlide: function(value) {
                $('list').setStyle({top: 0 - value + 'px'});
            },
            onChange: function(value) {
                $('list').setStyle({top: 0 - value + 'px'});
            }
        });
}

function mousedown (event) {
	//IE
	if (1 == uaType) {
		if (event.button == 1){
            new Effect.Move('list',{x: 0, y: 0,duration: 0.3});
            //new Effect.Move('divHandle',{x: 0, y: 0,duration: 0.5})
		}
	}
	//NOT IE
	else {
		if (event.button == 0){
			var listTopPost = Math.abs(parseInt($('list').getStyle('top')));
            new Effect.Move('list',{x: 0, y: listTopPost,duration: 0.3});
	        //new Effect.Move ('divHandle',{x: 0, y: (sliderMove),duration: 0.5});
			$('divHandle').setStyle({top: '0px'});
		}
	}
}

//add newsfeed
function addNewsFeed(msg, picurl) {
	if ("" != msg) {
		postActivityWithPic(msg, picurl);
	}
	var chomeBoard = thisMovie('chomeBoard');
	new PeriodicalExecuter(function(pe) {
		if (chomeBoard){
		try{
			chomeBoard.forwardCallBack();
		}catch(e){}
		}
			pe.stop();
		}, 2);

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
