/**
 * chomeboard(/chomeboard/rank.js)
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/05/14    Liz
 */

var ParkingRankCanMoveOne = 1;
var ParkingRankCanMoveLast = 1;
var parkingMove;
var enoughMove = 1;

/**
 * windows load function
 * Register function on window
 */
Event.observe(window, 'load', function() {
    
    var allInvite = $F('txtAllInvite');
    if (allInvite) {
        enoughMove = 0;
    }
	doFilter(1,1);
});

/**
 * do filter
 *
 */
function doFilter(type1,type2)
{
    if (type1 == 3){
        type1 = $('txtType1').value;
    }
    else {
        $('txtType1').value = type1;
    }
    
    if (type2 == 3) {
        type2 = $('txtType2').value;
    }
    else {
        $('txtType2').value = type2;
    }
    
    if (type1==1) {
        var para = document.getElementById('friendSphereTabsHref').className = "active";
        var para = document.getElementById('allSphereTabsHref').className = "";
    }
    else {
        var para = document.getElementById('allSphereTabsHref').className = "active";
        var para = document.getElementById('friendSphereTabsHref').className = "";
    }
    
    if (type2==2) {
        var para = document.getElementById('totalRankingTabHref').className = "";
        var para = document.getElementById('carsRankingTabHref').className = "active";
    }
    else {
        var para = document.getElementById('carsRankingTabHref').className = "";
        var para = document.getElementById('totalRankingTabHref').className = "active";
    }
    getRankList(1);
}

/**
 * get ranklist
 *
 * @param  integer
 * @return void
 */
function getRankList(page)
{
    $('pageIndex').value = page;
    
    var requestObject = new Object();
    requestObject.page = page;
    requestObject.type1 = $F('txtType1');
    requestObject.type2 = $F('txtType2');
    
    var jsonRequest = Object.toJSON(requestObject);

    //send the ajax request
    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/chomeboard/ranking';

    var myAjax = new Ajax.Request(url, {method: 'get', parameters:
        'request='+escape(jsonRequest)+'&r='+escape(rand),
        // summary: callback function if time out.
        onTimeout: function() {
            $('totalRanking').setStyle({display: 'none'});
        },
        // summary: callback function on oncreate.
        //      show loading image.
        onCreate : getDataFromServer_Rank,
        // summary: callback function if success.
        //      if delete successfully ,after 3 senconds ,back to calendar page
        // param: response  String
        onSuccess: renderResults_Rank});
}

/**
* show loading image
*/
function getDataFromServer_Rank()
{
    $('totalRanking').setStyle({display: 'none'});
    $('loading').innerHTML = '<div style="*height:121px;"></div>';
}

/**
* process data from server
*
* @param string response
*/
function renderResults_Rank(response)
{
    var responseObject = response.responseText.evalJSON();
    
    if (responseObject.rankStatus == 1) {
        var html = showRankInfo(responseObject.rankInfo, responseObject.count, 1);
    }
    else {
        var html = showRankInfo(responseObject.rankInfo, responseObject.count, 1);
    }
    $('ranking').innerHTML = html;    
    
    var topRankHtml = showTopRank(responseObject.topRank, responseObject.topCount);
    $('topRank').innerHTML = topRankHtml;
    
    $('txtRankCount').value = responseObject.countArr.rankCount;
    $('txtAllCount').value = responseObject.countArr.allCount;
    $('txtRightCount').value = responseObject.countArr.rightCount;
    $('txtLeftCount').value = 0;
    $('ranking').setStyle({left: '0px'});
    $('loading').innerHTML = '';
    $('totalRanking').setStyle({display: ''});
    /*if (responseObject.countArr.rankCount < 8) {
        $('ranking').setStyle({left:(8-responseObject.countArr.rankCount)*58 + 'px'});  
    }*/
}

/**
 * show top rank info
 *
 */
