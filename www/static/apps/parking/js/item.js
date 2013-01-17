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
    pageIndex = pageIndex+1;
    window.frames[0].location.reload();
    changePageAction(pageIndex);
    top.location.href = getCookie('app_top_url') + '#bodyArea';
   
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
    top.location.href = getCookie('app_top_url') + '#bodyArea';
}

function changePageAction(page)
{   
	if (page == 1) {
		adjustHeight(1565);
	}
	else {
		adjustHeight(910);
	}
	
    $('pageIndex').value = page;
    var url = UrlConfig.BaseUrl + '/ajax/parking/getitem';

    var myAjax = new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex')
        },
        // callback function if time out
        onTimeout: function() {
            $('divItemList').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
      
        
        // summary: callback function if success
        onSuccess: function(response) {
           if (response.responseText != '' && response.responseText != 'false'){
                var responseObject = response.responseText.evalJSON();
                var html = showItemList(responseObject.info);
                
                var length = responseObject.info.length;
                var count = Number($F('count'));
                $('showingtop').update(count+"件中"+Number(pageSize*(pageIndex-1)+1)+"-"+Number(pageSize*(pageIndex-1)+length)+"件を表示");
                $('showinglow').update(count+"件中"+Number(pageSize*(pageIndex-1)+1)+"-"+Number(pageSize*(pageIndex-1)+length)+"件を表示");
                
                $('divItemList').update(html);
             }
          
        }});
        
}

function showItemList(array)
{
    var asset=$F('userAsset');
    var html = '';
    for(i = 0; i < array.length; i++) {
        
       html += '<div id="car'+i+'" class="section">'
            +  '     <p class="carPic"><img  alt="" src="' + UrlConfig.StaticUrl + '/apps/parking/img/items/'+array[i].sid+'_b.gif" />' 
            +  '     </p>'
            +  '     <h3>'+array[i].name+'</h3>'
            +  '     <ul class="status">'
            +  '          <li class="price">'+(array[i].price).formatToAmount()+'</li>'
            +  '          <li class="item">'+(array[i].introduce == null?"":array[i].introduce)+'</li>'
            +  '     </ul>'
            +  '     <ul class="btnList">';
        //    +  '          <li class="btnBuy disable">';
       if(array[i].price > Number(asset)){
            html += '         <li class="btnBuy disable">'
                 +  '               <a>購入する</a>';
       }
       else{
            html += '         <li class="btnBuy">'
                 +  '               <a href="javascript:void(0);" onclick="buyItem(' + array[i].sid + ','+array[i].price+',&quot;'+array[i].name+'&quot;)">購入する</a>';
       }
       
       html += '              </li>'
            +  '      </ul>'
            +  '</div>';

     }
    
    return html;
}

function buyItem(cid,price,name)
{
    var asset=$F('userAsset');
    
    if(price > Number(asset) ){
        return false;
    }
    var html = '';
   
    html += '   <div class="head">'
         +  '       <h2>アイテムを購入する</h2>'
         +  '       <p class="btnClose"><a href="javascript:void(0);" onclick="removeDialog();"><img width="18" height="18" alt="閉じる" src="' + UrlConfig.StaticUrl + '/apps/parking/img/overlay/btn_close.png"/></a></p>'
         +  '   </div><!--/.head-->'
         +  '   <div id="friendPresent" class="body">'
         +  '        ' + name + ''
         +  '   &nbsp&nbsp&nbsp&nbsp'
         +  '        価格: ¥' + price.formatToAmount() + ''
         +  '   &nbsp&nbsp&nbsp&nbsp'
         +  '       <div class="innerBody">'
         +  '           <div class="toFriend">'
         +  '             <p class="car"><img id="imgCar" src="' + UrlConfig.StaticUrl + '/apps/parking/img/items/'+cid+'_b.gif" /></p>'
         +  '           </div>'
         +  '       </div>'
         +  '   <ul class="btnList">'
         +  '       <li class="submit"><a href="javascript:void(0);" onclick="buyItemSubmit('+cid+',\''+name+'\');">決定</a></li>'
         +  '       <li class="cancel"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/apps/linnobox/img/btn_cancel.png" alt="キャンセル" height="24" width="100"></a></li>'
         +  '   </ul>'
         +  '   </div><!--/.body-->';
    var dialog = new baseDialog();
    dialog.insertHTML(html);
}

function buyItemSubmit(cid,name){
    var url = UrlConfig.BaseUrl + '/ajax/parking/buyitem';
    
    var myAjax = new Ajax.Request(url, {
        parameters: {
            cid : cid
        },
        onSuccess: function(response){
            var message = '';
            if ("0" == response.responseText ) {
                message = '現金が不足しています。';
            }
            else if ("1" == response.responseText) {
                message = '購入しました。';
            }
            else if ("-2" == response.responseText) {
                message = '有料駐車場カードは一枚しか持つことができません。';
            }
            else if ("-3" == response.responseText) {
                message = '無料駐車場がないので、このカードは購入できません。';
            }
            else {
                message = 'システムエラー。'; 
            }            
            
            var html = '';
            var returnResult = response.responseText;
       
            html += '   <div class="head">'
                 +  '       <h2>アイテムを購入する</h2>'
                 +  '   </div>'
                 +  '   <div id="friendPresent" class="body">';
            if("1" == returnResult){
                html += '  ' + name + 'を購入しました';
            }
            else{
                html +=  ' ' + message + '';
            }
            html +=  '       <div class="innerBody">'
                 +   '           <div class="toFriend">'
                 +   '               <p class="car"><img  src="' + UrlConfig.StaticUrl + '/apps/parking/img/items/'+cid+'_b.gif"/></p>'
                 +   '           </div>'
                 +   '       <div>'
                 +   '   </div>';
            var dialog = new baseDialog();
            dialog.insertHTML(html);
            
            _PerExec = new PeriodicalExecuter(function(pe) {
                if( "1" == response.responseText ){
                    freshAsset();
                    freshPage(cid);
                }
                removeDialog();
                 
                pe.stop();
                
             }, Item.closeTime);
            
        }
    });
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
