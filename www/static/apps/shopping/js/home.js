/**
 * home(/shopping/home.js)
 * shopping home
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/10    
 */

/**
 * windows load function
 * register funcion
 */
var CONST_DEFAULT_PAGE_SIZE = 5;
 var AJAX_COMPLATE = false;

//jquery scroll bar
var objRank1 = {
    scrollFrame : '#memberFrame1',
    innaerFrame : '#innaerFrame1',
    navLeft : '#btnLeft1',
    navRight : '#btnRight1',
    moveSetp : 81,
    minWidth : 0,
    maxWidth : 0
};

//jquery scroll bar
var objRank2 = {
    scrollFrame : '#memberFrame2',
    innaerFrame : '#innaerFrame2',
    navLeft : '#btnLeft2',
    navRight : '#btnRight2',
    moveSetp : 81,
    minWidth : 0,
    maxWidth : 0
};

//jquery scroll bar
var objRank3 = {
    scrollFrame : '#memberFrame3',
    innaerFrame : '#innaerFrame3',
    navLeft : '#btnLeft3',
    navRight : '#btnRight3',
    moveSetp : 81,
    minWidth : 0,
    maxWidth : 0
};

//jquery scroll bar
var objRank4 = {
    scrollFrame : '#memberFrame4',
    innaerFrame : '#innaerFrame4',
    navLeft : '#btnLeft4',
    navRight : '#btnRight4',
    moveSetp : 81,
    minWidth : 0,
    maxWidth : 0
};

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
    //init rank price my mixi
    init10ManRank();
    
    //init rank price all 
    init50ManRank();
    
    //init rank fortune my mixi
    init100ManRank();
    
    //init rank fortune all
    init500ManRank();
    
    //mixi all 10
    $j('#all1').click(function (){
        rankInit(1, 'all', objRank1);
        $j('#hidRank1').val('all');
    });
    
    //mixi all 50
    $j('#all2').click(function (){
        rankInit(2, 'all', objRank2);
        $j('#hidRank2').val('all');
    });
    
    //mixi all 100
    $j('#all3').click(function (){
        rankInit(3, 'all', objRank3);
        $j('#hidRank3').val('all');
    });
    
    //mixi all 500
    $j('#all4').click(function (){
        rankInit(4, 'all', objRank4);
        $j('#hidRank4').val('all');
    });
    
    //mixi all 10
    $j('#friend1').click(function (){
        rankInit(1, 'friend', objRank1);
        $j('#hidRank1').val('friend');
    });
    
    //mixi all 50
    $j('#friend2').click(function (){
        rankInit(2, 'friend', objRank2);
        $j('#hidRank2').val('friend');
    });
    
    //mixi all 100
    $j('#friend3').click(function (){
        rankInit(3, 'friend', objRank3);
        $j('#hidRank3').val('friend');
    });
    
    //mixi all 500
    $j('#friend4').click(function (){
        rankInit(4, 'friend', objRank4);
        $j('#hidRank4').val('friend');
    });
    adjustHeight();
});

/*
add class 
 @param : integer pos
*/
function activeCss(pos, model)
{
    if (model == 'all') {
        $j('#all' + pos).parent().removeClass();
        $j('#all' + pos).parent().addClass("active");
        $j('#friend' + pos).parent().removeClass();
    }
    else {
        $j('#friend' + pos).parent().removeClass();
        $j('#friend' + pos).parent().addClass("active");
        $j('#all' + pos).parent().removeClass();
    }
}

/**
 * rank init
 * @param  integer pos 1:price_depart10,price_depart50,price_depart100,price_depart500
 * @param  string  model all,friend
 * @param  string  objRank :objRank1,objRank2,objRank3,objRank4
*/
function rankInit(pos, model, objRank)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/listinit'; 
    try {
        $j.ajax({
            type: "GET",   
            url: ajaxUrl,
            data:"pos=" + pos + "&model=" + model,
            dataType: "json",
            success: function(responseObject) {
                //show response array data to list table
                if (responseObject) {
                    var infoLeader = responseObject.info[0];
                    $j('#hidCntRank' + pos).val(responseObject.count);
                    $j('#hidRankprev' + pos).val(responseObject.rankprev);
                    $j('#hidRanknext' + pos).val(responseObject.ranknext);                       
                    if (null != infoLeader && 0 != infoLeader.length) {
                        var strHtmlLeader = showRankInfo(infoLeader, 1);                        
                        $j('#ulRankLeader' + pos).html('').append(strHtmlLeader);
                        initMoveBar(objRank, 0, 0);
                    }
                    var info = responseObject.info[1];
                    var strHtml = showRankInfo(info, 1);                        
                    $j('#ulRank' + pos).html('').append(strHtml);
                    activeCss(pos, model);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(textStatus);
            }
        });
    }
    catch (e) {
        //alert(e);
    }
}

