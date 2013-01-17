/*
----------------------------------------------
Millionminds profile JavaScript

Created Date: 2009/07/27
Author: Liz

----------------------------------------------
*/

//function fillNature()
jQuery(function($) {
    //showNature([0,0,0,3,0]);
});

/*
 * array[morality,love,count,instinct,combine];
 * 0-5, please sequence into
 *
 */
function showNature(array)
{
    var position = [[[89,83],[93,89],[98,96],[102,102],[106,108],[111,114]],   //morality
                    [[86,71],[86,62],[86,54],[86,47],[86,39],[86,31]],         //love
                    [[80,83],[77,89],[73,95],[68,101],[63,107],[58,103]],      //count
                    [[78,74],[72,73],[63,71],[56,68],[48,67],[41,63]],         //instinct
                    [[91,76],[98,73],[105,71],[113,68],[120,66],[127,64]]];    //combine
                    
    var x = [position[0][array[0]][0],position[2][array[2]][0],position[3][array[3]][0],position[1][array[1]][0],position[4][array[4]][0]];
    var y = [position[0][array[0]][1],position[2][array[2]][1],position[3][array[3]][1],position[1][array[1]][1],position[4][array[4]][1]];
    
    var jg = new jsGraphics("divSelf");
    jg.setColor("#d1ff88");  //self:#ff8080  other:#d1ff88 
    jg.fillPolygon(x,y);
    jg.paint();
}

function topInvite() {
    gotoTop();
    invite();
}

