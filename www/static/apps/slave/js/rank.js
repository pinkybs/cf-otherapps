/**
 * rank(/slave/rank.js)
 * slave rank
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/23    zhangxin
 */

var CONST_DEFAULT_PAGE_SIZE = 5;


//jquery scroll bar
var objRank1 = {
	scrollFrame : '#memberFrame1',
	innaerFrame : '#innaerFrame1',
	navLeft : '#btnLeft1',
	navRight : '#btnRight1',
	moveSetp : 82,
	minWidth : 0,
	maxWidth : 0
};

//jquery scroll bar
var objRank2 = {
	scrollFrame : '#memberFrame2',
	innaerFrame : '#innaerFrame2',
	navLeft : '#btnLeft2',
	navRight : '#btnRight2',
	moveSetp : 82,
	minWidth : 0,
	maxWidth : 0
};

//jquery scroll bar
var objRank3 = {
	scrollFrame : '#memberFrame3',
	innaerFrame : '#innaerFrame3',
	navLeft : '#btnLeft3',
	navRight : '#btnRight3',
	moveSetp : 82,
	minWidth : 0,
	maxWidth : 0
};

//jquery scroll bar
var objRank4 = {
	scrollFrame : '#memberFrame4',
	innaerFrame : '#innaerFrame4',
	navLeft : '#btnLeft4',
	navRight : '#btnRight4',
	moveSetp : 82,
	minWidth : 0,
	maxWidth : 0
};

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	
	//init rank price my mixi
	initPriceFriendRank();
	
	//init rank price all 
	initPriceAllRank();
	
	//init rank fortune my mixi
	initTotalFriendRank();
	
	//init rank fortune all
	initTotalAllRank();

	adjustHeight();
});

/********************** rank price friend  ***************************/
function initPriceFriendRank()
{
	//goto bottom rank
	$j('#btnLeftMore1').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankfriend';
		var rankStart = parseInt($j('#hidCntRank1').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
		var rankEnd = $j('#hidCntRank1').val();
		listPriceFriendRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore');		
		$j('#btnRightMore1').removeAttr("disabled");
		return false;
	});
	
	//goto top rank
	$j('#btnRightMore1').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankfriend';
		var rankStart = 3;
		var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
		listPriceFriendRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore');
		$j('#btnLeftMore1').removeAttr("disabled");			
		return false;
	});
	
	//left one
	$j('#btnLeft1').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
			return;
		}
		var pos = measurePosition(objRank1);
		if (pos < objRank1.minWidth) {
			$j(this).attr("disabled","disabled");
			slide('left',objRank1);
			$j('#btnRightMore1').removeAttr("disabled");
		}
		else {
			var cntMax = parseInt($j('#hidCntRank1').val());
			if (parseInt($j('#hidRanknext1').val()) < cntMax) {
				$j(this).attr("disabled","disabled");
				var end = (parseInt($j('#hidRanknext1').val()) + CONST_DEFAULT_PAGE_SIZE);
				var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext1').val())) : CONST_DEFAULT_PAGE_SIZE;
				var start = parseInt($j('#hidRanknext1').val()) + 1;
				var end = end > cntMax ? cntMax : end;
				
				listPriceFriendRank($j('#hidRankprev1').val(), end, start, cntSize, 'leftStep');
			}
		}
		return false;
	});
	
	//right one
	$j('#btnRight1').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank1').val()) <= 7) {
			return;
		}
		var pos = measurePosition(objRank1);
		if (pos > objRank1.maxWidth) {			
			$j(this).attr("disabled","disabled");
			slide('right',objRank1);
			$j('#btnLeftMore1').removeAttr("disabled");
		}
		else {
			if (parseInt($j('#hidRankprev1').val()) > 3) {
				$j(this).attr("disabled","disabled");
				var start = (parseInt($j('#hidRankprev1').val()) - CONST_DEFAULT_PAGE_SIZE);
				var cntSize = start<3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
				start = start > 3 ? start : 3;
				var end = $j('#hidRanknext1').val();
				
				listPriceFriendRank(start, end, start, cntSize, 'rightStep');
			}
		}
		
		return false;
	});
}


/**
 * jquery list price friend ranking
 * @param  integer rankStart
 * @param  integer rankEnd
 * @param  integer cntSize
 * @param  string moveMethod [rightMore / leftMore / rightStep / leftStep]
 * @return void
 */
