

(function($) {

var CONST_DEFAULT_PAGE_SIZE = 5;

//jquery scroll bar
var objRank2 = {
    scrollFrame : '#memberFrame2',
    innaerFrame : '#innaerFrame2',
    moveSetp : 89,
    minWidth : 0,
    maxWidth : 0
};

$().ready(function() {
    jQuery.rank.initMoveBar(objRank2, 0, 0);
});

$.rank = {

    //move left one
    leftOne : function()
    {
       
        if ('disabled' == $('#navLeft').attr("disabled")) {
            return;
        }
        
        var pos = jQuery.rank.measurePosition(objRank2);
      
        if (pos < objRank2.minWidth) {
            $('#navLeft').attr("disabled","disabled");
            jQuery.rank.slide('left',objRank2);
            $('#navMaxRight').removeAttr("disabled");
        }
        else {
            var cntMax = parseInt($('#rankCount').val());
            var lastUserRankNum = parseInt($('#lastUserRankNum').val());
            
            if (lastUserRankNum < cntMax) {
                $('#navLeft').attr("disabled","disabled");
                var end = (parseInt($('#lastUserRankNum').val()) + CONST_DEFAULT_PAGE_SIZE);
                var cntSize = end > cntMax ? (cntMax - parseInt($('#lastUserRankNum').val())) : CONST_DEFAULT_PAGE_SIZE;
                var start = parseInt($('#lastUserRankNum').val()) + 1;
                var end = end > cntMax ? cntMax : end;
                
                jQuery.rank.getNextRankUser("left", cntSize);
                $('#navMaxRight').removeAttr("disabled");
            }
        }
        
    },
    
    //move right one
    rightOne : function()
    {
      
        if ('disabled' == $('#navRight').attr("disabled")) {
            return;
        }
        var pos = jQuery.rank.measurePosition(objRank2);
       
        if (pos > objRank2.maxWidth) {  
            $('#navRight').attr("disabled","disabled");        
            jQuery.rank.slide('right',objRank2);
            $('#navMaxLeft').removeAttr("disabled");
        }
        else {
            if (parseInt($('#rankPrev').val()) > 2) {
                $('#navRight').attr("disabled","disabled"); 
                var start = (parseInt($('#rankPrev').val()) - CONST_DEFAULT_PAGE_SIZE);
                var cntSize = start<3 ? (start + 3) : CONST_DEFAULT_PAGE_SIZE;
                start = start > 3 ? start : 3;
                var end = $('#lastUserRankNum').val();
            
                jQuery.rank.getNextRankUser("right", cntSize);
                
                $('#navMaxLeft').removeAttr("disabled");
            }
        }
  
    },
    
    getNextRankUser : function(direction, cntSize)
    {   
        var lastUserRankNum = Number($('#lastUserRankNum').val());
        var oldLastUserRankNum = lastUserRankNum;
        var rankCount = Number($('#rankCount').val());
        var rankPrev = Number($('#rankPrev').val());
        var oldRankPrev = rankPrev;
        
        if (!AJAX_COMPLETE) {
            AJAX_COMPLETE = true;
            
            jQuery.ajax({
                type : "POST",
                url : UrlConfig.BaseUrl + '/ajax/dynamite/getnextuser',
                dataType : "json",
                data : {lastUserRankNum : lastUserRankNum,
                        rankPrev : rankPrev,
                        rankCount : rankCount,
                        direction : direction,
                        type : Number($('#rankType').val()),
                        range : Number($('#rankRange').val())
                       },
                timeout : 5000,
                error : function() {
                    jQuery.item.createAjaxTimeOutErrorWindow();
                },
                success : function(response){
                    if ($('#rankCount').val() != response.rankCount) {
                        $('#rankCount').val(response.rankCount);
                    }
                    var html = jQuery.rank.showResult(response, direction, oldLastUserRankNum, oldRankPrev);
                    
                    if (direction == 'left') {
                        $('#lastUserRankNum').val(response.lastRankNum);
                        $('#userList').prepend(html);
                    }
                    else if (direction == 'right') {
                        $('#rankPrev').val(response.rankPrev);
                        $('#userList').append(html);
                    }
                    //move
                    if (direction == 'left') {
                        $(objRank2.innaerFrame).css('left', 0 + (-1) * cntSize * objRank2.moveSetp);
                        
                        jQuery.rank.initMoveBar(objRank2, 0, cntSize * objRank2.moveSetp);
                        
                        jQuery.rank.slide('left',objRank2);
                    }
                    else if (direction == 'right') {
                        jQuery.rank.initMoveBar(objRank2, 0, cntSize * objRank2.moveSetp);
                    
                        jQuery.rank.slide('right',objRank2);
                    }
                    
                    AJAX_COMPLETE = false;
                }
            }
            );
            
        }
    },
    
    showResult : function(response, direction, oldLastUserRankNum, oldRankPrev)
    {
        var html = '';
        var info = response.info;
        var rankRange = Number($('#rankRange').val());
        
        for (var i = 0; i < info.length; i++) {
            var rankNum;
            if (direction == 'left') {
                rankNum = oldLastUserRankNum + info.length - i;
            }
            else if (direction == 'right') {
                rankNum = oldRankPrev - i - 1;
            }
            
            html += '<li id="'+info[i].uid+'">'
                  + '   <p class="number">'+rankNum+'</p>'
                  + '   <p class="name"><nobr><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+info[i].uid+')">'+info[i].displayName+'</a></nobr></p>'
                  + '   <p class="pic"><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+info[i].uid+')" style="background-image: url('+info[i].thumbnailUrl+')"></a></p>';
            if (rankRange == 1) {
                html += '   <p class="price">'+info[i].reward+'</p>';
            }
            else {
                html += '   <p class="price">'+info[i].bonus+'</p>';
            }
            if (info[i].online == 1) {
                html += '<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/icon/warning.gif" width="16" height="16" id="loginPlayer" alt="" />';
            } 
            html += '</li>'; 
              
        }
        return html;
    },
    
    //マイミクシィ
    otherTypeRank : function(rankType, rankRange)
    {
      
        if (rankType == null || rankType == '') {
            rankType = Number($('#rankType').val());
        }
        if (rankRange == null || rankRange == '') {
            rankRange = Number($('#rankRange').val());
        }
        if (!AJAX_COMPLETE) {
            AJAX_COMPLETE = true;
            
            jQuery.ajax({
                type : "POST",
                url : UrlConfig.BaseUrl + '/ajax/dynamite/othertyperank',
                dataType : "json",
                data : {
                            rankType: rankType,
                            rankRange: rankRange
                       },
                timeout : 5000,
                error : function() {
                    jQuery.item.createAjaxTimeOutErrorWindow();
                },
                success : function(response){
                    jQuery.rank.showOtherTypeRankResult(response, rankType, rankRange);
                    AJAX_COMPLETE = false;
                }
            }
            );
        }
     
    },
    
    showOtherTypeRankResult : function(response, rankType, rankRange)
    {   
        var rankCount = Number(response.rankCount);
        var rankUser = response.rankUser;
        var userRankNum = Number(response.userRankNum);
        var topRankUser = response.topRankUser;
        var inviteUser = response.inviteUser;
        var lastUserRankNum = Number(response.lastUserRankNum);
        var rankPrev;
        if (lastUserRankNum <= 6) {
            rankPrev = 2;
        }
        else {
            rankPrev = lastUserRankNum - 4;
        }
        
        if (rankPrev != 2) {
            $('#navMaxRight').removeAttr("disabled");   
        }
        else if (rankPrev == 2) {
            $('#navMaxRight').attr("disabled","disabled");
        }
        var html = '';
        if (inviteUser != null) {
            for (var i = 0; i < inviteUser.length; i++) {
                html += '<li>'
                      + '   <p class="number">'+(6-i)+'</p>'
                      + '   <p class="name"><nobr><a href="javascript:void(0)" onclick="invite()">'+inviteUser[i].displayName+'</a></nobr></p>'
                      + '   <p class="pic"><a href="javascript:void(0)" onclick="invite()" style="background-image: url('+inviteUser[i].thumbnailUrl+')"></a></p>'
                      + '   <p class="price">'+inviteUser[i].bonus+'</p>'
                      + '</li>';
                     
            }
        }
        if (rankUser != null) {
            for (var i = 0; i < rankUser.length; i++) {
                html += '<li>'
                      + '   <p class="number">'+(lastUserRankNum-i)+'</p>'
                      + '   <p class="name"><nobr><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+rankUser[i].uid+')">'+rankUser[i].displayName+'</a></nobr></p>'
                      + '   <p class="pic"><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+rankUser[i].uid+')" style="background-image: url('+rankUser[i].thumbnailUrl+')"></a></p>';
                if (rankRange == 1) {
                    html += '   <p class="price">'+rankUser[i].reward+'</p>';
                }
                else if (rankRange == 2) {
                    html += '   <p class="price">'+rankUser[i].bonus+'</p>';
                }
                
                if (rankUser[i].online == 1) {
                    html += '<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/icon/warning.gif" width="16" height="16" id="loginPlayer" alt="" />';
                }
                html += '</li>';
            }
        }
        
        var topHtml = '';
        if (topRankUser != null) {
            for (var i = 0; i < topRankUser.length; i++) {
                 topHtml += '<li>'
                         + '   <p class="number">1</p>'
                         + '   <p class="name"><nobr><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+topRankUser[i].uid+')">'+topRankUser[i].displayName+'</a></nobr></p>'
                         + '   <p class="pic"><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+topRankUser[i].uid+')" style="background-image: url('+topRankUser[i].thumbnailUrl+')"></a></p>';
                         
                 if (rankRange == 1) {
                    topHtml += '   <p class="price">'+topRankUser[i].reward+'</p>';
                 }
                 else if (rankRange == 2) {
                    topHtml += '   <p class="price">'+topRankUser[i].bonus+'</p>';
                 }
                 if (topRankUser[i].online == 1) {
                    topHtml += '<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/icon/warning.gif" width="16" height="16" id="loginPlayer" alt="" />';
                 }
                 topHtml += '</li>';
                         
            }
        }
        
        $('#userList').html(html); 
        $('#firstUser').html(topHtml);
        
        if (rankRange == 1) {
            $('#tabTotalRanking').addClass("disable");
            $('#tabPriceRanking').removeClass("disable");
        }
        else if (rankRange == 2) {
            $('#tabPriceRanking').addClass("disable");
            $('#tabTotalRanking').removeClass("disable");
        }
        
        if (rankType == 1) {
            $('#btnListMyMixi').addClass("disable");
            $('#btnListAll').removeClass("disable");
        }
        else if (rankType == 2) {
            $('#btnListAll').addClass("disable");
            $('#btnListMyMixi').removeClass("disable");
        }
        
        $('#rankType').val(rankType);
        $('#rankRange').val(rankRange);
        $('#rankCount').val(rankCount);
        $('#rankPrev').val(rankPrev);
        $('#lastUserRankNum').val(lastUserRankNum);
        
        $('#currentRight').val(rankPrev);
        
        jQuery.rank.initMoveBar(objRank2, 0, 0);
    },

    getNextTenUser : function(direction)
    {   
       
        var rankPrev = Number($('#rankPrev').val());
        var lastRankNum = Number($('#lastUserRankNum').val());
        var rankCount = Number($('#rankCount').val());
        var currentRight = Number($('#currentRight').val());
        
        if (rankPrev != 2) {
            $('#navMaxRight').removeAttr("disabled");   
        }
        
        if ((currentRight + 4) != rankCount) {
            $('#navMaxLeft').removeAttr("disabled");
        }
    
        if (rankCount <= 6) {
            return;
        }
        if (direction == 'left') {
            if ('disabled' == $('#navMaxLeft').attr("disabled")) {
                return;
            }
            
            if (currentRight + 4 == rankCount) {
                return;
            }
            
            //$('#navMaxLeft').attr("disabled","disabled");
        }
        else if (direction == 'right') {
            if ('disabled' == $('#navMaxRight').attr("disabled")) {
                return;
            }
            if (currentRight == 2) {
                return;
            }
            //$('#navMaxRight').attr("disabled","disabled");
        }
        if (!AJAX_COMPLETE) {
            AJAX_COMPLETE = true;
            jQuery.ajax({
                type : "POST",
                url : UrlConfig.BaseUrl + '/ajax/dynamite/nexttenuser',
                dataType: "json",
                data : {
                            type: $('#rankType').val(),
                            direction : direction,
                            range : Number($('#rankRange').val()),
                            currentRight : currentRight
                       },
                timeout : 5000,
                error : function() {
                    jQuery.item.createAjaxTimeOutErrorWindow();
                },
                success : function(response){
                    jQuery.rank.showNextTenRankUserResult(response, direction);
                    AJAX_COMPLETE = false;
                }
            }
            );
            
        }
    },
    
    showNextTenRankUserResult : function(response, direction)
    {
        var rankCount = Number(response.rankCount);
        var rankUser = response.rankUser;
        var rankRange = Number($('#rankRange').val());
        var currentRight = Number(response.currentRight);
        
        if (direction == 'left') {
            $('#rankCount').val(rankCount);
        }
        
        var html = '';
        if (rankUser != null) {
            for (var i = 0; i < rankUser.length; i++) {
                html += '<li>'
                      + '   <p class="number">'+(currentRight+4-i)+'</p>'
                      + '   <p class="name"><nobr><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+rankUser[i].uid+')">'+rankUser[i].displayName+'</a></nobr></p>'
                      + '   <p class="pic"><a href="javascript:void(0)" onclick="jQuery.dynamite.goUserDynamite('+rankUser[i].uid+')" style="background-image: url('+rankUser[i].thumbnailUrl+')"></a></p>'
                      + '   <p class="price">'+rankUser[i].bonus+'</p>';
                    
                if (rankUser[i].online == 1) {
                    html += '<img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/icon/warning.gif" width="16" height="16" id="loginPlayer" alt="" />';
                }
                html += '</li>';
            }
        }
        $('#userList').html(html);
        $('#userList').show();
        
        if (direction == 'right') { 
            $('#navMaxLeft').removeAttr("disabled");
        }
        else if (direction == 'left') {
            $('#navMaxRight').removeAttr("disabled");
        }
        $('#currentRight').val(currentRight);
        $('#rankPrev').val(currentRight);
        $('#lastUserRankNum').val(currentRight+4);
        
        jQuery.rank.initMoveBar(objRank2, 0, 0);
    },
    
    /**
     * jquery measure postion
     * @param  object objStaticRank
     * @return integer
    */
    measurePosition : function (objStaticRank)
    {
        var nowPosition = $(objStaticRank.innaerFrame).css('left');
        nowPosition = nowPosition.replace('px','');
        return nowPosition
    },
    
    
    initMoveBar : function(objStaticRank, leftAdd, rightAdd)
    {
        if (0 == leftAdd && 0 == rightAdd) {
            var listSize = $(objStaticRank.innaerFrame).find('li').size();
            objStaticRank.maxWidth = objStaticRank.moveSetp * (listSize-5) * (-1);
            objStaticRank.minWidth = 0;
            $(objStaticRank.innaerFrame).css('left', '0');
        }
        else {
            objStaticRank.minWidth += leftAdd;
            objStaticRank.maxWidth -= rightAdd;   
        }
    },
    
    slide : function(direction, objStaticRank, moveStep)
    {
        if(direction == 'left') {
            if (null == moveStep) {
                moveStep = objStaticRank.moveSetp;
            }
            $(objStaticRank.innaerFrame).animate({left : '+=' + moveStep}, 300, function() {
                $('#navLeft').removeAttr("disabled");
            });
            
            $('#currentRight').val(Number($('#currentRight').val())+1);
        } 
        else if(direction == 'right') {
            $(objStaticRank.innaerFrame).animate({left : '-=' + objStaticRank.moveSetp}, 300, function() {
                $('#navRight').removeAttr("disabled");
            });
            
            $('#currentRight').val(Number($('#currentRight').val())-1);
        }
    }

};

})(jQuery);