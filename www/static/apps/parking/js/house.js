var pageIndex = 1;
var pageSize = 8;

function nextPage(){
    var count = Number($F('count'));
    var totalPages = Math.floor(count/pageSize) + (count%pageSize==0 ? 0:1); 
 
    if(count <= pageSize){
        return false;
    }
    if(pageIndex >= totalPages){
        return false;
    }
    pageIndex = pageIndex + 1;
    window.frames[0].location.reload();
    changePageAction(pageIndex);
    top.location.href = getCookie('app_top_url') +  '#bodyArea';
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
    top.location.href = getCookie('app_top_url') +  '#bodyArea';
}

function changePageAction(page)
{
    $('pageIndex').value = page;
    var url = UrlConfig.BaseUrl + '/ajax/parking/gethouse';

    var myAjax = new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex')
        },
        // callback function if time out
        onTimeout: function() {
            $('divHouseList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },

        // summary: callback function if success
        onSuccess: function(response) {
            if (response.responseText != '' && response.responseText != 'false'){      
                var responseObject = response.responseText.evalJSON();
                var html = showHouseList(responseObject.info);
                
                var count = Number($F('count'));
                var length = responseObject.info.length;
                $('showingtop').update(count+"件中"+Number(pageSize*(pageIndex-1)+1)+"-"+Number(pageSize*(pageIndex-1)+length)+"件を表示");
                $('showinglow').update(count+"件中"+Number(pageSize*(pageIndex-1)+1)+"-"+Number(pageSize*(pageIndex-1)+length)+"件を表示");
                    
                $('divHouseList').update(html);
                
                adjustHeight();
             }
           
        }});
}

function showHouseList(array)
{
    var html = '';
    
    var asset = Number($F('userAsset'));
    var oldHousePrice = Number($F('oldHousePrice'));
    var oldHouseType = $F('oldHouseType');
    var oldHouseId = $F('oldHouseId');
     
    for(i = 0; i < array.length; i++) {
        var houseType;
        switch(array[i].type){
            case 'A' : houseType = 3; break;
            case 'B' : houseType = 4; break;
            case 'C' : houseType = 5; break;
            case 'D' : houseType = 6; break;
            case 'E' : houseType = 7; break;
            case 'F' : houseType = 8; break;
        }
        
        var balance = Number(array[i].price - oldHousePrice * 0.9);
        
        html += '<div id="car'+i+'" class="section">'
             +  '     <p class="carPic"><img  alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/background/'+array[i].cav_name+'_s.gif'  + '" />' 
             +  '     </p>'
             +  '     <h3>'+array[i].name+'</h3>'
             +  '     <ul class="status">';
     //   if(balance < 0){
     //       html += '      <li class="price">購入できません　(資産価値:¥'+ array[i].price.formatToAmount() +'円)</li>';   
    //    }
        if(oldHouseId == array[i].id || oldHouseType > houseType){
            html +=  '     <li class="price">'+ array[i].price.formatToAmount() +'</li>';
        }
        else if(array[i].price != balance){
            html +=  '     <li class="price"> '+ array[i].price.formatToAmount() +' （購入価格:¥'+ balance.formatToAmount()+'）</li>';
        }
        //if user's housetype='A'
        else{
            html +=  '     <li class="price"> '+ array[i].price.formatToAmount() +'</li>';
        }
        html += '          <li class="estate">駐車料金＝¥'+(array[i].fee).formatToAmount()+'/15分、駐車区画＝'+houseType+'台 </li>'
             +  '     </ul>'
             +  '     <ul class="btnList">';
             
        if(oldHouseId == array[i].id || oldHouseType > houseType || balance > asset){
             html +=  '    <li class="btnBuy disable">'
                  +   '         <a>購入する</a>';
        }
        else{
             html +=  '    <li class="btnBuy">'
                  +   '         <a href="javascript:void(0);" onclick="buyHouse(' + array[i].id + ','+array[i].price+','+array[i].fee+',\''+array[i].cav_name+'\','+houseType+','+oldHouseType+')">購入する</a>';
        }
                     
        html += '          </li>'
             +  '      </ul>'
             +  '</div>';
      }

    return html;
}