/********************** rank 10 ***************************/
function init10ManRank()
{
    //goto bottom rank
    $j('#btnLeftMore1').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankfriend';
        var rankStart = parseInt($j('#hidCntRank1').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
        var rankEnd = $j('#hidCntRank1').val();
        var model = $j('#hidRank1').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore', 1, model, objRank1);        
        $j('#btnRightMore1').removeAttr("disabled");
        return false;
    });
    
    //goto top rank
    $j('#btnRightMore1').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankfriend';
        var rankStart = 3;
        var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
        var model = $j('#hidRank1').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore', 1, model, objRank1);
        $j('#btnLeftMore1').removeAttr("disabled");         
        return false;
    });
    
    //left one
    $j('#btnLeft1').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank1);
        if (pos < objRank1.minWidth) {
            $j(this).attr("disabled","disabled");
            slide('left',objRank1);
            $j('#btnRightMore1').removeAttr("disabled");
        }
        else {
            var cntMax = parseInt($j('#hidCntRank1').val());
            if (parseInt($j('#hidRanknext1').val()) < cntMax) {
                $j(this).attr("disabled","disabled");
                var end = (parseInt($j('#hidRanknext1').val()) + CONST_DEFAULT_PAGE_SIZE);
                var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext1').val())) : CONST_DEFAULT_PAGE_SIZE;
                var start = parseInt($j('#hidRanknext1').val()) + 1;
                var end = end > cntMax ? cntMax : end;
                var model = $j('#hidRank1').val();
                listRank($j('#hidRankprev1').val(), end, start, cntSize, 'leftStep', 1, model, objRank1);
            }
        }
        return false;
    });
    
    //right one
    $j('#btnRight1').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank1);
        if (pos > objRank1.maxWidth) {          
            $j(this).attr("disabled","disabled");
            slide('right',objRank1);
            $j('#btnLeftMore1').removeAttr("disabled");
        }
        else {
            if (parseInt($j('#hidRankprev1').val()) > 3) {
                $j(this).attr("disabled","disabled");
                var start = (parseInt($j('#hidRankprev1').val()) - CONST_DEFAULT_PAGE_SIZE);
                var cntSize = start<3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
                start = start > 3 ? start : 3;
                var end = $j('#hidRanknext1').val();
                var model = $j('#hidRank1').val();
                listRank(start, end, start, cntSize, 'rightStep', 1, model, objRank1);
            }
        }        
        return false;
    });
}
/********************** rank 10 end ***************************/


/********************** rank 50  ***************************/
/**
 * jquery init rank 50 
 *
 * @return void
 */
