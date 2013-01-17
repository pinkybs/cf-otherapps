/**
 * application list(static/admin/js/manager/listapp.js)
 * application list 
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/19    zhangxin   
 */
 
/**
 *  page load function
 *  register funcion and init page
 */
Event.observe(window, 'load', function() { 
    changePageAction("1");
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
    var url = UrlConfig.BaseUrl + '/ajaxmanager/listapp';
    new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex'),
            pageSize : CONST_DEFAULT_PAGE_SIZE
        },
        onTimeout: function() {
            $('divAppList').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
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
    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
    $('divAppList').update(html);
    $('mixiapps_admin').scrollTo();
} 
       
/**
 * response from application view ajax request
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
            	$('divAppList').update(html + nav);
            }
            else {
            	$('divAppList').update('まだ何もありません。');
            }
        }
    } catch (e) {
        //alert(e);
    }
}

/**
 * show application table
 * @param  object array
 * @return string
 */
function showInfo(array)
{
    //concat html tags to array data
    var html = '';
    
    html += '<table width="100%" cellpadding="0" cellspacing="0" border="0" id="dataGrid">'
    		+ '<thead>'
            + '<tr class="head">'
            + '<th>アプリケーション名</th>'
            + '<th>メニュー</th>'
            + '</tr>'
            + '</thead>'
            + '<tbody>';

    //for each row data
    for (var i = 0 ; i < array.length ; i++) {        
        var cssClass = 'a';
        if (1 == i % 2) {
        	cssClass = 'b';
        }
        
        var linkStat = '';
        var linkManage = '';
        var linkContents = '';
        if (array[i].menu_link_stat != null && array[i].menu_link_stat != '') {
        	if (!$F('hidWatcher')) {
        		linkStat = '<a href="' + UrlConfig.BaseUrl + array[i].menu_link_stat + '">［統計レポート］</a>';
        	}
        }
        if (array[i].menu_link_manage != null && array[i].menu_link_manage != '') {
        	if (!$F('hidWatcher') && !$F('hidViewer')) {
        		linkManage = '<a href="' + UrlConfig.BaseUrl + array[i].menu_link_manage + '">［アプリケーション管理］</a>';
        	}
        }
        if (array[i].menu_link_contents != null && array[i].menu_link_contents != '') {
        	if (!$F('hidViewer')) {
        		linkContents = '<a href="' + UrlConfig.BaseUrl + array[i].menu_link_contents + '">［コンテンツ監視・編集］</a>';
        	}
        }
        html += '<tr class="' + cssClass + '">'
              + '    <td>' + array[i].app_name.escapeHTML() + '</td>'
              + '    <td>' + linkStat + linkManage + linkContents + '</td>'                       
              + '</tr>';
    }
    
    html += '</tbody>'
            + '</table>';
    
    return html;
}