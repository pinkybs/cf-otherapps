/**
 * parking(/parking/indexinfo.js)
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/19    Liz
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
    
    userPark = new park();
    getCarInfo($('cid0').value, 0);
    showMinifeed();
    showHandle();
    var allInvite = $F('txtAllInvite');
    if (allInvite) {
        enoughMove = 0;
    }
});

/**
 * get car individual info
 *
 * @param  integer
 * @return void
 */
function getCarInfo(cid, listCarId)
{
    //on mouse over change div class
    ['0','1','2','3','4','5','6','7'].each(function(s) {
      var divIdOut = 'listCar'+s;
      if ($(divIdOut)) {
        var para = document.getElementById(divIdOut).className = "";
      }
    });
    
    listCarId = listCarId;
    //set valuse
    document.getElementById("carId").value = listCarId;
    var divId = 'listCar'+listCarId;
    var para = document.getElementById(divId).className = "active";
    
    var requestObject = new Object();
    if (cid == 0 || !cid) {
        cid = $F('cid0');
    }
    //get the car id
    requestObject.cid = cid;      
    
    var jsonRequest = Object.toJSON(requestObject);

    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/parking/getcarinfo';

    new Ajax.Request(url, {
        method: 'get',
        parameters: 'request='+escape(jsonRequest)+'&r='+escape(rand),
        onTimeout: function() {
            $('boardList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        onCreate : getDataFromServer_getCarInfo,
        onComplete: renderResults_getCarInfo});
}

/**
 * show processing info
 */
function getDataFromServer_getCarInfo()
{
    //$('carIndividual').innerHTML = '<div class="loading" style="display:inline;" >' + '<img src="' + UrlConfig.StaticUrl + '/apps/parking/img/loading.gif" alt="" />' + '</div>';
}

/**
 * callback funtion when success
 *  set the callback data to the showArea of the page
 *
 * @param string response
 * @return void
 */
function renderResults_getCarInfo(response)
{
    var responseObject = response.responseText.evalJSON();
    
    if ( responseObject.car == "")
    {
        $('carIndividual').innerHTML = 'まだ車がありません';
    }
    else
    {
        var html = showCarInfo(responseObject.car, responseObject.canSendCar);
        
        $('carIndividual').innerHTML = html;
        $('carIndividual').show();
    }
}

/**
 * show car individual info
 *
 * @param array car info
 * @return string
 */
function showCarInfo(car, canSendCar)
{
    var iconClass;
    if ( car.iconType == 1 ) {
        iconClass = "icon alert";
    }
    else if ( car.iconType == 2 ) {
        iconClass = "icon free";
    }
    else if ( car.iconType == 3 ) {
        iconClass = "icon gas";
    }
    else {
        iconClass = "icon loss";
    }
    
    var html = '';
    
    //set each friend
    html += '<p class="carPic"><img class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + car.cav_name + '/big/' + car.car_color + '.png?' + version.js + '" alt="' + car.name + '" /></p>'
          + '<h3>' + car.name + '</h3>';
        
    if (car.carStatus == 1) {
        html += '<p class="' + iconClass + '">' + car.status + '</p>'
              + '<ul class="btnList">';
    
        if ( canSendCar == 1) {
            html += '    <li id="btnPresent"><a href="javascript:void(0);" onclick="sendCarToFriend(&quot;' + car.cav_name + '&quot;,' + car.car_id + ',&quot;' + car.car_color + '&quot;);">友達にプレゼントする</a></li>';
        }
        else {
            html += '    <li id="btnPresent" class="disable"><a>友達にプレゼントする</a></li>';
        }
        
        html += '    <li id="btnChange"><a  href="'+ UrlConfig.BaseUrl +'/parking/carshop?car_id=' + car.car_id + '&car_color=' + car.car_color + '">売却して、別の車と交換する</a></li>'
              + '</ul>';
    }
    else {
        html += '<p class="' + iconClass + '" style="height:34px;">' + car.status + '</p>'
              + '<ul class="btnList">'
              + '   <li id="btnPresent" class="disable"><a>友達にプレゼントする</a></li>'
              + '   <li id="btnChange" class="disable"><a>売却して、別の車と交換する</a></li>'
              + '</ul>';
    }

    return html;
}

/**
 * send car to friend
 *
 */
function sendCarToFriend(cav_name, car_id, car_color)
{
    var friend = $F('txtUserFriend').evalJSON();
    
    var dialog = new baseDialog();
    var html = '';
    
   
    html += '   <div class="head">'
          + '       <h2>友達に車をプレゼントする</h2>'
          + '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
          + '   </div><!--/.head-->'
          + '   <div id="friendPresent" class="body">'
          + '       <h3>送付先を選択してください</h3>'
          + '       <div class="innerBody">'
          + '           <div class="toFriend" id="divSelectFriend">'
          + '               <select id="ddlUser" title="友達を選択してください。" class="validate-selection">';
          //+ '                   <option value="0" >選択して下さい</option>';
          
    for (i = 0; i < friend.length; i++) {
        html += '  <option value="' + friend[i].uid + '">' + friend[i].displayName + '</option>';
    }
         
    html += '               </select>さんにプレゼントする。'
          + '           </div><div id="advice-validate-selection-ddlUser" class="validation-advice" style="display:none;color:#FF0000;">友達を選択してください。</div>'
          + '           <p class="car">';
          
    html += '<img class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + cav_name + '/big/' + car_color + '.png" alt=""/>';
    
    html += '			</p>'
          + '       </div>'
          + '       <ul class="btnList">'
          + '           <li class="submit"><a href="javascript:void(0);" onclick="sendCarToFriendSumit(' + car_id + ',&quot;' + car_color + '&quot;);">決定</a></li>'
          + '           <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();">キャンセル</a></li>'
          + '       </ul>'
          + '   </div><!--/.body-->';
   
    dialog.insertHTML(html);
    
    if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
    	var elements = $('friendPresent').getElementsByClassName("alphafilter");
		for (var i=0; i<elements.length; i++) {
			var element = elements[i];
			if(element.nodeName=="IMG"){
				var newimg           = document.createElement("b");
				for(var key in element.currentStyle){
					newimg.style[key]=element.currentStyle[key];
				}
				newimg.className     = element.className;
				newimg.style.display = "inline-block";
				newimg.style.width   = element.width;
				newimg.style.height  = element.height;
				newimg.style.float   = element.align;
				newimg.style.filter  = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="+element.src+",sizingMethod='scale')";
				element.replace(newimg);
			}
		}
    }
}