function buyHouse(hid,price,fee,cavname,houseType,oldHouseType)
{
    var asset = Number($F('userAsset'));
    var oldHouseId = $F('oldHouseId');
    var oldHousePrice = Number($F('oldHousePrice'));
    var balance = Number(price - oldHousePrice * 0.9);

    if( balance > asset ){
        return false;
    }
    if(oldHouseId == hid){
        return false;
    }
    if(oldHouseType > houseType){
        return false;
    }
    var html = '';
   
    html += '   <div class="head">'
         +  '       <h2>不動産を購入する</h2>'
         +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
         +  '   </div><!--/.head-->'
         +  '   <div id="friendPresent" class="body">'
         +  '     <p>駐車料金＝¥'+ fee.formatToAmount() +'/15分、駐車区画＝'+ houseType +'台になります。<p>';
    if( balance >= 0){
            
            html += ' 差額<strong style="color:red">¥'+balance.formatToAmount()+'</strong>の支払いになります。';
    }
    
    html +=  '       <div class="innerBody">'
         +   '           <div class="toFriend">'
         +   '             <p class="car"><img id="imgCar" src="' + UrlConfig.StaticUrl + '/apps/parking/img/background/'+cavname+'_s.gif"/></p>'
         +   '           </div>'
         +   '       </div>'
         +   '       <ul class="btnList">'
         +   '           <li class="submit"><a class="disable" href="javascript:void(0);" onclick="buyHouseSubmit('+hid+','+fee+',\''+cavname+'\');">決定</a></li>'
         +   '           <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();">キャンセル</a></li>'
         +   '       </ul>'
         +   '   </div><!--/.body-->';
     var dialog = new baseDialog();
     dialog.insertHTML(html);
     
}

function buyHouseSubmit(hid,fee,cavname)
{
    
    var url = UrlConfig.BaseUrl + '/ajax/parking/buyhouse';
    
    var myAjax = new Ajax.Request(url, {
        parameters: {
            hid : hid
            
        },
        onSuccess: function(response){
         
            var message = '';
       
            if ("0" == response.responseText) {
                message = '現金が不足しています。';
            }
            else if ("1" == response.responseText) {
                message = '購入しました。';
            }
            else if ("2" == response.responseText)
            {
                message = '既に所有しています。';
            }
            else if ("3" == response.responseText)
            {
                message = '今の不動産より、安いものは購入できません。';
            }
            else {
                message = 'システムエラー。'; 
            }             
            
            var html = '';
            var returnResult = response.responseText;
          
            html += '   <div class="head">'
                 +  '       <h2>不動産を購入する</h2>'
                 +  '   </div>'
                 +  '   <div id="friendPresent" class="body">'
                 +  '      ' + message + '';
            if ( "1" == returnResult ){
                 
                 html +=   '<p>駐車料金＝¥'+ fee.formatToAmount() +'/15分</p>';
                      
             }
             html +=  '       <div class="innerBody">'
                  +   '           <div class="toFriend">'
                  +   '               <p class="car"><img  src="' + UrlConfig.StaticUrl + '/apps/parking/img/background/'+cavname+'_s.gif"/></p>'
                  +   '           </div>'
                  +   '       <div>'
                  +   '   </div>';
            var dialog = new baseDialog();
            dialog.insertHTML(html);
           
            _PerExec = new PeriodicalExecuter(function(pe) {
                if( "1" == response.responseText ){
                    window.location = UrlConfig.BaseUrl + '/parking/index';
                 }
                removeDialog();
                 
                pe.stop();
                
             }, Item.closeTime);
         }
    });
}


