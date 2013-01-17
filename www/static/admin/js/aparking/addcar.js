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
	Event.observe('btnConfirm', 'click', doConfirm);
    Event.observe('btnRevision', 'click', doReturn);
    Event.observe('btnSubmit', 'click', doSubmit);
});

var valid = new Validation('frmAddCar', {immediate:true,useTitles:true});

/**
 * show confirm page
 * @param  null
 * @return void
 */
function doConfirm()
{
    if(!valid.validate()){
        return false;
    }
    
    $('pName').innerHTML = $('txtName').value.escapeHTML();
    $('pPrice').innerHTML = $('txtPrice').value.escapeHTML();
    $('pCavName').innerHTML = $('txtCavName').value.escapeHTML();
    $('pTimes').innerHTML = $('txtTimes').value.escapeHTML();
    
    $('step1').hide();
    $('step2').show();
}

/**
 * return to add page
 * @param  null
 * @return void
 */
function doReturn()
{
    $('step1').show();
    $('step2').hide();
}

/**
 * do submit
 * @param  null
 * @return void
 */
function doSubmit() 
{
	if (!valid.validate()) {
    	return;
    }
	var frmSubmit = $('frmAddCar');
    frmSubmit.action = UrlConfig.BaseUrl + '/ajaxaparking/addcar';
    frmSubmit.request({
         onCreate : loading,
         onSuccess : renderResults4AddCar
    });
}

/**
 * change page show when ajax is request -ing
 * @param  null
 * @return void
 */
function loading()
{
	$('step2').hide();
    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
    $('loading').update(html);
    $('loading').show();
}
       
/**
 * response from application view ajax request
 * @param  object response
 * @return void
 */
function renderResults4AddCar(response)
{ 
    try {    
        if (response.responseText != '' && response.responseText == 'true') {      
            $('loading').hide();
            $('step3').show();
	        new PeriodicalExecuter(function(pe) {
	           window.location = UrlConfig.BaseUrl + '/aparking/addcar';
	           pe.stop();
	           }, 3);
        } else if ("-1" == response.responseText){
            $('loading').hide();
        	$('errMsg').update('<p>ユニークKEYが存在しました。</p>');        	
            $('step1').show();
        	$('errMsg').show();        	
        }　else {
            $('loading').hide();
            $('errMsg').update('<p>自動車新規が失敗しました。</p>');           
            $('step1').show();
            $('errMsg').show();         
        }
    } catch (e) {
        //alert(e);
    }
}