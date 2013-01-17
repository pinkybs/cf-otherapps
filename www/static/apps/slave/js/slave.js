/*
----------------------------------------------
Grope Slave common JavaScript

Created Date: 2009/06/22
Author: zhangxin
----------------------------------------------
*/

var $j = jQuery.noConflict();
	
function cm_initScrollPane(objPane) 
{
	/*スクロールバー変更*/
	$j.extend($j.fn.jScrollPane.defaults, {
		dragMinHeight: 16,
		dragMaxHeight: 200,
		reinitialiseOnImageLoad: true,
		scrollbarOnLeft : true,
		animateTo:true,
		animateInterval:50,
		animateStep:3
	});
	$j(objPane.scrolledArea).jScrollPane({
		scrollbarWidth: 15
	});
	
	/*奇数行に .even を追加*/
	$j(objPane.evenList).addClass('even');
	
	/*高さ揃え*/
	function equalHeight(group) {
		tallest = 0;
		group.each(function() {		
			thisHeight = $j(this).height();
			if(thisHeight > tallest) {
				tallest = thisHeight;
			}
		});
		group.height(tallest);
	}
	
	equalHeight($j(objPane.btnList));
}

//trim all space in word begin and end  
function cm_trimAll(strContent)
{
	return strContent.replace(/^[\s　]+|[\s　]+$/, '').replace(/[\s　]+/g, ' ');
}
	
//escape html tags
function cm_escapeHtml(strContent) 
{
    return strContent.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#039;");
}

//replace \n to html tag <br/>
function cm_nl2br(strContent) 
{
	return strContent.replace(/\r\n|\r|\n/g,'<br/>');
}

//get cookie
function cm_getCookie(name)
{
	name += '_slave';
    var result = null;
    var myCookie = document.cookie + ";";
    var searchName = name + "=";
    var startOfCookie = myCookie.indexOf(searchName);
    var endOfCookie;
    if (startOfCookie != -1) {
        startOfCookie += searchName.length;
        endOfCookie = myCookie.indexOf(";",startOfCookie);
        result = unescape(myCookie.substring(startOfCookie, endOfCookie));
    }
    return result;
}

/**
 * show page nav
 *
 * @param integer count
 * @param integer pageindex
 * @param integer pagesize
 * @param integer pagecount
 * @return string
 */
function cm_showPagerNav(count,pageindex,pagesize,pagecount,action)
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
        forward += '<p class="preButton"><a href="javascript:' + action + '(' + (pageindex - 1) + ');">&lt; Prev</a></p>';
    }

    if (maxpage > pageindex) {
    	next += '<p class="nextButton"><a href="javascript:' + action + '(' + (pageindex + 1) + ');">Next &gt;</a></p>';
    }

    var page = Math.ceil(pagecount/2);

    //all page count
    var i = 1;

    //left nav
    var left = 0;
    for (left = pageindex - 1; left > 0 && left > pageindex - page; left--) {
        i++;
        pagerleft = '--><li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li><!--' + pagerleft;
    }

    //current nav number
    pagercurrent = '--><li><a href="javascript:' + action + '(' + pageindex + ');" class="disable">' + pageindex + '</a></li><!--';

    //right nva
    var right = 0;
    for (right = pageindex + 1; right <= maxpage && right < pageindex + page ; right++) {
        i++;
        pagerright = pagerright + '--><li><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li><!--';
    }

    //If right side is not enough, show the page number for left until the page number number is up to 1
    if (i < pagecount && left >= 1) {
        for (; left > 0 && i < pagecount; left--,i++) {
        	pagerleft = '--><li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li><!--' + pagerleft;            
        }
    }

    //If left side is not enough, showthe page number for right until the page number number is up to max
    if (i < pagecount && right <= maxpage) {
        for (; right <= maxpage && i < pagecount; right++,i++) {
        	pagerright = pagerright + '--><li><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li><!--';
        }
    }

	nav = '<div id="pager">' + forward + '<ul><!--' + pagerleft + pagercurrent + pagerright + '--></ul>' + next + '</div><!--/#pager-->';
    return nav;
}


//****************************************************

//go back
function cm_goBack()
{
	document.location.href = document.referrer;
	return false;
}