function listPriceFriendRank(rankStart, rankEnd, cntStart, cntSize, moveMethod)
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankfriend';
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "rankStart=" + cntStart + "&fetchSize=" + cntSize,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var aryInfo = responseObject.info;
	            	$j('#hidRankprev1').val(rankStart);
	            	$j('#hidRanknext1').val(rankEnd);	            	  	
	            	if (null != aryInfo && 0!=aryInfo.length) {
	            		var strHtml = showRankInfo(aryInfo);
	            		if ('rightMore' == moveMethod || 'leftMore' == moveMethod) {
	            			$j('#olRank1').html('').append(strHtml);
	            			initMoveBar(objRank1, 0, 0);
	            		}
	            		else if ('rightStep' == moveMethod) {
	            			$j('#olRank1').append(strHtml);
	            			initMoveBar(objRank1, 0, cntSize * objRank1.moveSetp);
							slide('right',objRank1);
							$j('#btnLeftMore1').removeAttr("disabled");
	            		}
	            		else if ('leftStep' == moveMethod) {
	            			$j('#olRank1').html(strHtml + $j('#olRank1').html());
	            			$j(objRank1.innaerFrame).css('left', 0 + (-1) * cntSize * objRank1.moveSetp);
							initMoveBar(objRank1, 0, cntSize * objRank1.moveSetp);				
							slide('left',objRank1);
							$j('#btnRightMore1').removeAttr("disabled");
	            		}
	            		return true;
	            	}
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

/********************** rank price friend end ***************************/


/********************** rank price all  ***************************/
/**
 * jquery init rank price all 
 *
 * @return void
 */
function initPriceAllRank()
{
	//goto bottom rank
	$j('#btnLeftMore2').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankall';
		var rankStart = parseInt($j('#hidCntRank2').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
		var rankEnd = $j('#hidCntRank2').val();
		listPriceAllRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore');		
		$j('#btnRightMore2').removeAttr("disabled");
		return false;
	});
	
	//goto top rank
	$j('#btnRightMore2').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankall';
		var rankStart = 3;
		var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
		listPriceAllRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore');
		$j('#btnLeftMore2').removeAttr("disabled");			
		return false;
	});
	
	//left one
	$j('#btnLeft2').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
			return;
		}
		var pos = measurePosition(objRank2);
		if (pos < objRank2.minWidth) {
			$j(this).attr("disabled","disabled");
			slide('left',objRank2);
			$j('#btnRightMore2').removeAttr("disabled");
		}
		else {
			var cntMax = parseInt($j('#hidCntRank2').val());
			if (parseInt($j('#hidRanknext2').val()) < cntMax) {
				$j(this).attr("disabled","disabled");
				var end = (parseInt($j('#hidRanknext2').val()) + CONST_DEFAULT_PAGE_SIZE);
				var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext2').val())) : CONST_DEFAULT_PAGE_SIZE;
				var start = parseInt($j('#hidRanknext2').val()) + 1;
				var end = end > cntMax ? cntMax : end;
				listPriceAllRank($j('#hidRankprev2').val(), end, start, cntSize, 'leftStep');		
			}
		}
		return false;
	});
	
	//right one
	$j('#btnRight2').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank2').val()) <= 7) {
			return;
		}
		var pos = measurePosition(objRank2);
		if (pos > objRank2.maxWidth) {			
			$j(this).attr("disabled","disabled");
			slide('right',objRank2);
			$j('#btnLeftMore2').removeAttr("disabled");
		}
		else {
			if (parseInt($j('#hidRankprev2').val()) > 3) {
				$j(this).attr("disabled","disabled");
				var start = (parseInt($j('#hidRankprev2').val()) - CONST_DEFAULT_PAGE_SIZE);
				var cntSize = start < 3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
				start = start > 3 ? start : 3;
				var end = $j('#hidRanknext2').val();
				listPriceAllRank(start, end, start, cntSize, 'rightStep');
			}
		}
		return false;
	});
}

/**
 * jquery list price all ranking
 * @param  integer rankStart
 * @param  integer rankEnd
 * @param  integer cntSize
 * @param  string moveMethod [rightMore / leftMore / rightStep / leftStep]
 * @return void
 */
