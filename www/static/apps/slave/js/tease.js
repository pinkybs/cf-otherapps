/*
----------------------------------------------
tease JavaScript

Created Date: 2009/07/06
Author: xiali
----------------------------------------------
*/

var objPanelUseID = {
	scrolledArea : '#teaseFeed',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox ul li ul.actionList li a'
};

var objPanelUseIDTwo = {
	scrolledArea : '#teaseFeed2',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox ul li ul.actionList li a'
};

$j(document).ready(function() {
	$j("#teaseStepOneNext").css('background-position','-331px');
	$j("#teaseStepOneNext").css('cursor','default');
	
    $j("#TeaseStepTwo").hide();
	$j("#teaseStepOneNext").click(teaseNbShow);
	
	$j("#teaseStepTwoNext").click(function (){		
		var teaseId = $j("#ulFeedTwo > li.selected").find("input").val();
		if (teaseId != ''  && teaseId != null){
			if(38 == Number(teaseId)){
				var custom_tease = $j("#txtCustomTease").val();
		   		if ('' == custom_tease || null == custom_tease){
					return;
				}
			}
			$j("#twoColumn").removeClass();
			$j("#twoColumn").addClass('teaseConfirm');
			$j("#TeaseStepConfrim").show();
			$j("#TeaseStepTwo").hide();
			$j("#TeaseStepOne").hide();
			testConfirm();
		}
		else{
			return;
		}
		if (null != cm_getCookie('app_top_url')) {
            top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
        }
	});
	
	$j("#teaseStepTwoReset").click(function (){
		$j("#twoColumn").removeClass();
		$j("#twoColumn").addClass('teaseS1');
		$j("#TeaseStepConfrim").hide();
		$j("#TeaseStepTwo").hide();
		$j("#TeaseStepOne").show();
		var msName = $j("#hidMsName").val();
		$j("#pIntroOne").html(msName + 'をからかうよ！ からかわせるドレイちゃんを選択してね。');
		$j("#hidSlaveId").val('');
		if (null != cm_getCookie('app_top_url')) {
			top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
		}
	});
	
	$j("#teaseConfirmNext").click(function (){
		$j("#twoColumn").removeClass();
		$j("#twoColumn").addClass('teaseFinish');
		$j("#TeaseStepTwo").hide();
		$j("#TeaseStepOne").hide();
		$j("#TeaseStepConfrim").hide();
		var teaseId = $j("#ulFeedTwo > li.selected").find("input").val();
		if(38 == Number(teaseId)){
			customTeaseAction();
	    }
	    else { 
			teaseAction();
		}
		if (null != cm_getCookie('app_top_url')) {
			top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
		}
	});
	
	$j("#teaseConfirmReset").click(function (){
		$j("#twoColumn").removeClass();
		$j("#twoColumn").addClass('teaseStep2');
		$j("#TeaseStepConfrim").hide();
		$j("#TeaseStepOne").hide();
		$j("#TeaseStepTwo").show();
		$j("#hidTeaseId").val('');
		if (null != cm_getCookie('app_top_url')) {
			top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
		}
	});
	
	$j("#uploadReset").click(function (){	
		$j("#errortips").hide();
		$j("#errorUrl").hide();
		
		$j("#uploadBox").hide();
		$j("#twoColumn").removeClass();
		$j("#twoColumn").addClass('teaseStep2');
		$j("#overlay").hide();
		
		//defalut image
		pic_small = UrlConfig.StaticUrl + '/apps/slave/img/feed/pork/0.jpg';
        $j("#showUploadBox").attr('style', 'background-image:url(' + pic_small + ')');
        $j("#hidPic_small").val('');
	});
	validationUpPhoto();	
	addEvent("#ulFeedOne");

	var liSize = $j("#ulFeedOne > li").size();
	if (liSize < 4){
		var heightSize = liSize * 102;	
	    $j("#teaseFeed").attr('style', 'padding: 0px; overflow: hidden; height: '+ heightSize +'px; width: 669px;');
    }
    cm_initScrollPane(objPanelUseID);
    
	adjustHeight();
});

