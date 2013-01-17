/**
 * manageroleandresource list(static/admin/js/manager/manageroleandresource.js)
 * manageroleandresource 
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/25    zhangxin   
 */
 
/**
 *  page load function
 *  register funcion and init page
 */
Event.observe(window, 'load', function() { 
    //changePageAction("1");
    Event.observe('btnOk', 'click', doSubmit);
    $('btnOk').disable();
});

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 200;


function showRelRes(rid, name) 
{
	$('rid').value = '';
    //ajax get select role's relative resource
    var url = UrlConfig.BaseUrl + '/ajaxmanager/getresourceofrole';
    new Ajax.Request(url, {
        parameters : {
            id : rid
        },
        onTimeout: function() {
            $('divResource').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
        },
        onCreate : function() {
		    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
		    $('divResource').update(html);
		    $('mixiapps_admin').scrollTo();
		},
        onSuccess: function(response) { 
	   
		        if (response.responseText != '' && response.responseText != 'false') {      
		            var responseObject = response.responseText.evalJSON(); 
		            //show response array data to list table
		            if (responseObject && responseObject.info && responseObject.info.length > 0) {            
		            	var html = showInfo(responseObject.info);
		            	var nav = showPagerNav(responseObject.count, Number($F('pageIndex')), CONST_DEFAULT_PAGE_SIZE);        
		            	$('divResource').update(html + nav);
		            }
		            else {
		            	$('divResource').update('まだ何もありません。');
		            }
		            $('rid').value = rid;
		            $('lblSelRole').update('(' + name + ')');
		            $('btnOk').enable();
		        }
		}
    });
}

/**
 * show table
 * @param  object array
 * @return string
 */
function showInfo(array)
{
    //concat html tags to array data
    var html = '';
    
    html += '<ul style="list-style-type:square">';

    //for each row data
    for (var i = 0 ; i < array.length ; i++) {        
    	var ischecked = '';
    	if (array[i].ischeck) {
    		ischecked =' checked ';
    	}        
        html += '<li><input type="checkbox" name="chkRes[]" id="chk' + array[i].pid + '" value="' + array[i].pid + '" ' + ischecked + ' />' + array[i].page_url + '</li>';
    }
    
    html += '</ul>';
    
    //$('lblSelRole').update('(' + array[0].role_name + ')');
    return html;
}

/**
 * do submit
 * @param  null
 * @return void
 */
function doSubmit() 
{

	var frmSubmit = $('frmSet');
    frmSubmit.action = UrlConfig.BaseUrl + '/ajaxmanager/manageroleandresource';
    frmSubmit.request({
         onCreate : function() {         	
		    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
		    $('loading').update(html);
		    $('loading').show();
		    $('btnOk').disable();
         },
         onSuccess : function(response) {
			try {    
				if (response.responseText != '' && response.responseText == 'true') {
				    $('loading').update('Done!');
				}
				else {
					var errHtml = response.responseText;
					$('loading').update('error:' + errHtml);
				}			    
			} catch (e) {
			    alert(e);
			}
         }
    });
}