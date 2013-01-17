/**
 * home(/slave/home.js)
 * slave home
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/06/23    zhangxin
 */

var CONST_DEFAULT_PAGE_SIZE = 1;

var objPane = {
	scrolledArea : '#newsFeed',
	evenList : '#newsFeed ul li:even',
	btnList : '#doreiActionBox1 ul li ul.actionList li a'
};

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	
	//daily visit gift
	//if (null != $j('#hidValid').val() && '' != $j('#hidValid').val()) {
	//	$j('#btnSel').click(visitGift);
	//}
		
	//listFeed($j('#hidUid').val(), 1);
	cm_initScrollPane(objPane);
	
	$j('#btnOwn').click(function() {		
		listFeed($j('#hidUid').val(), 1);
		return false;
	});
	$j('#btnMyMixi').click(function() {		
		listFeed($j('#hidUid').val(), 0);
		return false;
	});
	
	//gift info
	if (0 < parseInt($j("#hidCntGift").val())) {
	
		$j("#btnLeft").click(function() {
			var nextPage = (parseInt($j("#pageIndex").val()) - 1 < 1) ? $j("#hidCntGift").val() : parseInt($j("#pageIndex").val()) - 1; 
	    	giftNavAction(nextPage);
	    	return false;
	    });
	    
	    $j("#btnRight").click(function() {
	    	var nextPage = (parseInt($j("#pageIndex").val()) + 1 > parseInt($j("#hidCntGift").val())) ? 1 : parseInt($j("#pageIndex").val()) + 1; 
	    	giftNavAction(nextPage);
	    	return false;
	    });
	    
		//giftNavAction(1);
	}
	
	adjustHeight();
	
	//activity
	/*if (null != $j('#activity') && $j('#activity').val()!=undefined && ''!= $j('#activity').val()) {
		var aryAct = $j('#activity').val().split('|');
		for (var i=0; i<aryAct.length; i++) {
			if ('' != aryAct[i]) {
				postActivityWithPic(aryAct[i],$j('#activityPic').val());
			}
		}
		//tar act
		var aryActTar = $j('#activityNew').val().split('|');
		for (var i=0; i<aryActTar.length; i++) {
			if ('' != aryActTar[i]) {
				var aryData = aryActTar[i].split('&');
				postActivityWithPic(aryData[0], $j('#activityPic').val(), '',  aryData[1]);
			}
		}
	}*/
	
	if (null != $j('#isTodayFirstLogin') && $j('#isTodayFirstLogin').val()!=undefined && ''!= $j('#isTodayFirstLogin').val()) {		
		$j('#overlay').show();
		$j('#uploadBox').show();
	}
});

function startGame()
{
	$j('#uploadBox').hide();
	$j('#overlay').toggle('slow');
}

/**
 * jquery daily visit gift
 *
 * @return void
 */
function visitGift() 
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/dailygift';
	try {
	    $j.ajax({
		    type: "GET",
		    url: ajaxUrl,
		    data: 'rdoGift=' + $j('input:radio[name=rdoGift]:checked').val() + '&valid=' + $j('#hidValid').val(),
		    dataType: "text",
		    success: function(response) {
	            //show response array data to list table
	            if ('false' == response) {
					$j('#uploadBody').html('ビジターギフト失敗しました。');
	            }
	            else {
	            	$j('#uploadBody').html('そんなあなたには、特別に' + response + '円をプレゼント♪');
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

	setTimeout(function() {
		$j('#uploadBox').toggle('slow');
		$j('#overlay').toggle('slow');
		listFeed($j('#hidUid').val(), 1);
	}, 3000);
	return false;
}

/**
 * jquery get the gift list
 *
 * @return void
 */
function giftNavAction(page)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/getgiftinfo';

	$j("#pageIndex").val(page);
	//$j('#divgift').fadeOut('slow');
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "pageIndex=" + page + "&pageSize=" + CONST_DEFAULT_PAGE_SIZE + '&uid=' + $j('#hidUid').val(),
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var strHtml = showGiftInfo(responseObject.info);	           
	            	$j('#divgift').html(strHtml).fadeIn('fast');            	
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

/**
 * jquery show gift info
 * @param  object array
 * @return string
 */
function showGiftInfo(array)
{

    //concat html tags to array data
    var html = '';      
	var img = UrlConfig.StaticUrl + '/apps/slave/img/dummy/pic_n_s.png';
    if (null != array.gift_big_pic && '' != array.gift_big_pic) {
		img = array.gift_big_pic;
	}
	
	//html += '<p class="pic"><a href="' + UrlConfig.BaseUrl + '/slave/torakuten?gid=' + encodeURIComponent(array.gid) + '" style="background-image:url(' + img + ')">' + cm_escapeHtml(array.name) + '</a></p>';
	html += '<p class="pic" style="cursor:default;"><span style="background-image:url(' + img + ')">' + cm_escapeHtml(array.name) + '</span></p>';
	html += '<p class="name">' + cm_escapeHtml(array.name) + '</p>';
	html += '<p class="price">価格：¥' + array.format_price + '</p>';
	html += '<ul class="action box"><!--';
	html += '--><li><a href="' + UrlConfig.BaseUrl + '/slave/presentgift?id=' + array.id + '">プレゼントする</a></li><!--';
	html += '--><li><a href="' + UrlConfig.BaseUrl + '/slave/sellgift?id=' + array.id + '">ギフト屋に売る</a></li><!--';
	html += '--></ul><!--/.action.box-->';
	
	$j('#lnkToarkuten').attr('href', UrlConfig.BaseUrl + '/slave/torakuten?gid=' + encodeURIComponent(array.gid));
    return html;
}


/**
 * jquery get the feed list
 *
 * @param  string uid
  * @param  integer mode
 * @return void
 */
function listFeed(uid, mode)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/listfeed';

	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    data: "uid=" + uid + "&mode=" + mode,
		    dataType: "json",
		    success: function(responseObject) {
	            //show response array data to list table
	            if (responseObject) {
	            	var strHtml = showFeedInfo(responseObject.info);	           
	            	$j('#newsFeed').html(strHtml);	  
	            	//init scroll bar
					cm_initScrollPane(objPane);
					adjustHeight();
					
					if (1 == mode) {
						$j('#tabMyMixi').removeClass();
						$j('#tabOwn').addClass('active');
					}
					else {
						$j('#tabOwn').removeClass();
						$j('#tabMyMixi').addClass('active');
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

/**
 * jquery show feed info
 * @param  object array
 * @return string
 */
function showFeedInfo(array) 
{

	//concat html tags to array data
    var html = '';      
    if (null == array ||0 == array.length) {
    	html += '<p>まだ何もありません。</p>';
    	return html;
    }
      
	html += '<ul><!--';
	
    //for each row data    
    for (var i = 0 ; i < array.length ; i++) {
        html += '--><li>';
        html += '<p class="pic"><a class="noLink" style="background-image:url(' + array[i].pic_url + ');"></a></p>';
        html += '<p class="date">[' + array[i].format_time + ']</p>';
        html += '<p class="action">' + array[i].message + '</p>';       
        html += '</li><!--';        
    }
    html += '--></ul>';
    return html;
}
