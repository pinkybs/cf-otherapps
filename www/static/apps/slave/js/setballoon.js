/**
 * set balloon(/slave/setballoon.js)
 * slave set balloon
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
	$j('#btnSet').click(setBalloon);
	
	$j('#txtBalloon').focus(function() {
		if ($j(this).val() == $j(this).attr('title')) {
			$j(this).val('');
		}
	});
	$j('#txtBalloon').blur(function() {
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
	
	$j('#txtBalloon').keyup(function() {
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
	
	$j('#txtBalloon').focus();
	adjustHeight();
});

/**
 * jquery go to confirm 
 *
 * @return void
 */
function doConfirm()
{
	if (cm_trimAll($j('#txtBalloon').val()) == '') {
		return;
	}
	
	$j('#twoColumn').removeClass('');
	$j('#twoColumn').addClass('changeFukidashiC');
	$j('#lblBalloon').html($j('#txtBalloon').val() + ' <span></span>');
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
 * jquery set Balloon
 *
 * @return void
 */
function setBalloon()
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/setballoon';	
	$j('#btnSet').attr("disabled","disabled");
	
	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    data: "uid=" + $j('#hidTarUid').val() + "&txtBalloon=" + cm_trimAll($j('#txtBalloon').val()),
		    dataType: "text",
		    success: function(responseText) {
	            //show response array data to list table
	            if (responseText == 'false') {  
	            	$j('#step3').html('<p class="intro">フキダシを変更失敗しました。</p>');
	            }
	            else if ('2' == responseText){
	            	window.location.href = UrlConfig.BaseUrl + '/slave/punish';
	            	return;
	            }
	            $j('#twoColumn').removeClass('');
	            $j('#twoColumn').addClass('changeFukidashiF');
	            $j('#lblBalloon1').html($j('#txtBalloon').val() + ' <span></span>');
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