/**
 * send car to friend
 *
 */
function sendCarToFriendSumit(car_id, car_color)
{
	/*
    var validSendFriend = new Validation('divSelectFriend', {immediate:true, useTitles:true});
    if (!validSendFriend.validate()) {
        return false;
    }*/
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/sendfriend';
    
    var myAjax = new Ajax.Request(url, {
        parameters: {
            fid : $F('ddlUser'),
            car_id : car_id,
            car_color : car_color
        },
        onSuccess: function(response){
            var message = '';
            
            if (Number(response.responseText) == '1') {   
                $('carCount').value = $('carCount').value - 1;              
                message = '友達に車をプレゼントしました。';
                showUserAsset();
                doFilter(1, 1);
                showMyCar();
                showHandle();
            }
            else if (response.responseText == '-2'){
                message = '既に8台の車を持っている友達には、プレゼントできません。';
            }
            else if (response.responseText == '-3'){
                message = '既にこの車を持っている友達には、プレゼントできません。';
            }
            else if (response.responseText == '-4'){
                message = '前回のプレゼントから1ヶ月経っていないので、まだプレゼントできません。';
            }
            else if (response.responseText == '-5'){
                message = '既に今月、1台の車をもらっているので、まだプレゼントできません。';
            }
            else if (response.responseText == '-6'){
                message = '1台しか持っていない車を友達にプレゼントすることはできません。';
            }
            else {
                message = 'システムエラー。';
            }
            
            var dialog = new baseDialog();
            var html = '';
            html += '<div class="head">'
                  + '   <h2>友達に車をプレゼントする</h2>'
                  //+ '   <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                  + '</div><!--/.head-->'
                  + '<div id="friendPresent" class="body">'
                  + '   <div class="innerBody">'
                  + '       <p class="alert">' + message + '</p>'
                  + '   </div>'
                  + '</div><!--/.body-->';
                  
            dialog.insertHTML(html);
            
            _PerExec = new PeriodicalExecuter(function(pe) {
                removeDialog();
                pe.stop();
            }, 3);
        }});
}

