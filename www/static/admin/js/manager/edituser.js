/**
 * add user (static/admin/js/manager/adduser.js)
 * add user 
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/23    zhangxin   
 */
 
/**
 *  page load function
 *  register funcion and init page
 */
Event.observe(window, 'load', function() { 
	Event.observe('btnConfirm4update', 'click', doConfirm4update);
	Event.observe('btnBack4update', 'click', doBack4update);
    Event.observe('btnUpdate', 'click', doUpdate);
    Event.observe('btnConfirm4delete', 'click', doConfirm4delete);
    Event.observe('btnBack4delete', 'click', doBack4delete);
    Event.observe('btnDelete', 'click', doDelete);});

var _valid = new Validation('frmEditUser', {immediate:true,useTitles:true});
var curNum = $F('userAppCount')-1;

/**
 * go back to edit page 
 * @param  null
 * @return void
 */
function doBack4update() 
{
	$('loading').hide();
	$('step2').hide();
    $('step1').show(); 
}

/**
 * show confirm page 
 * @param  null
 * @return void
 */
function doConfirm4update()
{
    //curNum = $F('userAppCount');    
	$('divError').hide();
	
    if (!_valid.validate()) {
    	return;
    }
    
    var userRole = $F('userRole');
    
    if (1 != userRole && '' == $('selAuth').value) {
    	$('divError').update('権限を選択してください。');
    	$('divError').show();
    	return;
    }
    
    var arySelect = new Array();
    var blnError = false;
    for (var i = 0; i <= curNum; i++) {
    	var blnRepeat = false;
    	for (var j = 0; j < arySelect.length; j++) {
    		if ('' != $('selApp' + i).value && arySelect[j] == $('selApp' + i).value) {
    			blnRepeat = true;
    			blnError = true;
    			break;
    		}
    	}
    	if ('' != $('selApp' + i).value && !blnRepeat) {
    		arySelect[i] = $('selApp' + i).value;
    	}
    }
    
    if (1 != userRole && 0 == arySelect.length) {
    	$('divError').update('利用範囲を選択してください。');
    	$('divError').show();
    	return;
    }
    
    if (1 != userRole && blnError) {
    	$('divError').update('利用範囲を重複追加しないでください。');
    	$('divError').show();
    	return;
    }
    
    $('lblName').update($F('txtName').escapeHTML());
    $('lblEmail').update($F('txtEmail'));
    for (var i = 0; i < $('selAuth').options.length; i++) {
    	if ($F('selAuth') == $('selAuth').options[i].value) {
    		$('lblAuth').update($('selAuth').options[i].text);
    		break;
    	}
    }
    
    var strApp = '';
    for (var i = 0; i < arySelect.length; i++) {
	    for (var j = 0; j < $('selApp0').options.length; j++) {
	    	if (arySelect[i] == $('selApp0').options[j].value) {
	    		strApp += ',' + $('selApp0').options[j].text;
	    		break;
	    	}
	    }
    }
    if (strApp.length > 0) {
    	strApp = strApp.substr(1);
    }
    $('lblApps').update(strApp);
    
    $('step1').hide();
    $('step2').show();    
}

/**
 * do submit
 * @param  null
 * @return void
 */
function doUpdate() 
{
	if (!_valid.validate()) {
    	return;
    }
    
	var frmSubmit = $('frmEditUser');
    frmSubmit.action = UrlConfig.BaseUrl + '/ajaxmanager/edituser';
    frmSubmit.request({
         onCreate : function() {
         	$('step2').hide();
		    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
		    $('loading').update(html);
		    $('loading').show();
		    $('mixiapps_admin').scrollTo();	
         },
         onSuccess : function(response) {
			try {    
				if (response.responseText != '' && response.responseText == 'true') {      
				    $('loading').hide();
					$('step3').show();
				}
				else {
					var errHtml = response.responseText;
					$('loading').update('<p>' + errHtml + '</p>' + '<input type="button" onclick="doBack4update();" value="　戻る　" />');
		    		$('loading').show();
				}				
			    
			} catch (e) {
			    //alert(e);
			}
         }
    });
}

/**
 * go back to edit page 
 * @param  null
 * @return void
 */
function doBack4delete() 
{
    $('loading').hide();
    $('step4').hide();
    $('step1').show(); 
}

/**
 * show confirm page 
 * @param  null
 * @return void
 */
function doConfirm4delete()
{
    $('divError').hide();
    
    if (!_valid.validate()) {
        return;
    }
    
    var userRole = $F('userRole');
    
    if (1 != userRole && '' == $('selAuth').value) {
        $('divError').update('権限を選択してください。');
        $('divError').show();
        return;
    }
    
    var arySelect = new Array();
    var blnError = false;
    for (var i = 0; i <= curNum; i++) {
        var blnRepeat = false;
        for (var j = 0; j < arySelect.length; j++) {
            if ('' != $('selApp' + i).value && arySelect[j] == $('selApp' + i).value) {
                blnRepeat = true;
                blnError = true;
                break;
            }
        }
        if ('' != $('selApp' + i).value && !blnRepeat) {
            arySelect[i] = $('selApp' + i).value;
        }
    }
    
    $('lblName4Delete').update($F('txtName').escapeHTML());
    $('lblEmail4Delete').update($F('txtEmail'));
    for (var i = 0; i < $('selAuth').options.length; i++) {
        if ($F('selAuth') == $('selAuth').options[i].value) {
            $('lblAuth4Delete').update($('selAuth').options[i].text);
            break;
        }
    }
    
    var strApp = '';
    for (var i = 0; i < arySelect.length; i++) {
        for (var j = 0; j < $('selApp0').options.length; j++) {
            if (arySelect[i] == $('selApp0').options[j].value) {
                strApp += ',' + $('selApp0').options[j].text;
                break;
            }
        }
    }
    if (strApp.length > 0) {
        strApp = strApp.substr(1);
    }
    $('lblApps4Delete').update(strApp);
    
    $('step1').hide();
    $('step4').show();    
}

/**
 * do submit
 * @param  null
 * @return void
 */
function doDelete() 
{
    if (!_valid.validate()) {
        return;
    }
    
    var frmSubmit = $('frmEditUser');
    frmSubmit.action = UrlConfig.BaseUrl + '/ajaxmanager/deleteuser';
    frmSubmit.request({
         onCreate : function() {
            $('step4').hide();
            var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
            $('loading').update(html);
            $('loading').show();
            $('mixiapps_admin').scrollTo(); 
         },
         onSuccess : function(response) {
            try {    
                if (response.responseText != '' && response.responseText == 'true') {      
                    $('loading').hide();
                    $('step5').show();
                }
                else {
                    var errHtml = response.responseText;
                    $('loading').update('<p>' + errHtml + '</p>' + '<input type="button" onclick="doBack4delete();" value="　戻る　" />');
                    $('loading').show();
                }               
                
            } catch (e) {
                //alert(e);
            }
         }
    });
}

/**
 * add select row 
 * @param  null
 * @return void
 */
function addRow()
{
	//alert($('selApp0').innerHTML);
	curNum ++;
	
	var addHtml = '';
	addHtml = '<br />';
	addHtml += '<select id="selApp' + curNum + '" name="selApp[]">' + $('selApp0').innerHTML + '</select>';
	new Insertion.Bottom('divMulSelect', addHtml);	
    $('selApp' + curNum).options[0].selected = true;
}