(function($) {

//jquery scroll bar
var objRank;
var objRank1 = {
    scrollUl : '#rankList',
    rankCount : '#rankCount',
    lastUserRankNum : '#lastUserRankNum',
    navLeft : '#navLeft',
    navRight : '#navRight',
    rankPrev : '#rankPrev',
    moveSetp : 81,
    minWidth : 0,
    maxWidth : 0,
    pageSize : 7,
    ajaxComplete : false
};
var objRank2 = {
    scrollUl : '#rankListMixi',
    rankCount : '#rankCountMixi',
    lastUserRankNum : '#lastUserRankNumMixi',
    navLeft : '#navLeftMixi',
    navRight : '#navRightMixi',
    rankPrev : '#rankPrevMixi',
    moveSetp : 81,
    minWidth : 0,
    maxWidth : 0,
    pageSize : 7,
    ajaxComplete : false
};


/**
 * windows load function
 *  register funcion
 */
$().ready(function() {
    var answerCount = $('#answerCount').val();
    var nav = jQuery.millionmindscommon.showPageNav(Number(answerCount), Number($('#pageIndex').val()), Number(jQuery.profile.userAnswerPageSize), 10, 'jQuery.profile.changePageAction');
    $('#divAnswer').append(nav);
    
    jQuery.profile.initMoveBar(objRank1, 0, 0);
    jQuery.profile.initMoveBar(objRank2, 0, 0);
});

$.profile = {
    userAnswerPageSize : $('#pageSize').val(),
    userAnswerType : ['all','character','politics','life','entertainment','hobby'],
    
    /**
    * change page
    *
    */
    changePageAction : function(page, type, isFromCheckType)
    {
        window.location="#divAnswer";
        $('#pageIndex').val(page);
        
        if ( isFromCheckType != 1 ) {
            type = $('#userAnswerType').val();
        }
        
        $('#userAnswerType').val(type);
        
        var uid = $("#txtMindsUid").val();
        
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/millionminds/getuseranswer',
            dataType: "json",
            data : {uid : uid,
                    type : type,
                    pageIndex : page,
                    pageSize : jQuery.profile.userAnswerPageSize},
            timeout : 10000,
            success : function(response){
                jQuery.profile.showBanner(type);
                
                $('#arrayUserAnswer').remove();
                $('#divAnswer').children('.pager').remove();
                $('#divAnswer').children('.null').remove();
                
                if ( response.answerCount > 0 ) {
                    jQuery.profile.showUserAnswer(response.userAnswer, response.userInfo);
                    
                    var nav = jQuery.millionmindscommon.showPageNav(Number(response.answerCount), Number(page), Number(jQuery.profile.userAnswerPageSize), 10, 'jQuery.profile.changePageAction');
                    
                    $('#divAnswer').append(nav);
                }
                else {
                    jQuery.profile.showUserAnswerNull();
                }
                
                adjustHeight();
            },
            error : function(request, settings) {
                jQuery.profile.showBanner(type);
                
                if (settings == 'timeout') {
                    error = '<p>通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください。<p>';
                }
                else {
                    error = '<p>システムエラー。<p>';
                }
                
                $('#divAnswer').append(error);
            }
        });
    },
    
    /**
     * show banner html
     *
     */
    showBanner : function(type)
    {        
        $('#divAnswer').find('table.list').remove().end().find('p').remove().end()
                       .find('ul.cat > li > a.active').removeClass().end()
                       .find('ul.cat > li.' + jQuery.profile.userAnswerType[type] + ' > a').addClass("active");
    },
        
    /**
     * show user answer
     *
     */
    showUserAnswer : function(answer, userInfo)
    {
        var html = '<ul class="truth" id="arrayUserAnswer">';
        
        for ( i = 0, iCount = answer.length; i < iCount; i++ ) {
            var liClass = i%2 == 0 ? 'i' : 'ii';
            
            var imgAnswerType = '';
            var author = '';
            
            if ( answer[i].type == 1 ) {
                imgAnswerType = '<span class="chara">性格診断</span>';
            }
            else {
                if (answer[i].nickname_auth ==1) {
                    imgAnswerType = '<span><a href="' + UrlConfig.BaseUrl + '/millionminds/profile/uid/' + answer[i].question_uid + '" style="background-image: url(' + answer[i].thumbnailUrl + ');" title="' + answer[i].displayName + '">' + answer[i].displayName + '</a></span>';
                }
                else {
                    imgAnswerType = '<span class="secret">匿名希望</span>';
                }
            }
            
            if ( answer[i].type != 1 ) {
                if (answer[i].nickname_auth ==1) {
                    author = '<p class="user"><a href="' + UrlConfig.BaseUrl + '/millionminds/profile/uid/' + answer[i].question_uid + '">' + answer[i].displayName + '</a>さん</p>';
                }
                else {
                    author = '<p class="user">匿名希望さん</p>';
                }
            }
            
            html += '<li class="' + liClass + '">'
                  + '   <table width="100%" cellpadding="0" cellspacing="0" border="0">'
                  + '       <tr>'
                  + '           <th>' + imgAnswerType + '</th>'
                  + '           <td>' + author + '<p class="content stringCut" style="width:500px;"><strong><a href="' + UrlConfig.BaseUrl + '/millionminds/question/qid/' + answer[i].qid + '">' + answer[i].question.escapeHTML() + '</a></strong></p></td>'            
                  + '       </tr>'
                  + '       <tr>'
                  + '           <th><span><a href="' + UrlConfig.BaseUrl + '/millionminds/profile/uid/' + userInfo.uid + '" style="background-image: url(' + userInfo.thumbnailUrl + ');" title="' + userInfo.displayName + '">' + userInfo.displayName + '</a></span></th>'
                  + '           <td>'
                  + '               <p class="user">' + userInfo.displayName + '</p>'
                  + '               <p class="content"><strong>' + answer[i].answer.escapeHTML() + '</strong></p>'
                  + '           </td>'
                  + '       </tr>'
                  + '   </table>'
                  + '</li>';
        }

        html += '</ul><!--/.truth-->';
        
        $('#divAnswer').append(html);
    },
    
    /**
     * when answer is null,show image
     *
     */
    showUserAnswerNull : function()
    {
        var html = '<p class="null"><a href="' + UrlConfig.BaseUrl + '/millionminds/newquestion">クエスチョンを作成する</a></p>';
        $('#divAnswer').append(html);
    },
    
    /**
     * send nature request
     *
     */
    sendNatureRequest : function(type)
    {
        var activity;
        var message;
        if ( type == 1) {
            message = $('#txtMindsUserName').val() + 'さんに性格診断するよう、リクエストを送りました。';
            activityTitle = $('#txtMindsUserName').val() + 'に性格診断をリクエストしました。';
        }
        else {
            message = '性格診断してもらえるよう、マイミク全員にリクエストを送りました。';
            activityTitle = 'マイミクに性格診断をリクエストしました。';
        }
        
        var html = '<div id="overlay"></div>'
                 + '<div id="overWindow">'
                 + '    <h2>性格診断をリクエスト</h2>'
                 + '    <div class="inner">'
                 + '        <p>' + message + '</p>'
                 + '    </div><!--/.inner-->'
                 + '</div>';
        
        $('#fullOverlay').html(html);
        $('#fullOverlay').show();
        
        postActivity(activityTitle);
        
        //auto close after 5 sec
        jQuery.profile.autoClose(5, 'fullOverlay');
    },
    
    /**
     * auto close 
     */
    autoClose : function(secs, divId)
    {    
        if(--secs>0){
           autoCloseTimeOut = setTimeout("jQuery.profile.autoClose(" + secs + ",'" + divId + "')", 1000);
        }
        else{
           $('#' + divId).html('');
           $('#' + divId).hide();
        }
    },
    
    /**
     * move left one
     * 
     */
    leftOne : function(type)
    {
        if ( type == 1 ) {
            objRank = objRank1;
        }
        else {
            objRank = objRank2;
        }
        
        if ('disabled' == $(objRank.navLeft).attr("disabled")) {
            return;
        }
        
        var pos = jQuery.profile.measurePosition(objRank);
        var cntMax = Number($(objRank.rankCount).val());
        var lastUserRankNum = Number($(objRank.lastUserRankNum).val());
            
        if (pos < objRank.minWidth) {
            $(objRank.navLeft).attr("disabled","disabled");
            jQuery.profile.slide('left',objRank);
        }
        else if ( lastUserRankNum < cntMax )  {
            $(objRank.navLeft).attr("disabled","disabled");
            //get count size
            var cntSize = lastUserRankNum > (cntMax - objRank.pageSize) ? (cntMax - lastUserRankNum) : objRank.pageSize;
            
            jQuery.profile.getNextRankUser("left", cntSize, type);
        }
           
    },
    
    /**
     * move right one
     * 
     */
    rightOne : function(type)
    {            
        if ( type == 1 ) {
            objRank = objRank1;
        }
        else {
            objRank = objRank2;
        }
        
        if ('disabled' == $(objRank.navRight).attr("disabled")) {
            return;
        }
        
        var pos = jQuery.profile.measurePosition(objRank);
        var rankPrev = Number($(objRank.rankPrev).val());
        
        if (pos > objRank.maxWidth) {  
            $(objRank.navRight).attr("disabled","disabled");  
            jQuery.profile.slide('right',objRank);
        }
        else if ( rankPrev > 1 ) {
            $(objRank.navRight).attr("disabled", "disabled");
            //get count size
            var cntSize = rankPrev <= objRank.pageSize ? (rankPrev - 1) : objRank.pageSize;
            
            jQuery.profile.getNextRankUser("right", cntSize, type);
        }
    },
    
    /**
     * get next rank user info
     * 
     */
    getNextRankUser : function(direction, cntSize, type)
    {   
        var lastUserRankNum = Number($(objRank.lastUserRankNum).val());
        var rankCount = Number($(objRank.rankCount).val());
        var rankPrev = Number($(objRank.rankPrev).val());
        
        if (!objRank.ajaxComplete) {
            objRank.ajaxComplete = true;
            
            if ( type == 1 ) {
                var url = UrlConfig.BaseUrl + '/ajax/millionminds/getmorealluser';
            }
            else {
                var url = UrlConfig.BaseUrl + '/ajax/millionminds/getmoremymixi';
            }
            
            jQuery.ajax({
                type : "POST",
                url : url,
                dataType : "json",
                data : {lastUserRankNum : lastUserRankNum,
                        rankPrev : rankPrev,
                        rankCount : rankCount,
                        direction : direction
                       },
                success : function(response){
                    var html = jQuery.profile.showResult(response.rankInfo, direction);
                    
                    if (direction == 'left') {
                        $(objRank.lastUserRankNum).val(response.lastUserRankNum);
                        $(objRank.scrollUl).prepend(html);
                    }
                    else if (direction == 'right') {
                        $(objRank.rankPrev).val(response.rankPrev);
                        $(objRank.scrollUl).append(html);
                    }
                    
                    //move
                    if (direction == 'left') {
                        $(objRank.scrollUl).css('left', 0 + (-1) * cntSize * objRank.moveSetp);
                        
                        jQuery.profile.initMoveBar(objRank, 0, cntSize * objRank.moveSetp);
                        
                        jQuery.profile.slide('left',objRank);
                    }
                    else if (direction == 'right') {
                        jQuery.profile.initMoveBar(objRank, 0, cntSize * objRank.moveSetp);
                    
                        jQuery.profile.slide('right',objRank);
                    }
                    
                    objRank.ajaxComplete = false;
                }
            });
        }
    },
    
    /**
     * show result
     * 
     */
    showResult : function(info, direction)
    {
        var html = '';
        
        for (var i = 0; i < info.length; i++) {            
            html += '<li><span><a href="' + UrlConfig.BaseUrl + '/millionminds/profile/uid/' + info[i].uid + '" style="background-image: url(' + info[i].thumbnailUrl + ');" title="' + info[i].displayName + '"></a></span></li>';
        }
        return html;
    },
    
    /**
     * jquery measure postion
     * @param  object objStaticRank
     * @return integer
     */
    measurePosition : function (objStaticRank)
    {
        var nowPosition = $(objStaticRank.scrollUl).css('left');
        nowPosition = nowPosition.replace('px','');
        return nowPosition
    },
    
    /**
     * slide
     * 
     */
    slide : function(direction, objStaticRank, moveStep)
    {
        //move ul
        if(direction == 'left'){
            if (null == moveStep) {
                moveStep = objStaticRank.moveSetp;
            }
            $(objRank.scrollUl).animate({left : '+=' + moveStep},300, function() {
                $(objRank.navLeft).removeAttr("disabled");
            });
        } 
        else if(direction == 'right'){
            $(objRank.scrollUl).animate({left : '-=' + objStaticRank.moveSetp},300, function() {
                $(objRank.navRight).removeAttr("disabled");
            });
        }
    },
    
    /**
     * init move bar
     * 
     */
    initMoveBar : function(objStaticRank, leftAdd, rightAdd)
    {
        //get max width and min width
        if (0 == leftAdd && 0 == rightAdd) {
            var listSize = $(objStaticRank.scrollUl).find('li').size()-1;
            objStaticRank.maxWidth = objStaticRank.moveSetp * (listSize - objStaticRank.pageSize + 1 ) * (-1);
            objStaticRank.minWidth = 0;
            $(objStaticRank.scrollUl).css('left', '0');
        }
        else {
            objStaticRank.minWidth += leftAdd;
            objStaticRank.maxWidth -= rightAdd;   
        }
    }
};

})(jQuery);