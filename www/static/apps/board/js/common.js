/**
 * common(/common.js)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create	   2009/02/11    Liz
 */

/**
 * show page nav
 *
 * @param integer count
 * @param integer pageindex
 * @param integer pagesize
 * @param integer pagecount
 * @return string
 */
function showPagerNav(count,pageindex,pagesize,pagecount,action)
{
    if (!pagecount) {
        pagecount = 10;
    }

    if (!action) {
        action = 'changePageAction';
    }

    if (count <= pagesize) {
        return '';
    }

    var nav = '';

    var forward = '';
    var pagerleft = '';
    var pagercurrent = '';
    var pagerright = '';
    var next = '';
    var maxpage = Math.ceil(count/pagesize);

    if (pageindex > 1) {
        //forward = '<p id="numberPrev"><a href="javascript:' + action + '(' + (pageindex - 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_prev.png" alt="" /></a></p>';
        forward = '<li id="numberNext"><a href="javascript:' + action + '(' + (pageindex - 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_prev.png" alt="" /></a></p>';
    }

    if (maxpage > pageindex) {
      //  next = '<p id="numberNext"><a href="javascript:' + action + '(' + (pageindex + 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_next.png" alt="" /></a></p>';
        next = '<li id="numberNext"><a href="javascript:' + action + '(' + (pageindex + 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_next.png" alt="" /></a></p>';
    }

    var page = Math.ceil(pagecount/2);

    //all page count
    var i = 1;

    //left nav
    var left = 0;
    for (left = pageindex - 1; left > 0 && left > pageindex - page; left--) {
        i++;
        pagerleft = '<li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
    }

    //current nav number
    pagercurrent = '<li class="active"><a href="javascript:' + action + '(' + pageindex + ');">' + pageindex + '</a></li>';

    //right nva
    var right = 0;
    for (right = pageindex + 1; right <= maxpage && right < pageindex + page ; right++) {
        i++;
        pagerright = pagerright + '<li><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
    }

    //If right side is not enough, show the page number for left until the page number number is up to 1
    if (i < pagecount && left >= 1) {
        for (; left > 0 && i < pagecount; left--,i++) {
            pagerleft = '<li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
        }
    }

    //If left side is not enough, showthe page number for right until the page number number is up to max
    if (i < pagecount && right <= maxpage) {
        for (; right <= maxpage && i < pagecount; right++,i++) {
            pagerright = pagerright + '<li><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
        }
    }
  //  nav = '<div id="numberList"><div class="inner">' + forward +'<ol>' + pagerleft + pagercurrent + pagerright+'</ol>' + next + '</div></div><!--/#numberList-->';
    nav = '<div id="numberList"><div class="inner"><ul>' + forward + pagerleft + pagercurrent + pagerright + next + '</ul></div></div><!--/#numberList-->';
    return nav;
}

function showPagerNavForList(count,pageindex,pagesize,pagecount,action)
{
    if (!pagecount) {
        pagecount = 10;
    }

    if (!action) {
        action = 'changePageActionList';
    }

    if (count <= pagesize) {
        return '';
    }

    var nav = '';

    var forward = '';
    var pagerleft = '';
    var pagercurrent = '';
    var pagerright = '';
    var next = '';
    var maxpage = Math.ceil(count/pagesize);

    if (pageindex > 1) {
        //forward = '<p id="numberPrev"><a href="javascript:' + action + '(' + (pageindex - 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_prev.png" alt="" /></a></p>';
        forward = '<li id="numberNext"><a href="javascript:' + action + '(' + (pageindex - 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_prev.png" alt="" /></a></p>';
    }

    if (maxpage > pageindex) {
      //  next = '<p id="numberNext"><a href="javascript:' + action + '(' + (pageindex + 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_next.png" alt="" /></a></p>';
        next = '<li id="numberNext"><a href="javascript:' + action + '(' + (pageindex + 1) + ');"><img src="' + UrlConfig.StaticUrl + '/apps/board/img/btn_next.png" alt="" /></a></p>';
    }

    var page = Math.ceil(pagecount/2);

    //all page count
    var i = 1;

    //left nav
    var left = 0;
    for (left = pageindex - 1; left > 0 && left > pageindex - page; left--) {
        i++;
        pagerleft = '<li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
    }

    //current nav number
    pagercurrent = '<li class="active"><a href="javascript:' + action + '(' + pageindex + ');">' + pageindex + '</a></li>';

    //right nva
    var right = 0;
    for (right = pageindex + 1; right <= maxpage && right < pageindex + page ; right++) {
        i++;
        pagerright = pagerright + '<li><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
    }

    //If right side is not enough, show the page number for left until the page number number is up to 1
    if (i < pagecount && left >= 1) {
        for (; left > 0 && i < pagecount; left--,i++) {
            pagerleft = '<li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
        }
    }

    //If left side is not enough, showthe page number for right until the page number number is up to max
    if (i < pagecount && right <= maxpage) {
        for (; right <= maxpage && i < pagecount; right++,i++) {
            pagerright = pagerright + '<li><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
        }
    }
  //  nav = '<div id="numberList"><div class="inner">' + forward +'<ol>' + pagerleft + pagercurrent + pagerright+'</ol>' + next + '</div></div><!--/#numberList-->';
    nav = '<div id="numberList"><div class="inner"><ul>' + forward + pagerleft + pagercurrent + pagerright + next + '</ul></div></div><!--/#numberList-->';
    return nav;
}