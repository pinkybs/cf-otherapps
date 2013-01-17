/**
 * forbidword (static/admin/js/aboard/forbidword.js)
 * set forbid words
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/06    zhangxin   
 */
 
/**
 *  page load function
 *  register funcion and init page
 */
Event.observe(window, 'load', function() { 
    changePageAction('1');
});

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 1000;

/**
 * change page ajax request
 * @param  integer page
 * @return void
 */
function changePageAction(page)
{   
    //ajax show list request
    $('pageIndex').value = page;
    var url = UrlConfig.BaseUrl + '/ajaxaboard/listforbidword';
    new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex'),
            pageSize : CONST_DEFAULT_PAGE_SIZE
        },
        onTimeout: function() {
            $('divContent').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
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
    $('divContent').update(html);
    $('lblError').scrollTo();
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
            if (responseObject) {            
            	var html = showInfo(responseObject.info);
            	var nav = showPagerNav(responseObject.count, Number($F('pageIndex')), CONST_DEFAULT_PAGE_SIZE);        
            	$('divContent').update(html + nav);
            	$('lblCount').update(responseObject.count);
            	$('lblDate').update(responseObject.date);
            	$('lblUser').update(responseObject.name.escapeHTML());
            }
            else {
            	$('divContent').update('エラーが出ました。');
            }
        }
    } catch (e) {
        //alert(e);
    }
}

var _rowNo = 0;

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
            + '<th width="5%">No</th>'
            + '<th width="20%">禁止語</th>'
            + '<th width="25%">分類</th>'
            + '<th width="25%">作成者</th>'
            + '<th width="25%">設定</th>'
            + '</tr>'
            + '</thead>'
            + '<tbody>';

    //for each row data
    var cssClass;
    _rowNo = 0;
    for (var i = 0 ; i < array.length ; i++) {        
        cssClass = 'a';
        if (1 == i % 2) {
        	cssClass = 'b';
        }
        
        var linkStat = '';
        var linkManage = '';
        var linkContents = '';
        
        html += '<tr class="' + cssClass + '">'
        	  + '    <td>' + (i+1) + '</td>'
              + '    <td><div id="divWord' + i + '">' + array[i].word.escapeHTML() + '</div></td>'
              + '    <td><div id="divType' + i + '">' + array[i].type_name.escapeHTML() + '</div></td>'                       
              + '    <td><div>' + array[i].admin_name.escapeHTML() + '</div></td>'                       
              + '    <td><div id="divCmd' + i + '">' + '<input type="button" onclick="javascript:beginEdit(' + i + ',' + array[i].id +');" value="　編集　" />' + '</div></td>'                       
              + '</tr>';
              
        _rowNo ++;
    }
    
    cssClass = (cssClass == 'a' ? 'b' : 'a');
    html += '<tr id="trAdd" class="' + cssClass + '">'
		  + '<td></td>'
		  + '<td></td>'
		  + '<td></td>'
		  + '<td></td>'
		  + '<td><input type="button" onclick="javascript:addRow();" value="　新規追加　" /></td>'
		  + '</tr>';
		  
    html += '</tbody>'
            + '</table>';
    
    return html;
}

/**
 * add a new editable row
 * @param  null
 * @return null
 */
function addRow() 
{	
	var cssClass;
	var addHtml = '';
	
	//css style
	cssClass = $('trAdd').hasClassName('a') ? 'a' : 'b';
	$('trAdd').removeClassName(cssClass);
	$('trAdd').toggleClassName(cssClass == 'a' ? 'b' : 'a');
	
	//input word
	var strInput = '<input type="text" id="txtWord' + _rowNo + '" value="" maxlength="200" />';
	//select type
	var strSelect = '<select id="selType' + _rowNo + '">' + $('hidSelType').innerHTML + '</select>';
	
	addHtml += '<tr class="' + cssClass + '">'
			  + '    <td>' + '' + '</td>'
              + '    <td>' + strInput + '</td>'
              + '    <td>' + strSelect + '</td>'                       
              + '    <td>' + $F('hidUserName') + '</td>'                       
              + '    <td>' + '<input type="button" onclick="javascript:doInsert(' + _rowNo + ');" value="　作成　" />' + '</td>'                       
              + '</tr>';
	new Insertion.Before('trAdd', addHtml);	
	$('txtWord' + _rowNo).focus();
	_rowNo ++;	
}

/**
 * edit a row data 
 * @param  integer rowNo 
 * @param  integer editId
 * @return null
 */
