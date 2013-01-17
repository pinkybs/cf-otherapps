/*
----------------------------------------------
Dynamite archives JavaScript

Created Date: 2009/07/06
Author: liz

----------------------------------------------
*/

var AJAX_COMPLETE = false;
var NEED_SELECT_HITMAN = 0;

(function($) {

//var isAlliance = false;
var canSetBomb = '1';
var canRemoveBomb = false;
var hadSetBomb = false;
var dynamiteHadSetBomb = false;
var canBomb = false;
var row = -1;
var userId = $('#txtUid').val();
var arrDynamiteInfo = evalJSON($('#txtArrDynamite').val());
var userDynamite = evalJSON($('#txtUserDynamite').val());
var userHitmanBomb = arrDynamiteInfo.current.hitmanBomb;
var userSetBomb = arrDynamiteInfo.current.setBomb;
var isFriend = arrDynamiteInfo.current.isFriend;
var showInfoFlag = 0;
var removeBombInfo = new Array();

var autoCloseTimeOut;
var isGoBack = false;
var isGoNext = false;
var isGoHome = true;
var isCurrent = false;
var ajaxLoad = true;
var canMove = true;
var hitmanHadBomb = '0';
var moveDirection = '';

var securityCode = 0;

$().ready(function() {
    adjustHeight();
    
    showInfoFlag = userDynamite.show_set_bomb;
    $.dynamite.goUserDynamite(userDynamite.uid);
    
    $.dynamite.autoRefresh();
});

$.dynamite = {
    
    /**
     * get dynamite html
     *
     */
     getDynamite : function()
     {        
        $.dynamite.updateDynamiteHeader(userDynamite);
        
        var html = '';
        
        html += $.dynamite.getDynamiteHtml(userDynamite, userHitmanBomb, userSetBomb, 1);
        
        return html;
     },
    
    /**
     * get dynamite html
     *
     */
    getDynamiteHtml : function(arrDynamite, arrHitmanBomb, arrUserSetBomb, currentType)
    {   
        if ( currentType == 1 ) {
            isCurrent = true;
        }
        
        var myGameMode = $('#gameMode').val();
        var html = '';
        
        //get background image by hitman type
        var backGround;
        if ( arrDynamite.pic_id < 10 ) {
            backGround = '0' + arrDynamite.pic_id;
        }
        else {
            backGround = arrDynamite.pic_id;
        }
        
        html += '<div class="inner" id="'+ userDynamite.uid +'" style="background-image: url(' + UrlConfig.StaticUrl + '/apps/dynamite/img/bg/' + backGround + '.png)">'
              + '   <ul id="personList"><!--';
              
        for ( i = 1; i < 5; i++ ) {
            arrDynamite['hitman_life' + i];
            
            //if is dead
            if ( arrDynamite['hitman_life' + i] == '0' ) {
                html += '--><li style="background-image: url(' + UrlConfig.StaticUrl + '/apps/dynamite/img/hitman/dead.gif)">'
                      + '</li><!--';
            }
            else {
                //get garment type
                var garmentType = 'a';
                if ( arrDynamite['hitman_life' + i] < (arrDynamite['max_life']/2) ) {
                    garmentType = 'b';
                }
                
                var picType;
                if ( arrDynamite.pic_id < 10 ) {
                    picType = '0' + arrDynamite.pic_id;
                }
                else {
                    picType = arrDynamite.pic_id;
                }
                
                var hitmanReward = Math.round(arrDynamite['bonus'] * 0.1 > 10000 ? 10000 : arrDynamite['bonus'] * 0.1);
                html += '--><li style="background-image: url(' + UrlConfig.StaticUrl + '/apps/dynamite/img/hitman/' + picType + garmentType + '.gif)">'
                      + '   <p class="price">$' + formatToAmount(hitmanReward) + '</p>'
                      + '   <p class="stamina"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/stamina/max' + arrDynamite['max_life'] + '/' + arrDynamite['hitman_life' + i] + '.gif" width="134" height="14" alt="" /></p>';
                
                //get user hitman bomb info
                if ( arrHitmanBomb ) {
                    html += '<ul class="bomb"><!--';
                        
                        
                        for ( m = 0, mCount = arrHitmanBomb.length; m < mCount; m++ ) {
                            if ( arrHitmanBomb[m].bomb_hitman == i ) {
                                var bombPicType = '';
                                if ( arrHitmanBomb[m].uid == userId ) {
                                    bombPicType = '_mine';
                                    
                                    bombPicType = arrHitmanBomb[m].bomb_power > 0 && arrHitmanBomb[m].needWait != 1 ? bombPicType + '_use' : bombPicType;
                                }
                                html += '--><li><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/dynamite/b/' + arrHitmanBomb[m].bomb_power + bombPicType + '.gif" width="23" height="32" alt="" /></li><!--';
                            }
                        }
                        
                    html += '--></ul>'
                          + '<ul class="bomber"><!--';
                          
                        for ( n = 0, nCount = arrHitmanBomb.length; n < nCount; n++ ) {
                            if ( arrHitmanBomb[n].bomb_hitman == i ) {
                                html += '--><li><a href="javascript:void(0);" onclick="jQuery.dynamite.goUserDynamite(' + arrHitmanBomb[n].uid + ');" style="background-image: url(' + arrHitmanBomb[n].miniThumbnailUrl + ')">[' + arrHitmanBomb[n].displayName + ']</a></li><!--';
                            }
                        }
                        
                    html += '--></ul>';
                }
                
                //check is my dynamite
                if ( arrDynamite.uid == userId ) {
                    //check can remove bomb
                    $.dynamite.checkCanRemoveBomb(arrHitmanBomb, i);
                    
                    if ( canRemoveBomb == '1' ) {
                        html += '<p class="btn_removal"><a href="javascript:void(0);" onclick="jQuery.dynamite.removeBomb(' + i + ')">ダイナマイト撤去</a></p>';
                        html += '<p id="removeBomb' + i + '" class="img_blasting" style="display:none;"></p>';
                    }
                    else if ( canRemoveBomb == '2' ) {
                        //html += '<p class="btn_removal"><a href="javascript:void(0);" onclick="jQuery.dynamite.showDynamiteMessage(-4);">ダイナマイト撤去</a></p>';
                        html += '<p class="disable btn_removal btn_removal_disable a"><a>ダイナマイト撤去</a></p>';
                    }
                    else {
                        //html += '<p class="disable btn_removal"><a href="javascript:void(0);">ダイナマイト撤去</a></p>';
                    }
                }
                else {
                    //check can set bomb 
                    $.dynamite.checkCanSetBomb(arrUserSetBomb, i);
                    
                    var enemyGameMode = arrDynamite.game_mode;
                    
                    if (myGameMode == 0 && enemyGameMode == 1 && !isFriend) {
                        canSetBomb = '5';
                    }
                    if (myGameMode == 1 && enemyGameMode == 0 && !isFriend) {
                        canSetBomb = '5';
                    }
                    if (myGameMode == 1 && enemyGameMode == 1 && !isFriend) {
                        canSetBomb = '5';
                    }
        
                    var btnSetupClass = '';
                    if ( hitmanHadBomb == '1' ) {
                        btnSetupClass = 'margin-top: 27px;';
                        
                    }
                    
                    if ( canSetBomb == '1' ) {
                        html += '<p class="btn_setup" style="' + btnSetupClass + '"><a href="javascript:void(0);" onclick="jQuery.dynamite.setBombType(' + i + ')">ダイナマイト設置</a></p>';
                    }
                    else if (hadSetBomb) {
                        if ( canBomb ) {
                            html += '<p class="btn_blasting"><a id="triggerBombBtn' + i + '" href="javascript:void(0);" onclick="jQuery.dynamite.triggerBomb(' + i + ')">ダイナマイト爆破</a></p>';
                            html += '<p id="triggerBomb' + i + '" class="img_blasting" style="display:none;"></p>';
                        }
                        else {
                            html += '<p class="disable btn_blasting btn_blasting_disable"><a>ダイナマイト爆破</a></p>';
                        }
                    }
                    else if ( canSetBomb == '3' ) {
                        //html += '<p class="disable btn_setup" style="' + btnSetupClass + '"><a href="javascript:void(0);" onclick="jQuery.dynamite.showDynamiteMessage(-5);" >ダイナマイト設置</a></p>';
                        html += '<p class="disable btn_setup btn_setup_disable" style="' + btnSetupClass + '"><a href="javascript:void(0);">ダイナマイト設置</a></p>';
                    }
                    else {
                        html += '<p class="disable btn_setup btn_setup_disable" style="' + btnSetupClass + '"><a href="javascript:void(0);">ダイナマイト設置</a></p>';
                    }
                }
                
                html += '</li><!--';
             }
        }
        
        html += '   --></ul><!--/#personList-->'
              + '</div><!--/.inner-->';
        
        return html;
        
    },
    
    /**
     * check user can set bomb
     *
     */
    checkCanSetBomb : function(arrUserSetBomb, hitmanId)
    {
        canSetBomb = '1';
        hitmanHadBomb = '0';
        
        var myRemainderBombCount = $('#txtMyRemainderBomb').val();
        
        $.dynamite.checkUserHadSetBomb(arrUserSetBomb, hitmanId);

        if ( hadSetBomb ) {
            canSetBomb = '2';
        }
        else if ( myRemainderBombCount == 0 ) {
            canSetBomb = '3';
        }
        else if ( userDynamite['hitman_bomb_count' + hitmanId] == 4 ) {
            canSetBomb = '4';
        }
        
        if ( userDynamite['hitman_bomb_count' + hitmanId] < 1 ) {
            hitmanHadBomb = '1';
        }
    },
    
    /**
     * check user can remove bomb
     *
     */
    checkCanRemoveBomb : function(arrHitmanBomb, hitmanId)
    {
        canRemoveBomb = false;
        
        if ( arrHitmanBomb ) {
            for ( b=0,bCount=arrHitmanBomb.length; b<bCount; b++ ) {
                if ( arrHitmanBomb[b].bomb_hitman == hitmanId && arrHitmanBomb[b].bomb_power > 0 ) {
                    canRemoveBomb = '1';
                    removeBombInfo[hitmanId] = arrHitmanBomb[b];
                    break;
                }
                else if ( arrHitmanBomb[b].bomb_hitman == hitmanId && arrHitmanBomb[b].bomb_power == 0 ) {
                    canRemoveBomb = '2';
                }
            }
        }
    },
    
    /**
     * check user had set bomb
     *
     */
    checkUserHadSetBomb : function(arrUserSetBomb, hitmanId)
    {
        hadSetBomb = false;
        canBomb = false;
        
        if ( arrUserSetBomb ) {
            //check this hitman is had set bomb
            for ( b=0,bCount=arrUserSetBomb.length; b<bCount; b++ ) {
                //this hitman had set bomb
                if ( arrUserSetBomb[b].bomb_hitman == hitmanId ) {
                    hadSetBomb = true;
                    
                    if ( isCurrent ) {
                        dynamiteHadSetBomb = true;
                    }
                    
                    if ( arrUserSetBomb[b].canBomb == 1 && arrUserSetBomb[b].needWait != 1 ) {
                        canBomb = true;
                    }
                    break;
                }
            }
        }
    },
    
    /**
     * go to user dynamite by uid
     */
    goUserDynamite : function(uid, topLocation, goNext, status, pic, powerTime, showInfoFlag)
    {         
        ajaxLoad = false;
        $("#overlay").html('');
        $("#overlay").hide();
        
        var url = UrlConfig.BaseUrl + '/ajax/dynamite/getuserdynamite';
        
        $.ajax({
             type: "POST",
             url: url,
             data: {uid : uid,
                    moveDirection : moveDirection
                    },
             timeout : 5000,
             error : function () {
                canMove = true;
             },
             success: function(response){
                //eval json
                var responseObject = evalJSON(response);
                
                jQuery.dynamite.showGoUserDynamiteResult(responseObject, topLocation, goNext);
                
                if ( status == 1 && showInfoFlag == 0) {
                    var picUrl = UrlConfig.StaticUrl + '/apps/dynamite/img/hitman/animation/' + pic + '.gif';
                    setTimeout(function(){$.dynamite.showDynamitePicMessage(1, picUrl, powerTime);}, 900);
                }
                else if (showInfoFlag == 1) {
                    AJAX_COMPLETE = false;
                }
            }
        });
        
    },
    
    /**
     * go user dynamite, show result
     */
    showGoUserDynamiteResult : function(responseObject, topLocation, goNext)
    {
        userDynamite = responseObject.current.dynamite;
        userHitmanBomb = responseObject.current.hitmanBomb;
        userSetBomb = responseObject.current.setBomb;
        isFriend = responseObject.current.isFriend;
        var nextUid = responseObject.nextUid;
        
        $('#currentUid').val(nextUid);
        
        
        if ( userDynamite['hitman_count'] == 0 && goNext ) {
            canMove = true;
            if ( goNext == 1 ) {
                $.dynamite.goNext();
            }
            else {
                $.dynamite.goBack();
            }
        }
        
        else {
            dynamiteHadSetBomb = false;
            
            $.dynamite.updateDynamiteHeader(userDynamite);

            var html = $.dynamite.getDynamite();
            
            var width = '';
            var direction = '';
            if ( isGoBack ) {
                width = '687';
                $('#playAreaWrap').prepend(html);
                direction = 'left';
            }
            else if ( isGoNext ) {
                var width = '0';
                $('#playAreaWrap').append(html);
                direction = 'right';
            }
            else {
                width = '0';
                $('#playAreaWrap').html(html);
                direction = 'home';
            }
            
            $('#playAreaWrap').css('left','-' + width + 'px');
            
            //var firstChildId = $('#playAreaWrap')[0].childNodes[0] ? $('#playAreaWrap')[0].childNodes[0].id : '';
            var playAreaWrapChildren = $('#playAreaWrap').children('.inner');
            var firstChildId = playAreaWrapChildren[0].id;
            var lastChildId = playAreaWrapChildren[playAreaWrapChildren.length - 1].id;
            
            dynamiteSlideNavOperate(direction, firstChildId, lastChildId, userDynamite.uid);
                              
            isGoBack = false;
            isGoNext = false;
            isGoHome = true;
            ajaxLoad = true;
            //canMove = true;
            setTimeout(function(){canMove = true;}, 800);
            
            if (topLocation == 1) {
                if ( null != getCookie('app_top_url_dynamite') ) {
                    top.location.href = getCookie('app_top_url_dynamite') +  '#pagetop';            
                }
            }
            
            moveDirection = '';
        }
    },
    
    /**
     * update dynamite header info
     */
    updateDynamiteHeader : function(userDynamite)
    {        
        var gameMode = userDynamite.game_mode;
        var modeName = '';
        if (gameMode == 1) {
            modeName = 'マイミクモード';
        }
        else {
            modeName = '全体モード';
        }
        
        var html = '';
        html += '<h1>' + userDynamite.displayName + '組のアジト</h1>';
        
        if (gameMode == 1) {
            html += '<p class="selectedMode">'+modeName+'</p>';
        }
        
        html += '<p class="pic" style="background-image: url(' + userDynamite.miniThumbnailUrl + ')">' + userDynamite.displayName + '</p>'
              + '<p class="price">所持金：$&nbsp;' + formatToAmount(userDynamite.bonus) + '</p>';
                 
        $('#mainHdr').html(html);
    },
    
    /**
     * update bomb box
     */
    updateBombBox : function(uid)
    {
        var url = UrlConfig.BaseUrl + '/ajax/dynamite/getuserbomb';

        $.ajax({
             type: "POST",
             url: url,
             timeout : 5000,
             error : function () {
             },
             success: function(response){
                //eval json
                var responseObject = evalJSON(response);
                
                jQuery.dynamite.showUpdateBombBoxResult(responseObject);
             }
        });
    },
    
    /**
     * show result
     */
    showUpdateBombBoxResult : function(responseObject)
    {
        //update remainder bomb count
        $('#txtMyRemainderBomb').val(responseObject.allRemainderCount);
        
        var html = '<ul><!--';
        
        if (responseObject.userBomb) {
            for ( j=0,jcount=responseObject.userBomb.length; j<jcount; j++ ) {
                var bombPicType = '_mine';
                
                bombPicType = responseObject.userBomb[j].bomb_power > 0 && responseObject.userBomb[j].needWait != 1 ? bombPicType + '_use' : bombPicType;
                
                html += '--><li class="bomb' + responseObject.userBomb[j].bomb_power + '"><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite(' + responseObject.userBomb[j].bomb_uid + ')"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/dynamite/b/' + responseObject.userBomb[j].bomb_power + bombPicType + '.gif" width="23" height="32" alt="' + responseObject.userBomb[j].bomb_power + '" /></a><span><nobr>' + responseObject.userBomb[j].displayName + '</nobr></span></li><!--';
            }
        }
        
        
        for ( m = 0, mCount = responseObject.userRemoveBomb.length; m < mCount; m++ ) {
            html += '--><li class="bomb' + responseObject.userRemoveBomb[m].bomb_power + '"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/dynamite/b/' + responseObject.userRemoveBomb[m].bomb_power + '_not.gif" width="23" height="32" alt="" /></li><!--';
        }
        
        for ( k=0; k<responseObject.remainderBombCount; k++ ) {
            html += '--><li class="bomb0"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/dynamite/b/nomal.gif" width="23" height="32" alt="" /></li><!--';
        }
        
        for ( l=0; l<responseObject.emptyBombCount; l++ ) {
            html += '--><li><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/dynamite/b/null.gif" width="23" height="32" alt="" /></li><!--';
        }
        
        html += '--></ul>';
        
        $('#bombListBox').html(html);
        
        $('#bombListBox ul li[class^="bomb"] a').hover(
            function(){
                $(this).next().fadeIn(150);
            },
            function(){
                $(this).next().fadeOut(200);
            }
        )
    },
    
    /**
     * go to back user dynamite
     */
    goBack : function()
    {
        if ( !canMove ) {
            return;
        }
 
        canMove = false;
        
        isGoBack = true;
        moveDirection = 'back';
        $.dynamite.goUserDynamite($('#currentUid').val(), 0, 1);
    },

    /**
     * go to next user dynamite
     */
    goNext : function()
    {
        if ( !canMove ) {
            return;
        }
        
        canMove = false;
        
        isGoNext = true;
        moveDirection = 'next';
        $.dynamite.goUserDynamite($('#currentUid').val(), 0, 1);
    },

    /**
     * go to my dynamite
     */
    goHome : function()
    {
        row = -1;
        isGoHome = true;
        $.dynamite.goUserDynamite(userId);
    },
    
    
    /**
     * set bomb type
     */
    setBombType : function(hitmanId)
    {   
        $("#isChecked").val('');
       
        if ( ajaxLoad ) {
            ajaxLoad = false;
            var url = UrlConfig.BaseUrl + '/ajax/dynamite/setbomb';
    
            $.ajax({
                 type: "POST",
                 url: url,
                 data: {bombUid : userDynamite.uid,
                        bombHitman : hitmanId},
                 timeout : 5000,
                 error : function () {
                    jQuery.dynamite.showDynamiteMessage(-6);
                 },
                 success: function(response){ $.dynamite.renderResults_setBomb(response, showInfoFlag); }
            });
        }
        
    },
    
    /**
     * callback funtion when success
     *  set the callback data to the showArea of the page
     *
     * @param string response
     * @return void
     */
    renderResults_setBomb : function(response, showInfoFlag)
    {   
        var responseObject = evalJSON(response);
        
        if ( responseObject.status == 1) {
            $.dynamite.updateBombBox(userId);
            $.dynamite.goUserDynamite(userDynamite.uid, '', '', 1, responseObject.hitman_pic, responseObject.power_time, showInfoFlag);
            
            clearTimeout(autoCloseTimeOut);
        }
        else if ( responseObject.status == 2 ) {
            $.dynamite.showDynamiteMessage(3);
        }
        else if ( responseObject.status == 3 ) {
            //jQuery.init.sendUserBomb();
            $('#sendBombAfterConfiscate').val(1);
            $.dynamite.showDynamiteMessage(3);
        }
        else if ( responseObject.status == -2 ) {
            //set bomb user have game over
            jQuery.init.restartGame();
        }
        else if ( responseObject.status == -10 ){
            jQuery.dynamite.createSecurityCodeWindow();
        }
        else {
            $.dynamite.showDynamiteMessage(-1);
        }
        
        ajaxLoad = true;
        
    },
    
    /**
     * sumbit trigger bomb
     */
    triggerBomb : function(hitmanId)
    {   
        $('#autoSendBomb').val('');
        
        for ( m=1; m<5; m++ ) {
            if ( $('#triggerBombBtn' + m) ) {
                $('#triggerBombBtn' + m).removeAttr('onclick');
            }
        }
            
        ajaxLoad = false;
        
        var url = UrlConfig.BaseUrl + '/ajax/dynamite/triggerbomb';

        $.ajax({
             type: "POST",
             url: url,
             data: {bombUid : userDynamite.uid,
                    bombHitman : hitmanId
                    //userHitmanBomb : toJSON(userHitmanBomb)
                    },
             timeout : 5000,
             error : function () {
                jQuery.dynamite.showDynamiteMessage(-6);
             },
             success: function(response){
                
                var responseObject = evalJSON(response);
                
                var data = new Date();
                var bombImgUrl = UrlConfig.StaticUrl + '/apps/dynamite/img/explode.gif?' + data.getTime();
                
                if (responseObject.autoSendBomb) {
                    $('#autoSendBomb').val(responseObject.autoSendBomb);
                }
                if ( responseObject.status == 1 ) {
                    $('#overlay1').show();
                    
                    $('#triggerBomb' + responseObject.bomb_hitman).show();
                    $('#triggerBomb' + responseObject.bomb_hitman).css('background-image', 'url('+bombImgUrl+')').css('background-repeat','no-repeat').css('background-position','center');
                    
                    //after 3sec
                    setTimeout(function(){
                        //present bomb
                        if ( responseObject.presentBomb == 1 ) {
                            $('#overlay1').hide();
                            $.dynamite.showDynamiteMessage(2, 1, Number(responseObject.hitmanRemainderSelf), Number(responseObject.bombPower));
                        }
                        else {
                            $('#overlay1').hide();
                            $.dynamite.showDynamiteMessage(2, '', Number(responseObject.hitmanRemainderSelf), Number(responseObject.bombPower));
                        }
                    }, 2000)
                }
                else if ( responseObject.status == 2 ) {
                    NEED_SELECT_HITMAN = responseObject.selectHitman;
                    
                    $('#overlay1').show();
                    
                    $('#triggerBomb' + responseObject.bomb_hitman).css('background-image', 'url('+bombImgUrl+')').css('background-repeat','no-repeat').css('background-position','center');
                    
                    $('#triggerBomb' + responseObject.bomb_hitman).show();
                    
                    jQuery.rank.otherTypeRank(Number($('#rankType').val()), Number($('#rankRange').val()));
                    
                    //after 3sec
                    setTimeout(function(){
                        jQuery.item.refreshCardCount(responseObject.sendCid);
                        
                        //present bomb
                        if ( responseObject.presentBomb == 1 ) {
                            $('#overlay1').hide();
                            jQuery.init.confirmGift(responseObject.sendCid, userDynamite.uid, 1, 1, responseObject.getBonus);
                        }
                        else {
                            $('#overlay1').hide();
                            jQuery.init.confirmGift(responseObject.sendCid, userDynamite.uid, 0, 1, responseObject.getBonus);
                        }
                    }, 2000)
                }
                else if ( responseObject.status == -2 ) {
                    $.dynamite.showDynamiteMessage(-2);
                }
                else if ( responseObject.status == -3 ) {
                    jQuery.init.restartGame();
                }
                else if ( responseObject.status == -4 ) {
                    $.dynamite.showDynamiteMessage(-3);
                }
                else if ( responseObject.status == -10 ) {
                    $.dynamite.createSecurityCodeWindow();
                }
                else {
                    $.dynamite.showDynamiteMessage(-1);
                }
                
                ajaxLoad = true;
             }
        });
  
    },
    
    /**
     * remove bomb submit
     */
    removeBomb : function(hitmanId)
    {
        if ( ajaxLoad ) {
            ajaxLoad = false;
            
            var url = UrlConfig.BaseUrl + '/ajax/dynamite/removebomb';
    
            $.ajax({
                 type: "POST",
                 url: url,
                 data: {bombUid : userDynamite.uid,
                        bombHitman : hitmanId
                        //bombType : bombType,
                        //removeBombInfo : toJSON(removeBombInfo[hitmanId]),
                        //userHitmanBomb : toJSON(userHitmanBomb)
                        },
                 timeout : 5000,
                 error : function () {
                    jQuery.dynamite.showDynamiteMessage(-6);
                 },
                 success: function(response){
                    var responseObject = evalJSON(response);
                    
                    //remover bomb success
                    if ( responseObject.status == 1 ) {
                        
                        var removeBombMsg = '';
                        if ( responseObject.removeBombCount > 1 ) {
                            removeBombMsg = responseObject.removeMaxBombName + '組らが仕掛けたダイナマイトを撤去し、未使用ダイナマイトを' + responseObject.removeBombCount + 'つゲットしました！';
                        }
                        else {
                            removeBombMsg = responseObject.removeMaxBombName + '組が仕掛けたダイナマイトを撤去し、未使用ダイナマイトを' + responseObject.removeBombCount + 'つゲットしました！';
                        }
                        
                        var html = '<div id="overlayBox" class="remove success" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent(1, 0)">'
                                 + '<div id="overlayBoxInner">'
                                 + '    <h2>ダイナマイト爆破</h2>'
                                 + '    <p class="btnClose"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.dynamite.removeOverlay()"/></p>'
                                 + '    <p class="pic_item"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/item/b/dynamite.gif" width="90" height="90" alt="" /></p>'
                                 + '    <div class="floatBox">'
                                 + '        <p>'
                                 + '            <strong>' + removeBombMsg + '</strong>'
                                 + '        </p>'
                                 + '    </div><!--/.floatBox-->'
    							 + '</div>'
                                 + '</div><!--/#overlayBox-->';
                                                    
                        $('#overlay').html(html);
                        $('#overlay').show(); 
                        
                        $('#txtMyRemainderBomb').val(responseObject.remainderBombCount);
                        
                        setTimeout(function(){
                            $('#dynamiteBody').bind('click', function(){
                                jQuery.dynamite.removeOverlay();
                            });
                        }, 500);
                        //auto close after 5 sec
                       // $.dynamite.autoClose(5, 'overlay');
                    }
                    else if ( responseObject.status == -10 ) {
                        $.dynamite.createSecurityCodeWindow();
                    }
                    else {
                        $.dynamite.showDynamiteMessage(-1);
                    }
                    
                    ajaxLoad = true;
                 }
            });
        }
    },
    
    /**
     * if user's bomb count=0, send 4 bombs to user
     */
    presentBomb : function()
    {
        $('#dynamiteBody').bind('click', function(){
            $.dynamite.removeOverlay();
        });
        
        var html = '';
        html += '<div id="overlayBox" class="addDynamite" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent(1, 0)">'
              + '<div id="overlayBoxInner">'
              + '   <h2>ダイナマイト追加</h2>'
              + '   <p class="btnClose"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.dynamite.removeOverlay()"/></p>'
              + '   <p>ダイナマイトをすべて消費したため、新しいダイナマイトが' + $('#autoSendBomb').val() + 'つ支給されました。</p>'
              + '   <ul class="bombList">'
              + '       <li><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/img_present_bomb.png" width="213" height="74" alt="" /></li>'
              + '   </ul>'
			  + '</div>'
              + '</div>';
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    /**
     * show dynamite message
     */
    showDynamiteMessage : function(status, type, remainBlood, bombPower)
    {    
         
        if ( !type ) {
            type = 0;
        }
        
        var message = '';
        var title = '';
        var classType = 'set';

        if ( status == -1 ) {
            message = 'システムエラーが発生しました。<br/>このページを再度読み込み直してから、実行してください。';
            title = 'ダイナマイト';
            classType = '';
        }
        else if ( status == -2 ) {
            message = 'どうやら他のヒットマンに先を越されたようです。';
            title = 'ダイナマイト爆破';
            classType = 'explode';
        }
        else if ( status == -3 ) {
            message = '設置後、5分未満のダイナマイトは爆破できません。';
            title = 'ダイナマイト爆破';
            classType = 'explode';
        }
        else if ( status == -4 ) {
            message = '設置後、5分未満のダイナマイトは撤去できません。';
            title = 'ダイナマイト撤去';
        }
        else if ( status == -5 ) {
            message = '未使用ダイナマイトがありません。<br />※設置ダイナマイトをすべて爆破させると、新しいダイナマイトが4つ支給されます。';
            title = 'ダイナマイト設置';
            classType = 'set';
        }
        else if ( status == -6 ) {
            message = '通信エラーが発生しました。<br />インターネット接続状況を確認し、このページを再度読み込み直してください。';
            title = '通信エラー';
        }
        else if ( status == 1 ) {
            message = 'ダイナマイトの設置が完了しました。<br />※設置から5分後に爆破することができますが、<strong style="color:#c00;">待てば待つほど与えられるダメージが大きく</strong>なります。';
            title = 'ダイナマイト設置';
            classType = 'set';
        }
        else if ( status == 2 ) {
            message = userDynamite.displayName + '組のヒットマンに' + bombPower + 'のダメージを与えました。<br />※懸賞金を獲得するには、あと' + remainBlood + 'のダメージを与える必要があります。';
            title = 'ダイナマイト爆破';
            classType = 'explode';
        }
        else if ( status == 3 ) {
            message = 'ダイナマイトほいほいが仕掛けられていたため、設置したダイナマイトが没収されてしまいました！';
            title = 'ダイナマイト設置';
            classType = 'set';
        }
        
        var html = '<div id="overlayBox" class="' + classType + '" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent(1, ' + type + ', ' + status + ')">'
                 + '<div id="overlayBoxInner">'
                 + '    <h2>' + title + '</h2>'
                 + '    <p class="btnClose"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.dynamite.removeOverlay(' + type + ')"/></p>'
                 + '    <p>' + message + '</p>'
				 + '</div>'
                 + '</div><!--/#overlayBox-->';
        
        $('#overlay').html(html);
        $('#overlay').show();
        
        setTimeout(function(){
            $('#dynamiteBody').bind('click', function(){
                $.dynamite.removeOverlay(type, status);
            });
          }, 1500)
        
    },
    
    /**
     * show dynamite message,with pic
     */
    showDynamitePicMessage : function(status, picUrl, powerTime)
    {
        
        $('#dynamiteBody').bind('click', function(){
            $.dynamite.removeOverlay(0 ,1);
        });
        
        var message = '';
        var title = '';
        
        if ( status == 1 ) {
            message = 'ヒットマンがダイナマイトを仕掛けました。<br /><strong>※'+ powerTime +'分後に爆破できますが、待てば待つほど、相手のヒットマンに与えられるダメージが大きくなります。</strong>'
                    +  '<div><input id="chk" name="chk" type="checkbox" style="vertical-align: middle;" onclick="jQuery.dynamite.addCheckAttr()"/> 次回からこのメッセージを表示しない</div>';
            title = 'ダイナマイト設置';
        }
        
        var html;

        html += '<div id="overlayBox" class="set" onmouseover="jQuery.common.removeClickEvent()" onmouseout="jQuery.common.addClickEvent(1, 0, '+status+')">'
              + '<div id="overlayBoxInner">'
              + '   <h2>' + title + '</h2>'
              + '   <p class="btnClose"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.dynamite.removeOverlay(0, '+ status +')"/></p>'
              + '   <p class="pic_item"><img src="' + picUrl + '" width="90" height="90" alt="" /></p>'
              + '   <div class="floatBox">'
              + '       <p>' + message + '</p>'
              + '   </div>'
              + '</div>'
              + '</div>';
              
        $('#overlay').html(html);
        $('#overlay').show();
        //auto close after 5 sec
        //$.dynamite.autoClose(5, 'overlay');
    },
    
    /**
     * remove overlay
     */
    removeOverlay : function(type, status)
    {  
        $('#overlay').html('');
        $('#overlay').hide();
        
        if ( type == 1 ) {
            $.dynamite.presentBomb();
        }
        else {
            if (status != 1) {
                //$.dynamite.updateBombBox(userId);
                //$.dynamite.goUserDynamite(userDynamite.uid);
                jQuery.dynamite.autoRefreshUserInfo();
                clearTimeout(autoCloseTimeOut);
            }
        }
        
        $('#dynamiteBody').unbind('click');
        //user's bomb been confiscated
        if (status == 3 && $('#sendBombAfterConfiscate').val() == 1) {
            jQuery.init.sendUserBomb(userDynamite.uid);
        }
        //no show set bomb message
        if($("#isChecked").val() == 1) {
            jQuery.dynamite.updateBombFlag();
        }
        //if user's bonus >= 1000, need select hitman
        if (!type && !status && NEED_SELECT_HITMAN == 1) {
            jQuery.init.selectHitman();
        }
        $('#sendBombAfterConfiscate').val('');
        AJAX_COMPLETE = false;
    },
    
    /**
     * auto close 
     */
    autoClose : function(secs, divId)
    {    
        if(--secs>0){
           autoCloseTimeOut = setTimeout("jQuery.dynamite.autoClose(" + secs + ",'" + divId + "')", 1000);
        } 
        else{
           $('#' + divId).html('');
           $('#' + divId).hide();
           
           $.dynamite.updateBombBox(userId);
           $.dynamite.goUserDynamite(userDynamite.uid);
        }
    },
    
    /**
     * auto refresh 
     */
    autoRefresh : function()
    {
        //after 5min
        setTimeout(function(){
            if ( !$('#overlay').html() ) {
                //jQuery.dynamite.updateBombBox(userId);
                //jQuery.dynamite.goUserDynamite(userDynamite.uid);
                jQuery.dynamite.autoRefreshUserInfo();
            }
            
            $.dynamite.autoRefresh();
        }, 5*60*1000)
    },
    
    addCheckAttr : function()
    {   
        if ($('#chk').attr("checked") == true) {
            $("#isChecked").val(1);
        }
        else {
            $("#isChecked").val(0);
        }
    },
    
    updateBombFlag : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/updateshowsetbombinfoflag',
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                showInfoFlag = 1;
                $('#isChecked').val('');
                AJAX_COMPLETE = false;
            }
        });
    },
    
    autoRefreshUserInfo : function()
    {
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/autorefreshuserinfo',
            data : {targetUid : userDynamite.uid},
            dataType : "json",
            timeout : 5000,
            error : function() {
                //jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                var bombInfo = response.bombInfo;
                var userInfo = response.userInfo;
                
                jQuery.dynamite.showUpdateBombBoxResult(bombInfo);
                jQuery.dynamite.showGoUserDynamiteResult(userInfo);
               
            }
        });
    },
    
    createSecurityCodeWindow : function()
    {
        securityCode = Math.floor((Math.random() + 1) * 1000);
       
        var html = '';

        html += '<div id="overlayBox">'
              + '<div id="overlayBoxInner">'
              + '   <h2>画像認証</h2>'
              + '   <div class="floatBox">'
              + '       <p>&nbsp;&nbsp;画像認証: '+securityCode+'</p>'
              + '       <p>&nbsp;&nbsp;数字を入力してください</p>'
              + '       &nbsp;&nbsp;<input type="text" name="textSecurityCode" id="textSecurityCode" maxlength=4>'
              + '       <input type="button" name="submit1" value="確認" onclick="jQuery.dynamite.checkSecurityCode()">'
              + '   </div>'
              + '</div>'
              + '</div>';
              
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    checkSecurityCode : function()
    {
        if (securityCode == $('#textSecurityCode').val()) {
            $('#overlay').html('');
            $('#overlay').hide();
        }
        else {

            securityCode = Math.floor((Math.random() + 1) * 1000);
            
            var html = '';

            html += '<div id="overlayBox" class="set">'
                  + '<div id="overlayBoxInner">'
                  + '   <h2>画像認証</h2>'
                  + '   <div class="floatBox">'
                  + '       <font color="red">&nbsp;&nbsp;もう一度入力してください</font>'
                  + '       <p>&nbsp;&nbsp;画像認証: '+securityCode+'</p>'
                  + '       <p>&nbsp;&nbsp;数字を入力してください</p>'
                  + '       &nbsp;&nbsp;<input type="text" name="textSecurityCode" id="textSecurityCode" maxlength=4>'
                  + '       <input type="button" name="submit1" value="確認" onclick="jQuery.dynamite.checkSecurityCode()">'
                  + '   </div>'
                  + '</div>'
                  + '</div>';
                  
            $('#overlay').html(html);
            $('#overlay').show();
        }
        
        jQuery.dynamite.autoRefreshUserInfo();
    }

};

})(jQuery);