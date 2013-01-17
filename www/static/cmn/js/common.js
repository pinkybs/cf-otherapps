
/**
 * common(/common.js)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/23    Liz
 */

/**
 * get browser
 *
 * @return integer
 */
function getOs()
{
    //IE,return 1
    if (navigator.userAgent.indexOf("MSIE")>0) {
       return 1;
    }
    //Firefox,return 2
    if (isFirefox=navigator.userAgent.indexOf("Firefox") > 0) {
       return 2;
    }
    //Safari,return 3
    if (isSafari=navigator.userAgent.indexOf("Safari") > 0) {
       return 3;
    }
    //Camino,return 4
    if (isCamino=navigator.userAgent.indexOf("Camino") > 0) {
       return 4;
    }
    //Gecko,return 5
    if (isMozilla=navigator.userAgent.indexOf("Gecko/") > 0) {
       return 5;
    }

    return 0;
}

/**
 * Get Absolute Location Ex
 *
 * @param object element
 * @return integer
 */
function GetAbsoluteLocationEx(element)
{
    if ( arguments.length != 1 || element == null ) {
        return null;
    }
    var elmt = element;
    var offsetTop = elmt.offsetTop;
    var offsetLeft = elmt.offsetLeft;
    var offsetWidth = elmt.offsetWidth;
    var offsetHeight = elmt.offsetHeight;
    while( elmt = elmt.offsetParent ) {
        //add this judge
        if ( elmt.style.position == 'absolute' || elmt.style.position == 'relative'
            || ( elmt.style.overflow != 'visible' && elmt.style.overflow != '' ) ) {
            break;
        }
        offsetTop += elmt.offsetTop;
        offsetLeft += elmt.offsetLeft;
    }
    return { absoluteTop: offsetTop, absoluteLeft: offsetLeft,
        offsetWidth: offsetWidth, offsetHeight: offsetHeight };
}

/**
 * get Absolute Left
 *
 * @param object  e
 * @return integer
 */
function   getAbsLeft(e)
{
    var l = e.offsetLeft;
    while (e=e.offsetParent) {
          l += e.offsetLeft;
    }
    return   l;
}

/**
 * get Absolute Left
 *
 * @param object  e
 * @return integer
 */
function   getAbsTop(e)
{
    var t=e.offsetTop;
    while (e=e.offsetParent) {
        t += e.offsetTop;
    }
    return   t;
}


Effect.Mask = function(id,parent,px,py,width,height) {
    var obj = $(id);
    if (obj != null) {
        obj.show();
        return obj;
    }

    var bgObj=document.createElement("div");

    bgObj.setAttribute('id',id);

    bgObj.style.position = 'absolute';
    bgObj.style.top = py + 'px';
    bgObj.style.left = px + 'px';
    bgObj.style.background = '#dddddd';
    bgObj.style.filter = 'alpha(opacity=30)';
    bgObj.style.opacity = '0.3';
    bgObj.style.width = width + 'px';
    bgObj.style.height = height + 'px';

    parent.appendChild(bgObj);

    return bgObj;
}


