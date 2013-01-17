
(function($) {

var sendBomb = 0;
var changeHitmanSuccess = 0;

$().ready(function() {
    
});

$.item = {
    
    useItem : function(itemId)
    {   
        if ( null != getCookie('app_top_url_dynamite') ) {
            top.location.href = getCookie('app_top_url_dynamite') +  '#pagetop';            
        }
        if (!AJAX_COMPLETE) {
            AJAX_COMPLETE = true;
            
            jQuery.ajax({
                    type : "POST",
                    url : UrlConfig.BaseUrl + '/ajax/dynamite/gethitmancondition',
                    timeout : 5000,
                    error : function() {
                        jQuery.item.createAjaxTimeOutErrorWindow();
                    },
                    success : function(response){
                        if (response != '0') {
                            jQuery.item.useItemSubmit(itemId)
                        }
                        else {
                            jQuery.init.restartGame();
                        }
                    }
            });
           
        }
        
    },  
    
    useItemSubmit : function(itemId)
    {
       
        $('#dynamiteBody').unbind('click');
        
        $('#useCard').val('');
        $('#useCardResult').val('');
    
        switch (itemId) {
            //元気ドリンク
            case 1 :
                $.item.useRecoverBloodCard(1);
                break;
            //元気ドリンクDX
            case 2 : 
                $.item.useRecoverBloodCard(2);
                break;
            //ミラクルドリンク
            case 3 :
                $.item.useRecoverUserAndAllianceCard();
                break;
            //復活のシャワー
            case 4 :
                $.item.useReviveUserAndAllianceCard();
                break;
            //ダイナマイトほいほい
            case 5 : 
                $.item.useConfiscateBombCard();
                break;
            //復活の儀式
            case 6 :
                $.item.useReviveCard();
                break;
            //最終兵器
            case 7 :
                $.item.useFinalWeaponCard();
                break;
            //マイミクシェルター
            case 8 : 
                $.item.changeModeToFriend();
                break;
            //宣戦布告
            case 9 : 
                $.item.changeModeToAll();
                break;
            //神々の怒り 
            case 10 : 
                $.item.useAngryCard();
                break;
            default :
                break;
        }
     
    },
    
    //元気ドリンク
    useRecoverBloodCard : function(itemId)
    {   
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/getlessbloodhitman',
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                var html = '';
                if (response != -1) {
                     var hitman1 = response.info.hitman1 == null ? '' : Number(response.info.hitman1);
                     var hitman2 = response.info.hitman2 == null ? '' : Number(response.info.hitman2);
                     var hitman3 = response.info.hitman3 == null ? '' : Number(response.info.hitman3);
                     var hitman4 = response.info.hitman4 == null ? '' : Number(response.info.hitman4);
                     var bg = response.info.bg;
                     var hitmanType = response.info.hitmanType;
                     var maxLife = response.info.maxLife;
                     
                     html += '<div id="overlayBox" class="useItem drink" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent()">'
              			   + '<div id="overlayBoxInner">'
                           + '   <h2>アイテムを使う</h2>'
                           + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.item.closeWindow('+itemId+')"/></p>'
                           + '   <p>'
                           + '   元気ドリンクカードを使います。<br />'
                           + '   どのヒットマンの体力を回復させますか？'
                           + '   </p>'
                           + '   <ul id="itemList">';
                    
                     var hitmanBlood = {1 : hitman1, 2 : hitman2, 3 : hitman3, 4 : hitman4};
                     
                     for (var i=1; i <= 4; i++) {
                         
                         if (hitmanBlood[i] != '') {
                             var garmentType = 'a';
                             if ( hitmanBlood[i] < (maxLife/2) ) {
                                 garmentType = 'b';
                             }
                         
                             html += '<li>'
                                   + '<a href="javascript:void(0)" onclick="jQuery.item.useRecoverBloodCardSubmit('+i+', '+itemId+', '+hitmanType+')">アイテム'+i+''
                                   + '<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/stamina/max'+maxLife+'/'+hitmanBlood[i]+'_s.gif" class="stamina" />'
                                   + '<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/hitman/'+bg+''+garmentType+'.gif" class="hitman" />'
                                   + '</a></li>';
                         }
                     }
                   
                     html += '   </ul>'
					 	   + '</div>'
                           + '</div>';
                     
                 }
                 else {
                     html += '<div id="overlayBox" class="useItem" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent()">'
                 		   + '<div id="overlayBoxInner">'
                           + '   <h2>アイテムを使う</h2>'
                           + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.item.closeWindow()"/></p>';
                     if (itemId == 1) {
                         html += '   <p>負傷しているヒットマンがいないので、元気ドリンクは使えません。';
                     }
                     else if (itemId == 2) {
                         html += '   <p>負傷しているヒットマンがいないので、元気ドリンクDXは使えません。';
                     }
                     html += '   </p>'
					 	   + '</div>'
                           + '</div>';
                     
                     $('#dynamiteBody').bind('click', function(){
                         jQuery.item.closeWindow();
                     });
                   
                 }
                 
                 $('#overlay').html(html);
                 $('#overlay').show(); 
                 
                 AJAX_COMPLETE = false;
            }
        });
        
    },
    
    //元気ドリンクDX
    useRecoverBloodCardSubmit : function(hitman, itemId, hitmanType)
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/userecoverbloodcard',
            data : {hitman : hitman,
                    itemId : itemId,
                    hitmanType : hitmanType
                   },
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                jQuery.item.showResult(response, itemId);
            }
        });
    },
    

    //ミラクルドリンク
    useRecoverUserAndAllianceCard : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/userecoveruserandalliancecard',
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                jQuery.item.showResult(response, 3);
            }
        });
    },
    
    //復活のシャワー
    useReviveUserAndAllianceCard : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/reviveuserandalliancehitman',
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                var result = response.responseInfo.result;
                if (result == 1) {
                    postActivityWithPic(response.responseInfo.activity, response.responseInfo.activity_pic, "image/gif");
                }
                jQuery.item.showResult(result, 4);
            }
        });
    },
    
    //ダイナマイトほいほい
    useConfiscateBombCard : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/useconfiscatebombcard',
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                jQuery.item.showResult(response, 5);
            }
        });
    },
    
    //復活の儀式
    useReviveCard : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/userevivecard',
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                var result = response.responseInfo.result;
                /*
                if (result == 1) {
                    postActivity(response.responseInfo.activity);
                }
                */
                jQuery.item.showResult(result, 6);
            }
        });
    },

    
    //最終兵器
    useFinalWeaponCard : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/usefinalweaponcard',
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                var result = response.responseInfo.result;
                if (result == 1) {
                    postActivityWithPic(response.responseInfo.activity, response.responseInfo.activity_pic, "image/gif");
                }
                jQuery.item.showResult(result, 7);
            }
        });
    },
    
    //神々の怒り
    useAngryCard : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/useangrycard',
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                var result = response.status;
                var pic = response.hitmanPicType;
                if (result == 1) {
                    changeHitmanSuccess = 1;
                    postActivityWithPic(response.activity, response.activity_pic, "image/gif");
                }
                $('#hitmanPicType').val(pic);
                jQuery.item.showResult(result, 10);
            }
        });
    },
    
    //マイミクシェルター
    changeModeToFriend : function()
    {
        var html = '';
        html += '<div id="overlayBox" class="useItem drink">'
              + '<div id="overlayBoxInner">'
              + '   <h2>マイミクシェルター使う</h2>'
              + '   <p class="btnClose"><img width="36" height="36" onclick="jQuery.item.changeModeCancel()" alt="閉じる" src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png"/></p>'
              + '   <p>使用後、マイミク限定でのバトルロワイアルに変更されます。設置ダイナマイトはすべて無効となります。よろしいですか？</p>'
              + '   <ul id="comfirmList">'
              + '       <li class="yes"><a href="javascript:void(0)" onclick="jQuery.item.changeModeToFriendSubmit();">はい</a></li>'
              + '       <li class="no"><a href="javascript:void(0);" onclick="jQuery.item.changeModeCancel();">いいえ</a></li>'
              + '   </ul>'
              + '</div>'
              + '</div>';
         
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    changeModeToFriendSubmit : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/changemodetofriend',
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                
                jQuery.item.showResult(response, 8);
            }
        });
    },
    
    //宣戦布告
    changeModeToAll : function()
    {
        var html = '';
        html += '<div id="overlayBox" class="useItem drink">'
              + '<div id="overlayBoxInner">'
              + '   <h2>マイミクシェルター使う</h2>'
              + '   <p class="btnClose"><img width="36" height="36" onclick="jQuery.item.changeModeCancel()" alt="閉じる" src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png"/></p>'
              + '   <p>使用後、ミクシィ全体でのバトルロワイアルに変更されます。不特定多数のユーザーへの攻撃が可能ですが、不特定多数のユーザーから攻撃されることもあります。よろしいですか？</p>'
              + '   <ul id="comfirmList">'
              + '       <li class="yes"><a href="javascript:void(0)" onclick="jQuery.item.changeModeToAllSubmit();">はい</a></li>'
              + '       <li class="no"><a href="javascript:void(0);" onclick="jQuery.item.changeModeCancel();">いいえ</a></li>'
              + '   </ul>'
              + '</div>'
              + '</div>';
         
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    changeModeToAllSubmit : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/changemodetoall',
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                
                jQuery.item.showResult(response, 9);
            }
        });
    },
    
    //show use card result
    showResult : function(response, itemId)
    {   
        $('#useCard').val(itemId);
        $('#useCardResult').val(response);
            
        if (itemId > 2) {
            $('#dynamiteBody').bind('click', function(){
                jQuery.item.closeWindow(itemId);
            });
        }
        
        var message = '';
        //元気ドリンク
        if (itemId == 1) {
            if (response == 1) {
               
                message = 'ヒットマン1人の体力が少し回復しました。';

                $('#overlayBox p:last').html(message);
                $('#itemList').remove();
                jQuery.item.refreshCardCount(itemId);
                
                $('#dynamiteBody').bind('click', function(){
                    jQuery.item.closeWindow(itemId);
                });
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //元気ドリンクDX
        if (itemId == 2) {
            if (response == 1) {
                
                message = 'ヒットマン1人の体力が完全回復しました。';

                $('#overlayBox p:last').html(message);
                $('#itemList').remove();
                jQuery.item.refreshCardCount(itemId);
                
                $('#dynamiteBody').bind('click', function(){
                    jQuery.item.closeWindow(itemId);
                });
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //ミラクルドリンク
        if (itemId == 3) {
            
            if (response == 1) {
                message = 'ヒットマン全員の体力が完全回復しました。';
                
                jQuery.item.refreshCardCount(itemId);
                jQuery.item.getResultHtml(message, 3);
            }
            else if (response == -2) {
                message = '負傷しているヒットマンがいないので、ミラクルドリンクは使えません。';
                
                jQuery.item.getResultHtml(message);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //復活のシャワー
        if(itemId == 4) {
            
            if (response == 1) {
                message = 'あなたの率いるヒットマン全員が完全回復しました。';
                
                jQuery.item.refreshCardCount(itemId);
                jQuery.item.getResultHtml(message, 4);
            }
            else if (response == -2) {
                message = '殉職/負傷しているヒットマンがいないので、復活のシャワーは使えません。';
                
                jQuery.item.getResultHtml(message);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
            
        }
        //ダイナマイトほいほい
        if (itemId == 5) {
            if (response == 1) {
                message = '使用後3時間、設置されたダイナマイトを自動的に没収';
                
                jQuery.item.refreshCardCount(itemId);
                jQuery.item.getResultHtml(message, 5);
            }
            else if (response == -2) {
                message = 'ダイナマイトが設置されていないため、ダイナマイトほいほいは使えません。';

                jQuery.item.getResultHtml(message);
            }
            else if (response == -3) {
                message = 'ダイナマイトほいほい「使用中」';

                jQuery.item.getResultHtml(message);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //復活の儀式
        if (itemId == 6) {
            if (response == 1) {
                message = '殉職ヒットマン1人が完全復活しました。';
                
                jQuery.item.refreshCardCount(itemId);
                jQuery.item.getResultHtml(message, 6);
            }
            else if (response == -2) {
                message = '殉職ヒットマンがいないので、復活の儀式は使えません。';

                jQuery.item.getResultHtml(message);
            }
            else if (response == -3) {
                message = '殉職ヒットマンがいないので、復活の儀式は使えません。';

                jQuery.item.getResultHtml(message);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //最終兵器
        if (itemId == 7) {
            if (response == 1) {
                message = 'ダイナマイトの数が26個になりました。';
                
                jQuery.item.refreshCardCount(itemId);
                jQuery.item.getResultHtml(message, 7);
            }
            else if (response == -2) {
                message = 'ダイナマイトは26個以上増やせないので、最終兵器は使えません。';

                jQuery.item.getResultHtml(message);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //神々の怒り
        if (itemId == 10) {
            if (response == 1) {
                message = '「神々の怒り」を使用しました。どこからともなく、最強のヒットマンが怒りの制裁を加えるために現れました。';

                jQuery.item.getResultHtml(message, 10);
                jQuery.item.refreshCardCount(itemId);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //マイミクシェルター
        if (itemId == 8) {
            var status = response.status;
            sendBomb = response.sendBomb;
            if (status == 1) {
                $('#gameMode').val(1);
                
                message = 'マイミク限定でのバトルロワイアルに変更されました';
                jQuery.item.getResultHtml(message, 8);
                jQuery.item.refreshCardCount(itemId);
            }
            else if (status == -2) {
                message = 'マイミクが5人以上いないと、マイミクシェルターを使う事ができません。マイミクをアプリに招待して、マイミクバトルを楽しもう！！';
                jQuery.item.getResultHtml(message, 8);
            }
            else if (status == -3) {
                message = 'マイミクシェルターは、全体モードでプレイ中の時のみ使用できます。全体モードへの切り替えには「宣戦布告」を使おう';
                jQuery.item.getResultHtml(message, 8);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        //宣戦布告
        if (itemId == 9) {
            var status = response.status;
            if (status == 1) {
                $('#gameMode').val(0);
                
                message = 'ミクシィ全体のバトルロワイアルに変更されました';
                jQuery.item.getResultHtml(message, 9);
                jQuery.item.refreshCardCount(itemId);
            }
            else if (status == -2) {
                message = '宣戦布告は、マイミクモードでプレイ中の時のみ使用できます。マイミクモードへの切り替えには「マイミクシェルター」を使おう！';
                jQuery.item.getResultHtml(message, 9);
            }
            else {
                jQuery.item.createSystemErrorWindow();
            }
        }
        AJAX_COMPLETE = false;
    },
    
    //refresh user card count
    refreshCardCount : function(itemId, sendHelpCard)
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/refreshcard',
            data : {itemId : itemId,
                    sendHelpCard : sendHelpCard},
            dataType : "json",
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                var cardCount = response.cardCount;
                var useTime = response.useTime;
                var canUse = response.canUse;
                
                $('#li'+itemId+' .rest').html('<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/num.png" width="33" height="24" alt="残り" />'+cardCount+'');
                var html = '';
                
                if (itemId == 5) {
                    if (useTime <= 3) {
                        html += '<p class="btnUse using"><a>使用中</a>';
                    }
                    else {
                        if (cardCount > 0) {
                            html += '<p class="btnUse"><a href="javascript:void(0)" onclick="jQuery.item.useItem('+itemId+')">使用する</a>';
                        }
                        else {
                            html += '<p class="btnUse disabled"><a>使用する</a>';
                        }
                    }
                }
                else if (itemId == 10) {
                    if ( !canUse ) {
                        html += '<p class="btnUse disabled"><a>使用する</a>';
                    }
                    else {
                        if (cardCount > 0) {
                            html += '<p class="btnUse"><a href="javascript:void(0)" onclick="jQuery.item.useItem('+itemId+')">使用する</a>';
                        }
                        else {
                            html += '<p class="btnUse disabled"><a>使用する</a>';
                        }
                    }
                }
                else {
                    if (cardCount > 0) {
                        html += '<p class="btnUse"><a href="javascript:void(0)" onclick="jQuery.item.useItem('+itemId+')">使用する</a>';
                    }
                    else {
                        html += '<p class="btnUse disabled"><a>使用する</a>';
                    }
                }
                $('#li'+itemId+' .btnUseDiv').html(html);
            }
        }
        );
    },
    
    closeWindow : function(itemId, messsage)
    {   
        $('#overlay').html('');
        $('#overlay').hide();
       
        if (itemId >0 && itemId <= 10) {
            jQuery.dynamite.goUserDynamite($('#txtUid').val());
            
            if (itemId == 5 || itemId == 7 || itemId == 8 || itemId == 10) {
                jQuery.dynamite.updateBombBox($('#txtUid').val());
            }
            
            if (itemId == 10 && changeHitmanSuccess == 1) {
                jQuery.item.updateCharaBox();
            }
        }
        
        if (sendBomb) {
            sendBomb = 0;
            jQuery.dynamite.presentBomb();
        }
        $('#dynamiteBody').unbind('click');
        
    },
    
    createSystemErrorWindow : function()
    {
        var html = '';
        html += '<div id="overlayBox" class="error system" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent()">'
              + '<div id="overlayBoxInner">'
              + '   <h2>システムエラー</h2>'
              + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.item.closeWindow()"/></p>'
              + '   <p>'
              + '       システムエラーが発生しました。<br />'
              + '       このページを再度読み込み直してから、実行してください。'
              + '   </p>'
              + '</div>'
              + '</div>';
         
        $('#overlay').html(html);
        $('#overlay').show();
        $('#dynamiteBody').bind('click', function(){
            jQuery.common.close();
        });
    },
    
    createAjaxTimeOutErrorWindow : function()
    {   
        AJAX_COMPLETE = false;
        var html = '';
        html += '<div id="overlayBox" class="error communication" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent()">'
              + '<div id="overlayBoxInner">'
              + '   <h2>通信エラー</h2>'
              + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.item.closeWindow()"/></p>'
              + '   <p>'
              + '       通信エラーが発生しました。<br />'
              + '       インターネット接続状況を確認し、このページを再度読み込み直してください。'
              + '   </p>'
              + '</div>'
              + '</div>';
         
        $('#overlay').html(html);
        $('#overlay').show();
        $('#dynamiteBody').bind('click', function(){
            jQuery.common.close();
        });
    },

    getResultHtml : function(message, itemId)
    {
        var html = '';
        
        html += '<div id="overlayBox" class="useItem" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent()">'
              + '<div id="overlayBoxInner">'
              + '   <h2>アイテムを使う</h2>'
              + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.item.closeWindow('+itemId+', &quot;'+message+'&quot;)"/></p>'
              + '   <p> '+message+''
              + '   </p>'
			  + '</div>'
              + '</div>';
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    updateCharaBox : function()
    {
        var hitmanPicType = $('#hitmanPicType').val();
        
        var html = '<p>アイツ、爆破しちゃいましょう</p>'
                 + '<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/hitman/11'+hitmanPicType+'.gif" width="97" height="137" />';
                 
        $('#playingCharaArea').html(html);
        $('#playingCharaArea').removeClass();
        $('#playingCharaArea').addClass('chara11');
        $('#playingCharaArea').show();
    },
    
    changeModeCancel : function()
    {
        AJAX_COMPLETE = false;
        $('#overlay').html('');
        $('#overlay').hide();
    }
    
};

})(jQuery);
