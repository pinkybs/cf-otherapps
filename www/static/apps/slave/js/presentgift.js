/*
----------------------------------------------
buy slave JavaScript

Created Date: 2009/06/25
Author: xiali
----------------------------------------------
*/


var objPanelUseID = {
	scrolledArea : '#presentGiftFeed',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox ul li ul.actionList li a'
};

$j(document).ready(function() {

	$j("#presentNext").css('background-position','-331px');
	$j("#presentNext").css('cursor','default');

	$j("#presentNext").click(function () {
		if($j("#hidSelectedFriendId").val() != '' || $j("#hidSelectedGid").val() != ''){
			presentGiftAction();
		}
	});

	var liSize = $j("#ulFeed > li").size();
    if (liSize < 4){
        var heightSize = 0;
        var cssModel = $j("#hidCss").val();
        if (cssModel == 'none') {
	        $j("#ulFeed > li").each(function (){
	           heightSize += Number($j(this).height());
	        });
	        heightSize += liSize* (24 + 3);
        }
        else {
            heightSize = liSize * 102; 
        }
        $j("#presentGiftFeed").attr('style', 'padding: 0px; overflow: hidden; height: '+ heightSize +'px; width: 669px;');
    }
    else {
       cm_initScrollPane(objPanelUseID);
    }
    
	addEvent();	
	adjustHeight();
});


function presentGiftAction()
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/presentgift';	
	var friendId = 0;
	var keyGid = 0;
		
	var selectGift = $j("#hidSelectedGid").val();
	
	if (selectGift == '' || selectGift == null){
		friendId = $j("#hidSelectedFriendId").val();
		keyGid = $j("#hidKeyGid").val();
	} 
	else {
		friendId = $j("#hidFriendId").val();
		keyGid = $j("#hidSelectedGid").val();
	}

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    dataType: "json",
		    data:"friendId=" + friendId + "&keyGid=" + keyGid,
		    success: function(responseObject) {          
	            if (responseObject.state == 'true') {
	            	var strHtml = showFinish(responseObject.info);
	            	$j("#friendInfo").hide();
	            	$j("#giftInfo").hide();
	            	$j("#presentGift").hide();
	            	$j("#pFinish").show();
	            	$j("#presendGiftFinish").show();
	            	$j("#presendGiftFinish").html(strHtml);
	            	if (null != cm_getCookie('app_top_url')) {
		   				top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
		   			}
		   			
		   			var arrayObject = responseObject.activity;
		   			//alert(arrayObject[0].info  + arrayObject[0].pic + arrayObject[0].id);
                    postActivityWithPic(arrayObject[0].info, arrayObject[0].pic, '', arrayObject[0].id);
				}
				else if (responseObject.state == 'false'){
					$j('#pInfo').html('<p class="intro">ギフトを贈させる失敗しました。</p>');
					$j("#friendInfo").show();
	            	$j("#giftInfo").show();
	            	$j("#presentGift").show();
	            	$j("#pFinish").hide();
	            	$j("#presendGiftFinish").hide();
				}	
				adjustHeight();
				if (null != cm_getCookie('app_top_url')) {
					top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
				}
			}
		});
	}catch (e) {
		//alert(e);
	}
}

/*
add event
*/
function addEvent()
{
	$j("#ulFeed > li").each(function (){
		$j(this).hover(
			function (){$j(this).addClass("active");},
			function (){
				if (!$j(this).hasClass("selected")) {
					$j(this).removeClass("active");
				}
			}
		);
		
		$j(this).click(function (){
			var selectedId = $j(this).find("input").val();
			if($j("#hidKeyGid").val() == '' || $j("#hidKeyGid").val() == null){
				$j("#hidSelectedGid").val(selectedId);
			}
			else{
				//selected friend info id hidFriendId
				$j("#hidSelectedFriendId").val(selectedId);
				$j("#hidFriendId").val(selectedId);
			}
			
			$j("#presentNext").css('background-position','0');
			$j("#presentNext").css('cursor','pointer');
			
			//click remove before all state
			$j("#ulFeed > li").each(function(){
					$j(this).removeClass("selected");
					$j(this).removeClass("active");
				}
			);
			//logo current selected li
			$j(this).addClass("selected");
			$j(this).addClass("active");
		});
	});	
}

/*
show finish
*/
function showFinish(array)
{
	var html = '';     
    if (null == array ||0 == array.length) {
    	html += '<p>まだ何もありません。</p>';
    	return html;
    }
    var info = ''+ array.displayName + 'にギフトをプレゼントしたよ。'+ array.displayName +'の市場価値が￥'+ array.format_price +'増えたよ。';
	
	$j("#pInfo").html(info);
    html += '<p class="pic"><a style="cursor:default;background-image:url('+ array.gift_small_pic +')">'+ array.gift_name +'</a></p>';
    html += '<p class="name"><span>'+ array.gift_name +'</span></p>';
    html += '<p class="price">価格：¥'+ array.gift_format_price+'</p>';
    //html += '<p class="buy"><a href="'+ UrlConfig.BaseUrl + '/slave/torakuten?gid='+ array.gid + '">この商品を楽天市場で購入する</a></p>';
    
    return html;
}