Effect.Loading = function() {
    var sWidth,sHeight;
    sWidth = document.body.offsetWidth;
    sHeight = document.body.offsetHeight;

    var bgObj = document.createElement("div");

    bgObj.setAttribute('id','divLoading');

    bgObj.setStyle({
          position: 'absolute',
          top: '0',
          left: '0',
          background: 'white',
          opacity: '0.6',
          width: sWidth + 'px',
          height: sHeight + 'px'
        });

    document.body.appendChild(bgObj);

    var left = (screen.width - 80)/2;
    var top = (screen.height - 80)/2;

    var loading = document.createElement("img");

    loading.setAttribute('src',UrlConfig.StaticUrl + '/_cmn/img/photoeffect/loading.gif');
    loading.setStyle({
          position: 'absolute',
          top: top + 'px',
          left: left + 'px',
          width: '80px',
          height: '80px'
        });

    bgObj.appendChild(loading);

    return bgObj;
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
    var classA = 'border:1px solid #8AD84D;display:block;text-decoration:none;text-align:center;';
    var classAActive = classA + 'background-color:#8AD84D;color:#FFFFFF;';
    var classLi = 'float:left;margin-right:5px;display:inline;width:20px;';
    var classUl = 'clear:both;';

    if (pageindex > 1) {
        forward += '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + (pageindex - 1) + ');">&lt;&lt;</a></li>';
    }

    if (maxpage > pageindex) {
        next = '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + (pageindex + 1) + ');">&gt;&gt;</a></li>';
    }

    var page = Math.ceil(pagecount/2);

    //all page count
    var i = 1;

    //left nav
    var left = 0;
    for (left = pageindex - 1; left > 0 && left > pageindex - page; left--) {
        i++;
        pagerleft = '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
    }

    //current nav number
    pagercurrent = '<li style="' + classLi + '"><a style="' + classAActive + '" href="javascript:' + action + '(' + pageindex + ');" class="active">' + pageindex + '</a></li>';

    //right nva
    var right = 0;
    for (right = pageindex + 1; right <= maxpage && right < pageindex + page ; right++) {
        i++;
        pagerright = pagerright + '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
    }

    //If right side is not enough, show the page number for left until the page number number is up to 1
    if (i < pagecount && left >= 1) {
        for (; left > 0 && i < pagecount; left--,i++) {
            pagerleft = '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
        }
    }

    //If left side is not enough, showthe page number for right until the page number number is up to max
    if (i < pagecount && right <= maxpage) {
        for (; right <= maxpage && i < pagecount; right++,i++) {
            pagerright = pagerright + '<li style="' + classLi + '"><a style="' + classA + '" href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
        }
    }

    nav = '<ul style="' + classUl + '">' + forward + pagerleft + pagercurrent + pagerright + next + '</ul>';

    return nav;
}

/**
 * quote String
 *
 * @param string str
 * @return string
 */
function quoteString(str)
{
    str = replaceAll(str,'\n','<br>');
    str = replaceAll(str,' ','&nbsp;');

    return str;
}

/**
 * replace the string
 *
 * @param string strOrg
 * @param string strFind
 * @param string strReplace
 * @return string
 */
function replaceAll(strOrg,strFind,strReplace)
{
     var index = 0;
     while (strOrg.indexOf(strFind,index) != -1) {
        strOrg = strOrg.replace(strFind,strReplace);
        index = strOrg.indexOf(strFind,index);
     }
     return strOrg
}


var baseDialog = Class.create();

baseDialog.prototype = {
    /**
     * initialize
     *
     * @param string cssname
     * @retrun void
     */
    initialize : function(css) {        
        removeDialog();
        if (!$('overlay')) {
            this.setupDialog();
        }
    },

    /**
     * set up dialog windows
     *
     * @retrun void
     */
    setupDialog : function() {
        //if ie.hide all selectbox
        if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
           this.allSelect = $A(document.getElementsByTagName('select'));
           this.allSelect.each(Element.hide);
        }

        var bgObj = $(document.createElement('div'));
        bgObj.setAttribute('id','fullOverlay');
        bgObj.style.display = 'block';
        document.body.appendChild(bgObj);
        
        $('fullOverlay').innerHTML = '<div id="overlay"></div><div id="overWindow"></div>';
        
        bgObj = null;
    },
    
    insertHTML : function(html, closeType, tarUrl) {
    	$('overWindow').innerHTML = html;
    	modifyDialogHeight(tarUrl);
        
        if (closeType != 2) {
            Event.observe('overlay', 'click', removeDialog);
        }
    }
}

/*
 * remove dialog
 *
 */