function teaseNbShow()
{
    var slaveId = $j("#ulFeedOne > li.selected").find("input").val();
      //judge sell's slave id
    if (slaveId != '' && slaveId != null){
        $j("#TeaseStepTwo").show();
        $j("#teaseStepTwoNext").css('background-position','-331px');
        $j("#teaseStepTwoNext").css('cursor','default');
        
        $j("#twoColumn").removeClass();
        $j("#twoColumn").addClass('teaseStep2');
        $j("#TeaseStepOne").hide(); 
        
        //forbid custom tease
        var forbid = $j('#hidForbid').val();
        if (forbid == '1') {
            $j('#first').hide();
        }
       
        //click image
        $j("#showUploadBox").click(function (){
            showUpLoad();
        });
    
        //click 写真を追加
        $j("#addImage").click(function (){
            showUpLoad();
        });
        
        addEvent("#ulFeedTwo");
        cm_initScrollPane(objPanelUseIDTwo);
        adjustHeight();
        if (null != cm_getCookie('app_top_url')) {
            top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
        }
    }
    else {
        return;
   }
}

/*
validation upphoto
*/
function validationUpPhoto()
{
	$j("#uploadSubmit").click(function (){
	
	   if ($j("#uploadSubmit").attr("disabled") == "disabled") {
	       return;
	   }

		$j("#errortips").hide();
		$j("#errorUrl").hide();

		var divState = $j("#hidState").val();
		if (divState == '' || divState == null) {
		      return;
		}
		
	    $j("#uploadSubmit").attr("disabled","disabled");
	    $j("#loading").show();
        $j("#text1").hide();
        $j("#text2").hide();
        $j("#uploadDiv").hide();
           
		if ('urlSelect' == divState){	
			var imgUrl = $j("#upUrl").val();
			var suffix = imgUrl.substring(imgUrl.length-4 , imgUrl.length);
			if(imgUrl.indexOf('.jpg') > 0 || imgUrl.indexOf('.jpeg') > 0){
				var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/validateurl';
				$j.ajax({
				    type: "POST",
				    url: ajaxUrl,
				    dataType: "text",
				    data:'upUrl=' + imgUrl,
				    success: function(responseText) {				    
				    	$j('#uploadSubmit').removeAttr("disabled");
				    	$j("#loading").hide();

			            if (responseText != 'false') {
			            	$j("#showUploadBox").attr('style', 'background-image:url(' + responseText + ')');
							$j("#hidPic_small").val(responseText);						
			            	upLoadCallback();
						}
						else {
							$j("#errorUrl").show();
							$j("#errortips").hide();
							$j("#text1").show();
	                        $j("#text2").show();
	                        $j("#uploadDiv").show();
	                        
							$j("#uploadSubmit").removeAttr("disabled");
						}					
					}
				});
			}
			else{
			    $j("#loading").hide();
				$j("#uploadSubmit").removeAttr("disabled");
				$j("#errortips").show();
				$j("#errorUrl").hide();
				$j("#text1").show();
                $j("#text2").show();
                $j("#uploadDiv").show();
			}		
		}
		else{
			var upPhoto = $j("#upPhoto").val();	
			$j('#uploadSubmit').removeAttr("disabled");
			if(upPhoto.indexOf('.jpg') > 0 || upPhoto.indexOf('.jpeg') > 0){
				var frmUpLoad = $j("#upLoadFrm");
			    frmUpLoad[0].action = UrlConfig.BaseUrl + '/ajax/slave/upphoto';
			    frmUpLoad[0].submit();
			}
			else{			
			    $j("#loading").hide();	
				$j("#upPhoto").val('');
				$j("#errortips").show();
				$j("#text1").show();
                $j("#text2").show();
                $j("#uploadDiv").show();
				return;
			}
		}		
		return false;
	});
}

/*
Upload picture's callback
*/
function setCustomPic(response)
{  
	$j("#showUploadBox").attr('style', 'background-image:url(' + response.info.pic + ')');
	$j("#hidPic_small").val(response.info.pic);
	upLoadCallback();
	$j("#loading").hide();
}

/*
Upload picture and validate url after
*/
function upLoadCallback() {
	$j("#uploadBox").hide();
	$j("#twoColumn").removeClass();
	$j("#twoColumn").addClass('teaseStep2');
	$j("#overlay").hide();
	$j("#hidTeaseId").val('38');
	$j("#uploadSubmit").removeAttr("disabled");
}