function init50ManRank()
{
    //goto bottom rank
    $j('#btnLeftMore2').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankall';
        var rankStart = parseInt($j('#hidCntRank2').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
        var rankEnd = $j('#hidCntRank2').val();
        var model = $j('#hidRank2').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore', 2, model, objRank2);       
        $j('#btnRightMore2').removeAttr("disabled");
        return false;
    });
    
    //goto top rank
    $j('#btnRightMore2').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankall';
        var rankStart = 3;
        var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
        var model = $j('#hidRank2').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore', 2, model, objRank2);
        $j('#btnLeftMore2').removeAttr("disabled");         
        return false;
    });
    
    //left one
    $j('#btnLeft2').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank2);
        if (pos < objRank2.minWidth) {
            $j(this).attr("disabled","disabled");
            slide('left',objRank2);
            $j('#btnRightMore2').removeAttr("disabled");
        }
        else {
            var cntMax = parseInt($j('#hidCntRank2').val());
            if (parseInt($j('#hidRanknext2').val()) < cntMax) {
                $j(this).attr("disabled","disabled");
                var end = (parseInt($j('#hidRanknext2').val()) + CONST_DEFAULT_PAGE_SIZE);
                var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext2').val())) : CONST_DEFAULT_PAGE_SIZE;
                var start = parseInt($j('#hidRanknext2').val()) + 1;
                var end = end > cntMax ? cntMax : end;
                var model = $j('#hidRank2').val();
                listRank($j('#hidRankprev2').val(), end, start, cntSize, 'leftStep', 2, model, objRank2);       
            }
        }
        return false;
    });
    
    //right one
    $j('#btnRight2').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank2);
        if (pos > objRank2.maxWidth) {          
            $j(this).attr("disabled","disabled");
            slide('right',objRank2);
            $j('#btnLeftMore2').removeAttr("disabled");
        }
        else {
            if (parseInt($j('#hidRankprev2').val()) > 3) {
                $j(this).attr("disabled","disabled");
                var start = (parseInt($j('#hidRankprev2').val()) - CONST_DEFAULT_PAGE_SIZE);
                var cntSize = start < 3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
                start = start > 3 ? start : 3;
                var end = $j('#hidRanknext2').val();
                var model = $j('#hidRank2').val();
                listRank(start, end, start, cntSize, 'rightStep', 2, model, objRank2);
            }
        }
        return false;
    });
}
/********************** rank 50 end ***************************/



/********************** rank 100 ***************************/

/**
 * jquery init rank 100 
 *
 * @return void
 */
function init100ManRank()
{
    //goto bottom rank
    $j('#btnLeftMore3').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankfriend';
        var rankStart = parseInt($j('#hidCntRank3').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
        var rankEnd = $j('#hidCntRank3').val();
        var model = $j('#hidRank3').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore', 3, model, objRank3);        
        $j('#btnRightMore3').removeAttr("disabled");
        return false;
    });
    
    //goto top rank
    $j('#btnRightMore3').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankfriend';
        var rankStart = 3;
        var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
        var model = $j('#hidRank3').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore', 3, model, objRank3);
        $j('#btnLeftMore3').removeAttr("disabled");         
        return false;
    });
    
    //left one
    $j('#btnLeft3').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank3);
        if (pos < objRank3.minWidth) {
            $j(this).attr("disabled","disabled");
            slide('left',objRank3);
            $j('#btnRightMore3').removeAttr("disabled");
        }
        else {
            var cntMax = parseInt($j('#hidCntRank3').val());
            if (parseInt($j('#hidRanknext3').val()) < cntMax) {
                $j(this).attr("disabled","disabled");
                var end = (parseInt($j('#hidRanknext3').val()) + CONST_DEFAULT_PAGE_SIZE);
                var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext3').val())) : CONST_DEFAULT_PAGE_SIZE;
                var start = parseInt($j('#hidRanknext3').val()) + 1;
                var end = end > cntMax ? cntMax : end;
                var model = $j('#hidRank3').val();
                listRank($j('#hidRankprev3').val(), end, start, cntSize, 'leftStep', 3, model, objRank3);
            }
        }
        return false;
    });
    
    //right one
    $j('#btnRight3').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank3);
        if (pos > objRank3.maxWidth) {          
            $j(this).attr("disabled","disabled");
            slide('right',objRank3);
            $j('#btnLeftMore3').removeAttr("disabled");
        }
        else {
            if (parseInt($j('#hidRankprev3').val()) > 3) {
                $j(this).attr("disabled","disabled");
                var start = (parseInt($j('#hidRankprev3').val()) - CONST_DEFAULT_PAGE_SIZE);
                var cntSize = start<3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
                start = start > 3 ? start : 3;
                var end = $j('#hidRanknext3').val();
                var model = $j('#hidRank3').val();
                listRank(start, end, start, cntSize, 'rightStep', 3, model, objRank3);
            }
        }
        
        return false;
    }); 
}

/********************** rank 100 end ***************************/


