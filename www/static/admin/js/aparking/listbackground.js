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
    var url = UrlConfig.BaseUrl + '/ajaxaparking/listbackground';
    new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex'),
            pageSize : CONST_DEFAULT_PAGE_SIZE
        },
        onTimeout: function() {
            $('divBackgroundList').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
        },
        onCreate : getDataFromServer,
        onSuccess: renderResults4CarList
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
    $('divBackgroundList').update(html);
} 
       
/**
 * response from user view ajax request
 * @param  object response
 * @return void
 */
function renderResults4CarList(response)
{ 
    try {    
        if (response.responseText != '' && response.responseText != 'false') {      
            var responseObject = response.responseText.evalJSON(); 
            //show response array data to list table
            if (responseObject && responseObject.info && responseObject.info.length > 0) {            
            	var html = showInfo(responseObject.info);
            	var nav = showPagerNav(responseObject.count, Number($F('pageIndex')), CONST_DEFAULT_PAGE_SIZE);        
            	$('divBackgroundList').update(html + nav);
            }
            else {
            	$('divBackgroundList').update('まだ何もありません。');
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
    	  + '<li><a href="/aparking/addbackground"><strong>＋新規作成</strong></a></li>';

    //for each row data
    for (var i = 0 ; i < array.length ; i++) {
      	html += '<li><a href="' + UrlConfig.BaseUrl + '/aparking/editbackground?id=' + array[i].id + '&pageIndex=' + $F('pageIndex') + '">' + array[i].name.escapeHTML() + '<br />ランク：' + array[i].type + '</a></li>';
    }
    
    html += '</ul>';    
    return html;
}

function outputDollars(number)
{
  if (number.length<= 3) {
  return (number == '' ? '0' : number);
  } else {
    var mod = number.length%3;
    var output = (mod == 0 ? '' : (number.substring(0,mod)));
    for (i=0 ; i< Math.floor(number.length/3) ; i++)
    {
      if ((mod ==0) && (i ==0))
      output+= number.substring(mod+3*i,mod+3*i+3);
      else
      output+= ',' + number.substring(mod+3*i,mod+3*i+3);
    }
    return (output);
  }
}