var car_id;
var car_color;
var oldCarPrice;
var pageIndex = 1;
var pageSize = 8;

Event.observe(window, 'load', function() {	
	pageIndex = parseInt($F('pageIndex'));
    car_id = GetUrlQuery("car_id");
    car_color = GetUrlQuery("car_color");
   
    if( car_id != null && car_color != null ){
        oldCarPrice = getOldCarPrice( car_id );
        loadPage();
    }
});

function GetUrlQuery( Name ) { 
    var reg = new RegExp("(^|&)" + Name + "=([^&]*)(&|$)"); 
    var r = window.location.search.substr(1).match(reg); 
    if (r != null){ 
        return unescape(r[2]); 
    }
    return null; 
} 

function loadPage(){
    top.location.href = getCookie('app_top_url') +  '#bodyArea';
}

function nextPage(){
    var count = Number($F('count'));
    var totalPages = Math.floor(count/pageSize) + (count%pageSize==0 ? 0:1); 

    if(count <= pageSize){
        return false;
    }
    if(pageIndex >= totalPages){
        return false;
    }
    pageIndex = pageIndex+1;
    window.frames[0].location.reload();
    changePageAction(pageIndex);
}

function prePage(){
    var count = Number($F('count'));
    var totalPages = Math.floor(count/pageSize) + (count%pageSize==0 ? 0:1); 
    if(pageIndex <= 1){
        return false;
    }
    pageIndex = pageIndex-1;
    window.frames[0].location.reload();
    changePageAction(pageIndex);
}

function changePageAction(page)
{
	/*
	if (Prototype.Browser.IE) {
		if (page == 3) {
        	adjustHeight(940);
        }
        else{
        	adjustHeight(1560);
        }
        
    	new PeriodicalExecuter(function(pe) {
			pe.stop();
            location.href = "/parking/carshop?page_index=" + page;
        }, 1);
    	
    	return;
    }*/
                
    $('pageIndex').value = page;
    var url = UrlConfig.BaseUrl + '/ajax/parking/getcarshop';

    var myAjax = new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex')
        },
        // callback function if time out
        onTimeout: function() {
            $('divCarList').innerHTML('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        // summary: callback function if success
        onSuccess: function(response) {
           
            if (response.responseText != '' && response.responseText != 'false'){      
                var responseObject = response.responseText.evalJSON();
                
                var length = responseObject.info.length;
                
                if (length <= 4) {
                	adjustHeight(940);
                }
                else if (length == 5) {
                	adjustHeight(1095);
                }
                else if (length == 6) {
                	adjustHeight(1250);
                }
                else if (length == 7) {
                	adjustHeight(1405);
                }
                else {
                	adjustHeight(1560);
                }
                
                var html = showCarList(responseObject.info);
                
                var count = Number($F('count'));
            
                $('showingtop').update(count+"件中"+Number(pageSize*(pageIndex-1)+1)+"-"+Number(pageSize*(pageIndex-1)+length)+"件を表示");
                $('showinglow').update(count+"件中"+Number(pageSize*(pageIndex-1)+1)+"-"+Number(pageSize*(pageIndex-1)+length)+"件を表示");
                    
                $('divCarList').update(html);
             }
             
             loadPage();   
        }});
}
    