function removeDialog() 
{
    //remove backcolor
    if ($('fullOverlay')) {
        Event.stopObserving('overlay', 'click', removeDialog);
        document.body.removeChild($('fullOverlay'));
        
        if (typeof(_PerExec)!="undefined"){
        	_PerExec.stop();
        }
    }
}

function focusFirstEle() 
{
    if ($('overWindow') != null) {
        var temp = $('overWindow').getElementsBySelector('[type="text"]');
        
        if (temp.length == 0) {
            temp = $('overWindow').getElementsByTagName('textarea');
        }
        
        if (temp.length != 0) {
            temp[0].focus();
        }
        
        modifyDialogHeight();
    }
}

function modifyDialogHeight(tarUrl)
{	
	var h = document.body.clientHeight;
    var height = $('overWindow').getHeight();
    $('overWindow').setStyle({ top: '100px'});
    if (null == tarUrl || '' == tarUrl) {
    	parent.parent.location.href = getCookie('app_top_url') + '#bodyArea';
    }
    else {
    	parent.parent.location.href = tarUrl + '#bodyArea';
    }
    
    /*	
    if (Prototype.Browser.IE && parseInt(Prototype.Browser.Version) < 7) {
        var h = findWindowHeight();
        
        var height = $('overWindow').getHeight();
        $('overWindow').setStyle({ top: (Number(h)-Number(height))/2 + document.documentElement.scrollTop + 'px'});
    }
    else {
    	
        var h = document.body.clientHeight;
        var height = $('overWindow').getHeight();
        $('overWindow').setStyle({ top: (Number(h)-Number(height))/2 + 'px'});  
    }
    
    function findWindowHeight()
    {
        if (window.innerHeight) {
            winHeight = window.innerHeight;
        }
        else if ((document.body) && (document.body.clientHeight)) {
            winHeight = document.body.clientHeight;
        }
        
        if (document.documentElement && document.documentElement.clientHeight){
            winHeight = document.documentElement.clientHeight;
        }
        return winHeight;
    }*/
}

/*
 * when ajax request create
 *
 */
function ajaxLoading()
{
    if (!$('overWindow')) {
        new baseDialog();
    }
    
    $('overWindow').innerHTML = '<div class="inner">'
                           + '	<p class="loadingBar">' + '<img src="' + UrlConfig.StaticUrl + '/_cmn/img/fullOverlay/loading.gif" alt="" />' + '</p>'
                           + '</div>';
                   
    modifyDialogHeight();
}

/*
 * when ajax request occur error
 *
 */
function ajaxError(message)
{
    if (!$('overWindow')) {
        new baseDialog();
    }
    
    if(message == null) {
        message = '<p>システムエラーが発生しました。しばらく待ってから再度お試しください。</p>';
    }
    
    $('overWindow').innerHTML = '<div class="inner">'
                           + '	<div class="headLine">'
                           + '    	<h1><p>システムエラー</p></h1>'
                           + '		<p class="close"><a href="javascript:void(0);" onclick="removeDialog();"><img src="' + UrlConfig.StaticUrl + '/_cmn/img/icn/close.png" width="16" height="16" alt="�]����" /></a></p>'
                           + '	</div>'
                           + '  <div class="nowLoading"><p>' + message + '</p></div>'
                           + '</div>';
                           
    modifyDialogHeight();
}

/*
 * truncate money
 * 
 * return string
 */
function truncatemoney(number)
{
    var length = number.length;
    
    if ( length <= 8 ) {
        number = round(number/10000);
        number += '万';
    }
    else if ( length == 9 ) {
        number = round(number/100000000, 2);
        number += '億';
    }
    else if ( length == 10 ) {
        number = round(number/100000000, 2);
        number += '億';
    }
    else if ( length == 11 ) {
        number = round(number/100000000, 1);
        number += '億';
    }
    return number;
}

function round(v,e)
{
    var t=1;
    for( ; e>0; t*=10, e--);
    for( ; e<0; t/=10, e++);
    return Math.round(v*t)/t;
} 

function getCookie(name)
{
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


