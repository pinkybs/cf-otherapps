/**
 * parking(/parking/index.js)
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/19    Liz
 */
var userPark;

var park = Class.create();

park.prototype = {
    /**
     * initialize
     */
    initialize : function()
    {
    	this.ie6 = Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7; 
        this.canPark = false;
        this.row = -1;
        this.uid = $F('txtUid');
        this.friend = $F('txtAllFriend').evalJSON();
        this.moveSpeed = 1;
        this.canParkMove = 1;
        this.park = $('park');
        this.ajaxLoad = true;
        this.loadStartData();
        this.loadUserPark();
        this.firstLogin();
        this.todayFirstLogin();
        
        parkingMove = new parkMove(this.parkCurrent.locaCount);
        Event.observe('nextFriend', 'click', this.goNext.bindAsEventListener(this));
        Event.observe('prevFriend', 'click', this.goBack.bindAsEventListener(this));
    },
    firstLogin : function()
    {
        if ($F('txtFirstLogin') == '') {
            return;
        }
        

        var dialog = new baseDialog();
        var html = '';
        
        html += '   <div class="head">'
              + '       <h2>ようこそ、駐車戦争へ</h2>'
              //+ '    <p class="btnClose">'
              //+ '       <a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a>'
              //+ '    </p>'
              + '   </div><!--/.head-->'
              + '   <div id="firstLogin" class="body">'
              + '      <div id="divFlash" style="width: 736px; height: 425px"></div>'
              + '   </div><!--/.body-->';
        
        dialog.insertHTML(html, 2);
        $('overWindow').setStyle({width: '750px'});

        swfobject.embedSWF(UrlConfig.StaticUrl + '/apps/parking/swf/help.swf?prm=1', "divFlash", "736", "425", "7.0.0");
    },
    
    firstLoginSendCar : function()
    {
        if ($F('txtFirstLogin') == '') {
            return;
        }
        
        var car = $F('txtFirstLogin').evalJSON();

        var dialog = new baseDialog();
        var html = '';
        
        html += '   <div class="head">'
              + '       <h2>ウェルカムギフト</h2>'
              //+ '       <p class="btnClose">'
              //+ '           <a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a>'
              //+ '       </p>'
              + '   </div><!--/.head-->'
              + '   <div id="firstLogin" class="body">'
              + '       <h3>' + car.name + 'と40万円をプレゼント！ 高級外車ペラリーニ購入を目指し、がんばってください。</h3>'
              + '       <div align="center">'
              + '           <img class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + car.cav_name + '/big/' + car.color + '.png" alt=""/>'
              + '       </div>'
              + '   </div><!--/.body-->';
        
        dialog.insertHTML(html);
        
        _PerExec = new PeriodicalExecuter(function(pe) {
            removeDialog();
            pe.stop();
        }, 3);
    },
    
    todayFirstLogin : function()
    {
        if ($F('txtTodayFirstLogin') == '') {
            return;
        }
        
        var card = $F('txtTodayFirstLogin').evalJSON();
        
        var dialog = new baseDialog();
        var html = '';
        
        html += '   <div class="head">'
              + '       <h2>ビジターギフト</h2>'
             // + '    <p class="btnClose">'
             // + '       <a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a>'
             // + '    </p>'
              + '   </div><!--/.head-->'
              + '   <div id="firstLogin" class="body">'
              + '      <div id="divFlash" style="width:480px;height:245px;"></div>'
              + '   </div><!--/.body-->';
        
        dialog.insertHTML(html, 2);
        
        freshPage(card.cid);
        swfobject.embedSWF(UrlConfig.StaticUrl + '/apps/parking/swf/card.swf?prm=' + card.cid, "divFlash", "480", "245", "7.0.0");
    },
    
    loadStartData : function()
    {        
        this.user = $F('txtUser').evalJSON();
        
        var array = $F('txtPark').evalJSON();
        
        this.parkCurrent = array.current.user;
        
        this.parkCurrentCar = array.current.car;
        
        this.checkCanPark(this.parkCurrent.uid, 1);
    },
    
    loadUserPark : function(parkUid)
    {
        if (parkUid == null) {
            parkUid = this.uid;
        }
        
        this.showParking();
    },
    
    /**
     * show calendar
     */
    showParking : function()
    {
        this.park.update(this.getParkHtml());
    },
    
    getParkHtml : function(display)
    {        
        var isSelf = this.parkCurrent.uid == this.uid;
        
        var html = "";
        
        if ( !this.canPark && !isSelf ) {
            html += '<div id="parkingAlert"><span>' + this.parkCurrent.displayName + 'さんの友人・知人であれば、<a target="_top" href="http://platform001.mixi.jp/add_friend.pl?id=' + this.parkCurrent.profileUrl.substring(45) + '">マイミクシィに追加</a>して一緒に遊びましょう。</span></div>';
        }
        
        if (this.parkCurrent.uid < 0) {
        	html += '<div id="parkingAlert"><span>練習用パーキングです。マイミクシィが参加するまで、遠慮なく駐車して稼ぎましょう！</span></div>';
        }
        
        html += '<div id="dynamicArea" style="background-image: url(' + UrlConfig.StaticUrl + '/apps/parking/img/background/' + this.parkCurrent.bg_cav_name + '.gif); left: 0px;">'
              + '   <div class="parking">';

        //show current park
        html += this.getParkHtmlTD(this.parkCurrent, this.parkCurrentCar, 1);
        
        html += '   </div>'
              + '</div>';

        return html;
    },
    
    getParkHtmlTD : function(array, car, isCurrent)
    {    	
        var html = '';
        
        html += '<div class="inner">'
              + '   <ul id="trafficSigns">';
        
        for (i =0; i < array.locaCount; i++) {
            
            html += '<li id="trafficSign' + i + '">';
            
            if (array.free_park == (i+1) ) { 
                html += '<img height="75" width="72" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/parking/free.gif"/>';
            }
            else {
                html += '<img height="75" width="72" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/parking/parking.gif"/>';
            }
            
            html += '</li>';
        }
       
        html += '</ul>'
              + '<ul id="parkingCars">';
             
        for (i =0; i < array.locaCount; i++) {
            
            var parkCar = this.getParkCar(car, (i+1), array);
            
            this.isUserYanki = 2;
            this.checkYankiTime(array['location' + (i+1)]);
            this.isShowBomb = 2;
            this.checkShowBomb(array['bomb' + (i+1)], this.user.lastEvasionTime);

            html += '<li id="parkingCar' + i + '">';
            if (parkCar == '') {
                var isfree = (array.free_park == (i+1)) ? 1 : 0;
                
                //has no yanki card
                if ( this.isUserYanki == 2 ) {
                
                    var isSelf = array.uid == this.uid;
                    //friend
                    if (this.canPark) {
                        //friend park has bomb
                        if ( this.isShowBomb == 1 ) {
                            html += '<div>'
                                  + '<p class="carPic">';
                                  
                            if (this.ie6) {                	
			                	html += '<div height="120" width="240"'
			                		  + ' style="margin-top:-15px;background-image:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader'
									  + '(src=' + UrlConfig.StaticUrl + '/apps/parking/img/trap.png);" />';
			                }
			                else {
			                    html += '<img height="120" width="240" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/trap.png"/>';
			                }
			                
                            html += '</p>'
                                  + '</div>';
                        }
                        //no bomb
                        else {
                            html += '   <p class="btnCommands parking">'
                                  + '       <a href="javascript:void(0);" id="park_' + array.type + '_' + array.uid + '_' + (i+1) + '" onclick="userPark.parking(' + array.type + ',\'' + array.uid + '\',' + (i+1) + ',' + isfree + ');">駐車する</a>'
                                  + '   </p>';
                        }
                    }//my park has bomb
                    else if ( isSelf ) {
                        if ( array['bomb' + (i+1)] == "1" ) {
                            html += '<div>'
                                  + '<p class="carPic">';
                                  
                            if (this.ie6) {                	
			                	html += '<div height="120" width="240"'
			                		  + ' style="margin-top:-15px;background-image:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader'
									  + '(src=' + UrlConfig.StaticUrl + '/apps/parking/img/trap.png);" />';
			                }
			                else {
			                    html += '<img height="120" width="240" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/trap.png"/>';
			                }
			                
                            html += '</p>'
                                  + '</div>';
                        }
                    }
                }//yanki card
                else {
                    html += '<div>'
                          + '<p class="carPic">';
                                  
                    if (this.ie6) {                	
	                	html += '<div height="120" width="240"'
	                		  + ' style="margin-top:-15px;background-image:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader'
							  + '(src=' + UrlConfig.StaticUrl + '/apps/parking/img/yankee.png);" />';
	                }
	                else {
	                    html += '<img height="120" width="240" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/yankee.png"/>';
	                }
	                
                    html += '</p>'
                          + '</div>';
                }

                html += '<div style="display:none;margin-top:100px;*top:100px;" id="div_' + array.type + '_' + array.uid + '_' + (i+1) + '">' + array.uid + '</div>';
            }
            else {
                html += parkCar;
            }            
            html += '</li>';
        }
        html += '   </ul>'
              + '</div>';
        
        return html;
    },
    
    getParkCar : function(car,loca,user) 
    {
        var html = '';
        
        for (j = 0; j < car.length; j++) {
            if (car[j].location == loca) {
                var money = Math.floor(car[j].park_time/900);
                var hour = money > 32 ? 32 : money;
                money = round(hour * Number(user.fee) * Number(car[j].times));
                
                var rand = Math.random();
                
                html += '<div id="div' + rand + '">';
                
                if (user.free_park != loca) {
                    if (user.uid == this.uid) {
                        //if is friend ,you can stick
                        if (this.checkIsFriend(car[j].uid) ) {
                            html += '<p class="btnCommands command"><a href="javascript:void(0);" onclick="userPark.stick(' + loca + ',\'div' + rand + '\',\'' + car[j].displayName.replace(/\'/g,"@") + '\',' + money + ');">取り締まる</a></p>';
                        }
                        else {
                            html += '<p class="btnCommands command disable"><a style="_background-position:0px -27px;">取り締まる</a></p>';
                        }
                    }
                    else {
                        if (car[j].uid != this.uid) {
                            if (this.checkIsFriend(car[j].uid) && user.free_park != loca) {
                                if ( $F('txtReportCount') > 0 ) {
                                    html += '<p class="btnCommands report"><a href="javascript:void(0);" onclick="userPark.reportAnonymous(\'' + user.uid + '\',\'' + car[j].uid + '\',' + car[j].car_id + ',&quot;' + car[j].car_color + '&quot;,' + loca + ');">'
                                          + '通報する</a></p>';
                                }
                                else {
                                    html += '<p class="btnCommands report"><a href="javascript:void(0);" onclick="userPark.report(\'' + user.uid + '\',\'' + car[j].uid + '\',' + car[j].car_id + ',&quot;' + car[j].car_color + '&quot;,' + loca + ');">'
                                          + '通報する</a></p>';
                                }
                            }
                            else {//button button
                                html += '<p class="btnCommands report disable"><a style="_background-position:-174px -27px;">通報する</a></p>';
                            }
                        }
                        else {
                            html += '<p class="btnCommands parking disable"><a style="_background-position:-348px -27px;">駐車する</a></p>';
                        }
                    }
                }
                else {
                    if ( user.uid == this.uid ) {
                        html += '<p class="btnCommands command disable"><a style="_background-position:0px -27px;">取り締まる</a></p>';
                    }
                    else if ( car[j].uid == this.uid ) {
                        html += '<p class="btnCommands parking disable"><a style="_background-position:-348px -27px;">駐車する</a></p>';
                    }
                    else {
                        html += '<p class="btnCommands report disable"><a style="_background-position:-174px -27px;">通報する</a></p>';
                    }
                }
                
                html += '<p class="carPic">';
                
                if (this.ie6) {
                    var divOnclickAd;
                    if ( car[j].car_type == 2 ) {
                        divOnclickAd = 'onclick="mixiNavigateTo(\'' + car[j].ad_url + '\');"';
                    }
                    
                	html += '<div ' + divOnclickAd + ' height="120" width="240" style="cursor:hand;"'
                		  + ' style="margin-top:-15px;background-image:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader'
						  + '(src=' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + car[j].cav_name + '/big/' + car[j].car_color + '.png?' + version.js + ');" />';
                }
                else {
                    if ( car[j].car_type == 2 ) {
                        var adUrl = car[j].ad_url;
                        html += '<a href="javascript:void(0);" onclick="mixiNavigateTo(\'' + adUrl + '\');"><img height="120" width="240" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + car[j].cav_name + '/big/' + car[j].car_color + '.png?' + version.js + '"/></a>';
                    }
                    else {
                        html += '<img height="120" width="240" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + car[j].cav_name + '/big/' + car[j].car_color + '.png"/>';
                    }
                }
                
                html += '</p>'
                      + '<div class="parkingStatus">'
                      + '   <p class="userPic">'
                      + '       <img height="27" width="27" alt="" src="' + car[j].thumbnailUrl + '"/>'
                      + '   </p>';
                
                if (user.free_park == loca) {
                    html += '<p class="parkingMeter">'
                          + '   <img height="4" width="145" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/carlist/meter_0.gif"/>'
                          + '</p>'
                          + '<p class="carInfo">無料駐車場</p>';
                }
                else {
                    var temp = hour/4 + 1;                    
                    html += '   <p class="parkingMeter">'
                          + '       <img height="4" width="145" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/carlist/meter_' + Math.floor(temp) + '.gif"/>'
                          + '   </p>'
                          + '   <p class="carInfo">¥' + money.formatToAmount() + '</p>';
                }
                
                //html += '   <p class="userName">' + loca + '番目</p>'
                html += '   <p class="userName">' + car[j].displayName + '</p>'
                      + '</div></div>';
                
                return html;
            }
        }
        
        return html;
    },
    
    checkCanPark : function(uid,type) 
    {
        this.canPark = false;
        
        for (l = 0; l < this.friend.length; l++ ) {
            if (this.friend[l].uid == uid && this.friend[l].type == type) {
               this.canPark = true;
               break; 
            }
            else if (type ==2 ) {
                this.canPark = true;
                break;
            }
        }
    },
    
    checkYankiTime : function(time) 
    {
        var now = Math.round(new Date().getTime()/1000.0);
        if ((now - time) <= 259200 ) {
            this.isUserYanki = 1;
        }
    },
    
    checkShowBomb : function(bombCount, lastEvasionTime)
    {
        if (bombCount==1) {
            var now = Math.round(new Date().getTime()/1000.0);
            if ((now - lastEvasionTime) <= 48*3600 ) {
                this.isShowBomb = 1;
            }
        }    
    },
    
    checkIsFriend : function(uid) 
    {        
        for (k = 0; k < this.friend.length; k++ ) {
            if (this.friend[k].uid == uid && this.friend[k].type == 1) {
               return true;
            }
        }
        return false;
    },
    
    /**
     * go to back user park
     */
    goBack : function()
    {
    	urchinTracker('/nav');
        if (this.row <= 0) {
            this.row = this.friend.length-1;
        }
        else {
            this.row --;
        }        
        
        this.getFriendPark(this.friend[this.row].uid,this.friend[this.row].type);
    },

    /**
     * go to next user park
     */
    goNext : function()
    {
    	urchinTracker('/nav');
        if (this.row == this.friend.length - 1) {
            this.row = 0;
        }
        else {
            this.row ++;
        }
        
        this.getFriendPark(this.friend[this.row].uid,this.friend[this.row].type);
    },

    goHome : function()
    {
        this.row = -1;
        this.getFriendPark(this.uid, 1);
    },
    
    goUserPark : function(uid, type, topLocation)
    {
    	//refresh hdr ad
    	window.frames[0].location.reload();
    	
        if (uid == null) {
            uid = this.uid;
            type = 1;
        }
        
        if ( uid < 0 ) {
            type = 2;
        }
        this.goUserUid = uid;
        this.goUserType = type;
        removeDialog();
        
        /*
        if(document.documentElement.scrollTop > 175) {
            Effect.ScrollTo('mixi');            
        }*/
        if (topLocation != 1) {
            top.location.href = getCookie('app_top_url') + '#bodyArea';
        }
        
        this.row = -1;
        
        for (i = 0; i < this.friend.length; i++) {
            if (this.friend[i].uid == uid && this.friend[i].type == type) {
                this.row = i;
                break;
            }
        }
        
        this.getFriendPark(uid,type);
    },
    
    getFriendPark : function(uid, type) {
    	//refresh hdr ad
    	window.frames[0].location.reload();
    	
        this.ajaxLoad = false;
        var requestObject = new Object();
        requestObject.id = uid;
        requestObject.type = type;

        var jsonRequest = Object.toJSON(requestObject);

        //send the ajax request
        var rand=Math.random();
        var url = UrlConfig.BaseUrl+ '/ajax/parking/getuserpark';

        var myAjax = new Ajax.Request(url, {
            method: 'get', 
            parameters : 'request='+escape(jsonRequest)+'&r='+escape(rand),
            onTimeout: function() {
                $('park').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
            },
            // summary: callback function on oncreate.
            //      show loading image.
            onCreate : function() {
                //$('park').update('<div style="min-height:98px;"><div class="loading" style="display:inline;"><img src="' + UrlConfig.StaticUrl + '/apps/parking/img/loading.gif" alt="" /></div></div>');
                
            },
            // summary: callback function if success.
            // param: response  String
            onSuccess: function(response) {
                var array = response.responseText.evalJSON();
                
                userPark.parkCurrent = array.current.user;
                userPark.parkCurrentCar = array.current.car;
                
                //get data sussess
                userPark.ajaxLoad = true;
                
                userPark.checkCanPark(userPark.parkCurrent.uid, userPark.parkCurrent.type);
                
                userPark.showParking();
                userPark.updateParkingHead(array.current.user, userPark.parkCurrent.type);
                parkingMove.setParkCount(userPark.parkCurrent.locaCount);
            }
        });
    },
    
    updateParkingHead : function(user, type)
    {
    	 var html = '';
         if (type == 1) {  
             html = '<p class="pic"><img src="' + user.thumbnailUrl + '" width="25" height="25" alt="" /></p>'
                  + '<h1>' + user.displayName + 'さんのパーキング</h1>';

             html +='<a href="javascript:void(0);" onclick="userPark.showCarList(\'' + user.uid + '\');" id="btnCarList"><img src="' + UrlConfig.StaticUrl + '/apps/parking/img/parking/btn_carlist.gif" width="127" height="24" alt="" /></a>'
                  + '<a target="_top" href="' + user.profileUrl + '" id="btnProfile"><img src="' + UrlConfig.StaticUrl + '/apps/parking/img/parking/btn_profile.gif" width="127" height="24" /></a>';
         }
         else {
         	var pic = (user.uid == -1) ? 'taro.gif' : 'hanako.gif';
         	
            html = '<p class="pic"><img src="' + UrlConfig.StaticUrl + '/apps/parking/img/neighbor/' + pic + '" width="25" height="25" alt="" /></p>'
                 + '<h1>' + user.displayName + 'さんのパーキング</h1>';
         }
         $('parkingHead').innerHTML = html;
         
         var htmlFee = '15分 <span>¥' + user.fee.formatToAmount() + '</span>';
         $('groundFee').innerHTML = htmlFee;
    },

    showCarList : function(uid)
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/getusercarlist';
    
        var myAjax = new Ajax.Request(url, {
            parameters: {
                uid : uid
            },
            onSuccess: function(response){
                var responseObject = response.responseText.evalJSON();
                var car = responseObject.cars; 
                
                var dialog = new baseDialog();
                
                var html = '';

                html += '<div class="head">'
                      + '    <h2><img src="' + userPark.parkCurrent.thumbnailUrl + '" width="25" height="25" alt="" /><span>' + userPark.parkCurrent.displayName + 'さんの所有車</span></h2>'
                      + '    <p class="btnClose">'
                      + '       <a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a>'
                      + '    </p>'
                      + '</div>'
                      + '<div id="userCar" class="body" >'
                      + '   <div class="innerBody"'; 
                      
                  //if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
                        if (car.length == 1 || car.length == 0) {
                            html += ' style="height:140px;">';
                        }
                        else if (car.length == 2) {
                            html += ' style="height:280px;">';
                        }
                        else
                        {
                            html += 'style="height:400px;overflow-y:auto;">';
                        }
                    //}

                if (car.length == 0) {
                    html += '<div class="inner">現在、車を所有していません。</div>';
                }
                else {
                    var height = car.length > 3 ? 300 : car.length*100;
                    html += '<ul class="userCarList">';                         
                    
                    for (i = 0; i < car.length; i++) {
                        html += '<li style="_width:480px;">'
                              + '   <img alt="" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + car[i].cav_name + '/small/' + car[i].car_color + '.png?' + version.js + '"/>'
                              + '   <p>' + car[i].name + '<br/>' + car[i].status + '</p>'
                              + '</li>';
                    }
                    
                    html += '</ul></div>';
                }
                
                html += '</div>';
                
                dialog.insertHTML(html);
            }
          });
    },
    
    parking : function(type,uid,loca,isfree)
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/getusercarlist';
    
        var myAjax = new Ajax.Request(url, {
            parameters: {
                uid : this.uid
            },
            onSuccess: function(response){
                var responseObject = response.responseText.evalJSON();
                var car = responseObject.cars; 
                
                var dialog = new baseDialog();
                
                var html = '';
                
                html += '<div class="head">'
                      + '    <h2>駐車する</h2>'
                      + '    <p class="btnClose">'
                      + '       <a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a>'
                      + '    </p>'
                      + '</div>'
                      + '<div id="userCar" class="body" >'
                      + '   <div class="innerBody"';
                      
                      //if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
                        if (car.length == 1 || car.length == 0) {
                            html += ' style="height:140px;">';
                        }
                        else if (car.length == 2) {
                            html += ' style="height:280px;">';
                        }
                        else
                        {
                            html += 'style="height:400px;overflow-y:auto;">';
                        }
                    //}
                    
                html += '   <h3>車を選択してください</h3>';
                      
                if (car.length == 0) {
                    html += '<div class="inner">現在、車を所有していません。</div>';
                }
                else {
                    var height = car.length > 3 ? 300 : car.length*100;
                    html += '<ul class="userCarList">';                         
                    
                    var j = 1;
                    for (i = 0; i < car.length; i++) {
                        if ( car[i].carStatus == 1 ) {

                            if (j == 1) {
                                //the first one, checked = true
                                html += '<li>'
                                      + '   <label class="active">'
                                      + '   <input type="radio" onclick="userPark.setCheckClass(this.parentNode);" checked="true" name="rdoCars" id="rdoCars" value="' + car[i].car_id + '-' + car[i].car_color + '-' + car[i].cav_name + '">';
                                j = 2;
                            }
                            else {
                                html += '<li>'
                                      + '   <label>'
                                      + '   <input type="radio" onclick="userPark.setCheckClass(this.parentNode);" name="rdoCars" id="rdoCars" value="' + car[i].car_id + '-' + car[i].car_color + '-' + car[i].cav_name + '">';
                            }     
                                 
                            html += '       <img alt="" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + car[i].cav_name + '/small/' + car[i].car_color + '.png" />'
                                  + '       <span class="description">' + car[i].name + '<br/>' + car[i].status + '</span>'
								  + '	</label>'
                                  + '</li>';
                        }
                    }
                    
                    html += '</ul></div>';
                }
                
                html += '<ul class="btnList">'
                      + '   <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();">キャンセル</a></li>'
                      + '   <li class="submit"><a href="javascript:void(0);" onclick="userPark.parkingSubmit(' + type + ',\'' + uid + '\',' + loca + ',' + isfree + ');">決定</a></li>'
                      + '</ul>'
                      + '</div>';
                
                dialog.insertHTML(html);
            }
          });
    },
            
    setCheckClass : function(ele)
    {
        var count = ele.parentNode.parentNode.childNodes.length;
        for (var i = 0; i < count; i++)
        {
            if ( i < count ) {
                var labelNm = ele.parentNode.parentNode.childNodes[i].childNodes.length;
                var labelNumber = labelNm-1;
                ele.parentNode.parentNode.childNodes[i].childNodes[labelNumber].className = '';
            }
        }

        ele.className = 'active';
    },


    parkingSubmit : function(type,uid,loca,isfree) 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/parking';
        
        var rdo = $('userCar').getElementsBySelector('[name="rdoCars"]');
        var temp;
        for(r = 0; r < rdo.length; r++) {
            if (rdo[r].checked) {
                temp = rdo[r].value.split('-');
                break;
            }
        }
        
        if (temp == null) {
            return;
        }
        
        var myAjax = new Ajax.Request(url, {
            parameters: {
                uid : this.uid,
                park_uid : uid,
                car_id : temp[0],
                car_color : temp[1],
                loca : loca,
                type : type
            },
            onSuccess: function(response){
                removeDialog();
                var array = response.responseText.evalJSON();
                
                var isComplete;
                if ( array.status == 1 ) {
                    isComplete = 1;
                }//検問カード
                else if (array.status == -6) {
                    userPark.showParkingError(array.status, array);
                    
                    isComplete = 1;
                }//廃車カード
                else if (array.status == -5 || array.status == -7) {
                    userPark.showParkingError(array.status);
                    
                    doFilter(1, 1);
                    showMyCar();
                }
                else {
                    userPark.showParkingError(array.status);
                }
                
                if (isComplete == 1) {
                    var btn = $('park_' + type + '_' + uid + '_' + loca);
                    if (btn != null) {
                        btn.hide();
                    }
                    
                    var div = $('div_' + type + '_' + uid + '_' + loca);
                    
                    var html = '<p class="btnCommands parking disable"><a style="_background-position:-348px -27px;">駐車する</a></p>';

                    html += '<p class="carPic">';
                    
                    if (userPark.ie6) {
                        var divOnclickAd;
                        if ( array.car_type == 2 ) {
                            divOnclickAd = 'onclick="mixiNavigateTo(\'' + array.ad_url + '\');"';
                        }
	                	html += '<div ' + divOnclickAd + ' height="120" width="240"'
	                		  + ' style="margin-top:-15px;background-image:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader'
							  + '(src=' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + temp[2] + '/big/' + temp[1] + '.png?' + version.js + ');" />';
	                }
	                else {
                        if ( array.car_type == 2 ) {
	                       html += '<a href="javascript:void(0);" onclick="mixiNavigateTo(\'' + array.ad_url + '\');"><img height="120" width="240" alt="" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + temp[2] + '/big/' + temp[1] + '.png?' + version.js + '"/></a>';
                        }
                        else {
                           html += '<img height="120" width="240" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + temp[2] + '/big/' + temp[1] + '.png"/>'; 
                        }
                    }
	                
                    html += '</p>'
                          + '<div class="parkingStatus">'
                          + '   <p class="userPic">'
                          + '       <img height="27" width="27" alt="" src="' + userPark.user.thumbnailUrl + '"/>'
                          + '   </p>';
                    
                    if (isfree) {
                        html += '<p class="parkingMeter">'
                              + '   <img height="4" width="145" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/carlist/meter_0.gif"/>'
                              + '</p>'
                              + '<p class="carInfo">無料駐車場</p>';
                    }
                    else {
                        html += '   <p class="parkingMeter">'
                              + '       <img height="4" width="145" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/carlist/meter_1.gif"/>'
                              + '   </p>'
                              + '   <p class="carInfo">¥0</p>';
                    }
               
                    //html += '   <p class="userName">' + loca + '番目</p>';
                    html += '   <p class="userName">' + userPark.user.displayName + '</p>';
                    
                    div.innerHTML = html;
                    div.setStyle({display: ''});
                    new Effect.Move (div,{ x: 0, y: -100, mode: 'relative',duration: userPark.moveSpeed})
                    
                    showUserAsset();
                    doFilter(1, 1);
                    showMyCar();
                    showHandle();
                }
            }});
    },
        
    showParkingError : function(status, lastParkUser) 
    {
        var message = '';
        
        if (status == -1) {
            message = 'システムエラー。';
        }
        else if (status == -2) {
            message = '既に別の車が駐車しています。';
        }
        else if (status == -3) {
            message = '同じ駐車場には、連続で駐車できません。';
        }
        else if (status == -4) {
            message = '1時間未満なので、移動できません。';
        }
        else if (status == -5) {
            message = 'その区画には「廃車カード」のトラップが仕掛けられていました。　あなたの車は廃車になってしまいました。「整備カード」を使うまで動かせません。';
        }
        else if (status == -6) {
            message = '「検問カード」の効果により、あなたの所得は、' + lastParkUser.lastUserName + 'さんに奪われました。';
        }
        else if (status == -7) {
            message = '「検問カード」の効果により、あなたの所得は、' + lastParkUser.lastUserName + 'さんに奪われました。<br/>その区画には「廃車カード」のトラップが仕掛けられていました。　あなたの車は廃車になってしまいました。「整備カード」を使うまで動かせません。';
        }
        else if (status == -11) {
            message = 'この駐車場には廃車カードが設置してあるので、あなたの車を駐車する事ができません。'
        }
        else if (status == -12) {
            message = 'この駐車場には廃車カードが仕掛けられていましたが、トラップ回避カードの効力で、無事に切り抜けることができました。しかし、この場所には駐車できません。';
        }
        else if (status == -13) {
            message = '廃車カードが仕掛けられていました！！<br/>自動車保険カードのおかげで、この場は無事に切り抜けました。<br/>しかし、この場所には駐車できません。';
            freshPage(7);
        }
        else if (status == -14) {
            message = 'この駐車場には廃車カードが設置してあるので、あなたの車を駐車する事ができません。'
        }
        else {
            message = 'システムエラー。';
        }
        
        var dialog = new baseDialog();
                
        var html = '';
        
        html += '<div class="head">'
              + '    <h2>駐車する</h2>'
              + '</div>'
              + '<div id="parking" class="body">'
              + '   <div class="innerBody">'
              + '       <p class="alert">' + message + '</p>'
              + '    </div>'
              + '</div>';

        dialog.insertHTML(html);
        
        _PerExec = new PeriodicalExecuter(function(pe) {
            removeDialog();
            pe.stop();
        }, 3);
    },
    
    stick : function(l,id,nickname,money)
    {
    	nickname = nickname.replace(/@/g,"'")
    	
        var url = UrlConfig.BaseUrl + '/ajax/parking/stick';
        
        var myAjax = new Ajax.Request(url, {
            parameters: {
                uid : this.uid,
                loca : l
            },
            onSuccess: function(response){    
                var message = '';   
                var array = response.responseText.evalJSON();
                
                if (array.status == 1) {
                    showUserAsset();
                    doFilter(1, 1);
                    $(id).hide();
                    
                    message = nickname + 'の車を取り締まりました。罰金¥' + array.money + 'を獲得！';
                    postActivity(array.activity);
                }
                else if (array.status == -2){
                    message = '1時間未満なので、まだ取り締まることはできません。';
                }
                else {
                    message = 'システムエラー。';
                }
                
                var dialog = new baseDialog();
                
                var html = '';
                      
                html += '<div class="head">'
                      + '    <h2>取り締まる</h2>'
                      + '</div>'
                      + '<div id="parking" class="body">'
                      + '   <div class="innerBody">'
                      + '       <p class="alert">' + message + '</p>'
                      + '    </div>'
                      + '</div>';
                      
                dialog.insertHTML(html);
                
                _PerExec = new PeriodicalExecuter(function(pe) {
                    removeDialog();
                    pe.stop();
                }, 3);
            }});
    },
    
    report : function(park_uid, report_uid, car_id, car_color, l, isAnonymous, fromAnonymous)
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/report';
        
        var myAjax = new Ajax.Request(url, {
            parameters: {
                uid : this.uid,
                park_uid : park_uid,
                report_uid : report_uid,
                car_id : car_id,
                car_color : car_color,
                loca : l,
                isAnonymous : isAnonymous
            },
            onSuccess: function(response){
                var array = response.responseText.evalJSON();

                if ( array.reportCount ) {
                    $('txtReportCount').value = (Number(array.reportCount) - 1);
                }
                if (isAnonymous == 1) {
                    freshPage(2);
                }
                
                if ( Number(array.status) == "1" ) {
                    //show message
                    userPark.reportMessage(array.message);
                }
                else {
                    userPark.showParkingError(-1);
                }
            }});
    },
    
    reportMessage : function(message)
    {
        var dialog = new baseDialog();
                
        var html = '';
              
        html += '<div class="head">'
              + '    <h2>通報する</h2>'
              + '</div>'
              + '<div id="report" class="body">'
              + '   <div class="innerBody">'
              + '       <p class="alert">' + message + '</p>'
              + '    </div>'
              + '</div>';
              
        dialog.insertHTML(html);
        
        _PerExec = new PeriodicalExecuter(function(pe) {
            removeDialog();
            pe.stop();
        }, 3);
    },
    
    reportAnonymous : function(park_uid, report_uid, car_id, car_color, l)
    {
        var reportCount = $F('txtReportCount');
        var fromAnonymous = 1;
        if ( reportCount > 0 ) {
            var url = UrlConfig.BaseUrl + '/ajax/parking/checkreport';
        
            var myAjax = new Ajax.Request(url, {
                parameters: {
                    uid : this.uid,
                    park_uid : park_uid,
                    report_uid : report_uid,
                    car_id : car_id,
                    car_color : car_color,
                    loca : l
                },
                onSuccess: function(response){
                    var array = response.responseText.evalJSON();
                    
                    if ( Number(array.status) == "1" ) {
                        //show message
                        userPark.reportMessage(array.message);
                    }
                    else if ( Number(array.status) == "2" ) {
                    
                        var dialog = new baseDialog();
                        var html = '';
                       
                        html += '<div class="head">'
                              + '    <h2>通報する</h2>'
                              + '</div>'
                              + '<div id="report" class="body">'
                              + '   <div class="innerBody">'
                              + '       <p class="alert">ヒミツ通報カードを使いますか？<br/>カードを使うと、匿名で通報できます。</p>'
                              + '    </div>'
                              //button 使用しない
                              + '    <ul class="btnList_report">'
                              + '         <li class="submit"><a href="javascript:void(0);" onclick="userPark.report(\'' + park_uid + '\',\'' + report_uid + '\',' + car_id + ',\'' + car_color + '\',' + l + ',' + 1 + ',' + fromAnonymous + ');">使用する</a></li>'
                              + '         <li class="cancel"><a href="javascript:void(0);" onclick="userPark.report(\'' + park_uid + '\',\'' + report_uid + '\',' + car_id + ',\'' + car_color + '\',' + l + ',' + 0 + ',' + fromAnonymous + ');">使用しない</a></li>'
                              + '    </ul>'
                              + '</div>';
                        
                        dialog.insertHTML(html);
                        
                    }
                    else {
                        userPark.showParkingError(-1);
                    }
                }});
        }
        else {
            userPark.report(park_uid, report_uid, car_id, car_color, l, 0, fromAnonymous);
        }
    }
}