function showCarList(array)
{
     var asset = $F('userAsset');
     var maxPrice = $F('maxPrice');
    
     var html = '';
     for(i = 0; i < array.length; i++) {
          
          html += '<div id="car'+i+'" class="section">'
               +  '     <p class="carPic"><img alt="" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+array[i].cav_name+'/big/'+array[i].color+'.png'  + '" />' 
               +  '     </p>'
               +  '     <h3>'+array[i].name+'</h3>'
               +  '     <ul class="status">'
               +  '          <li class="price">'+(array[i].price).formatToAmount()+'</li>'
               +  '          <li class="car">駐車料金×'+(array[i].times == null?"":array[i].times)+'倍</li>'
               +  '     </ul>'
               +  '     <ul class="btnList">';
       
          if(array[i].price > Number(asset)){
                html +=  '   <li class="btnBuy disable">'
                     +   '       <a>購入する</a>';           
          }
          else{
                html +=  '   <li class="btnBuy">'
                     +   '       <a href="javascript:void(0);" onclick="buyCar(' + array[i].cid + ',\'' + array[i].color + '\',\'' + array[i].name + '\',' + array[i].price + ',\''+array[i].cav_name+'\');">購入する</a>';
          }
          
          html += '          </li>'
               +  '       <li class="btnTrade">';
            
          if( car_id != null && car_color != null ){
                if(array[i].price - Number($F('oldCarPrice'))* 0.9 > asset){
                    html += '<a class="disable">所有車を売却して、購入する</a>';
                }
                else{
                    html += ' <a href="javascript:void(0);" onclick="changeCarSelectFromParkingPage(' + array[i].cid + ',' + array[i].price + ',\'' + array[i].name + '\',\'' + array[i].color + '\',\''+array[i].cav_name+'\');">所有車を売却して、購入する</a>';
                }
          }
          else if((array[i].price - maxPrice * 0.9) > asset){
                html += '<a class="disable">所有車を売却して、購入する</a>';
          }
          else{
                
                html += '  <a href="javascript:void(0);" onclick="changeCar(' + array[i].cid + ',' + array[i].price + ',\'' + array[i].name + '\',\'' + array[i].color + '\',\''+array[i].cav_name+'\');">所有車を売却して、購入する</a>';
          }
          html += '       </li>'
               +  '     </ul>'
               +  '</div><!--/.section-->';
    }    

    return html;
}

function buyCar(cid, color, name, price, cavname)
{
    var asset = $F('userAsset');
    
    if(price > Number(asset)){
        return false;
    }
    var url = UrlConfig.BaseUrl + '/ajax/parking/getusercar';
    
    var myAjax = new Ajax.Request(url, {
        parameters : {
            cid: cid
        },
        onSuccess: function(response){
            var message = '';
            var html = '';
            if( "1" == response.responseText ){
                
                message = '所有車種のため、所有許可カードを使用して購入します。';
           
                html += '   <div class="head">'
                     +  '       <h2>車を購入する</h2>'
                     +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                     +  '   </div><!--/.head-->'
                     +  '   <input type="hidden" id="txtBuyCarId" value="' + cid + '" >'
                     +  '   <div id="friendPresent" class="body">'
                     +  '      <strong>' + name + '</strong>'
                     +  '      &nbsp;&nbsp;カラー '
                     +  '               <select id="ddlCarColor">'
                     +  '                   <option value="black">黒</option>'
                     +  '                   <option value="white">白</option>'
                     +  '                   <option value="silver">銀</option>'
                     +  '                   <option value="yellow">黄</option>'
                     +  '                   <option value="red">赤</option>'
                     +  '                   <option value="blue">青</option>'
                     +  '               </select>'
                     +  '   <br/>'
                     +  '   価格：¥' + price.formatToAmount() + ''
                     +  '   <br/>'
                     +  '      ' + message + ' '
                     +  '       <div class="innerBody">'
                     +  '           <div class="toFriend">'
                     +  '               <p class="car"><img id="imgCar" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/'+color+'.png'  + '"/></p>'
                     +  '           </div>' 
                     +  '       </div>'
                     +  '   <ul class="btnList">'
                     +  '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                     +  '       <li class="submit"><a href="javascript:void(0);" onclick="buyCarSubmit(\''+cavname+'\');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>'
                     +  '   </ul>'
                     +  '   </div><!--/.body-->';
            }
            else if( "2" == response.responseText ){
                message = '同じ車種を購入するには、所有許可カードが必要です';
                html += '   <div class="head">'
                     +  '       <h2>車を購入する</h2>'
                     +  '   </div>'
                     +  '   <div id="friendPresent" class="body">'
                     +  '      ' + message + ' '
                     +  '       <div class="innerBody">'
                     +  '           <div class="toFriend">'
                     +  '               <p class="car"><img  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/'+color+'.png'  + '"/></p>'
                     +  '           </div>'
                     +  '       <div>'
                     +  '   </div>';
                 
             }
             else if( "3" == response.responseText ){
                
                  html += '   <div class="head">'
                       +  '       <h2>車を購入する</h2>'
                       +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                       +  '   </div><!--/.head-->'
                       +  '   <input type="hidden" id="txtBuyCarId" value="' + cid + '" >'
                       +  '   <div id="friendPresent" class="body">'
                       +  '     <strong>' + name + ' </strong>'
                       +  '   &nbsp;&nbsp;カラー'
                       +  '               <select id="ddlCarColor">'
                       +  '                   <option value="black">黒</option>'
                       +  '                   <option value="white">白</option>'
                       +  '                   <option value="silver">銀</option>'
                       +  '                   <option value="yellow">黄</option>'
                       +  '                   <option value="red">赤</option>'
                       +  '                   <option value="blue">青</option>'
                       +  '               </select><br>'
                       +  '       価格：¥'+price.formatToAmount()+''
                       +  '       <div class="innerBody">'
                       +  '           <div class="toFriend">'
                       +  '             <p class="car"><img id="imgCar" class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/'+color+'.png'  + '"/></p>'
                       +  '           </div>'
                       +  '       </div>'
                       +  '   <ul class="btnList">'
                       +  '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                       +  '       <li class="submit"><a href="javascript:void(0);" onclick="buyCarSubmit(\''+cavname+'\');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>'
                       +  '   </ul>'
                       +  '   </div><!--/.body-->';
             }
         
             var dialog = new baseDialog();
             dialog.insertHTML(html);
             if( "1" == response.responseText || "3" == response.responseText ){
                for(var i=0;i<$('ddlCarColor').options.length;i++){
                    if($('ddlCarColor').options[i].value == color){
                        $('ddlCarColor').options[i].selected = true;
                    }
                }
           
                Event.observe('ddlCarColor', 'change', function() {
                	if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
                		var imgsrc = UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + $F('ddlCarColor') + '.png';
                		$('imgCar').setStyle({filter : "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="+imgsrc+",sizingMethod='scale')"});
                	}
                	else {
                    	$('imgCar').src = UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + $F('ddlCarColor') + '.png';
                	}
                });
             }
             if ( "2" == response.responseText ){
                _PerExec = new PeriodicalExecuter(function(pe) {
                    removeDialog();
                    pe.stop();
                }, Item.closeTime);
             }
       }});
}