/*
system tease
*/
function teaseAction()
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/tease';
	var frientId = $j("#hidFriendId").val();
	var slaveId = $j("#ulFeedOne > li.selected").find("input").val();
	var teaseId = $j("#ulFeedTwo > li.selected").find("input").val();
	var pic_small = $j("#hidPic_small").val();
	
	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    dataType: "json",
		    data:"teaseId=" + teaseId + "&slaveId=" + slaveId + "&frientId=" + frientId
		    	  + "&pic_small=" + pic_small,
		    success: function(responseObject) {       
	            if (responseObject == 0) {
	            	$j('#setpBack').show();
				}
				else{
					if (responseObject.state == 'false') {
		            	window.location.href = UrlConfig.BaseUrl + '/slave/punish';
					}
					else if (responseObject.state == 'true') {		
						var strHtml = showFinish(responseObject.info);
						$j("#TeaseFinish").html(strHtml);
						$j("#TeaseStepFinish").show();
						
						//send mixi activity
                        var arrayObject = responseObject.activity;
                        if (arrayObject != null) {
                            postActivityWithPic(arrayObject.info, arrayObject.pic, '', arrayObject.id);
                        }
                        
                        //alert(arrayObject[0].pic + arrayObject[0].info  + id); 
                        
	                    /*for ( var i = 0; i < arrayObject.length; i++ ) {
	                        if (null == arrayObject[i].id || arrayObject[i].id == ''){
	                            //alert(arrayObject[i].info  + arrayObject[i].pic);
	                            postActivityWithPic(arrayObject[i].info,arrayObject[i].pic);
	                        }
	                        else {
	                            //alert(arrayObject[i].id + arrayObject[i].info  + arrayObject[i].pic);     
	                            postActivityWithPic(arrayObject[i].info, arrayObject[i].pic, '', arrayObject[i].id);
	                        }
	                    }*/
					}
				}
				adjustHeight();
			}
		});
	}catch (e) {
		//alert(e);
	}
}

/*
custom tease
*/
function customTeaseAction()
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/tease';
	var frientId = $j("#hidFriendId").val();
	var slaveId = $j("#ulFeedOne > li.selected").find("input").val();
	var teaseId = $j("#ulFeedTwo > li.selected").find("input").val();
	var custom_tease = $j("#txtCustomTease").val();
	var pic_small = $j("#hidPic_small").val();
	
	if (pic_small == '' || pic_small == null){
		pic_small = UrlConfig.StaticUrl + '/apps/slave/img/feed/pork/0.jpg';
	}
	try {
	    $j.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    dataType: "json",
		    data:"teaseId=" + teaseId + "&slaveId=" + slaveId + "&frientId=" + frientId
		    	 + "&pic_small=" + pic_small + "&custom_tease=" + custom_tease,
		    success: function(responseObject) {
				if (responseObject == 0) {
                    $j('#setpBack').show();
                }
				else {
					if (responseObject.state == 'false') {
		            	window.location.href = UrlConfig.BaseUrl + '/slave/punish';
					}
					else if (responseObject.state == 'true') {		
						var strHtml = showFinish(responseObject.info);
						$j("#TeaseFinish").html(strHtml);
						$j("#TeaseStepFinish").show();
						
						//send mixi activity
                        var arrayObject = responseObject.activity;
                        if (arrayObject != null) {
                            postActivityWithPic(arrayObject.info, arrayObject.pic, '', arrayObject.id);
                        }
	                    //alert(arrayObject[0].pic + arrayObject[0].info  + id); 
                        /*for ( var i = 0; i < arrayObject.length; i++ ) {
                            if (null == arrayObject[i].id || arrayObject[i].id == ''){
                                alert(arrayObject[i].info  + arrayObject[i].pic);
                                postActivityWithPic(arrayObject[i].info,arrayObject[i].pic);
                            }
                            else {
                                alert(arrayObject[i].id + arrayObject[i].info  + arrayObject[i].pic);     
                                postActivityWithPic(arrayObject[i].info, arrayObject[i].pic, '', arrayObject[i].id);
                            }
                        }*/
					}
				}
				adjustHeight();
			}
		});
	} catch (e) {
		//alert(e);
	}
}

function showUpLoad()
{
	$j("#uploadBox").show();
	$j("#twoColumn").removeClass();
	$j("#twoColumn").addClass('teaseStep2 teaseStep2Photo');
	$j("#overlay").show();
    $j("#loading").hide();
    $j("#text1").show();
    $j("#text2").show();
    $j("#uploadDiv").show();
	divEvent();
}


