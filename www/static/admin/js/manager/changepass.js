/**
 * change password(static/admin/js/manager/changepass.js)
 * change user password
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/02/20    zhangxin   
 */
 
/**
 *  page load function
 *  register funcion and init page
 */
Event.observe(window, 'load', function() { 
    Event.observe('btnSubmit', 'click', doSubmit);    
});

var _valid = new Validation('frmChangePass', {immediate:true,useTitles:true});

function doSubmit() 
{
	if (!_valid.validate()) {
    	return;
    }
	var frmSubmit = $('frmChangePass');
    frmSubmit.action = UrlConfig.BaseUrl + '/ajaxmanager/changepass';
    frmSubmit.request({
         onCreate : getDataFromServer,
         onSuccess : renderResults
    });
}


/**
 * change page show when ajax is request -ing
 * @param  null
 * @return void
 */
function getDataFromServer()
{
	$('step1').hide();
    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
    $('loading').update(html);
    $('loading').show();
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
        if (response.responseText != '' && response.responseText == 'true') {      
            $('lblMsg').update('パスワードの変更が完了しました。');
            $('step2').show();
        }
        else {
        	$('errMsg').update('<p>パスワードの変更が失敗しました。現在のパスワードが違います。ご確認してください。</p>');        	
        	$('errMsg').show();
        	$('step1').show();
        }
        $('loading').hide();
        
    } catch (e) {
        //alert(e);
    }
}