function listPriceAllRank(rankStart, rankEnd, cntStart, cntSize, moveMethod)
{
	
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listpricerankall';
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "rankStart=" + cntStart + "&fetchSize=" + cntSize,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var aryInfo = responseObject.info;
	            	$j('#hidRankprev2').val(rankStart);
	            	$j('#hidRanknext2').val(rankEnd);	            	  	
	            	if (null != aryInfo && 0!=aryInfo.length) {
	            		var strHtml = showRankInfo(aryInfo);
	            		if ('rightMore' == moveMethod || 'leftMore' == moveMethod) {
	            			$j('#olRank2').html('').append(strHtml);
	            			initMoveBar(objRank2, 0, 0);
	            		}
	            		else if ('rightStep' == moveMethod) {
	            			$j('#olRank2').append(strHtml);
	            			initMoveBar(objRank2, 0, cntSize * objRank2.moveSetp);
							slide('right',objRank2);
							$j('#btnLeftMore2').removeAttr("disabled");
	            		}
	            		else if ('leftStep' == moveMethod) {
	            			$j('#olRank2').html(strHtml + $j('#olRank2').html());
	            			$j(objRank2.innaerFrame).css('left', 0 + (-1) * cntSize * objRank2.moveSetp);
							initMoveBar(objRank2, 0, cntSize * objRank2.moveSetp);
							slide('left',objRank2);
							$j('#btnRightMore2').removeAttr("disabled");
	            		}						
	            		return true;
	            	}
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
/********************** rank price all end ***************************/



/********************** rank total friend  ***************************/

/**
 * jquery init rank Total Friend 
 *
 * @return void
 */
function initTotalFriendRank()
{
	//goto bottom rank
	$j('#btnLeftMore3').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankfriend';
		var rankStart = parseInt($j('#hidCntRank3').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
		var rankEnd = $j('#hidCntRank3').val();
		listTotalFriendRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore');		
		$j('#btnRightMore3').removeAttr("disabled");
		return false;
	});
	
	//goto top rank
	$j('#btnRightMore3').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankfriend';
		var rankStart = 3;
		var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
		listTotalFriendRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore');
		$j('#btnLeftMore3').removeAttr("disabled");			
		return false;
	});
	
	//left one
	$j('#btnLeft3').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
			return;
		}
		var pos = measurePosition(objRank3);
		if (pos < objRank3.minWidth) {
			$j(this).attr("disabled","disabled");
			slide('left',objRank3);
			$j('#btnRightMore3').removeAttr("disabled");
		}
		else {
			var cntMax = parseInt($j('#hidCntRank3').val());
			if (parseInt($j('#hidRanknext3').val()) < cntMax) {
				$j(this).attr("disabled","disabled");
				var end = (parseInt($j('#hidRanknext3').val()) + CONST_DEFAULT_PAGE_SIZE);
				var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext3').val())) : CONST_DEFAULT_PAGE_SIZE;
				var start = parseInt($j('#hidRanknext3').val()) + 1;
				var end = end > cntMax ? cntMax : end;
				
				listTotalFriendRank($j('#hidRankprev3').val(), end, start, cntSize, 'leftStep');
			}
		}
		return false;
	});
	
	//right one
	$j('#btnRight3').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank3').val()) <= 7) {
			return;
		}
		var pos = measurePosition(objRank3);
		if (pos > objRank3.maxWidth) {			
			$j(this).attr("disabled","disabled");
			slide('right',objRank3);
			$j('#btnLeftMore3').removeAttr("disabled");
		}
		else {
			if (parseInt($j('#hidRankprev3').val()) > 3) {
				$j(this).attr("disabled","disabled");
				var start = (parseInt($j('#hidRankprev3').val()) - CONST_DEFAULT_PAGE_SIZE);
				var cntSize = start<3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
				start = start > 3 ? start : 3;
				var end = $j('#hidRanknext3').val();
				
				listTotalFriendRank(start, end, start, cntSize, 'rightStep');
			}
		}
		
		return false;
	});	
}

/**
 * jquery list total friend ranking
 * @param  integer rankStart
 * @param  integer rankEnd
 * @param  integer cntSize
 * @param  string moveMethod [rightMore / leftMore / rightStep / leftStep]
 * @return void
 */
function listTotalFriendRank(rankStart, rankEnd, cntStart, cntSize, moveMethod)
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankfriend';
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "rankStart=" + cntStart + "&fetchSize=" + cntSize,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var aryInfo = responseObject.info;
	            	$j('#hidRankprev3').val(rankStart);
	            	$j('#hidRanknext3').val(rankEnd);	            	  	
	            	if (null != aryInfo && 0!=aryInfo.length) {
	            		var strHtml = showRankInfo(aryInfo);
	            		if ('rightMore' == moveMethod || 'leftMore' == moveMethod) {
	            			$j('#olRank3').html('').append(strHtml);
	            			initMoveBar(objRank3, 0, 0);
	            		}
	            		else if ('rightStep' == moveMethod) {
	            			$j('#olRank3').append(strHtml);
	            			initMoveBar(objRank3, 0, cntSize * objRank3.moveSetp);
							slide('right',objRank3);
							$j('#btnLeftMore3').removeAttr("disabled");
	            		}
	            		else if ('leftStep' == moveMethod) {
	            			$j('#olRank3').html(strHtml + $j('#olRank3').html());
	            			$j(objRank3.innaerFrame).css('left', 0 + (-1) * cntSize * objRank3.moveSetp);
							initMoveBar(objRank3, 0, cntSize * objRank3.moveSetp);				
							slide('left',objRank3);
							$j('#btnRightMore3').removeAttr("disabled");
	            		}
	            		return true;
	            	}
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