function buyCarSubmit(cavname)
{   
    var color = $F('ddlCarColor');
    var url = UrlConfig.BaseUrl + '/ajax/parking/buycar';
        
    var myAjax = new Ajax.Request(url, {
        parameters: {
            cid : $F('txtBuyCarId'),
            color : $F('ddlCarColor')
            
        },
        onSuccess: function(response){
           
            var message = '';
            if ("1" == response.responseText) {
                message = '購入しました。';
            }
            else if ("-2" == response.responseText) {
                message = '現金が不足しています';
            }
            else if ("-3" == response.responseText) {
                message = '既に同じ車を所有しているので、購入できません。';
            }
            else if ("-4" == response.responseText) {
                message = '既に8台の車を所有しているので、追加で購入できません。';
            }
            else if ("-5" == response.responseText) {
                message = '同じ車種で、同じ色の車を2台以上持つことはできません。';
            }
            else {
                message = 'システムエラー。';
            }
            
            var html = '';
            html += '   <div class="head">'
                 +  '       <h2>購入完了</h2>'
                 +  '   </div>'
                 +  '   <div id="friendPresent" class="body">'
                 +  '      ' + message + ' '
                 +  '       <div class="innerBody">'
                 +  '           <div class="toFriend">'
                 +  '           <p class="car"><img  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/'+color+'.png'  + '"/></p>'
                 +  '           </div>'
                 +  '       <div>'
                 +  '   </div>';
                  
            var dialog = new baseDialog();
            dialog.insertHTML(html);
            
            if( "1" == response.responseText ){
                    freshAsset();
                    freshPage(4);
            }
            _PerExec = new PeriodicalExecuter(function(pe) {
                removeDialog();
                pe.stop();
                
             }, Item.closeTime);
            
        }});
}

var CarInfo = { cidNew : 0, colorNew : 1, priceNew : 0, cidOld : 0, colorOld : 0, priceOld : 0};

