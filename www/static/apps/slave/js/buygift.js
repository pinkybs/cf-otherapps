/*
----------------------------------------------
buy gift JavaScript

Created Date: 2009/06/29
Author: xiali
----------------------------------------------
*/

$j(document).ready(function() {
	$j("#buyNext").click(function (){
		buyGiftAction();
	});
	adjustHeight();
});

/*
buy gift
*/
function buyGiftAction()
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/buygift';
	
	try {
	    $j.ajax({
		    type: "GET",
		    url: ajaxUrl,
		    dataType: "json",
		    data: "gid=" + $j("#hidGid").val() + "&price=" + $j("#hidPrice").val(),
		    success: function(responseObject) {	            
	            if ('false' == responseObject) {               
                    $j("#pInfo").html("ギフトを購入させる失敗しました。");
                }
                else {                  
		            $j("#buySetp").hide();
		            $j("#pBuyGift").show();
		            $j("#pInfo").html("ギフトを購入しました。");
		                   
		                   //send mixi activity
		            //alert(responseObject[0].info  + responseObject[0].pic);
		            postActivityWithPic(responseObject[0].info, responseObject[0].pic);
                }
			}
		});
	}catch (e) {
		//alert(e);
	}
}