/********************** rank total friend end ***************************/


/********************** rank total all  ***************************/
function initTotalAllRank()
{
	//goto bottom rank
	$j('#btnLeftMore4').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank4').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankall';
		var rankStart = parseInt($j('#hidCntRank4').val()) - CONST_DEFAULT_PAGE_SIZE + 1;
		var rankEnd = $j('#hidCntRank4').val();
		listTotalAllRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'leftMore');		
		$j('#btnRightMore4').removeAttr("disabled");
		return false;
	});
	
	//goto top rank
	$j('#btnRightMore4').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank4').val()) <= 7) {
			return;
		}
		$j(this).attr("disabled","disabled");
		var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankall';
		var rankStart = 3;
		var rankEnd = rankStart + CONST_DEFAULT_PAGE_SIZE - 1;
		listTotalAllRank(rankStart, rankEnd, rankStart, CONST_DEFAULT_PAGE_SIZE, 'rightMore');
		$j('#btnLeftMore4').removeAttr("disabled");			
		return false;
	});
	
	//left one
	$j('#btnLeft4').click(function(){
		if ('disabled' == $j(this).attr("disabled")) {
			return;
		}
		var pos = measurePosition(objRank4);
		if (pos < objRank4.minWidth) {
			$j(this).attr("disabled","disabled");
			slide('left',objRank4);
			$j('#btnRightMore4').removeAttr("disabled");
		}
		else {
			var cntMax = parseInt($j('#hidCntRank4').val());
			if (parseInt($j('#hidRanknext4').val()) < cntMax) {
				$j(this).attr("disabled","disabled");
				var end = (parseInt($j('#hidRanknext4').val()) + CONST_DEFAULT_PAGE_SIZE);
				var cntSize = end > cntMax ? (cntMax - parseInt($j('#hidRanknext4').val())) : CONST_DEFAULT_PAGE_SIZE;
				var start = parseInt($j('#hidRanknext4').val()) + 1;
				var end = end > cntMax ? cntMax : end;
				
				listTotalAllRank($j('#hidRankprev4').val(), end, start, cntSize, 'leftStep');
			}
		}
		return false;
	});
	
	//right one
	$j('#btnRight4').click(function(){
		if ('disabled' == $j(this).attr("disabled") || parseInt($j('#hidCntRank4').val()) <= 7) {
			return;
		}
		var pos = measurePosition(objRank4);
		if (pos > objRank4.maxWidth) {			
			$j(this).attr("disabled","disabled");
			slide('right',objRank4);
			$j('#btnLeftMore4').removeAttr("disabled");
		}
		else {
			if (parseInt($j('#hidRankprev4').val()) > 3) {
				$j(this).attr("disabled","disabled");
				var start = (parseInt($j('#hidRankprev4').val()) - CONST_DEFAULT_PAGE_SIZE);
				var cntSize = start<3 ? (start + 2) : CONST_DEFAULT_PAGE_SIZE;
				start = start > 3 ? start : 3;
				var end = $j('#hidRanknext4').val();
				
				listTotalAllRank(start, end, start, cntSize, 'rightStep');
			}
		}
		
		return false;
	});
}


/**
 * jquery list total all ranking
 * @param  integer rankStart
 * @param  integer rankEnd
 * @param  integer cntSize
 * @param  string moveMethod [rightMore / leftMore / rightStep / leftStep]
 * @return void
 */
