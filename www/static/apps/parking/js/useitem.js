var _PerExec;
var Item = {
    closeTime : 3
};

Item.Base = Class.create({
    show : function() 
    {
        var html = '';
        
        html += '<div class="head">'
              + '   <h2>' + this.cardName + '</h2>'
              + '   <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png" width="18" height="18"></a></p>'
              + '</div>'
              + '<div id="useitem" class="body">'
              + '   <div class="innerBody">'
              + '       <p class="alert">' + this.cardName + 'を使用します。よろしいですか？</p>'
              + '   </div>'
              + '   <ul class="btnList">'
              + '       <li class="submit"><a href="javascript:void(0);" id="linkSubmit">使用する</a></li>'
              + '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();">使用しない</a></li>'
              + '   </ul>'
              + '</div>';
        
        this.dialog = new baseDialog();
        this.dialog.insertHTML(html);

        Event.observe('linkSubmit', 'click', this.decision.bindAsEventListener(this));
    },
    
    finish : function (message,cid) 
    {
        freshPage(cid);
        var html = '';
                
        html += '<div class="head">'
              + '   <h2>' + this.cardName + '</h2>'
              + '</div>'
              + '<div class="body">'
              + '   <div class="innerBody">'
              + '       <p class="alert">' + message + '</p>'
              + '   </div>'
              + '</div>';

        if (this.dialog == null) {
            this.dialog = new baseDialog();
        }
        
        this.dialog.insertHTML(html);
        
        _PerExec = new PeriodicalExecuter(function(pe) {            
            removeDialog();
            pe.stop();            
        }, Item.closeTime);        
    }
});


Item.Bomb = Class.create(Item.Base, {
    initialize : function() 
    {
        this.cardName = '廃車カード';
        this.show();
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/useitembomb';
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                var message = '';
                if (response.responseText == "-2") {
                    message = 'このカードを使える区画がありません。';
                }
                else if (response.responseText == "1") {
                    message = '廃車カードを駐車場に設置しました。';
                }
                else {
                    message = 'システムエラー。';
                }
                
                var cid = 5;
                self.finish(message,cid);
                
                new PeriodicalExecuter(function(pe) {
		            if (response.responseText == "1" && typeof(userPark)!="undefined") {
	                	userPark.goUserPark($F('txtUid'),1);
	                }
		            pe.stop();
	        	}, Item.closeTime);
            }
        });
    }
});


Item.Yanki = Class.create(Item.Base, {
    initialize : function() 
    {
        this.cardName = 'ヤンキーカード';
        this.select();
    },
    
    select : function()
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/getfriend';
        
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess : function(response){
                var friend = response.responseText.evalJSON();
                
                if (friend.length == 0) {
                    self.finish('ヤンキーカードは、友達以外には使用できません。');
                    return;
                }
                
                var html = '';
                
                html += '<div class="head">'
                      + '   <h2>' + self.cardName + '</h2>'
                      + '   <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png" width="18" height="18"></a></p>'
                      + '</div>'
                      + '<div class="body">'
                      + '   <div class="innerBody" id="divBody">'
                      + '       <p class="alert"> '
                      + '           <select id="ddlUser" class="validate-selection validation-failed" title="マイミクシィを選択してください">'
                      + '               <option value="0">マイミクシィを選択</option>';
                      
                for (i = 0; i < friend.length; i++){
                    html += '<option value="' + friend[i].uid + '">' + friend[i].displayName + '</option>';
                }
                
                html += '           </select>'
                      + '       </p>'
                      + '   </div>'
                      + '   <ul class="btnList">'
                      + '       <li class="submit"><a href="javascript:void(0);" id="linkSubmit">決定</a></li>'
                      + '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();">キャンセル</a></li>'
                      + '   </ul>'
                      + '</div>';
                
                self.dialog = new baseDialog();
                self.dialog.insertHTML(html);
                
                self.validFriend = new Validation('divBody', {immediate:true, useTitles:true});
                
                Event.observe('linkSubmit', 'click', self.decision.bindAsEventListener(self));
            }
        });
    },
    
    decision : function() 
    {  
        if (!this.validFriend.validate()) {
            
            return false;
        }
        
        var url = UrlConfig.BaseUrl + '/ajax/parking/useitemyanki';
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            parameters: {
                fid : $F('ddlUser')
            },
            onSuccess: function(response){
                var message = '';
                if (response.responseText == "1") {
                    message = 'ヤンキーカードを使用しました。この区画は72時間駐車できなくなりました。';
                }
                else if(response.responseText == "-2") {
                    message = 'このカードを使える区画がありません。';
                }
                else {
                    message = 'システムエラー。';
                }
                
                var fid = $F('ddlUser');
                var cid = 11;
                self.finish(message,cid);
                
                new PeriodicalExecuter(function(pe) {            
		            if (response.responseText == "1" && typeof(userPark)!="undefined") {
	                	userPark.goUserPark(fid,1);
	                }
		            pe.stop();            
	        	}, Item.closeTime);                
            }
        });
    }
});


