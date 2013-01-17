/**
 * work(/slave/work.js)
 * slave work
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/07/11    zhangxin
 */

var objWorkUseID = {
	scrolledArea : '#laborFeed',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox ul li ul.actionList li a'
};

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	$j('#btnConfirm').click(doConfirm);
	
	$j("#btnConfirm").css('background-position','-331px');
	$j("#btnConfirm").css('cursor','default');
	
	$j('#btnBack').click(goBack);
	$j('#btnWork').click(doWork);
	
	//init work list data
	initWorkList();	
	adjustHeight();
});

/**
 * jquery init work list data
 *
 * @return void
 */
function initWorkList() 
{
	//bind event
	$j("#ulSelWork > li").each(function(){
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
			$j("#ulSelWork > li").each(function() { $j(this).removeClass(); });				
			//add select css
			$j(this).addClass("selected");
			$j(this).addClass("active");
			
			var selWorkNeedHp = $j("#ulSelWork > li.selected").find('input.whealth').val();	
					
			if (parseInt($j('#hidTarHealth').val()) < parseInt(selWorkNeedHp)) {
				$j("#btnConfirm").css('background-position','-331px');
				$j("#btnConfirm").css('cursor','default');
			}
			else {
				$j("#btnConfirm").css('background-position','0');
				$j("#btnConfirm").css('cursor','pointer');
			}
		});
	});
	cm_initScrollPane(objWorkUseID);
}

/**
 * jquery go back to edit
 *
 * @return void
 */
function goBack()
{
	if (null != cm_getCookie('app_top_url')) {
		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	}
	$j('#twoColumn').removeClass();
	$j('#step2').hide();
	$j('#step1').show();
	return false;
}

/**
 * jquery work confirm check
 *
 * @return void
 */
function doConfirm()
{		
	var selWork = $j("#ulSelWork > li.selected").find('input.wid').val();
	var selWorkNeedHp = $j("#ulSelWork > li.selected").find('input.whealth').val();
	var msg = '';
	
	//must select work
	if (null == selWork || '' == selWork || 0 == selWork) {
		msg = '働かせる内容を選択してください。';		
	}	
	//health not enough
	else if (parseInt($j('#hidTarHealth').val()) < parseInt(selWorkNeedHp)) {
	    msg = '体力が不足です。';		
	}	
	if ('' != msg) {
		//alert(msg);	
		return;
	}
	
	if (null != cm_getCookie('app_top_url')) {
		top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	}
	$j('#confirmWorkPic').html($j("#ulSelWork > li.selected").find('p.pic').html());
	$j('#confirmWorkName').html($j("#ulSelWork > li.selected").find('p.work').html());
	$j('#confirmWorkPay').html($j("#ulSelWork > li.selected").find('ul > li.pay').html());
	$j('#confirmWorkHealth').html($j("#ulSelWork > li.selected").find('ul > li.cosumePower').html());
	$j('#twoColumn').addClass('workConfirm');
	$j('#step1').hide();
	$j('#step2').show();
	
	return false;
}

/**
 * jquery do work check
 *
 * @return void
 */
function doWork()
{
	var selWork = $j("#ulSelWork > li.selected").find('input.wid').val();
	var selWorkNeedHp = $j("#ulSelWork > li.selected").find('input.whealth').val();
	var msg = '';
	
	//must select work
	if (null == selWork || '' == selWork || 0 == selWork) {
		msg = '働かせる内容を選択してください。';		
	}	
	//health not enough
	else if (parseInt($j('#hidTarHealth').val()) < parseInt(selWorkNeedHp)) {
	    msg = '体力が不足です。';		
	}	
	if ('' != msg) {
		//alert(msg);	
		return;
	}
	
	$j('#twoColumn').removeClass();
	$j('#btnWork').attr("disabled", "disabled");
	$j('#step2').hide();
	//$j('#loading').show();
	work(selWork);
	return false;
}
	
/**
 * jquery work
 * @param string workId
 *
 * @return void
 */
function work(workId)
{		
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/work';
		
	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    data: "uid=" + $j('#hidTarUid').val() + "&workId=" + workId,
		    dataType: "json",
		    success: function(responseObject) {
		    	if (null != cm_getCookie('app_top_url')) {
					top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
				}
		    	//$j('#loading').hide();
	            //show response array data to display
	            if (responseObject == 'false') {
	            	$j('#step3').html('<p class="intro">強制労働させる失敗しました。</p>');
	            	$j('#step3').show();
	            }
	            else {
	            	if ('' != responseObject.price_up_percent) {
	            		$j('#lblWorkGet2').html(responseObject.work_gain);
	            		$j('#lblNextTitle').html(responseObject.next_level);
	            		$j('#lblUpPercent').html(responseObject.price_up_percent);	            			
	            		$j('#lblTotal2').html(responseObject.after_work_assets);
	            		var afterRank = responseObject.after_work_assets_rank;
	            		var beforeRank = $j('#hidMyTotalRank').val();
	            		var upOrDown = '→';
	            		if (parseInt(afterRank) > parseInt(beforeRank)) {
	            			upOrDown = '↓';
	            		}
	            		else if (parseInt(afterRank) < parseInt(beforeRank)) {
	            			upOrDown = '↑';
	            		}
	            		$j('#lblTotalRank2').html(afterRank + '位' + upOrDown);        			
	            		$j('#lblPrice').html(responseObject.after_work_price);
	            		var afterRankS = responseObject.after_work_price_rank;
	            		var beforeRankS = $j('#hidTarPriceRank').val();
	            		var upOrDownS = '→';
	            		if (parseInt(afterRankS) > parseInt(beforeRankS)) {
	            			upOrDownS = '↓';
	            		}
	            		else if (parseInt(afterRankS) < parseInt(beforeRankS)) {
	            			upOrDownS = '↑';
	            		}
	            		$j('#lblPriceRank').html(afterRankS + '位' + upOrDownS);	
	            			
	            		$j('#twoColumn').addClass('workFinish2');
	            		$j('#step4').show();            		
	            	}
	            	else {
	            		$j('#lblWorkGet').html(responseObject.work_gain);         			
	            		$j('#lblTotal').html(responseObject.after_work_assets);
	            		var afterRank = responseObject.after_work_assets_rank;
	            		var beforeRank = $j('#hidMyTotalRank').val();
	            		var upOrDown = '→';
	            		if (parseInt(afterRank) > parseInt(beforeRank)) {
	            			upOrDown = '↓';
	            		}
	            		else if (parseInt(afterRank) < parseInt(beforeRank)) {
	            			upOrDown = '↑';
	            		}
	            		$j('#lblTotalRank').html(afterRank + '位' + upOrDown);

						$j('#twoColumn').addClass('workFinish1');
						$j('#step3').show();						
	            	}
	            	//send mixi activity
                    //alert('info' + responseObject.activity_info + 'pic : ' + responseObject.activity_pic);
                    postActivityWithPic(responseObject.activity_info, responseObject.activity_pic);
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
}