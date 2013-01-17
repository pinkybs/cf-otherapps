/**
 * revolute(/slave/revolute.js)
 * slave revolute
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/25    zhangxin
 */

var objPanelUseID = {
	scrolledArea : '#revolutionFeed',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox ul li ul.actionList li a'
};

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {	
	$j('#btnRev1').click(revStep1);
	$j('#btnBack').click(goBack);
	$j('#btnRev2').click(revStep2);	
  	adjustHeight();
});

/**
 * jquery go back to edit
 *
 * @return void
 */
function goBack()
{
	//$j('#twoColumn').removeClass('');
	if (null != cm_getCookie('app_top_url')) {
  		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
  	}
	$j('#step2').hide();
	$j('#step1').show();	
	return false;
}

/**
 * jquery revolute step1
 *
 * @return void
 */
function revStep1()
{
	$j("#btnRev2").css('background-position','-331px');
	$j("#btnRev2").css('cursor','default');
	
	if (null == $j('#hidSlaveList').val() || '' == $j('#hidSlaveList').val()) {		
		$j('#btnRev1').attr("disabled","disabled");
		revolute(0);
	}
	else {
		if (null != cm_getCookie('app_top_url')) {
	  		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	  	}
		$j('#step1').hide();
		$j('#step2').show();
		//remove 
		$j("#ulSelSlave > li").not($j('#liFix')).each(function(){	
			$j(this).remove();
		});
		//add
		var objSlaveList = $j.json.decode($j('#hidSlaveList').val());
		var strHtml = showSlaveInfo(objSlaveList);
		$j("#ulSelSlave").append(strHtml);
		//bind event
		$j("#ulSelSlave > li").not($j('#liFix')).each(function(){
			//add hover css
			$j(this).hover(
				function () { $j(this).addClass("active"); },
				function () {
					//judge whether selected
					if (!$j(this).hasClass("selected")) {
						$j(this).removeClass();
					}
			});
			//add click event
			$j(this).click(function(){
				//remove other css
				$j("#ulSelSlave > li").not($j('#liFix')).each(function(){ $j(this).removeClass(); });				
				//add select css
				$j(this).addClass("selected");
				$j(this).addClass("active");
				
				$j("#btnRev2").css('background-position','0');
				$j("#btnRev2").css('cursor','pointer');
				
			});
		});
		
		cm_initScrollPane(objPanelUseID);
		adjustHeight();
	}
	return false;
}

/**
 * jquery revolute step2
 *
 * @return void
 */
function revStep2()
{	
	var selUid = $j("#ulSelSlave > li.selected").find('input').val();
	if (null == selUid || '' == selUid || 0 == selUid) {
		return;
	}
	$j('#btnRev2').attr("disabled","disabled");
	revolute(selUid);
	return false;
}

/**
 * jquery revolution
 * @param string sellUid
 *
 * @return void
 */
function revolute(sellUid)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/revolute';
		
	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    data: "sellUid=" + sellUid,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject == 'false') {
	            	$j('#step3').html('<p class="intro">革命を起こすが失敗しました。</p>');
	            }
	            else {
	            	//send mixi activity
	            	//alert(responseObject[0].info  + responseObject[0].pic);
					postActivityWithPic(responseObject[0].info,responseObject[0].pic);
	            }

				if (null != cm_getCookie('app_top_url')) {
			  		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
			  	}
			  	
	            $j('#step1').hide();
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
}

/**
 * jquery show slave li
 * @param object array
 *
 * @return void
 */
function showSlaveInfo(array)
{
	var html = '';
	html += '<!--';
	
	for(var i = 0; i < array.length; i++){
		var nickName = '';
		//judge whether null name 
    	if(null == array[i].nickname || '' == array[i].nickname){
    		nickName = array[i].displayName + 'ちゃん';
    	}else{
    		nickName = array[i].nickname;
    	}
    	
    	var imgUrl = array[i].thumbnailUrl;
    	//judge whether null image
    	if(null == array[i].thumbnailUrl){
    		imgUrl = UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_s_11.png';
    	}
    	
    	var balloon = array[i].balloon;
    	
    	if(null == array[i].balloon || '' == array[i].balloon){
    		balloon = "誰か私を買って下さい。";
    	}
    	
		html += '--><li id="sell_'+ i +'">';
		html += '<input type="hidden" value="'+ array[i].uid +'">';
		html += '<p class="pic"><a href="' + UrlConfig.BaseUrl+'/slave/profile?uid='+ array[i].uid+'" style="background-image:url('+ imgUrl +')">'+ array[i].displayName + '</a></p>';	
		html += '<p class="name"><span><a href="' + UrlConfig.BaseUrl+'/slave/profile?uid='+ array[i].uid+'">'+ array[i].displayName + '</a> 改め </span>'+ nickName +'</p>';
		html += '<p class="comment">'+ balloon +'<span></span></p>';
		html += '</li><!--';
			
	}
	html += '-->';	
	return html;
}