/********************** rank 500  ***************************/
function init500ManRank()
{
    //goto bottom rank
    $j('#btnLeftMore4').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank4').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankall';
        var rankStart = parseInt($j('#hidCntRank4').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
        var rankEnd = $j('#hidCntRank4').val();
        var model = $j('#hidRank4').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore', 4, model, objRank4);       
        $j('#btnRightMore4').removeAttr("disabled");
        return false;
    });
    
    //goto top rank
    $j('#btnRightMore4').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank4').val()) <= 7) {
            return;
        }
        $j(this).attr("disabled","disabled");
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankall';
        var rankStart = 3;
        var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
        var model = $j('#hidRank4').val();
        listRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore', 4, model, objRank4);
        $j('#btnLeftMore4').removeAttr("disabled");         
        return false;
    });
    
    //left one
    $j('#btnLeft4').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank4').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank4);
        if (pos < objRank4.minWidth) {
            $j(this).attr("disabled","disabled");
            slide('left',objRank4);
            $j('#btnRightMore4').removeAttr("disabled");
        }
        else {
            var cntMax = parseInt($j('#hidCntRank4').val());
            if (parseInt($j('#hidRanknext4').val()) < cntMax) {
                $j(this).attr("disabled","disabled");
                var end = (parseInt($j('#hidRanknext4').val()) + CONST_DEFAULT_PAGE_SIZE);
                var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext4').val())) : CONST_DEFAULT_PAGE_SIZE;
                var start = parseInt($j('#hidRanknext4').val()) + 1;
                var end = end > cntMax ? cntMax : end;
                var model = $j('#hidRank4').val();
                listRank($j('#hidRankprev4').val(), end, start, cntSize, 'leftStep', 4, model, objRank4);
            }
        }
        return false;
    });
    
    //right one
    $j('#btnRight4').click(function(){
        if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank4').val()) <= 7) {
            return;
        }
        var pos = measurePosition(objRank4);
        if (pos > objRank4.maxWidth) {          
            $j(this).attr("disabled","disabled");
            slide('right',objRank4);
            $j('#btnLeftMore4').removeAttr("disabled");
        }
        else {
            if (parseInt($j('#hidRankprev4').val()) > 3) {
                $j(this).attr("disabled","disabled");
                var start = (parseInt($j('#hidRankprev4').val()) - CONST_DEFAULT_PAGE_SIZE);
                var cntSize = start<3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
                start = start > 3 ? start : 3;
                var end = $j('#hidRanknext4').val();
                var model = $j('#hidRank4').val();
                listRank(start, end, start, cntSize, 'rightStep', 4, model, objRank4);
            }
        }        
        return false;
    });
}
/********************** rank 500 end ***************************/

/**
 * jquery list ranking
 * @param  integer rankStart
 * @param  integer rankEnd
 * @param  integer cntSize
 * @param  string  moveMethod [rightMore / leftMore / rightStep / leftStep]
 * @param  integer pos 1:price_depart10,price_depart50,price_depart100,price_depart500
 * @param  string  model all,friend
 * @param  string  objRank :objRank1,objRank2,objRank3,objRank4
 * @return void
 */

function listRank(rankStart, rankEnd, cntStart, cntSize, moveMethod, pos, model, objRank)
{
    if (!AJAX_COMPLATE) {
        AJAX_COMPLATE = true;
        var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/listrank';
        try {
            $j.ajax({
                type: "GET",
                url: ajaxUrl,
                data: "rankStart=" + cntStart + "&fetchSize=" + cntSize
                      + "&pos=" + pos + "&model=" + model,
                dataType: "json",
                success: function(responseObject) {
                    //show response array data to list table
                    if (responseObject) {
                        var aryInfo = responseObject.info;
                        $j('#hidRankprev' + pos).val(rankStart);
                        $j('#hidRanknext' + pos).val(rankEnd);                       
                        if (null != aryInfo && 0 != aryInfo.length) {
                            var strHtml = showRankInfo(aryInfo);
                            if ('rightMore' == moveMethod || 'leftMore' == moveMethod) {
                                $j('#ulRank' + pos).html('').append(strHtml);
                                initMoveBar(objRank, 0, 0);
                            }
                            else if ('rightStep' == moveMethod) {
                                $j('#ulRank' + pos).append(strHtml);
                                initMoveBar(objRank, 0, cntSize * objRank.moveSetp);
                                slide('right',objRank);
                                $j('#btnLeftMore' + pos).removeAttr("disabled");
                            }
                            else if ('leftStep' == moveMethod) {
                                $j('#ulRank' + pos).html(strHtml + $j('#ulRank' + pos).html());
                                $j(objRank.innaerFrame).css('left', 0 + (-1) * cntSize * objRank.moveSetp);
                                initMoveBar(objRank, 0, cntSize * objRank.moveSetp);              
                                slide('left',objRank);
                                $j('#btnRightMore' + pos).removeAttr("disabled");
                            }
                            
                        }
                   }
                    AJAX_COMPLATE = false;
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    //alert(textStatus);
                    AJAX_COMPLATE = false;
                }
            });     
        }
        
        catch (e) {
            //alert(e);     
        }
    }
}

