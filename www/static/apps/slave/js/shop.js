/*
----------------------------------------------
slave shop list JavaScript

Created Date: 2009/06/24
Author: xiali
----------------------------------------------
*/
var CONST_DEFAULT_PAGE_SIZE = 10;

$j(document).ready(function() {
	//sort butten css init
	cssInit();
	
	//sort menu change
	$j("#sortDesc").click(function (){
		if ('0' == $j("#hidSort").val()) {
			return;
		}
		$j("#hidSort").val(0);		
		changePageAction(1, "#sortDesc", "#sort");
	});
	$j("#sort").click(function (){
		if ('1' == $j("#hidSort").val()) {
			return;
		}		
		$j("#hidSort").val(1);		
		changePageAction(1, "#sort", "#sortDesc");
	});
	
	//切り替え
	$j("#mixiKey").click(function (){
		$j("#tabMyMixi").addClass('active');
		$j("#tabOwn").removeClass('active');
		$j("#hidKeyWord").val(1);
		changePageAction(1);
	});
	$j("#firendsKey").click(function (){
		$j("#tabMyMixi").removeClass('active');
		$j("#tabOwn").addClass('active');
		$j("#hidKeyWord").val(0);
		changePageAction(1);
	});
	
	adjustHeight();
	
});

/*
*@param : string red desc
*@param : string blue asc
*/
function sortAddCss(red,blue)
{
	$j(red).css('color','#DC4749');
	$j(blue).css('color','#258FB8');
	
	$j(red).css('fontWeight','bold');
	$j(blue).css('fontWeight','normal');
	
	$j(red).removeAttr('href');
	$j(blue).attr('href','javascript:void(0);');
}

//sort butten css init
function cssInit()
{
	$j("#sortDesc").css('color','#DC4749');
	$j("#sort").css('color','#258FB8');
	$j("#sortDesc").css('fontWeight','bold');
	$j("#sort").css('fontWeight','normal');
	$j("#sortDesc").removeAttr('href');
}


