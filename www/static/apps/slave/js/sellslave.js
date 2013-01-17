/**
 * sellslave(/slave/sellslave.js)
 * slave sellslave
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/25    zhangxin
 */

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	$j('#btnSell').click(sellSlave);
	adjustHeight();
});

/**
 * jquery sell slave
 *
 * @return void
 */
function sellSlave()
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/sellslave';
	
	$j('#btnSell').attr("disabled","disabled");
	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    data: "uid=" + $j('#hidSellUid').val(),
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject == 'false') {  
	            	$j('#step2').html('<p class="intro">ポイ捨て失敗しました。</p>');
	            }
	            else {
	            	//send mixi activity
					var arrayObject = responseObject;
					for ( var i = 0; i < arrayObject.length; i++ ) {
						if (null == arrayObject[i].id || arrayObject[i].id == ''){
						//alert(arrayObject[i].info  + arrayObject[i].pic);     
							postActivityWithPic(arrayObject[i].info, arrayObject[i].pic);
						}
						else {
						//alert(arrayObject[i].id + arrayObject[i].info  + arrayObject[i].pic);			
							postActivityWithPic(arrayObject[i].info, arrayObject[i].pic, '', arrayObject[i].id);
						}
		            }
		            $j('#twoColumn').addClass('releaseSlaveFini');
		            $j('#mainColumn').addClass('releaseSlaveFini');
		            $j('#step1').hide();
		            $j('#step2').show();
	            }
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