function showTopRank(topRank, topCount)
{
    var html = '';
        
    if (topCount == 1) {
        html += '<li id="2">'
              + '   <p class="ranking">2</p>'
              + '   <p class="name">&nbsp;</p>'
              + '   <p class="pic">'
              + '        <a target="_top" href="http://mixi.jp/send_message.pl"><img height="50" width="50" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/invite.gif"/></a>'
              + '   </p>'
              + '   <p class="plice">&nbsp;</p>';  
        html += '</li>';
    }
    
    for (i = 0; i < topRank.length; i++) {
        var rankId = topCount-i;
        html += '<li id="' + rankId + '">'
              + '   <p class="ranking">' + rankId + '</p>'
              + '   <p class="name"><a href="javascript:void(0);"  onclick="showChomeBoard(\'' + topRank[i].uid + '\')" title="' + topRank[i].displayName + '">' + topRank[i].displayName.unescapeHTML().truncate2(10).escapeHTML() + '</a></p>'
              + '   <p class="pic">'
              + '        <a href="javascript:void(0);" onclick="showChomeBoard(\'' + topRank[i].uid + '\')" title="' + topRank[i].displayName + '"><img height="50" width="50" alt="" src="' + topRank[i].thumbnailUrl + '"/></a>'
              + '   </p>'
              + '   <p class="plice">' + topRank[i].comment_count + 'ﾁｮﾒ</p>';
              
        //check user is online
        /*if ( topRank[i].online == 1 && topRank[i].uid != $F('txtUid') ) {
            html += '   <p class="icon">'
                  + '       <img height="16" width="16" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/icon/warning.gif"/>'
                  + '   </p>';
        }*/
        html += '</li>';
        
    }
    return html;
}

/**
 * show rank info
 *
 */
function showRankInfo(rankInfo, userRankNm, type)
{
    var html = '';
    
    enoughMove = 1;
    
    if ( type == 1 ) {
        if ( rankInfo.length < 12 ) {
            for ( i = 0, count = (12 - rankInfo.length); i < count; i++ ) {
                html += '<li id="' + (14-i) + '">'
                      + '   <p class="ranking">' + (14-i) + '</p>'
                      + '   <p class="name">&nbsp;</p>'
                      + '   <p class="pic">'
                      + '        <a target="_top" href="http://mixi.jp/send_message.pl"><img height="50" width="50" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/invite.gif"/></a>'
                      + '   </p>'
                      + '   <p class="plice">&nbsp;</p>';  
                html += '</li>';
            }
            
            enoughMove = 0;
        }
    } 
    for (i = 0; i < rankInfo.length; i++) {
        var rankId = Number(userRankNm)-i;
        
        html += '<li id="' + rankId + '">'
              + '   <p class="ranking">' + rankId + '</p>'
              + '   <p class="name"><a href="javascript:void(0);"  onclick="showChomeBoard(\'' + rankInfo[i].uid + '\')" title="' + rankInfo[i].displayName + '">' + rankInfo[i].displayName.unescapeHTML().truncate2(10).escapeHTML() + '</a></p>'
              + '   <p class="pic">'
              + '        <a href="javascript:void(0);"  onclick="showChomeBoard(\'' + rankInfo[i].uid + '\')" title="' + rankInfo[i].displayName + '"><img height="50" width="50" alt="" src="' + rankInfo[i].thumbnailUrl + '"/></a>'
              + '   </p>'
              + '   <p class="plice">' + rankInfo[i].comment_count + 'ﾁｮﾒ</p>';
              
        //check user is online
        /*if ( rankInfo[i].online == 1 && rankInfo[i].uid != $F('txtUid') ) {
            html += '   <p class="icon">'
                  + '       <img height="16" width="16" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/icon/warning.gif"/>'
                  + '   </p>';
        }*/
        
        html += '</li>';
    }
    
    return html;
}

/**
 * move div
 *
 */
