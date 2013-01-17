/*
----------------------------------------------
buy slave JavaScript

Created Date: 2009/06/25
Author: xiali
----------------------------------------------
*/

$j(document).ready(function() {
	$j("#sellGiftNext").click(function (){
		sellgiftAction();
	});
	adjustHeight();
});

function sellgiftAction()
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/sellgift';
	var keyId = $j("#hidKeyId").val();
	
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    dataType: "json",
		    data:"keyId=" + keyId,
		    success: function(responseObject) {          
	            if (responseObject.state == 'true') {
	            	var strHtml = showFinish(responseObject.info);
	            	$j("#twoColumn").addClass('sellGiftFini');
	            	$j("#sellStep1").hide();
	            	$j("#sellFinishInfo").html(strHtml);
	            	$j("#sellStep2").show();
	            	
	            	//send mixi activity
                    var arrayObject = responseObject.activity;
                    //alert(arrayObject[0].info  + arrayObject[0].pic);
                    postActivityWithPic(arrayObject[0].info, arrayObject[0].pic);                               	
				}
				else if (responseObject.state == 'false'){
					$j('#pIntro').html('<p class="intro">ギフトを売させる失敗しました。</p>');
					$j("#sellStep2").hide();
	            	$j("#sellStep1").show();
				}
			}
		});
	}catch (e) {
		//alert(e);
	}
}

function showFinish(array)
{
	var html = '';     
    if (null == array ||0 == array.length) {
    	html += '<p>まだ何もありません。</p>';
    	return html;
    }

	var imgUrl = array.thumbnailUrl;
   	//judge whether null image
   	if(null == array.thumbnailUrl){
   		imgUrl = UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_s_11.png';
   	}
   	
   	var oldRank = $j("#hidMyTotalRank").val();
   	var rank = '→';
   	if(oldRank > array.total_Rank){
   		rank = '↓';
   	}
   	else if (oldRank < array.total_Rank){
   		rank = '↑';
   	}
	html += '<p class="pic"><a style="cursor:default;background-image:url('+ imgUrl +')">'+ array.displayName +'</a></p>';
	html += '<p class="name"><span><a>'+ array.displayName +'</a> 改め</span> '+array.displayName+'</p>';
	html += '<p class="property">総資産：￥'+ array.total +'</p>';
	html += '<p class="propertyOrder">総資産ランキング：'+ array.total_Rank +'位'+ rank +'</p>';
	
	return html;
}