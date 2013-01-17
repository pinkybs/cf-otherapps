/*
----------------------------------------------
buy slave JavaScript

Created Date: 2009/06/25
Author: xiali
----------------------------------------------
*/

var objPanelUseID = {
	scrolledArea : '#buySlaveFeed',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox ul li ul.actionList li a'
};

$j(document).ready(function() {
	$j("#buySlaveFinishbtn").click(function (){
		var slaveId = $j("#ulFeed > li.selected").find("input").val();
		//judge sell's slave id
		if (slaveId != '' && slaveId != null){
			buySlaveAction();
		}
		else {
			//alert("choose sell's slave");
			return;
		}
	});
	
	//choose buy slave steps 
	showBuySlaveStep();
	adjustHeight();
	
	//activity
	/*if (null != $j('#activity') && $j('#activity').val()!=undefined && ''!= $j('#activity').val()) {
		var aryAct = $j('#activity').val().split('|');
		for (var i=0; i<aryAct.length; i++) {
			if ('' != aryAct[i]) {
				postActivityWithPic(aryAct[i], $j('#activityPic').val());
			}
		}
		//tar act
		var aryActTar = $j('#activityNew').val().split('|');
		for (var i=0; i<aryActTar.length; i++) {
			if ('' != aryActTar[i]) {
				var aryData = aryActTar[i].split('&');
				postActivityWithPic(aryData[0], $j('#activityPic').val(), '', aryData[1]);
			}
		}
	}*/
	
});

/*
*choose buy slave steps 
*/
function showBuySlaveStep()
{
	$j("#buySlaveNext").click(function (){	
		var slaveCount = $j("#slaveCount").val();
		//judge whether have the four slave
		if(parseInt(slaveCount) >= 4){
			$j("#buySlaveFinishbtn").css('background-position','-331px');
			$j("#buySlaveFinishbtn").css('cursor','default');
		
			$j("#buySlaveStepOne").hide();
			$j("#buySlaveStepTwo").show();
			
			$j("#twoColumn").removeClass();
			$j("#twoColumn").addClass("buySlaveStep2");
			
			//get slave list
			getSlaveLstAction();
		}
		else{
			//buy slave
			buySlaveAction();
		}
	});
	
	$j("#setpBack").click(function (){
		$j("#buySlaveStepTwo").hide();
		$j("#buySlaveStepOne").show();
		$j("#twoColumn").removeClass();
		$j("#twoColumn").addClass("buySlaveStep1");
		
		if (null != cm_getCookie('app_top_url')) {
			top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
		}
	});
}

/**
*display slaves list
*/
function getSlaveLstAction()
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/getslavelst';
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    dataType: "json",
		    success: function(responseObject) {          
	            if (responseObject) {
	            	var strHtml = showSlaveInfo(responseObject.info);    	
					$j("#ulFeed > li").each(function(){												
							//judge whether is the first
							if('default' == $j(this).attr('id')){
								return;
							}
							else{
								$j(this).remove();
							}
						}
					);
					$j("#ulFeed").append(strHtml);
					addEvent();
					cm_initScrollPane(objPanelUseID);
					adjustHeight();							
					if (null != cm_getCookie('app_top_url')) {
						top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
					}
				}
			}
		});
	}catch (e) {
		//alert(e);
	}
}

/*
buy slave 
*/
function buySlaveAction()
{
	//sumbit url
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/buyslave';
	var sellSlaveId = $j("#hidSellSlaveId").val();
	var buySlaveId = $j("#hidBuySlaveId").val();
	
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    dataType: "json",
		    data:"buySlaveId=" + buySlaveId + "&sellSlaveId=" + sellSlaveId,
		    success: function(responseObject) {
		    	if ('false' == (responseObject)) {
		    		alert('ドレイちゃん購入失敗しました。');
		    		window.location.href = UrlConfig.BaseUrl + '/slave/profile?uid=' + buySlaveId;
				}
				else {
					//step 1 hidden
					$j("#buySlaveStepOne").hide();
					//step 2 hidden
					$j("#buySlaveStepTwo").hide();
					//finish display
					$j("#buySlaveFinish").show();
					//remove all's css
					$j("#twoColumn").removeClass();
					//add css
					$j("#twoColumn").addClass("buySlaveFinish");
					$j("#finishPrice").html(toFarmat(Math.ceil(Number($j("#finishPrice").html()) * 1.2)));
					//send mixi activity
					var arrayObject = responseObject;
					
					/*var id = '';
					for ( var i = 0; i < arrayObject.length; i++ ) {
						if (null == arrayObject[i].id || arrayObject[i].id == ''){
							postActivityWithPic(arrayObject[i].info,arrayObject[i].pic);
						}
						else {		
						    id += arrayObject[i].id + ',';						    
						}						
					}					
					id = id.substr( 0, id.length - 1);*/
					
					//alert(arrayObject[0].info  + arrayObject[0].pic);
					postActivityWithPic(arrayObject[0].info, arrayObject[0].pic);
					
					adjustHeight();
					if (null != cm_getCookie('app_top_url')) {
						top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
					}
				}
			}
		});
	}catch (e) {
		//alert(e);
	}
}

