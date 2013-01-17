/*
----------------------------------------------
Millionminds common JavaScript

Created Date: 2009/07/27
Author: Liz

----------------------------------------------
*/
(function($) {

$.millionmindscommon = {

    /**
     * show page nav
     *
     * @param integer count
     * @param integer pageindex
     * @param integer pagesize
     * @param integer pagecount
     * @return string
     */
     showPageNav : function(count, pageIndex, pageSize, pageCount, action)
     {    
        if (!pageCount) {
            pageCount = 10;
        }
        
        if (count <= pageSize) {
            return '';
        }
    
        if (!action) {
            action = 'changePageAction';
        }
        
        var nav = '';
        var forward = '';
        var pagerLeft = '';
        var pagerCurrent = '';
        var pagerRight = '';
        var next = '';        
        var maxPage = Math.ceil(count/pageSize);
    
        if (pageIndex > 1) {
            forward += '<li><a href="javascript:void(0)" onclick="' + action + '(' + (pageIndex - 1) + ')">&laquo; 前へ</a></li>';
        }
        else {
            forward += '<li><span>&laquo; 前へ</span></li>';
        }
    
        if (maxPage > pageIndex) {
            next = '<li><a href="javascript:void(0)" onclick="' + action + '(' + (pageIndex + 1) + ')">次へ &raquo;</a></li>';
        }
        else {
            next = '<li><span>次へ &raquo;</span></li>';
        }
    
        var page = Math.ceil(pageCount/2);
    
        //all page count
        var i = 1;
    
        //left nav
        var left = 0;
        for (left = pageIndex - 1; left > 0 && left > pageIndex - page; left--) {
            i++;
            pagerLeft = '<li><a href="javascript:void(0)" onclick="' + action + '(' + left + ')">' + left + '</a></li>' + pagerLeft;
        }
    
        //current nav number
        pagerCurrent = '<li><strong>' + pageIndex + '</strong></li>';
    
        //right nva
        var right = 0;
        for (right = pageIndex + 1; right <= maxPage && right < pageIndex + page ; right++) {
            i++;
            pagerRight = pagerRight + '<li><a href="javascript:void(0)" onclick="' + action + '(' + right + ');">' + right + '</a></li>';
        }
    
        //If right side is not enough, show the page number for left until the page number number is up to 1
        if (i < pageCount && left >= 1) {
            for (; left > 0 && i < pageCount; left--,i++) {
                pagerLeft = '<li><a href="javascript:void(0)" onclick="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerLeft;
            }
        }
    
        //If left side is not enough, showthe page number for right until the page number number is up to max
        if (i < pageCount && right <= maxPage) {
            for (; right <= maxPage && i < pageCount; right++,i++) {
                pagerRight = pagerRight + '<li><a href="javascript:void(0)" onclick="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
            }
        }
    
        nav = '<ul class="pager">' + forward + pagerLeft + pagerCurrent + pagerRight + next + '</ul>';
    
        return nav;
     }
    
};

})(jQuery);

String.prototype.truncate2 = function(len,sep){
	if(len==null) len=2;
	if(sep==null) sep='';
	
	var a=0;
	
	for(var i=0;i<this.length;i++){
		if (this.charCodeAt(i)>255)
			a+=2;
		else
			a++;
		
		if(a>=len)
			return this.substr(0,i+1) + sep;
	}
	
	return this;
}

String.prototype.escapeHTML = function() {
    return this.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
}
  
String.prototype.unEscape = function() {	
	return this.replace(/&lt;/g,"<").replace(/&gt;/g,">").replace(/&amp;/g,"&");
}

/**
 * eval json
 *
 * @return string
 */
function evalJSON(strJson)
{
    return eval( "(" + strJson + ")");
}

/**
* @see  将javascript数据类型转换为json字符串
* @param 待转换对象,支持object,array,string,function,number,boolean,regexp
* @return 返回json字符串
*/
function toJSON(object)
{
    var type = typeof object;
    if ('object' == type) {
        if (Array == object.constructor)
            type = 'array';
        else if (RegExp == object.constructor)
            type = 'regexp';
        else
            type = 'object';
    }
    
    switch(type)
    {
         case 'undefined':
         case 'unknown':
            return;
            break;
         case 'function':
         case 'boolean':
         case 'regexp':
            return object.toString();
            break;
         case 'number':
            return isFinite(object) ? object.toString() : 'null';
            break;
         case 'string':
            return '"' + object.replace(/(\\|\")/g,"\\$1").replace(/\n|\r|\t/g,
                function(){  
                     var a = arguments[0];                   
                     return  (a == '\n') ? '\\n':  
                           (a == '\r') ? '\\r':  
                           (a == '\t') ? '\\t': "" 
                 }) + '"';
            break;
         case 'object':
            if (object === null) return 'null';
            var results = [];
            for (var property in object) {
                var value = toJSON(object[property]);
                if (value !== undefined)
                results.push(toJSON(property) + ':' + value);
            }
            return '{' + results.join(',') + '}';
            break;
         case 'array':
            var results = [];
            for(var i = 0; i < object.length; i++)
            {
                var value = toJSON(object[i]);
                if (value !== undefined) results.push(value);
            }
            return '[' + results.join(',') + ']';
            break;
     }
}

function gotoTop()
{
    if (this != top) {
    	top.location = getCookie('app_top_url_millionminds') + '#bodyArea';
    }
    //window.location = '#container';
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
