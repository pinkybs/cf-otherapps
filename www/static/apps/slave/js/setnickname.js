/**
 * set nickname(/slave/setnickname.js)
 * slave set nickname
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/26    zhangxin
 */

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	$j("#btnConfirm").css('background-position','-331px');
	$j("#btnConfirm").css('cursor','default');
	
	$j('#btnConfirm').click(doConfirm);
	$j('#btnBack').click(goBack);
	
	$j('#btnSet').click(setNickname);
	
	$j('#txtNickname').focus(function() {
		if ($j(this).val() == $j(this).attr('title')) {
			$j(this).val('');
			$j('#btnConfirm').attr("disabled","disabled");
		}
	});
	$j('#txtNickname').blur(function() {
		if ('' == cm_trimAll($j(this).val())) {
			$j(this).val($j(this).attr('title'));
			if ('' == cm_trimAll($j(this).val())) {
				$j("#btnConfirm").css('background-position','-331px');
				$j("#btnConfirm").css('cursor','default');
				$j('#btnConfirm').attr("disabled","disabled");
			}
			else {
				$j("#btnConfirm").css('background-position','0');
				$j("#btnConfirm").css('cursor','pointer');
				$j('#btnConfirm').removeAttr("disabled");
			}
		}
		
	});
	
	$j('#txtNickname').keyup(function() {
		if (cm_trimAll($j(this).val()) == '') {
			$j("#btnConfirm").css('background-position','-331px');
			$j("#btnConfirm").css('cursor','default');
			$j('#btnConfirm').attr("disabled","disabled");
		}
		else {
			$j("#btnConfirm").css('background-position','0');
			$j("#btnConfirm").css('cursor','pointer');
			$j('#btnConfirm').removeAttr("disabled");
		}
	});
	
	$j('#txtNickname').focus();
	adjustHeight();
});

/**
 * jquery go to confirm 
 *
 * @return void
 */
function doConfirm()
{	
	if($j('#btnConfirm').attr("disabled") == 'disabled' && cm_trimAll($j('#txtNickname').val()) == ''){
		return;
	}
	$j('#twoColumn').removeClass('');
	$j('#twoColumn').addClass('changeNicknameCo');
	$j('#lblNickname').html($j('#txtNickname').val());
	$j('#step1').hide();
	$j('#step2').show();
	$j('#btnSet').removeAttr("disabled");
}

/**
 * jquery go back to edit
 *
 * @return void
 */
function goBack()
{
	$j('#twoColumn').removeClass('');
	$j('#step2').hide();
	$j('#step1').show();	
}

/**
 * jquery set nickname
 *
 * @return void
 */
function setNickname()
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/setnickname';	
	$j('#btnSet').attr("disabled","disabled");
	
	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    data: "uid=" + $j('#hidTarUid').val() + "&txtNickname=" + cm_trimAll($j('#txtNickname').val()),
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject == 'false') {  
	            	$j('#step3').html('<p class="intro">ニックネームを変更失敗しました。</p>');
	            }
	            else if ('2' == responseObject){
	            	window.location.href = UrlConfig.BaseUrl + '/slave/punish';
	            	return;
	            }
	            
	            //send mixi activity
                var arrayObject = responseObject;
                /*for ( var i = 0; i < arrayObject.length; i++ ) {
                    if (null == arrayObject[i].id || arrayObject[i].id == ''){
                    alert(arrayObject[i].info  + arrayObject[i].pic);
                        postActivityWithPic(arrayObject[i].info, arrayObject[i].pic);
                    }
                    else {
                    alert(arrayObject[i].id + arrayObject[i].info  + arrayObject[i].pic);   
                        postActivityWithPic(arrayObject[i].info, arrayObject[i].pic, '', arrayObject[i].id);
                    }
                }*/
                //alert(arrayObject[1].info  + arrayObject[1].pic);
                postActivityWithPic(arrayObject[1].info, arrayObject[1].pic);
                
	            $j('#twoColumn').removeClass('');
	            $j('#twoColumn').addClass('changeNicknameFi');
	            $j('#lblNickname1').html($j('#txtNickname').val());
	            $j('#step2').hide();
	            $j('#step3').show();
		    },
		    error: function(XMLHttpRequest, textStatus, errorThrown) {
		    	//alert(textStatus);
		    }
		});
	}
	catch (e) {
		//alert(e);
	}
	return false;
}