Item.Free = Class.create(Item.Base, {
    initialize : function() 
    {
        this.cardName = '有料駐車場カード';
        this.show();
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/useitemfree';
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                var message = '';
                if (response.responseText == "1") {
                    message = '有料駐車場カードを使用しました。無料区画が有料になりました。';
                }
                else {
                    message = 'システムエラー。';
                }
                
                var cid = 1;
                self.finish(message, cid);
                
                if (typeof(userPark)!="undefined") {
                	userPark.goUserPark($F('txtUid'),1);
                }
            }
        });
    }
});


Item.Bribery = Class.create(Item.Base, {
    initialize : function() 
    {
        this.cardName = 'わいろカード';
        this.show();
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/usebribery';
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                var message = '';
                
                if (response.responseText == "1") {
                    message = 'わいろカードを使用しました。使用後72時間、警察からの取り締まりが免除されます。';
                }
                else if (response.responseText == "-2") {
                    message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                }
                else {
                    message = 'システムエラー。'; 
                }
                
                var cid = 3;
                self.finish(message, cid);
            }
        });
    }
});


Item.Check = Class.create(Item.Base, {
    initialize : function() 
    {
        
        this.cardName = '検問カード';
        this.show();
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/usecheck';    
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                var message = '';
                
                if (response.responseText == "1") {
                    message = '検問カードを使用しました';
                }
                else if (response.responseText == "-2") {
                    message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                }
                else {
                    message = 'システムエラー。'; 
                }
                
                var cid = 6;
                self.finish(message, cid);
            }
        });
    }
});


Item.Insurance = Class.create(Item.Base, {
    initialize : function() 
    {
        this.cardName = '自動車保険カード';
        this.show();
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/useinsurance';    
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                var message = '';
                
                if (response.responseText == "1") {
                    message = '自動車保険カードを使用しました。あなたの所有車を損害から一度だけ守ってくれます。';
                }
                else if (response.responseText == "2") {
                    message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                    self.finish(message);
                }
                else {
                    message = 'システムエラー。'; 
                }
                
                var cid = 7;
                self.finish(message, cid);
            }
        });
    }
});


Item.Evasion = Class.create(Item.Base, {
    initialize : function() 
    {
        this.cardName = 'トラップ回避カード';
        this.show();
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/useevasion';    
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                var message = '';
                
                if (response.responseText == "1") {
                    message = 'トラップ回避カードを使用しました。';
                }
                else if (response.responseText == "-2") {
                    message = 'このカードは、現在「使用中」なので、重複して使用する事はできません。';
                }
                else {
                    message = 'システムエラー。'; 
                }
                var cid = 8;
                self.finish(message,cid);
               
            }
        });
    }
});


Item.Guard = Class.create(Item.Base, {
    initialize : function() 
    {
        this.cardName = '警備員カード';
        this.show();
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/useguard';
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                var message = '';
                
                if (response.responseText == "1") {
                    message = '警備員カードを使用しました。ヤンキーを駐車場から見事に撃退しました。';
                }
                else if ( response.responseText == "-2" ){
                    message = 'ヤンキーがいないので、警備員カードを使えません。';
                }
                else {
                    message = 'システムエラー。'; 
                }
                
                var cid = 9;
                self.finish(message, cid);
                //userPark and goHome in index.js
                if (typeof(userPark)!="undefined") {
                    userPark.goHome();
                }
            }
        });
    }
});

