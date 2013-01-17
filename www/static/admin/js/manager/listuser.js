/**
 * user list(static/admin/js/manager/listuser.js)
 * user list 
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/19    zhangxin   
 */
 
/**
 *  page load function
 *  register funcion and init page
 */
Event.observe(window, 'load', function() {     
    changePageAction($F('pageIndex'));
});

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 10;

/**
 * change page ajax request
 * @param  integer page
 * @return void
 */
function changePageAction(page)
{   
    //ajax show list request
    $('pageIndex').value = page;
    var url = UrlConfig.BaseUrl + '/ajaxmanager/listuser';
    new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex'),
            pageSize : CONST_DEFAULT_PAGE_SIZE
        },
        onTimeout: function() {
            $('divUserList').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
        },
        onCreate : getDataFromServer,
        onSuccess: renderResults
    });
}

/**
 * change page show when ajax is request -ing
 * @param  null
 * @return void
 */
function getDataFromServer()
{
    var html = '読み込み中…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
    $('divUserList').update(html);
    $('mixiapps_admin').scrollTo();
}
       
/**
 * response from user view ajax request
 * @param  object response
 * @return void
 */
function renderResults(response)
{ 

    try {    
        if (response.responseText != '' && response.responseText != 'false') {      
            var responseObject = response.responseText.evalJSON(); 
            //show response array data to list table
            if (responseObject && responseObject.info && responseObject.info.length > 0) {            
            	var html = showInfo(responseObject.info);
            	var nav = showPagerNav(responseObject.count, Number($F('pageIndex')), CONST_DEFAULT_PAGE_SIZE);        
            	$('divUserList').update(html + nav);
            }
            else {
            	$('divUserList').update('まだ誰もいません。');
            }
        }
    } catch (e) {
        alert(e);
    }
}

/**
 * show user table
 * @param  object array
 * @return string
 */
function showInfo(array)
{
    //concat html tags to array data
    var html = '';
    
    html += '<ul>'
    	  + '<li><a href="' + UrlConfig.BaseUrl + '/manager/adduser"><strong>＋新規作成</strong></a></li>';

    //for each row data
    for (var i = 0 ; i < array.length ; i++) {
    	var isActiveUser = '';
    	if (0 == array[i].status) {
    		isActiveUser = '　(NOT ACTIVE)';
    	}
        html += '<li><a href="' + UrlConfig.BaseUrl + '/manager/edituser?uid=' + array[i].uid + '&pageIndex=' + $F('pageIndex') + '">' + array[i].email.escapeHTML() + isActiveUser + '<br />' + array[i].role_name.escapeHTML() + '</a></li>';
    }
    
    html += '</ul>';    
    return html;
}