function changeCar(cid, price, name, color, cavname)
{
    var asset = $F('userAsset');
    
    CarInfo.cidNew = cid;
    CarInfo.priceNew = price;
    CarInfo.nameNew = name;
    CarInfo.colorNew = color;

    CarInfo.cidOld = 0;
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/getallusercar';
    
    var myAjax = new Ajax.Request(url, {
        onSuccess: function(response){
            var responseObject = response.responseText.evalJSON();
            var car = responseObject.cars;
            var html = '';
           
            if( car.length == 0){
                html  += '  <div class="head">'
                      +  '       <h2>所有車を売却して、購入する</h2>'
                      +  '   </div>'
                      +  '   <div id="trade" class="body">'
                      +  '      <h3>売却する車を選択してください</h3>'
                      +  '       <div class="innerBody">'
                      +  '        <ul>'
                      +  '            <li>下取りできる車を持っていません</li>'
                      +  '        </ul>';
                      
             }
             else{
                html  += '  <div class="head">'
                      +  '       <h2>所有車を売却して、購入する</h2>'
                      +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                      +  '   </div>'
                      +  '   <div id="trade" class="body">'
                      +  '      <h3>売却する車を選択してください</h3>'
                      + '       <div class="innerBody"';
                     
                if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
                    if (car.length == 1) {
                        html += ' style="height:140px;">';
                    }
                    else if (car.length == 2) {
                        html += ' style="height:200px;">';
                    }
                    else
                    {   
                        html += '>';
                    }
                 }
                html +=  '           <ul class="userCarList" id="changeCarList">';
                var j=1;
                for (i = 0; i < car.length; i++){
                    if (j == 1){
                        html  += '     <li>'
                              +  '       <label class="active" id="label'+i+'">'
                              +  '           <input type="radio"  name="rdoCars" checked="true" onclick="radioClick(' + car[i].car_id + ',\'' + car[i].car_color + '\',' + car[i].price + ','+i+','+car.length+')">'
                              +  '           <img class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+car[i].cav_name+'/small/'+car[i].car_color+'.png'  + '" alt=""/>'
                              +  '		<span class="description">'
                              +  '           '+ car[i].name+'<br>'
                              +  '           下取り価格：¥'+( (car[i].price) * 0.9 ).formatToAmount()+' '
                              +  '		 </span>'
                              +  '       </label>'
                              +  '     </li>';
                        j = 2;
                        CarInfo.cidOld = car[i].car_id;
                        CarInfo.colorOld = car[i].car_color;
                        CarInfo.priceOld = car[i].price;
                    }
                    else{
                        html  += '     <li>'
                              +  '       <label class="" id="label'+i+'">'
                              +  '           <input type="radio" name="rdoCars" onclick="radioClick(' + car[i].car_id + ',\'' + car[i].car_color + '\',' + car[i].price + ','+i+','+car.length+')">'
                              +  '           <img class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+car[i].cav_name+'/small/'+car[i].car_color+'.png'  + '" alt=""/>'
                              +  '		 <span class="description">'
                              +  '           '+ car[i].name+'<br>'
                              +  '           下取り価格：¥'+( (car[i].price) * 0.9 ).formatToAmount()+' '
                              +  '		 </span>'
                              +  '        </label>'
                              +  '     </li>';
                     }
                 }
                  html += '         </ul>'
                       +  '      </div>'
                       +  '       <ul class="btnList">'
                       +  '           <li class="submit"><a href="javascript:void(0);" onclick="changeCarSelect(\''+cavname+'\',\''+color+'\');">決定</a></li>'
                       +  '           <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();">キャンセル</a></li>'
                       +  '       </ul>'
                       +  ' </div>';
            
            }
            
            var dialog = new baseDialog();
            dialog.insertHTML(html);
            modifyDialogHeight();
            if ( car.length == 0 ){
                _PerExec = new PeriodicalExecuter(function(pe) {
                    removeDialog();
                    pe.stop();
                
                }, Item.closeTime);
            }
           
        }
      });
}

function radioClick(cid, color, price, num, car_length)
{
    for(i=0; i < car_length; i++){
    
        $('label'+i).removeClassName('active');
        
        if(i == num){
            $('label'+num).addClassName('active');
        }
    }
    
    CarInfo.cidOld = cid;
    CarInfo.colorOld = color;
    CarInfo.priceOld = price;
 
}