/**
 * show my car list
 *
 */
function showMyCar()
{
    var url = UrlConfig.BaseUrl + '/ajax/parking/getmycar';
    
    var myAjax = new Ajax.Request(url, {
        onSuccess: function(response){
            var array = response.responseText.evalJSON();

            var html = '';

            for (i = 0; i < array.length; i++) {
            
                var listClass="";
                if (i==0)
                {
                    listClass = "active";
                }
                
                html += '<li id="listCar' + i + '" class="'+ listClass + '" onclick="getCarInfo(' + array[i].id + ',' + i + ');" onmouseover="changeClass(' + i + ')" onmouseout="out(' + i + ')">'
                      + '  <p class="carPic"><img class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + array[i].cav_name + '/small/' + array[i].car_color + '.png?' + version.js + '" width="145" height="73" alt="" /></p>'
                      + '  <div class="parkingStatus">'    
                      + '      <p class="parkingMeter"><img src="' + UrlConfig.StaticUrl + '/apps/parking/img/carlist/meter_' + array[i].temp + '.gif" width="145" height="4" alt="" /></p>'
                      + '      <p class="carInfo">' + array[i].money + '</p>'
                      + '      <p class="name"><a href="javascript:void(0);">' + array[i].name + '</a></p>'
                      + '  </div><!--/.parkingStatus-->'
                      + '  <input type="hidden" id="cid' + i + '" value="' + array[i].id + '">'
                      + '</li>';
            }
            $('ulItems').update(html);

            getCarInfo(array[0].id, 0);
        }});
}

/**
 * show user asset
 *
 */
function showUserAsset()
{
    var url = UrlConfig.BaseUrl + '/ajax/parking/getasset';
    
    var myAjax = new Ajax.Request(url, {
        onSuccess: function(response){
            $('myAsset').innerHTML = '¥' + response.responseText;
        }});
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
    var url = UrlConfig.BaseUrl + '/ajax/parking/ranking';

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
        var para = document.getElementById('friendSphereTabs').className = "active";
        var para = document.getElementById('allSphereTabs').className = "";
    }
    else {
        var para = document.getElementById('allSphereTabs').className = "active";
        var para = document.getElementById('friendSphereTabs').className = "";
    }
    
    if (type2==2) {
        var para = document.getElementById('totalRankingTab').className = "";
        var para = document.getElementById('carsRankingTab').className = "active";
    }
    else {
        var para = document.getElementById('carsRankingTab').className = "";
        var para = document.getElementById('totalRankingTab').className = "active";
    }
    getRankList(1);
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
        if ( rankInfo.length < 8 ) {
            for ( i = 0, count = (8 - rankInfo.length); i < count; i++ ) {
                html += '<li id="' + (10-i) + '">'
                      + '   <p class="ranking">' + (10-i) + '</p>'
                      + '   <p class="name">&nbsp;</p>'
                      + '   <p class="pic">'
                      + '        <a target="_top" href="http://platform001.mixi.jp/send_message.pl"><img height="50" width="50" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/invite.gif"/></a>'
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
              + '   <p class="name"><a href="javascript:void(0);" onclick="userPark.goUserPark(\'' + rankInfo[i].uid + '\', 1, 1);" title="' + rankInfo[i].displayName + '">' + rankInfo[i].displayName.unescapeHTML().truncate2(10).escapeHTML() + '</a></p>'
              + '   <p class="pic">'
              + '        <a href="javascript:void(0);" onclick="userPark.goUserPark(\'' + rankInfo[i].uid + '\', 1, 1);" title="' + rankInfo[i].displayName + '"><img height="50" width="50" alt="" src="' + rankInfo[i].thumbnailUrl + '"/></a>'
              + '   </p>'
              + '   <p class="plice">' + truncatemoney(rankInfo[i].ass) + '円</p>';
              
        //check user is online
        if ( rankInfo[i].online == 1 && rankInfo[i].uid != $F('txtUid') ) {
            html += '   <p class="icon">'
                  + '       <img height="16" width="16" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/icon/warning.gif"/>'
                  + '   </p>';
        }
        
        html += '</li>';
    }
    
    return html;
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
              + '        <a target="_top" href="http://platform001.mixi.jp/send_message.pl"><img height="50" width="50" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/invite.gif"/></a>'
              + '   </p>'
              + '   <p class="plice">&nbsp;</p>';  
        html += '</li>';
    }
    
    for (i = 0; i < topRank.length; i++) {
        var rankId = topCount-i;
        html += '<li id="' + rankId + '">'
              + '   <p class="ranking">' + rankId + '</p>'
              + '   <p class="name"><a href="javascript:void(0);" onclick="userPark.goUserPark(\'' + topRank[i].uid +  '\', 1, 1);" title="' + topRank[i].displayName + '">' + topRank[i].displayName.unescapeHTML().truncate2(10).escapeHTML() + '</a></p>'
              + '   <p class="pic">'
              + '        <a href="javascript:void(0);" onclick="userPark.goUserPark(\'' + topRank[i].uid +  '\', 1, 1);"><img height="50" width="50" alt="" src="' + topRank[i].thumbnailUrl + '"/></a>'
              + '   </p>'
              + '   <p class="plice">' + truncatemoney(topRank[i].ass) + '円</p>';
              
        //check user is online
        if ( topRank[i].online == 1 && topRank[i].uid != $F('txtUid') ) {
            html += '   <p class="icon">'
                  + '       <img height="16" width="16" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/icon/warning.gif"/>'
                  + '   </p>';
        }
        html += '</li>';
        
    }
    return html;
}

