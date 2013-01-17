
(function($) {

var firstLogin = Number($('#firstLogin').val());
var sendBombCount = Number($('#sendBombCount').val());
var needRestartGame = Number($('#needRestartGame').val());
var uid = Number($('#txtUid').val());
var sendHelpCard = Number($('#sendHelpCard').val());

$().ready(function() {
    
    //restart game
    if (needRestartGame == '') {
        return;
    }
    else if (needRestartGame == 1) {
        jQuery.init.restartGame();
    }
    else if (needRestartGame == -2) {
        jQuery.item.createSystemErrorWindow();
    }

    //no need to restart game
    if (needRestartGame == -1) {
        if (firstLogin == '' && sendBombCount == 4) {
            //not first login today and bomb count=0,only send 4 bombs
            jQuery.init.sendUserBomb();
        }
        else if (firstLogin == 1 && sendBombCount == ''){
            //first login today ,only send card
            jQuery.init.visiteGift();
        }
        else if (firstLogin == 1) {
            //first login today and bomb count=0, firstly,open send bomb window, secondly,open gift window
            jQuery.init.sendUserBomb();
        }
        
        if (sendHelpCard == 1) {
            jQuery.init.confirmGift(10)
        }
    }

});

$.init = {
    
    restartGame : function()
    {
        var html = '';
        html += '<iframe></iframe>'
              + '<div id="overlayBox" class="gameover">'
              + '<div id="overlayBoxInner">'
              + '   <h2>ゲームオーバー</h2>'
              + '   <p>あなたの留守中に敵対組織からの襲撃を受け、ヒットマンが全滅してしまいました…</p>'
              + '   <p class="btn">'
              + '          <a href="javascript:void(0)" onclick="jQuery.init.restartConfirm()"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/restart.png" width="545" height="45" alt="最初から始める" />'
              + '          </a>'
              + '   </p>'
              + '</div>'
              + '</div>';
        $('#overlay').html(html);
        $('#overlay').show();

    },
    
    restartConfirm : function()
    {   
        if (jQuery.browser.msie) {
            window.event.returnValue = false;
        } 
        window.location = UrlConfig.BaseUrl + '/dynamite/charaselect';
    },
    
    //first login today, send gift
    visiteGift : function()
    {
        var html = '';
        html += '<iframe frameborder="0" allowtransparency="true"></iframe>'
              + '<div id="overlayBox" class="visitersGift">'
              + '<div id="overlayBoxInner">'
              + '   <h2>ビジターギフト</h2>'
              + '   <p>'
              + '       どきどきダイナマイトパニックへようこそ！<br />'
              + '       1日1回プレゼントがもらえます。次の宝箱から1つ選んでください。'
              + '   </p>'
              + '   <ul id="giftList">'
              + '       <li><a href="javascript:void(0)" onclick="jQuery.init.getGift()">ギフト1</a></li>'
              + '       <li><a href="javascript:void(0)" onclick="jQuery.init.getGift()">ギフト2</a></li>'
              + '       <li><a href="javascript:void(0)" onclick="jQuery.init.getGift()">ギフト3</a></li>'
              + '       <li><a href="javascript:void(0)" onclick="jQuery.init.getGift()">ギフト4</a></li>'
              + '       <li><a href="javascript:void(0)" onclick="jQuery.init.getGift()">ギフト5</a></li>'
              + '   </ul>'
              + '</div>'
              + '</div>';
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    //user check picture,then get gift
    getGift : function()
    {
        jQuery.ajax({
                type : "POST",
                url : UrlConfig.BaseUrl + '/ajax/dynamite/getgift',
                dataType : "json",
                timeout : 5000,
                error : function () {
                    jQuery.item.createAjaxTimeOutErrorWindow();
                },
                success : function(response) {
                    var sendCid = response.cid;
                    NEED_SELECT_HITMAN = response.needSelectHitman;
                    jQuery.init.confirmGift(sendCid, uid);
                }
            }
        );
    },
    
    confirmGift : function(sendCid, aid, type, isFromTrigger, getBonus)
    {   
        var cardName = '';
        var introduce = '';
        var divClassName = 'visitersGift';
        if ( isFromTrigger == 1 ) {
            divClassName = 'bonus';
        }
        var sendCid = Number(sendCid);
        var money = 0;
        var sendBombCount = 0;
        
        switch (sendCid) {
            case 1 : 
                cardName = '元気ドリンク';
                introduce = 'ヒットマン1人の体力が少し回復';
                break;
            case 2 : 
                cardName = '元気ドリンクDX';
                introduce = 'ヒットマン1人の体力が完全回復';
                break;
            case 3 : 
                cardName = 'ミラクルドリンク';
                introduce = 'ヒットマン全員の体力が完全回復';
                break;
            case 4 : 
                cardName = '復活のシャワー';
                introduce = '自分のアジトのヒットマン全員が完全復活';
                break;
            case 5 : 
                cardName = 'ダイナマイトほいほい';
                introduce = '使用後3時間、設置されたダイナマイトを自動的に没収';
                break;
            case 6 : 
                cardName = '復活の儀式';
                introduce = '殉職したヒットマンが1人、完全復活';
                break;
            case 7 : 
                cardName = '最終兵器';
                introduce = '自分のアジトのダイナマイト数が最大化';
                break;
            case 10 : 
                cardName = '神々の怒り';
                introduce = '最強のヒットマンが怒りの制裁を与える';
                break;            
            case 60 : 
                cardName = 'ダイナマイト詰め合わせ（梅）';
                introduce = 'ダイナマイト2個の詰め合わせセットです';
                sendBombCount = 2;
                break;
            case 61 : 
                cardName = 'ダイナマイト詰め合わせ（竹）';
                introduce = 'ダイナマイト5個の詰め合わせセットです';
                sendBombCount = 5;
                break;
            case 62 : 
                cardName = 'ダイナマイト詰め合わせ（松）';
                introduce = 'ダイナマイト10個の詰め合わせセットです';
                sendBombCount = 10;
                break;
            case 50 : 
                cardName = 'ボーナス+500$';
                introduce = '500ドルの臨時ボーナスをゲットしました。';
                money = 500;
                break;
            case 51 : 
                cardName = 'ボーナス+1000$';
                introduce = '1000ドルの臨時ボーナスをゲットしました。';
                money = 1000;
                break;
            case 52 : 
                cardName = 'ボーナス+3000$';
                introduce = '3000ドルの臨時ボーナスをゲットしました。';
                money = 3000;
                break;
            default :
                break;
        }
        
        var html = '';
        html += '<div id="overlayBox" class="' + divClassName + '"  onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.init.addClickEvent('+sendCid+','+aid+', ' + type + ')">'
              + '<div id="overlayBoxInner">'
              + '   <h2>ビジターギフト</h2>'
              + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.init.closeGiftWindow('+sendCid+','+aid+', ' + type + ')"/></p>'
              + '   <p class="pic_item"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/item/b/'+sendCid+'.gif" width="90" height="90" alt="" /></p>'
              + '   <div class="floatBox">';
        if (isFromTrigger == 1) {
            if (sendCid >= 50 && sendCid <= 52) {
                html += '現金 $'+formatToAmount(Math.round(getBonus))+'<p>'+money+'ドルの臨時ボーナスをゲットしました。';
            }
            else if (sendCid >= 60 && sendCid <= 62) {
                html += '現金 $'+formatToAmount(Math.round(getBonus))+'<p>ダイナマイト'+sendBombCount+'個の詰め合わせセットです';
            }
            else {
                html += '<strong>現金 $'+formatToAmount(Math.round(getBonus))+'&nbsp;と  '+cardName+' を手に入れました。</strong>'
                      + '       <p><!--【詰め合わせ内容】<br />-->'+introduce+'</p>';
            }
        }
        else {     
            html += '       <p><strong>'+cardName+'</strong>を手に入れました。</p>'
                  + '       <p>【アイテム効果】<br />'+introduce+'</p>';
        }
        html += '   </div>'
              + '</div>'
              + '</div>';
        $('#overlay').html(html);
        $('#overlay').show();
        
        setTimeout(function(){
                    $('#dynamiteBody').bind('click', function(){
                        jQuery.init.closeGiftWindow(sendCid, aid, type);
                    });
                  }, 500);
        
    },
    
    //if user's bomb count=0, send bomb to user
    sendUserBomb : function(uid)
    {   
        jQuery.ajax({
                type : "POST",
                url : UrlConfig.BaseUrl + '/ajax/dynamite/getsendbombcount',
                timeout : 5000,
                error : function () {
                    jQuery.item.createAjaxTimeOutErrorWindow();
                },
                success : function(response) {
                    var html = '';
                    html += '<div id="overlayBox" class="addDynamite" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.init.addSendBombClickEvent('+uid+', '+response+')">'
                          + '<div id="overlayBoxInner">'
                          + '   <h2>ダイナマイト追加</h2>'
                          + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.init.closeSendBombWindow('+uid+', '+response+')"/></p>'
                          + '   <p>ダイナマイトをすべて消費したため、新しいダイナマイトが'+response+'つ支給されました。</p>'
                          + '   <ul class="bombList">';
                    for (var i = 1; i <= response; i++ ) {
                        html += '       <li><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/dynamite/b/nomal.gif" width="23" height="32" alt="" /></li>';
                    }
                       
                    html += '   </ul>'
                          + '</div>'
                          + '</div>';
                    $('#overlay').html(html);
                    $('#overlay').show();
                    
                    setTimeout(function(){
                                $('#dynamiteBody').bind('click', function(){
                                    jQuery.init.closeSendBombWindow(uid, response);
                                });
                              }, 500);
                            
                }
                }
        );
        
        
    },
    
    closeGiftWindow : function(sendCid, aid, type)
    {
        $('#overlay').html('');
        $('#overlay').hide();
        
        if ( type == 1 ) {
            jQuery.dynamite.presentBomb();
        }
        else {
            if (sendCid <= 10) {
                jQuery.item.refreshCardCount(sendCid, sendHelpCard);
                sendHelpCard = 0
            }
            
            jQuery.dynamite.updateBombBox($('txtUid').val());
           
            if (aid) {
                jQuery.dynamite.goUserDynamite(aid);
            }
            if (NEED_SELECT_HITMAN == 1) {
                jQuery.init.selectHitman();
            }
        }
        
        $('#dynamiteBody').unbind('click');
          
    },
    
    closeRestartWindow : function()
    {
        $('#overlay').html('');
        $('#overlay').hide();
        
        //if today first login, send card
        if (firstLogin == 1){
            jQuery.init.visiteGift();
        }
        /*
        else if ($('#haveAllianceApply').val()) {
            jQuery.allianceapply.confirmDynamiteApplication();
        }
        */
    },
    
    closeSendBombWindow : function(uid, response)
    {   
        $('#overlay').html('');
        $('#overlay').hide();
        
        jQuery.ajax({
                type : "POST",
                url : UrlConfig.BaseUrl + '/ajax/dynamite/sendbombtouser',
                data : {sendBomb : response},
                timeout : 5000,
                error : function () {
                    jQuery.item.createAjaxTimeOutErrorWindow();
                },
                success : function(response) {
                    jQuery.dynamite.updateBombBox($('txtUid').val());
                    
                    if (firstLogin == 1){
                        jQuery.init.visiteGift();
                    }
                    
                    if (uid) {
                        $.dynamite.goUserDynamite(uid);
                    }
                    /*
                    else if ($('#haveAllianceApply').val()) {
                        jQuery.allianceapply.confirmDynamiteApplication();
                    }
                    */
                }
            }
        );
        
        $('#dynamiteBody').unbind('click');
        
    },
    
    addClickEvent : function(sendCid, aid, type)
    {   
        $('#dynamiteBody').bind('click', function(){
            jQuery.init.closeGiftWindow(sendCid, aid, type);
        });
       
    },
    
    addSendBombClickEvent : function(uid, response)
    {
        $('#dynamiteBody').bind('click', function(){
            jQuery.init.closeSendBombWindow(uid, response);
        });
    },
    
    selectHitman : function()
    {
        NEED_SELECT_HITMAN = 0;
        
        var html = '';
        html += '<iframe></iframe>'
              + '   <div class="info" id="overlayBox">'
              + '       <div id="overlayBoxInner">'
              + '           <h2>ヒットマン変更</h2>'
              + '           <p>所持金が$1000を超えたので、最強のヒットマンは去っていきました</p>'
              + '           <p id="toShop">'
              + '               <a href="javascript:void(0)" onclick="jQuery.init.restartConfirm()">ヒットマン紹介所へ</a>'
              + '           </p>'
              + '       </div>'
              + '   </div>';
              
        $('#overlay').html(html);
        $('#overlay').show();
    }

};

})(jQuery);