function moveRanking(type)
{
    if ( ParkingRankCanMoveOne == 1 && enoughMove == 1 ) {
        ParkingRankCanMoveOne = 2;

        var rightCount = Number($('txtRightCount').value);
        var leftCount = Number($('txtLeftCount').value);
        var rankCount = Number($('txtRankCount').value);
        
        if (rankCount >= 1) {
            var liId;
            if (rankCount >12) {
                liId = rankCount-1;
            }
            else {
                liId = 11;
            }
            var rightLastId = $('ranking').down('li', liId).id; 
            var leftFirstId = $('ranking').down('li').id; 
        
            var duration = 0.5;
            var canMove = 1;
            
            //move right
            if (type==1) {
                //can move
                if ( rightCount > 0 ) {
                    var width = -58;
                    
                    $('txtRightCount').value = rightCount - 1;
                    $('txtLeftCount').value = leftCount + 1;
                }//the last one
                else if (rightLastId == 3) {
                    canMove = 2;
                }//get more
                else {
                    getMoreRankInfo(rightLastId, 1);
                    return;
                }
            }//move left
            else if (type==2) {
                //can move
                if ( leftCount > 0  ) {
                    var width = 58;
                    
                    $('txtRightCount').value = rightCount + 1;
                    $('txtLeftCount').value = leftCount - 1;
                }
                //the last one
                else if (leftFirstId == Number($('txtAllCount').value) ) {
                    canMove = 2;
                }
                else if (leftFirstId > Number($('txtAllCount').value) && leftFirstId==14 ) {
                    canMove = 2;
                }
                //get more
                else {
                    getMoreRankInfo(leftFirstId, 2);
                    return;
                }
            }
            else {
                canMove = 2;
            }
        }
        else {
            canMove = 2;
        }
        
        //move
        if (canMove == 1) {
            new Effect.Move ('ranking',{
                x: width, y: 0,
                duration: duration, 
                afterFinish:function() {
                   ParkingRankCanMoveOne = 1;
                }
            });
        }
        else {
            ParkingRankCanMoveOne = 1;
        }
    }
}

/**
 * get more rank list right
 *
 * @param  integer
 * @return void
 */
function getMoreRankInfo(rankId, isRight)
{
    var requestObject = new Object();
    requestObject.rankId = rankId;
    requestObject.isRight = isRight;
    requestObject.allCount = $F('txtAllCount');
    
    requestObject.type1 = $F('txtType1');
    requestObject.type2 = $F('txtType2');
    
    var jsonRequest = Object.toJSON(requestObject);

    //send the ajax request
    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/chomeboard/getmorerank';

    var myAjax = new Ajax.Request(url, {method: 'get', parameters:
        'request='+escape(jsonRequest)+'&r='+escape(rand),
        onSuccess: renderResults_getMoreRank});
}

/**
* process data from server
*
* @param string response
*/
function renderResults_getMoreRank(response)
{
    var responseObject = response.responseText.evalJSON();
    
    var moreRankHtml = showRankInfo(responseObject.rankInfo, responseObject.count, 2);
    if (moreRankHtml) {
        $('txtRankCount').value = Number($('txtRankCount').value) + Number(responseObject.rankInfo.length);

        $('txtAllCount').value = responseObject.allCount;
        ParkingRankCanMoveOne = 1;
        
        if ( responseObject.isRight == 1 ) {
            new Insertion.Bottom('ranking', moreRankHtml);
            $('txtRightCount').value = responseObject.rankInfo.length;
            moveRanking(1);
        }
        else {
            rankLeft = -58 * responseObject.rankInfo.length + parseInt($('ranking').getStyle('left'));
        	$('ranking').setStyle({left:rankLeft+'px'});  
            new Insertion.Top('ranking', moreRankHtml);
            $('txtLeftCount').value = responseObject.rankInfo.length;
            moveRanking(2);
        }
    }
}

/**
 * get more rank list left
 *
 * @param  integer
 * @return void
 */
function moveLast(isRight)
{
    if ( ParkingRankCanMoveLast == 1 && enoughMove == 1 ) {
        ParkingRankCanMoveLast = 2;
        var requestObject = new Object();
        requestObject.type1 = $F('txtType1');
        requestObject.type2 = $F('txtType2');
        requestObject.isRight = isRight;
        
        var jsonRequest = Object.toJSON(requestObject);
    
        //send the ajax request
        var rand=Math.random();
        var url = UrlConfig.BaseUrl + '/ajax/chomeboard/getlastrank';
    
        var myAjax = new Ajax.Request(url, {method: 'get', parameters:
            'request='+escape(jsonRequest)+'&r='+escape(rand),
            onSuccess: renderResults_getLastRank});
    }
}

/**
* process data from server
*
* @param string response
*/
function renderResults_getLastRank(response)
{
    if (response.responseText) {
        var responseObject = response.responseText.evalJSON();
            
        var html = showRankInfo(responseObject.rankInfo, responseObject.rankNm, 1);
        $('ranking').innerHTML = html;
        
        $('txtRankCount').value = responseObject.countArr.rankCount;
        $('txtAllCount').value = responseObject.countArr.allCount;
        $('txtRightCount').value = responseObject.countArr.rightCount;
        $('txtLeftCount').value = 0;
        $('ranking').setStyle({left: '0px'});
        
    }
    ParkingRankCanMoveLast = 1;
}