Item.Repair = Class.create(Item.Base, {
    initialize : function() 
    {
        
        this.cardName = '整備カード';
        this.selectCar();
    },
    
    selectCar : function()
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/getbreakcar';
        
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            onSuccess: function(response){
                
                var cars = response.responseText.evalJSON();
                
                if (cars.length == 0) {
                    self.finish('廃車状態の車がないので、使用できません。');
                    return;
                }
                
                var html = '';
                
                html += '<div class="head">'
                      + '   <h2>' + self.cardName + '</h2>'
                      + '   <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png" width="18" height="18"></a></p>'
                      + '</div>'
                      + '<div class="body" id="userCar">'
                      + '   <h3>車を選択してください</h3>'
                      + '   <div class="innerBody"';
                
             //   if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
                	if (cars.length == 1) {
                		html += ' style="height:140px;">';
                	}
                	else if (cars.length == 2) {
                		html += ' style="height:280px;">';
                	}
                	else
                	{
                		html += 'style="height:400px;overflow-y:auto;">';
                	}
            //    }
                
                html += '       <ul id="ulCarList" class="userCarList">';
                
                for (i = 0; i < cars.length; i++){
                    html += '<li selector="break">'
                          + '   <label id="label'+i+'" class="">'
                          + '       <input type="radio" name="carList" id="li.label'+i+'" car_id="' + cars[i].car_id + '" car_color="' + cars[i].car_color + ' " car_sequence="' + i + '"/>'
                          + '       <img alt="" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/' + cars[i].cav_name + '/big/' + cars[i].car_color  + '.png"/>'
                          + '   </label>'
                          + '</li>';
                }
                
                html += '       </ul>'
                      + '   </div>'
                      + '   <ul class="btnList">'
                      + '       <li class="submit"><a href="javascript:void(0);" id="linkSubmit">決定</a></li>'
                      + '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();">キャンセル</a></li>'
                      + '   </ul>'
                      + '</div>';
                
                self.dialog = new baseDialog();
                self.dialog.insertHTML(html);
                
                Event.observe('linkSubmit', 'click', self.decision.bindAsEventListener(self));
                
                $('ulCarList').down(2).checked = true;
                $('label0').addClassName('active');
                self.car_id = cars[0].car_id;
                self.car_color = cars[0].car_color;
                
                var liTemp;
                
                var lis = $('ulCarList').getElementsBySelector('[selector="break"]');
                lis.each(function(node){node.observe("click",function(){
                            liTemp = node.down(1);  
                            self.radioClick(liTemp.readAttribute('car_id'),liTemp.readAttribute('car_color'),cars.length,liTemp.readAttribute('car_sequence'));
                        })});
            }
        });
    },
    
    radioClick : function(car_id,car_color,car_length, car_sequence)
    {
        
        for(i=0; i < car_length; i++){
    
            $('label'+i).removeClassName('active');
            
            if(i == car_sequence){
                $('li.label'+i).checked = true;
                $('label'+car_sequence).addClassName('active');
            }
        }
        
        this.car_id = car_id;
        this.car_color = car_color;
    },
    
    decision : function() 
    {
        var url = UrlConfig.BaseUrl + '/ajax/parking/useitemrepair';
        var self = this;
        
        var myAjax = new Ajax.Request(url, {
            parameters: {
                car_id : this.car_id,
                car_color : this.car_color
            },
            onSuccess: function(response){
                var message = '';
                if (response.responseText == "1") {
                    message = '廃車を整備して乗れるようにしました。';
                    
                    if (typeof(userPark)!="undefined") {
	                    showUserAsset();
		                doFilter(1, 1);
		                showMyCar();
		                showHandle();
		                getCarInfo(0, 0);
                    }
                }
                else {
                    message = 'システムエラー。';
                }
                
                var cid = 10;
                self.finish(message,cid);
                
            }
        });
    }
});