function beginEdit(rowNo, editId) 
{
	var oldWord = $('divWord' + rowNo).innerHTML;
	var oldType = $('divType' + rowNo).innerHTML;
	$('divWord' + rowNo).update('<input type="text" id="txtWord' + rowNo + '" value="' + oldWord + '" maxlength="200" />');
	$('divType' + rowNo).update('<select id="selType' + rowNo + '">' + $('hidSelType').innerHTML + '</select>');
	for (var i = 0; i < $('selType' + rowNo).options.length; i++) {
    	if (oldType == $('selType' + rowNo).options[i].text) {
    		$('selType' + rowNo).selectedIndex = i;
    		break;
    	}
    }
    
	var cmdEdit = '<input type="button" onclick="javascript:doUpdate(' + rowNo + ',' + editId + ');" value="　変更　" />';
	var cmcDel = '<input type="button" onclick="javascript:doDelete(' + editId + ');" value="　削除　" />';
	$('divCmd' + rowNo).update(cmdEdit + cmcDel);	
	
	$('txtWord' + rowNo).focus();
}

/**
 * insert row to db
 * @param  integer rowNo 
 * @return null
 */
function doInsert(rowNo)
{
	//check validation
	$('lblError').hide(); 
	if ($F('txtWord' + rowNo) == null || $F('txtWord' + rowNo) == '') {
		$('lblError').update('禁止語を入力してください。'); 
		$('lblError').show();
		$('lblError').scrollTo();
		return false; 
	}
	if ($('selType' + rowNo) == null || $('selType' + rowNo).value == '') {
		$('lblError').update('分類を選択してください。'); 
		$('lblError').show();
		$('lblError').scrollTo();
		return false; 
	}
	
	var url = UrlConfig.BaseUrl + '/ajaxaboard/addforbidword';
    new Ajax.Request(url, {
        method: 'post',        
        parameters : {
            word : $F('txtWord' + rowNo),
            type : $('selType' + rowNo).value
        },
        onTimeout: function() {
            $('divContent').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
        },
        onCreate : function() {
		    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
		    $('divContent').update(html);
		    $('lblError').scrollTo();
		},
        onSuccess: function(response) { 	
       		var msg = '作成失敗しました。'; 
	        if (response.responseText != '' && response.responseText == 'true') {
	        	msg = '作成成功しました。';	  		            
	        }
	        $('lblError').update(msg);
	        $('lblError').show();  
	        changePageAction('1');
		}
    });
}

/**
 * update row to db
 * @param  integer rowNo 
 * @param  integer editId 
 * @return null
 */
function doUpdate(rowNo, editId)
{
	//check validation
	$('lblError').hide(); 
	if ($F('txtWord' + rowNo) == null || $F('txtWord' + rowNo) == '') {
		$('lblError').update('禁止語を入力してください。'); 
		$('lblError').show();
		$('lblError').scrollTo();
		return false; 
	}
	if ($('selType' + rowNo) == null || $('selType' + rowNo).value == '') {
		$('lblError').update('分類を選択してください。'); 
		$('lblError').show();
		$('lblError').scrollTo();
		return false; 
	}
	
	var url = UrlConfig.BaseUrl + '/ajaxaboard/editforbidword';
    new Ajax.Request(url, {
        method: 'post',        
        parameters : {
        	id : editId,
            word : $F('txtWord' + rowNo),
            type : $('selType' + rowNo).value
        },
        onTimeout: function() {
            $('divContent').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
        },
        onCreate : function() {
		    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
		    $('divContent').update(html);
		    $('lblError').scrollTo();
		},
        onSuccess: function(response) { 	
       		var msg = '変更失敗しました。'; 
	        if (response.responseText != '' && response.responseText == 'true') {
	        	msg = '変更成功しました。';	  		            
	        }
	        $('lblError').update(msg);
	        $('lblError').show();  
	        changePageAction('1');
		}
    });
}

/**
 * delete row to db
 * @param  integer editId 
 * @return null
 */
function doDelete(editId)
{
	var url = UrlConfig.BaseUrl + '/ajaxaboard/delforbidword';
    new Ajax.Request(url, {
        method: 'post',        
        parameters : {
        	id : editId
        },
        onTimeout: function() {
            $('divContent').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
        },
        onCreate : function() {
		    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
		    $('divContent').update(html);
		    $('lblError').scrollTo();
		},
        onSuccess: function(response) { 	
       		var msg = '削除失敗しました。'; 
	        if (response.responseText != '' && response.responseText == 'true') {
	        	msg = '削除成功しました。';	  		            
	        }
	        $('lblError').update(msg);
	        $('lblError').show();  
	        changePageAction('1');
		}
    });
}
