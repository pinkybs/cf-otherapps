/*
----------------------------------------------
Script Editor main JavaScript

Created Date: 2009/05/13
Author: Yu Uno
Last Up Date : 2009/05/13
Author: Yu Uno
----------------------------------------------
*/

var $j = jQuery.noConflict();

var chatUseID = {
	listID : '.entry .hdr',
	searchInput : 'input#searchInput'//,
	//textarea : '#editor textarea#inputCode'
};


/*onLoad*/
$j(function(){
	var entryId;
    
	/*searchInput*/
	$j(chatUseID.searchInput).focus(function(){
		inputedText = $j(this).val();
		$j(this).css('background-color', '#F5F5F5');
	}).blur(function(){
		inputedText = $j(this).val();
		if(inputedText == ''){
			$j(this).css('background-color', 'transparent');
		} else {
			return;
		}
	});
	
	/*リストにホバーで'.hover'を追加*/
	$j(chatUseID.listID).hover(
		function(){
			$j(this).addClass('hover')
            entryId = $j(this).attr("id"); 
		},
		function(){
			$j(this).removeClass('hover')
		}
	).click(
		function(){
			location.href = UrlConfig.BaseUrl + '/scripteditor/entry/eid/' + entryId;
		}
	);
	/*リストにホバーで'.hover'を追加*/
	
	
	/*tabキーでスペースを追加*/
	/*$j(chatUseID.textarea).bind('keydown', 'tab',
		function (){
			inputed = $j(this).val().replace(/\n$/, "");
			inputed += '    '
			$j(this).val(inputed);
			return false;
		}
	);*/
	
});

jQuery.extend(
{

      /**
       * @see  将json字符串转换为对象
       * @param   json字符串
       * @return 返回object,array,string等对象
       */
      evalJSON : function (strJson)
      {
            return eval( "(" + strJson + ")");
      },
      
      /**
       * @see  省略过长字符
       * @param string
       * @return string
       */
      truncateString : function (string, len, sep)
      {
            if(len==null) len=2;
            if(sep==null) sep='';
            
            var a=0;
            
            for(var i=0;i<string.length;i++){
                if (string.charCodeAt(i)>255)
                    a+=2;
                else
                    a++;
                
                if(a>=len)
                    return string.substr(0,i+1) + sep;
            }
            
            return string;
      },
      
      format : function(time, fmt) 
      { //author: meizz 
            var o = { 
                "M+" : time.getMonth()+1, //月份 
                "d+" : time.getDate(), //日 
                "h+" : time.getHours(), //小时 
                "m+" : time.getMinutes(), //分 
                "s+" : time.getSeconds(), //秒 
                "q+" : Math.floor((time.getMonth()+3)/3), //季度 
                "S" : time.getMilliseconds() //毫秒 
            }; 
            if(/(y+)/.test(fmt)) 
            fmt=fmt.replace(RegExp.$1, (time.getFullYear()+"").substr(4 - RegExp.$1.length)); 
            for(var k in o) 
            if(new RegExp("("+ k +")").test(fmt)) 
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length))); 
            return fmt; 
      },
      
      formatToDate : function(time, format)
      {
            if (!format) format = 'yyyy年MM月dd日 hh:mm';
            return $j.format(new Date(time.replace(/-/g,"/")), format);
      },
      
      getCheckValue : function(id)
      {
            //alert(id);
            var rPort = document.getElementsByName(id);
            //alert(rPort.length;);
            for(m=0; m<rPort.length; m++)
            {
             　　if(rPort[m].checked) {
               　    return rPort[m].value;
                 }
            }
            
      },
      
      //clear 'space'
      cTrim : function(sInputString,iType)
      {
            var sTmpStr = ' ';
            var i = -1;
            
            if(iType == 0 || iType == 1) {
                while(sTmpStr == ' ') {
                    ++i;
                    sTmpStr = sInputString.substr(i,1);
                }
                
                sInputString = sInputString.substring(i);
            }
            
            if(iType == 0 || iType == 2) {
                sTmpStr = ' ';
                i = sInputString.length;
                
                while(sTmpStr == ' ') {
                    --i;
                    sTmpStr = sInputString.substr(i,1);
                }
                sInputString = sInputString.substring(0,i+1);
            }
            
            return sInputString;
      },
      
     //escape html tags
    escapeHtml : function(strContent)
    {
        return strContent.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#039;");
    },
    
    nl2br : function(strContent)
    {
        return strContent.replace(/\r\n|\r|\n/g,'<br/>');
    },
    
    loadPage : function (){
        if ( null != $j.getCookie('app_top_url_scripteditor') ) {
            top.location.href = $j.getCookie('app_top_url_scripteditor') +  '#bodyArea';            
        }
    },
    
    getCookie : function(name)
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
    },
    
    ToCDB : function(str)
    {
        var tmp = "";
        for( var i=0; i<str.length; i++ ) {
            if( str.charCodeAt(i) > 65248 && str.charCodeAt(i) < 65375 ) {
                tmp += String.fromCharCode(str.charCodeAt(i) - 65248);
            }
            else {
                tmp += String.fromCharCode(str.charCodeAt(i));
            }
        }
        return tmp;
    }
});

function countDown(secs, surl)
{    
    if(--secs>0){
       setTimeout("countDown("+secs+",'"+surl+"')",1000);
    }
    else{
       location.href=surl;
    }
}

function toSearch()
{
    var search = $j('input#searchInput').val();
    location.href = UrlConfig.BaseUrl + '/scripteditor/search?search=' + search;
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
    var classA = 'border:1px solid #258FB8;display:block;text-decoration:none;text-align:center;';
    var classAActive = classA + 'background-color:#CCCCCC;color:#333333;';
    var classLi = 'float:left;margin-right:5px;display:inline;width:28px;height:28px;';
    var classUl = 'clear:both;';
    var classP = 'margin-right:5px;float:left;display:inline;width:40px;border:1px solid #258FB8;display:block;text-decoration:none;text-align:center;';
    if (pageindex > 1) {
        forward += '<li class="prev"><a style="' + classP + '" href="javascript:' + action + '(' + (pageindex - 1) + ');">&lt;Prev</a></li>';
    }

    if (maxpage > pageindex) {
        next = '<li class="next"><a style="' + classP + '" href="javascript:' + action + '(' + (pageindex + 1) + ');">Next&gt;</a></li>';
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
    
    nav = '<ol class="numberList">' + forward + pagerleft + pagercurrent + pagerright + next + '</ol>';
    
    return nav;
}