function changeCarSelect(cavname, color){
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/getusercar';
    var myAjax = new Ajax.Request(url, {
        parameters : {
            //new car id
            cid: CarInfo.cidNew 
         },
        onSuccess: function(response){
            
            var message = '';
            var html = '';
            var pay = Math.floor(CarInfo.priceNew-CarInfo.priceOld*0.9);
            
            if( "1" == response.responseText ){
                
                message = '所有車種のため、所有許可カードを使用して購入します。';
                 html  += '   <div class="head">'
                       +  '       <h2>所有車を売却して、購入する</h2>'
                       +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                       +  '   </div><!--/.head-->'
                       +  '   <div id="friendPresent" class="body">'
                       +  '        <strong>' + CarInfo.nameNew + '</strong>'
                  //     +  '   &nbsp&nbsp&nbsp&nbsp'
                  //     +  '        価格：¥ ' + CarInfo.priceNew.formatToAmount() + ''
                       +  '   &nbsp;&nbsp;カラー '
                      
                       +  '               <select id="ddlCarColor">'
                       +  '                   <option value="black">黒</option>'
                       +  '                   <option value="white">白</option>'
                       +  '                   <option value="silver">銀</option>'
                       +  '                   <option value="yellow">黄</option>'
                       +  '                   <option value="red">赤</option>'
                       +  '                   <option value="blue">青</option>'
                       +  '               </select>'
                       +  '<br/>'
                       +  ' 価格：¥' + CarInfo.priceNew.formatToAmount() + '（差額：¥'+Math.abs(pay).formatToAmount()+'）<br>'
                       +  '   '+message+' <p>';
                /*  if(pay >= 0){
                       html += '差額<strong style="color:red">¥' + Math.abs(pay).formatToAmount() + '</strong>円の支払いになります。';
                       if (Number($F('userAsset')) < Math.abs(pay)) {
                           html += '現金が不足しています。';
                       }
                  }*/
                  if(pay < 0){
                       html += 'お釣り<strong>¥' + (-pay).formatToAmount() + '</strong>円が返ってきます。';
                  }
                  html +=  '       <div class="innerBody">'
                       +   '           <div class="toFriend">'
                       +   '             <p class="car"><img id="imgCar"  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + CarInfo.colorNew + '.png"/></p>'
                       +   '           </div>'
                       +   '       </div>';
                       
                  if(pay >= 0){
                      
                      if (Number($F('userAsset')) < Math.abs(pay)) {
                            
                        html +=  '   <ul class="btnList">'
                             +   '     <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>';
                          
                      }
                      else {
                        
                        html += '   <ul class="btnList">'
                             +  '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +  '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmit(\''+cavname+'\');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                      }
                      
                   }
                  else {
                       
                        html +=  '   <ul class="btnList">'
                             +   '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +   '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmit(\''+cavname+'\');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                  }
                  
                  html += '   </ul>'
                       +  '</div>';
            }
            else if( "2" == response.responseText ){
                  message = '既に同じ車を所有しているので、購入できません。';
             
                  html += '   <div class="head">'
                       +  '       <h2>所有車を売却して、購入する</h2>'
                       +  '   </div>'
                       +  '   <div id="friendPresent" class="body">'
                       +  '      ' + message + ' '
                       +  '       <div class="innerBody">'
                       +  '           <div class="toFriend">'
                       +  '               <p class="car"><img  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + CarInfo.colorNew + '.png"/></p>'
                       +  '           </div>'
                       +  '       <div>'
                       +  '   </div>';
                   }
              else if( "3" == response.responseText ){
                  html += '   <div class="head">'
                       +  '       <h2>所有車を売却して、購入する</h2>'
                       +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                       +  '   </div><!--/.head-->'
                       +  '   <div id="friendPresent" class="body">'
                       +  '        <strong>' + CarInfo.nameNew + ' </strong>'
                       +  '   &nbsp;&nbsp;カラー '
                       +  '               <select id="ddlCarColor">'
                       +  '                   <option value="black">黒</option>'
                       +  '                   <option value="white">白</option>'
                       +  '                   <option value="silver">銀</option>'
                       +  '                   <option value="yellow">黄</option>'
                       +  '                   <option value="red">赤</option>'
                       +  '                   <option value="blue">青</option>'
                       +  '               </select>'
                       +  '<br>'
                       +  '    価格：¥' + CarInfo.priceNew.formatToAmount() + '（差額：¥' + Math.abs(pay).formatToAmount() + '）<br>';
              /*    if(pay >= 0){
                     //  html += '差額<strong style="color:red">¥' + Math.abs(pay).formatToAmount() + '</strong>の支払いになります。';
                       if (Number($F('userAsset')) < Math.abs(pay)) {
                            html += '現金が不足しています。';
                        }
                  }*/
                  if(pay < 0){
                       html += 'お釣り<strong>¥' + (-pay).formatToAmount() + '</strong>円が返ってきます。';
                  }
                  html +=  '       <div class="innerBody">'
                       +   '           <div class="toFriend">'
                       +   '             <p class="car"><img id="imgCar"  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + CarInfo.colorNew + '.png"/></p>'
                       +   '           </div>'
                       +   '       </div>';
                       
                  if(pay >= 0){
                      
                      if (Number($F('userAsset')) < Math.abs(pay)) {
                            
                         html +=  '   <ul class="btnList">'
                              +   '     <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>';
                          
                      }
                      else {
                        
                        html += '   <ul class="btnList">'
                             +  '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +  '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmit(\''+cavname+'\');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                      }
                      
                   }
                  else {
                       
                        html +=  '   <ul class="btnList">'
                             +   '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +   '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmit(\''+cavname+'\');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                  }
                  
                  html += '   </ul>'
                       +  '</div>';
             }
             
             var dialog = new baseDialog();
             dialog.insertHTML(html);
             
             if( "1" == response.responseText || "3" == response.responseText ){
                for(var i=0;i<$('ddlCarColor').options.length;i++){
                    if($('ddlCarColor').options[i].value == color){
                        $('ddlCarColor').options[i].selected = true;
                    }
                }
             
                Event.observe('ddlCarColor', 'change', function() {
                    $('imgCar').src = UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + $F('ddlCarColor') + '.png';
                });
             }
             if ( "2" == response.responseText ){
                _PerExec = new PeriodicalExecuter(function(pe) {
                    removeDialog();
                    pe.stop();
                
                }, Item.closeTime);
             }
       }});
}

function changeCarSubmit(cavname)
{
    var changedColor = $F('ddlCarColor');
    var url = UrlConfig.BaseUrl + '/ajax/parking/changecar';
    
    var myAjax = new Ajax.Request(url, {
        parameters: {
            cidNew : CarInfo.cidNew,
            cidOld : CarInfo.cidOld,
            colorNew : $F('ddlCarColor'),
            colorOld : CarInfo.colorOld
            
        },
        onSuccess: function(response){
           
            var message = '';
            if ( "1" == response.responseText ) {
                message = '下取りで、新しい車を購入しました。';
            }
            else if ( "-2" == response.responseText ) {
                message = '現金が不足しています。';
            }
            else if ( "-3" == response.responseText ) {
                message = '既に同じ車を所有しているので、購入できません。';
            }
            else {
                message = 'システムエラー。';
            }
           
            var html = '';
            
            html += '   <div class="head">'
                 +  '       <h2>所有車を売却して、購入する</h2>'
                 +  '   </div>'
                 +  '   <div id="friendPresent" class="body">'
                 +  '      ' + message + ''
                 +  '       <div class="innerBody">'
                 +  '           <div class="toFriend">'
                 +  '               <p class="car"><img  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/'+changedColor+'.png'  + '"/></p>'
                 +  '           </div>'
                 +  '       <div>'
                 +  '   </div>';
                  
            var dialog = new baseDialog();
            dialog.insertHTML(html);
            
            _PerExec = new PeriodicalExecuter(function(pe) {
                if( "1" == response.responseText ){
                    freshAsset();
                    freshPage(4);
                }
                removeDialog();
                 
                pe.stop();
                
             }, Item.closeTime);
            
        }});
}


function changeCarSelectFromParkingPage(newCid, newPrice, newName, newColor, cavname)
{
   
    var oldCid = car_id;
    var oldColor = car_color;
    
    if ( oldCid == null || oldColor == null ) {
        return ;
    }
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/getusercar';
    var myAjax = new Ajax.Request(url, {
        parameters : {
            //new car id
            cid: newCid 
         },
        onSuccess: function(response){
            var message = '';
            var html = '';
            var pay = Math.floor(newPrice-oldCarPrice*0.9);
            
            if( "1" == response.responseText ){
            
                 message = '所有車種のため、所有許可カードを使用して購入します。';
        
                 html  += '   <div class="head">'
                       +  '       <h2>所有車を売却して、購入する</h2>'
                       +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                       +  '   </div><!--/.head-->'
                       
                       +  '   <div id="friendPresent" class="body">'
                       +  '        <strong>' + newName + '</strong>'
                       +  '   &nbsp;&nbsp;'
                       +  '   カラー '
                       +  '               <select id="ddlCarColor">'
                       +  '                   <option value="black">黒</option>'
                       +  '                   <option value="white">白</option>'
                       +  '                   <option value="silver">銀</option>'
                       +  '                   <option value="yellow">黄</option>'
                       +  '                   <option value="red">赤</option>'
                       +  '                   <option value="blue">青</option>'
                       +  '               </select>'
                       +  '<br>'
                       +  '   価格：¥' + newPrice.formatToAmount() + '（差額：¥' + Math.abs(pay).formatToAmount() + '）<br>'
                       +  '   '+message+' <p>';
               /*   if(pay >= 0){
                       html += '差額<strong style="color:red">¥' + Math.abs(pay).formatToAmount() + '</strong>円の支払いになります。';
                       if (Number($F('userAsset')) < Math.abs(pay)) {
                            html += '現金が不足しています。';
                       }
                  }*/
                  if(pay < 0){
                       html += 'お釣り<strong>¥' + (-pay).formatToAmount() + '</strong>円が返ってきます。';
                  }
                  html +=  '       <div class="innerBody">'
                       +   '           <div class="toFriend">'
                       +   '             <p class="car"><img id="imgCar"  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + newColor + '.png"/></p>'
                       +   '           </div>'
                       +   '       </div>';
                       
                  if(pay >= 0){
                      
                     if (Number($F('userAsset')) < Math.abs(pay)) {
                            
                        html +=  '   <ul class="btnList">'
                             +  '     <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>';
                          
                      }
                      else {
                        
                        html += '   <ul class="btnList">'
                             +  '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +  '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmitFromParkingPage(\''+cavname+'\','+newCid+');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                      }
                      
                   }
                  else {
                       
                        html +=  '   <ul class="btnList">'
                             +   '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +   '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmitFromParkingPage(\''+cavname+'\','+newCid+');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                  }
                  
                  html += '   </ul>'
                       +  '</div>';
            }
            else if( "2" == response.responseText ){
                  message = '既に同じ車を所有しているので、購入できません';
            
                  html += '   <div class="head">'
                       +  '       <h2>所有車を売却して、購入する</h2>'
                       +  '   </div>'
                       +  '   <div id="friendPresent" class="body">'
                       +  '      ' + message + ' '
                       +  '       <div class="innerBody">'
                       +  '           <div class="toFriend">'
                       +  '               <p class="car"><img  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + newColor + '.png"/></p>'
                       +  '           </div>'
                       +  '       <div>'
                       +  '   </div>';
             }
             else if( "3" == response.responseText ){
             
                 html  += '   <div class="head">'
                       +  '       <h2>所有車を売却して、購入する</h2>'
                       +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
                       +  '   </div><!--/.head-->'
                       +  '   <div id="friendPresent" class="body">'
                       +  '       <strong>' + newName + ' </strong>'
                       +  '      &nbsp;&nbsp;'
                       +  '   カラー '
                       +  '               <select id="ddlCarColor">'
                       +  '                   <option value="black">黒</option>'
                       +  '                   <option value="white">白</option>'
                       +  '                   <option value="silver">銀</option>'
                       +  '                   <option value="yellow">黄</option>'
                       +  '                   <option value="red">赤</option>'
                       +  '                   <option value="blue">青</option>'
                       +  '               </select>'
                       +  '<br>'
                       +  '価格：¥' + newPrice.formatToAmount() + '（差額：¥' + Math.abs(pay).formatToAmount() + '）<br>';
                       
              /*    if(pay >= 0){
                       html += '差額<strong style="color:red">¥' + Math.abs(pay).formatToAmount() + '</strong>円の支払いになります。';
                       if (Number($F('userAsset')) < Math.abs(pay)) {
                            html += '現金が不足しています。';
                       }
                  }*/
                  if(pay < 0){
                       html += 'お釣り<strong>¥' + (-pay).formatToAmount() + '</strong>円が返ってきます。';
                  }
                  html +=  '       <div class="innerBody">'
                       +   '           <div class="toFriend">'
                       +   '             <p class="car"><img id="imgCar"  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + newColor + '.png"/></p>'
                       +   '           </div>'
                       +   '       </div>';
                       
                  if(pay >= 0){
                      
                     if (Number($F('userAsset')) < Math.abs(pay)) {
                            
                        html +=  '   <ul class="btnList">'
                             +   '     <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>';
                          
                      }
                      else {
                        
                        html += '   <ul class="btnList">'
                             +  '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +  '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmitFromParkingPage(\''+cavname+'\','+newCid+');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                      }
                      
                   }
                  else {
                       
                        html +=  '   <ul class="btnList">'
                             +   '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
                             +   '       <li class="submit"><a href="javascript:void(0);" onclick="changeCarSubmitFromParkingPage(\''+cavname+'\','+newCid+');"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/btn/btn_decide.png" height="24" width="100"></a></li>';
                  }
                  
                  html += '   </ul>'
                       +  '</div>';
              }
             
             var dialog = new baseDialog();
             dialog.insertHTML(html);
             
             if( "1" == response.responseText || "3" == response.responseText ){
                 for(var i=0; i<$('ddlCarColor').options.length; i++){
                    if($('ddlCarColor').options[i].value == newColor){
                        $('ddlCarColor').options[i].selected = true;
                    }
                 }
             
                 Event.observe('ddlCarColor', 'change', function() {
                    $('imgCar').src = UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/' + $F('ddlCarColor') + '.png';
                 });
             }
             if ( "2" == response.responseText ){
                _PerExec = new PeriodicalExecuter(function(pe) {
                    removeDialog();
                    pe.stop();
                
                }, Item.closeTime);
             }
       }});
}



function getOldCarPrice( cid ){
    var url = UrlConfig.BaseUrl + '/ajax/parking/getoldcarprice';
    
    var myAjax = new Ajax.Request(url, {
        parameters: {
            cid: cid
        },
        
        onSuccess: function( response ){
            if( response.responseText != null && response.responseText != 'false'){
                oldCarPrice = response.responseText;
            }
        }
      });  
}

function changeCarSubmitFromParkingPage(cavname, cid)
{
    var changedColor = $F('ddlCarColor');
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/changecar';
    
    var myAjax = new Ajax.Request(url, {
        parameters: {
            cidNew : cid,
            cidOld : car_id,
            colorNew : changedColor,
            colorOld : car_color
            
        },
        onSuccess: function(response){
            
            var message = '';
            if ("1" == response.responseText) {
                message = '下取りで、新しい車を購入しました。';
            }
            else if ("-2" == response.responseText) {
                message = '現金が不足しています。';
            }
            else if ("-3" == response.responseText) {
                message = '既に同じ車を所有しているので、購入できません。';
            }
            else {
                message = 'システムエラー。';
            }
            
            var html = '';
      
            html += '   <div class="head">'
                 +  '       <h2>所有車を売却して、購入する</h2>'
                 +  '   </div>'
                 +  '   <div id="friendPresent" class="body">'
                 +  '      ' + message + ' '
                 +  '       <div class="innerBody">'
                 +  '           <div class="toFriend">'
                 +  '               <p class="car"><img  class="alphafilter" src="' + UrlConfig.StaticUrl + '/apps/parking/img/car/'+cavname+'/big/'+changedColor+'.png'  + '"/></p>'
                 +  '           </div>'
                 +  '       <div>'
                 +  '   </div>';      
            var dialog = new baseDialog();
            dialog.insertHTML(html);
            
            _PerExec = new PeriodicalExecuter(function(pe) {
                if( "1" == response.responseText ){
                    freshAsset();
                    freshPage(4);
                }
                removeDialog();
                pe.stop();
                
               }, Item.closeTime);
           
        }});
}

function freshAsset(){
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/freshasset';
    
    var myAjax = new Ajax.Request(url, {
        
        onSuccess: function( response ){
            if( response.responseText != null && response.responseText != 'false'){
                var returnInfo = response.responseText.evalJSON();
                $('moneytop').innerHTML = '所持金：¥' + returnInfo.asset.formatToAmount();
                $('moneylow').innerHTML = '所持金：¥' + returnInfo.asset.formatToAmount();
                $('userAsset').value = returnInfo.asset;
            }
        }
      }); 
}