/*
* page change request action
*/
function changePageAction(page, desc, asc)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/getslaveshop';
	//current page
	$j("#pageIndex").val(page);
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "pageIndex=" + page + "&pageSize=" + CONST_DEFAULT_PAGE_SIZE +
		    	"&sort=" + parseInt($j("#hidSort").val()) + "&keyWord=" + parseInt($j("#hidKeyWord").val()),
		    dataType: "json",
		    success: function(responseObject) {
	            if (responseObject == false) {
	            	$j('#setpNull').show();
	            	$j('#pInfo').hide();
	            	$j('#slaveShop').html('');
	            }
	            else {    	
	            	//show response array data to list table
	            	var strHtml = showslaveshopInfo(responseObject.info);
	            	
					$j('#maxCount').html(responseObject.count == null ? 0 : responseObject.count);
			      	var numstart = (parseInt($j('#pageIndex').val()) - 1) * CONST_DEFAULT_PAGE_SIZE + 1;
			      	var numend = (numstart + CONST_DEFAULT_PAGE_SIZE - 1) > parseInt(responseObject.count) ? responseObject.count : (numstart + CONST_DEFAULT_PAGE_SIZE - 1);
			      	if (0 == responseObject.count) {
			      		numstart = 0;
			      		numend = 0;
			      	}
			      	$j('#lblNumS').html(numstart);
			      	$j('#lblNumB').html(numend);
			      	
            		var nav = cm_showPagerNav(responseObject.count, parseInt($j("#pageIndex").val()), CONST_DEFAULT_PAGE_SIZE);
	            	$j('#slaveShop').html(strHtml + nav);
	            	
	            	$j('#setpNull').hide();
	            	$j('#pInfo').show();
	            	//sort asc css
                    sortAddCss(desc, asc);            	
				}
				if (!desc) {
                    if (null != cm_getCookie('app_top_url')) {
                        top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
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
* slave shop display info
*/
function showslaveshopInfo(array)
{
	var html = '';     
    if (null == array ||0 == array.length) {
    	return html;
    }    
    
    $j("#slaveShop").show();
    $j("#pInfo").show();
    
    var slaveIdStr = $j("#hidSlaveUidStr").val();
    slaveIdStr = slaveIdStr.substr(0 , slaveIdStr.length-1);
    var slaveIds = slaveIdStr.split("|");
    
    html += '<ul><!--';
    //for each row data    

    for (var i = 0 ; i < array.length ; i++) {   
    	var nickName = '';
    	if(null == array[i].nickname || '' == array[i].nickname){
    		nickName = array[i].displayName + 'ちゃん';
    	}else{
    		nickName = array[i].nickname;
    	}
    	
    	var imgUrl = array[i].thumbnailUrl;
    	if(null == array[i].thumbnailUrl){
    		imgUrl = UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_s_11.png';
    	}
    	
 		var balloon = array[i].balloon;
 		if(null == array[i].balloon || '' == array[i].balloon){
    		balloon = "誰か私を買って下さい。";
    	}
    	
    	var buy = true;
		var tease = true;
		var gift = true;
		//hidUid's value is login user's id
		if($j("#hidUid").val() != array[i].uid ){
		
			//is master or no money		
			if($j("#hidMasterId").val() == array[i].uid){
				buy = false;
			}
					
			if(Number($j("#hidCash").val()) < Number(array[i].price)){
				buy = false;
			}
			//judge whether is login user's slave
			for(var j = 0 ; j < slaveIds.length ; j++){
				if(slaveIds[j] == array[i].uid){
					buy = false;				
					//only one slave
					if (1 == Number($j("#hidSlaveCount").val())) {
						tease = false;
					}
					break;
				}
			}
			
			//no slaves
			if(0 == Number($j("#hidSlaveCount").val())){
				tease = false;
			}			
			//no gift
			if(0 == Number($j("#hidGiftCount").val()) || null == $j("#hidGiftCount").val()){
				gift = false;
			}
		}else{
			buy = false;
			tease = false;
			gift = false;
		}	
		
		html += '--><li>';
		html += '<p class="pic"><a style="cursor:default;background-image:url('+ imgUrl +')">'+ array[i].displayName + '</a></p>';
		html += '<p class="name"><span>'+ array[i].displayName + ' 改め </span><a href="'+UrlConfig.BaseUrl+'/slave/profile?uid='+ array[i].uid+'">'+ nickName +'</a></p>';
		html += '<p class="comment">'+ balloon +'<span></span></p>';
		html += '<ul><!--';
		html += '--><li class="price">市場価値：¥'+ array[i].format_price + '</li><!--';
		html += '--><li class="job">職業：'+ array[i].work_category + '</li><!--';
		html += '--></ul>';
		html += '<ul class="actionList"><!--';	
		
		if(buy){
			html += '--><li class="actionName"><a href="'+ UrlConfig.BaseUrl +'/slave/buyslave?uid='+ array[i].uid+'">購入する</a></li><!--';
		}else{
			html += '--><li class="actionName"><a class="disable">購入する</a></li><!--';
		}
		
		if(tease){
			html += '--><li class="actionComment"><a href="'+ UrlConfig.BaseUrl +'/slave/tease?uid='+ array[i].uid+'">からかう</a></li><!--';
		}else{
			html += '--><li class="actionComment"><a class="disable">からかう</a></li><!--';
		}
		
		if(gift){
			html += '--><li class="actionPoke"><a href="'+ UrlConfig.BaseUrl +'/slave/presentgift?uid='+ array[i].uid+'">ギフトを贈る</a></li><!--';
		}else{
			html += '--><li class="actionPoke"><a class="disable">ギフトを贈る</a></li><!--';
		}
		html += '--></ul><!--/.actionList-->';
		html += '</li><!--';		
	}
	html += '--></ul>';
	return html;
}




	            	