/**
 * show minifeed
 *
 * @return void
 */
function showMinifeed()
{
    $('newsMyList').addClassName("ui-tabs-selected");
    $('newsFriendList').removeClassName("ui-tabs-selected");
    showFeed($F('txtMinifeed').evalJSON());
}

/**
 * show newsfeed
 *
 * @return void
 */
function showNewsfeed()
{
    $('newsFriendList').addClassName("ui-tabs-selected");
    $('newsMyList').removeClassName("ui-tabs-selected");
    showFeed($F('txtNewsfeed').evalJSON());
    
}

/**
 * show feed info
 *
 * @param array - feed info
 * @return string
 */
function showFeed(array)
{    
    var html = '<ul>';
    
    for (i = 0; i< array.length; i++) {
    	temp1 = array[i].icon.split('/');
        html += '<li class="' + temp1[temp1.length-1].replace('.gif','') + '">' + array[i].title.replace(/\{\*\}/g, '\'') + '<span class="date">' + array[i].create_time.formatToDate() + '</span>'
              + '</li>';
    }
    html += '</ul>';
    
    $('newsBody').innerHTML = html;
}

/**
 * on mouse over
 * 
 */
function changeClass(id)
{
    var divId = 'listCar'+id;
    var para = document.getElementById(divId).className = "active";
}

/**
 * on mouse out
 *
 */
function out(id)
{
    var carId = Number($('carId').value);
    var cid = id;
    if (carId != cid) {
        var divId = 'listCar'+id;
        var para = document.getElementById(divId).className = "";
    }
}

/**
 * show handle
 *
 */
function showHandle()
{
   var carCount = $('carCount').value;
   var showHandle = 1;
   var showSlider = 1; 
    
   if (carCount <5 ) { sliderWidth = 0.1; showSlider = 0; }
   else if (carCount == 5) { sliderWidth = 164; }
   else if (carCount == 6) { sliderWidth = 328; }
   else if (carCount == 7) { sliderWidth = 492; } 
   else { sliderWidth = 656; }

   if (showSlider == 1) {
       new Control.Slider($('divHandle'), $('divSlider'), {
          range: $R(0, sliderWidth),
          sliderValue: 0,
          onSlide: function(value) {
            $('ulItems').setStyle({left: 0 - value + 'px'});
          },
          onChange: function(value) { 
            $('ulItems').setStyle({left: 0 - value + 'px'});
          }
        });
   }
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
            if (rankCount >8) {
                liId = rankCount-1;
            }
            else {
                liId = 7;
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
                else if (leftFirstId > Number($('txtAllCount').value) && leftFirstId==10 ) {
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
    var url = UrlConfig.BaseUrl + '/ajax/parking/getmorerank';

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
        var url = UrlConfig.BaseUrl + '/ajax/parking/getlastrank';
    
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

function startGame()
{
    $('fullOverlay').hide();    
    //removeDialog();
}

function startApps()
{
    //removeDialog();
    $('fullOverlay').hide();    
    userPark.firstLoginSendCar();
}

function returnGame(){
    window.alert("returnGame");
}