/*
add hover and click event
*/
function addEvent()
{
	$j("#ulFeed > li").each(function (){
		var liId = $j(this).attr('id');
		if('default' == liId){
			return;
		}
		else{
			//add hover event
			$j(this).hover(
				//add css
				function (){$j(this).addClass("active");},
				function (){
					//judge whether selected
					if (!$j(this).hasClass("selected")) {
						$j(this).removeClass("active");
					}
				}
			);
		}

		//add click
		$j(this).click(function (){
			//find hidden's value
			var uid = $j(this).find("input").val();
			
			//selected slave info id
			$j("#hidSellSlaveId").val(uid);
			
			$j("#buySlaveFinishbtn").css('background-position','0');
			$j("#buySlaveFinishbtn").css('cursor','pointer');
			
			//click remove before all state
			$j("#ulFeed > li").each(function(){
					//get li's id
					var liId = $j(this).attr('id');
					//judge whether is the first
					if('default' == liId){
						return;
					}
					else{
						$j(this).removeClass("selected");
						$j(this).removeClass("active");
					}
				}
			);
			//logo current selected li
			$j(this).addClass("selected");
			$j(this).addClass("active");
		});
	});
}

/*
show slaves
*/
function showSlaveInfo(array)
{
	var html = '';     
    if ( null == array ||0 == array.length ) {
    	html += '<p>まだ何もありません。</p>';
    	return html;
    }
    
    html += '<!--';
	for ( var i = 0; i < array.length; i++ ){
		var nickName = '';
		//judge whether null name 
    	if (null == array[i].nickname || '' == array[i].nickname) {
    		nickName = array[i].displayName + 'ちゃん';
    	}
    	else {
    		nickName = array[i].nickname;
    	}
    	
    	var imgUrl = array[i].thumbnailUrl;
    	//judge whether null image
    	if (null == array[i].thumbnailUrl) {
    		imgUrl = UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_s_11.png';
    	}
    	
    	var balloon = array[i].balloon;
    	
    	if (null == array[i].balloon || '' == array[i].balloon) {
    		balloon = "誰か私を買って下さい。";
    	}  	
    	
		html += '--><li class="active" id="test_'+ i +'">';
		html += '<input type="hidden" value="'+ array[i].uid +'">';
		html += '<p class="pic"><a style="cursor:default;background-image:url('+ imgUrl +')">'+ array[i].displayName + '</a></p>';	
		html += '<p class="name"><span>'+ array[i].displayName + ' 改め </span>'+ nickName +'</p>';
		html += '<p class="comment">'+ balloon +'<span></span></p>';
		html += '</li><!--';
			
	}
	html += '-->';	
	return html;
}

function toFarmat(price) {
	var tmp= '' + price;
    
	var signa = 0;
	var ll = tmp.length   
	if (ll % 3 == 1) {   
		tmp = "00" + tmp;
		signa = 2;
	}   
	
	if (ll % 3 == 2){   
		tmp = "0" + tmp;
		signa = 1;  
	}   
	
	var tt = tmp.length / 3   
	var mm = new Array();
	for (i = 0; i < tt; i++) {   
		mm[i] = tmp.substring(i * 3, 3 + i * 3);
	}   
	
	var vv = "";
	for (var i=0; i < mm.length; i++) {
		vv += mm[i] + ",";
	}
	
	vv = vv.substring(signa, vv.length -1);
	return vv;
}
