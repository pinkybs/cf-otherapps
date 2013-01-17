/**
 * linno(/linno/common.js)
 * 
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/26    Liz
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
function showPagerNavByHtml(count,pageindex,pagesize,pagecount,action)
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
    var classA = 'border:1px solid #8AD84D;display:block;text-decoration:none;text-align:center;';
    var classAActive = classA + 'background-color:#8AD84D;color:#FFFFFF;';
    var classLi = 'float:left;margin-right:5px;display:inline;width:20px;';
    var classUl = 'clear:both;';

    if (pageindex > 1) {
        forward += '<li style="' + classLi + '"><a style="' + classA + '" href="' + action + '?page=' + (pageindex - 1) + '">&lt;&lt;</a></li>';
    }

    if (maxpage > pageindex) {
        next = '<li style="' + classLi + '"><a style="' + classA + '" href="' + action + '?page=' + (pageindex + 1) + '">&gt;&gt;</a></li>';
    }

    var page = Math.ceil(pagecount/2);

    //all page count
    var i = 1;

    //left nav
    var left = 0;
    for (left = pageindex - 1; left > 0 && left > pageindex - page; left--) {
        i++;
        pagerleft = '<li style="' + classLi + '"><a style="' + classA + '" href="' + action + '?page=' + left + '">' + left + '</a></li>' + pagerleft;
    }

    //current nav number
    pagercurrent = '<li style="' + classLi + '"><a style="' + classAActive + '" href="' + action + '?page=' + pageindex + '" class="active">' + pageindex + '</a></li>';

    //right nva
    var right = 0;
    for (right = pageindex + 1; right <= maxpage && right < pageindex + page ; right++) {
        i++;
        pagerright = pagerright + '<li style="' + classLi + '"><a style="' + classA + '" href="' + action + '?page=' + right + '">' + right + '</a></li>';
    }

    //If right side is not enough, show the page number for left until the page number number is up to 1
    if (i < pagecount && left >= 1) {
        for (; left > 0 && i < pagecount; left--,i++) {
            pagerleft = '<li style="' + classLi + '"><a style="' + classA + '" href="' + action + '?page=' + left + '">' + left + '</a></li>' + pagerleft;
        }
    }

    //If left side is not enough, showthe page number for right until the page number number is up to max
    if (i < pagecount && right <= maxpage) {
        for (; right <= maxpage && i < pagecount; right++,i++) {
            pagerright = pagerright + '<li style="' + classLi + '"><a style="' + classA + '" href="' + action + '?page=' + right + '">' + right + '</a></li>';
        }
    }

    nav = '<ul style="' + classUl + '">' + forward + pagerleft + pagercurrent + pagerright + next + '</ul>';

    return nav;
}