function listTotalAllRank(rankStart, rankEnd, cntStart, cntSize, moveMethod)
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listtotalrankall';
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "rankStart=" + cntStart + "&fetchSize=" + cntSize,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var aryInfo = responseObject.info;
	            	$j('#hidRankprev4').val(rankStart);
	            	$j('#hidRanknext4').val(rankEnd);	            	  	
	            	if (null != aryInfo && 0!=aryInfo.length) {
	            		var strHtml = showRankInfo(aryInfo);
	            		if ('rightMore' == moveMethod || 'leftMore' == moveMethod) {
	            			$j('#olRank4').html('').append(strHtml);
	            			initMoveBar(objRank4, 0, 0);
	            		}
	            		else if ('rightStep' == moveMethod) {
	            			$j('#olRank4').append(strHtml);
	            			initMoveBar(objRank4, 0, cntSize * objRank4.moveSetp);
							slide('right',objRank4);
							$j('#btnLeftMore4').removeAttr("disabled");
	            		}
	            		else if ('leftStep' == moveMethod) {
	            			$j('#olRank4').html(strHtml + $j('#olRank4').html());
	            			$j(objRank4.innaerFrame).css('left', 0 + (-1) * cntSize * objRank4.moveSetp);
							initMoveBar(objRank4, 0, cntSize * objRank4.moveSetp);				
							slide('left',objRank4);
							$j('#btnRightMore4').removeAttr("disabled");
	            		}
	            		return true;
	            	}
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
/********************** rank total all end ***************************/


/*********************** rank common ********************************/
/**
 * jquery show rank info
 * @param  object array
 * @return string
 */
function showRankInfo(array) 
{

	//concat html tags to array data
    var html = '';      
    if (null == array ||0 == array.length) {    	
    	return html;
    }
      
	html += '<!--';
    //for each row data    
    for (var i = 0 ; i < array.length ; i++) {
        html += '--><li>';
        html += '<p class="ranking">' + array[i].rankNo + '</p>';
        if (0 == array[i].uid) {
        	html += '<p class="name"><a href="javascript:void(0);" onclick="mixi_invite();return false;">' + array[i].name + '</a></p>';
        }
        else {
        	html += '<p class="name"><a href="' + UrlConfig.BaseUrl + '/slave/profile?uid=' + array[i].uid + '">' + array[i].name + '</a></p>';
        }
        if (0 == array[i].uid) {
        	html += '<p class="pic"><a href="javascript:void(0);" onclick="mixi_invite();return false;" style="background-image: url(' + UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_s_4.png);"><img src="' + UrlConfig.StaticUrl + '/apps/slave/img/dummy/spacer.gif" width="76px" height="76px" alt="' + array[i].name + '" /></a></p>';
        }
        else {
        	html += '<p class="pic"><a href="' + UrlConfig.BaseUrl + '/slave/profile?uid=' + array[i].uid + '"  style="background-image: url(' + array[i].pic + ');"><img src="' + UrlConfig.StaticUrl + '/apps/slave/img/dummy/spacer.gif" width="76px" height="76px" alt="' + array[i].name + '" /></a></p>';
        }
        html += '<p class="price">' + array[i].format_price + '</p>'; 
        html += '</li><!--';
    }
    html += '-->';
    return html;
}

/**
 * jquery init move bar
 * @param  object array
 * @param  integer leftAdd
 * @param  integer rightAdd
 * @return void
 */
function initMoveBar(objStaticRank, leftAdd, rightAdd)
{	
	if (0 == leftAdd && 0 == rightAdd) {
		var listSize = $j(objStaticRank.innaerFrame).find('li').size()-1;
		objStaticRank.maxWidth = objStaticRank.moveSetp * (listSize-4) * (-1);
		objStaticRank.minWidth = 0;
		$j(objStaticRank.innaerFrame).css('left', '0');
	}
	else {
		objStaticRank.minWidth += leftAdd;
		objStaticRank.maxWidth -= rightAdd;		
	}
}

/**
 * jquery slide
 * @param  string direction
 * @param  object objStaticRank
 * @param  integer moveStep
 * @return void
 */
function slide(direction,objStaticRank,moveStep)
{
	if(direction == 'left'){
		if (null == moveStep) {
			moveStep = objStaticRank.moveSetp;
		}
		$j(objStaticRank.innaerFrame).animate({left : '+=' + moveStep},300, function() {
			//var pos = measurePosition(objStaticRank);
			//if (pos > 0) {
			//		$j(objStaticRank.innaerFrame).css('left', '0px');
			//}
			$j(objStaticRank.navLeft).removeAttr("disabled");
		});
	} else if(direction == 'right'){
		$j(objStaticRank.innaerFrame).animate({left : '-=' + objStaticRank.moveSetp},300, function() {
			//var pos = measurePosition(objStaticRank);
			//if (pos < maxWidth) {
			//		$j(objStaticRank.innaerFrame).css('left',  maxWidth + 'px');
			//}
			$j(objStaticRank.navRight).removeAttr("disabled");
		});
	}
}

/**
 * jquery measure postion
 * @param  object objStaticRank
 * @return integer
 */
function measurePosition(objStaticRank)
{
	var nowPosition = $j(objStaticRank.innaerFrame).css('left');
	nowPosition = nowPosition.replace('px','');
	return nowPosition;
}

/*********************** rank common end ********************************/