/*
add hover and click event
*/
function addEvent(ulName)
{
	$j(ulName).find("li").each(function (){
		var liId = $j(this).attr('id');
		if('default' == liId){
			return;
		}
		else{
			$j(this).hover(
				function (){$j(this).addClass("active");},
				function (){
					if (!$j(this).hasClass("selected")) {
						$j(this).removeClass("active");
					}
				}
			);
		}

		$j(this).click(function (){
			var selectedId = $j(this).find("input").val();
			if($j(".teaseS1").length == 0){
				//get li's id
				var liId = $j(this).attr('id');
				//judge whether is the first
			}
			else {
				//get li's id
				var liId = $j(this).attr('id');
				$j("#hidSlavePriceRank").val(liId);
			}
			
			if (ulName == '#ulFeedOne') {
				$j("#teaseStepOneNext").css('background-position','0');
				$j("#teaseStepOneNext").css('cursor','pointer');
			}
			else {
			     if("first" == $j(this).attr('id')) {
                     $j("#teaseStepTwoNext").css('background-position','-331px');
                     $j("#teaseStepTwoNext").css('cursor','default');
                   
                     if (cm_trimAll($j('#txtCustomTease').val()) == '') {
                        $j("#teaseStepTwoNext").css('background-position','-331px');
                        $j("#teaseStepTwoNext").css('cursor','default');
                     }
                     else {
                        $j("#teaseStepTwoNext").css('background-position','0');
                        $j("#teaseStepTwoNext").css('cursor','pointer');
                     }
                                
			         $j('#txtCustomTease').keyup(function() {
				         if (cm_trimAll($j(this).val()) == '') {
				            $j("#teaseStepTwoNext").css('background-position','-331px');
				            $j("#teaseStepTwoNext").css('cursor','default');
				            
				         }
				         else {
				            $j("#teaseStepTwoNext").css('background-position','0');
				            $j("#teaseStepTwoNext").css('cursor','pointer');
				         }
			         });
			     }
			     else {			     
					$j("#teaseStepTwoNext").css('background-position','0');
	                $j("#teaseStepTwoNext").css('cursor','pointer');
                }
			}

			//click remove before all state
			$j(ulName).find("li").each(function(){
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

function divEvent()
{
	$j("#uploadDiv").find('div').each(function (){
		 $j(this).hover(
				function (){$j(this).addClass("active");},
				function (){
					if (!$j(this).hasClass("selected")) {
						$j(this).removeClass("active");
					}
				}
			);
			
			$j(this).click(function (){
				var selectedDiv = $j(this).attr('id');
				$j("#hidState").val(selectedDiv);
				
				//click remove before all state
				$j("#uploadDiv").find("div").each(function(){
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

function testConfirm()
{	
	$j("#cfmSlavePic").html($j("#ulFeedOne > li.selected").find("p.pic").html());
	var pname = $j("#ulFeedOne > li.selected").find("p.name").html();
	$j("#cfmSlaveName").html(pname + ' が');
	
	var liId = $j("#ulFeedTwo > li.selected").attr('id');
	if(liId == 'first'){
		var pic_small = $j("#hidPic_small").val();
		if(pic_small == null || pic_small == ''){
			pic_small = UrlConfig.StaticUrl + '/apps/slave/img/feed/pork/0.jpg';;
		}
		$j("#cfmTeasePic").html('<a style="cursor:default;background-image:url(' + pic_small + ')"></a>');
		$j("#cfmTeaseName").html($j("#txtCustomTease").val() + 'する');
		$j("#cfmTeaseComment").html('わるのり度 ★★☆');
	} 
	else {
		$j("#cfmTeasePic").html($j("#ulFeedTwo > li.selected").find("p.pic").html());
		$j("#cfmTeaseName").html($j("#ulFeedTwo > li.selected").find("p.name").html());
		$j("#cfmTeaseComment").html($j("#ulFeedTwo > li.selected").find("p.comment").html());
	}
	if (null != cm_getCookie('app_top_url')) {
   		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
   	}
}

function showFinish(array)
{
	$j("#pIntroFinish").html('<span>'+ array.displayName +'をからかったよ。</span><span>ドレイちゃんに誰かをからかわせると、わるのり度に応じて、市場価値がUPするよ。</span>');
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
    
    var nickName = '';
	//judge whether null name 
   	if(null == array.nickname || '' == array.nickname){
   		nickName = array.displayName + 'ちゃん';
   	}else{
   		nickName = array.nickname;
   	}
   	
   	var oldRank = $j("#hidSlavePriceRank").val();
   	var rank = '→';
   	if (oldRank < array.price_rank){
   		rank = '↓';
   	}
   	else if (oldRank > array.price_rank){
   		rank = '↑';
   	}
	html += '<p class="pic"><a style="cursor:default;background-image:url('+ imgUrl +')">'+ array.displayName +'</a></p>';
	html += '<p class="name"><span>'+ array.displayName +'</span> 改め '+ nickName +'</p>';
	html += '<p class="price">市場価格：￥' + array.format_price + '</p>';
	html += '<p class="popularOrder">人気ドレイちゃんランキング：' + array.price_rank + '位'+ rank +'</p>';
	return html;		
}