/*********************** rank common ********************************/
/**
 * jquery show rank info
 * @param  object array
 * @param  integer pos
 * @return string
 */
function showRankInfo(array, pos) 
{

    //concat html tags to array data
    var html = '';      
    if (null == array ||0 == array.length) {        
        return html;
    }
    html += '<!--';
    //for each row data    
    for (var i = 0 ; i < array.length ; i++) {
        html += '--><li>';
        if (0 == array[i].uid) {
            html += '<span class="rank">' + array[i].rankNo + '</span><span class="name">??????</span><a onclick="mixi_invite();return false;" href="javascript:void(0);" style="background-image:url(' + UrlConfig.StaticUrl + '/apps/shopping/img/dummy/thum_invite.png)"><img src="' + UrlConfig.StaticUrl + '/apps/shopping/img/spacer.gif" width="76"  height="76" alt="招待する" /></a><span class="money">??万円</span>';
        }
        else {
            html += '<span class="rank">' + array[i].rankNo + '</span><span class="name">' + array[i].name + '</span><a href="' + UrlConfig.BaseUrl + '/shopping/wish?uid=' + array[i].uid + '" style="background-image:url(' + array[i].pic + ')"><img src="' + UrlConfig.StaticUrl + '/apps/shopping/img/spacer.gif" width="76"  height="76" alt="' + array[i].name + '" /></a><span class="money">' + array[i].format_diff + '</span>';
        }
        html += '</li><!--';
    }
    html += '-->';
    return html;
}

/**
 * jquery init move bar
 * @param  object array
 * @param  integer leftAdd
 * @param  integer rightAdd
 * @return void
 */
function initMoveBar(objStaticRank, leftAdd, rightAdd)
{   
    if (0 == leftAdd && 0 == rightAdd) {
        var listSize = $j(objStaticRank.innaerFrame).find('li').size()-1;
        objStaticRank.maxWidth = objStaticRank.moveSetp * (listSize-4) * (-1);
        objStaticRank.minWidth = 0;
        $j(objStaticRank.innaerFrame).css('left', '0');
    }
    else {
        objStaticRank.minWidth += leftAdd;
        objStaticRank.maxWidth -= rightAdd;     
    }
}

/**
 * jquery slide
 * @param  string direction
 * @param  object objStaticRank
 * @param  integer moveStep
 * @return void
 */
function slide(direction, objStaticRank, moveStep)
{
    if(direction == 'left'){
        if (null == moveStep) {
            moveStep = objStaticRank.moveSetp;
        }
        $j(objStaticRank.innaerFrame).animate({left : '+=' + moveStep},300, function() {
            //var pos = measurePosition(objStaticRank);
            //if (pos > 0) {
            //      $j(objStaticRank.innaerFrame).css('left', '0px');
            //}
            $j(objStaticRank.navLeft).removeAttr("disabled");
        });
    } else if(direction == 'right'){
        $j(objStaticRank.innaerFrame).animate({left : '-=' + objStaticRank.moveSetp},300, function() {
            //var pos = measurePosition(objStaticRank);
            //if (pos < maxWidth) {
            //      $j(objStaticRank.innaerFrame).css('left',  maxWidth + 'px');
            //}
            $j(objStaticRank.navRight).removeAttr("disabled");
        });
    }
}

/**
 * jquery measure postion
 * @param  object objStaticRank
 * @return integer
 */
function measurePosition(objStaticRank)
{
    var nowPosition = $j(objStaticRank.innaerFrame).css('left');
    nowPosition = nowPosition.replace('px','');
    return nowPosition;
}

/*********************** rank common end ********************************/