function  freshPage(id){
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/freshpage';
    var html = '';
    var myAjax = new Ajax.Request(url, {
            parameters: {
                cid : id
            },
            onSuccess: function(response){
               
                var usedCardInfo = response.responseText.evalJSON();
                
                html += '<p class="img"><img src="'+UrlConfig.StaticUrl+'/apps/parking/img/items/'+id+'_s.gif" width="63" height="40" alt="" /></p>'
                     +  '<p class="name">'+usedCardInfo.name+'</p> '
                     +  '<p class="count">'+usedCardInfo.count+'</p> ';
                
                if( id == 1 ){
                    if( usedCardInfo.count == 0){
                        if( usedCardInfo.havaFreePark == 0){
                            html += ' <p class="status used"><a></a></p> ';
                        }
                    }
                    if( usedCardInfo.count > 0 ){
                         if( usedCardInfo.havaFreePark == 0){
                            html += ' <p class="status used"><a></a></p> ';
                         }
                         else{
                            html += ' <p class="status use"><a href="javascript:void(0);" onclick="new Item.Free();"></a></p> ';
                         }
                    }
                }
                
                if( id == 2 ){
                    if( usedCardInfo.count > 0) {
                        html += ' <p class="status able"><a></a></p> ';
                    }
                }
                
                if( id == 3 ){
                    if( usedCardInfo.count > 0){
                        if( usedCardInfo.last_bribery_time <= 3 ){
                            html += ' <p class="status used"><a></a></p> ';
                        }
                        if( usedCardInfo.last_bribery_time > 3 ){
                            html += ' <p class="status use"><a href="javascript:void(0);" onclick="new Item.Bribery();"></a></p> ';
                        }
                    }
                    if( usedCardInfo.count == 0){
                        if( usedCardInfo.last_bribery_time <= 3 ){
                            
                            html += ' <p class="status used"><a></a></p> ';
                        }
                    }
                    
                }
                
                if( id == 4 ){
                    if( usedCardInfo.count > 0) {
                        html += ' <p class="status able"><a></a></p> ';
                    }
                }
                
                if( id == 5 ){
                    if( usedCardInfo.count > 0 ){
                        html += '  <p class="status use"><a href="javascript:void(0);" onclick="new Item.Bomb();"></a></p>';
                    }
                }
                
                if( id == 6 ){
                    if( usedCardInfo.count > 0){
                        if( usedCardInfo.last_check_time <= 1 ){
                            html += ' <p class="status used"><a></a></p> ';
                        }
                        if( usedCardInfo.last_check_time > 1 ){
                            html += ' <p class="status use"><a href="javascript:void(0);" onclick="new Item.Check();"></a></p> ';
                        }
                    }
                    if( usedCardInfo.count == 0){
                        if( usedCardInfo.last_check_time <= 1 ){
                            html += ' <p class="status used"><a></a></p> ';
                        }
                    }
                    
                }
                
                if( id == 7 ){
                    if( usedCardInfo.count > 0 ){
                        if ( usedCardInfo.insurance_card == 0 ){
                            html += '  <p class="status use"><a href="javascript:void(0);" onclick="new Item.Insurance();"></a></p>';
                        }
                        if ( usedCardInfo.insurance_card == 1 ){
                            html += '  <p class="status used"><a></a></p>';
                        }
                    }
                    if( usedCardInfo.count == 0 ){
                        if ( usedCardInfo.insurance_card == 1 ){
                            html += '  <p class="status used"><a></a></p>';
                        }
                    }
                }
                
                if( id == 8 ){
                    
                    if( usedCardInfo.count>0){
                        if( usedCardInfo.last_evasion_time <= 2 ){
                            html += ' <p class="status used"><a></a></p> ';
                        }
                        if( usedCardInfo.last_evasion_time > 2 ){
                            html += ' <p class="status use"><a href="javascript:void(0);" onclick="new Item.Evasion();"></a></p> ';
                        }
                    }
                    if( usedCardInfo.count==0){
                        if( usedCardInfo.last_evasion_time <= 2 ){
                            html += ' <p class="status used"><a></a></p> ';
                        }
                    }
                    
                }
                
                if( id == 9 ){
                    if( usedCardInfo.count>0 ){
                        html += '  <p class="status use"><a href="javascript:void(0);" onclick="new Item.Guard();"></a></p>';
                    }
                }
                
                if( id == 10 ){
                    if( usedCardInfo.count>0 ){
                        html += '  <p class="status use"><a href="javascript:void(0);" onclick="new Item.Repair();"></a></p>';
                    }
                }
                
                if( id == 11 ){
                   
                    if( usedCardInfo.count > 0){
                        if(usedCardInfo.haveFriends != '' && usedCardInfo.haveFriends != 'false'){
                            html += '  <p class="status use"><a href="javascript:void(0);" onclick="new Item.Yanki();"></a></p>';
                        }
                    }
                   
                    
                }
                
                $('li.'+id).update(html);
            }
        });
     
}
 