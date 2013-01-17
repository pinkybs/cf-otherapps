/*
----------------------------------------------
search result list JavaScript

Created Date: 2009/06/26
Author: xiali
----------------------------------------------
*/
var CONST_DEFAULT_PAGE_SIZE = 10;

$j(document).ready(function() {
	//sort butten css init
	cssInit();
	
	$j("#txtKeyWord").focus();
	//sort menu change
	$j("#sortDesc").click(function (){
		if ('1' == $j("#hidSort").val()) {
			return;
		}		
		$j("#hidSort").val(1);	
		changePageAction(1, "#sortDesc", "#sort");
	});
	$j("#sort").click(function (){
		if ('2' == $j("#hidSort").val()) {
			return;
		}
		
		$j("#hidSort").val(2);
		changePageAction(1, "#sort", "#sortDesc");
	});
	
	$j('#txtKeyWord').keypress(function(event) {
    	if ( event.keyCode == 13 && cm_trimAll($j('#txtKeyWord').val()) != '' ) {
    		var frmSearchResult = $j("#SearchResultFrm");
			frmSearchResult[0].action = UrlConfig.BaseUrl + '/slave/searchresult';
			frmSearchResult[0].submit();
    	}
    }); 
	
	$j("#searchResultBtn").click(function (){
		if(null == cm_trimAll($j("#txtKeyWord").val())|| '' == cm_trimAll($j("#txtKeyWord").val())){
			return;
		}
		else{
			var frmSearchResult = $j("#SearchResultFrm");
			frmSearchResult[0].action = UrlConfig.BaseUrl + '/slave/searchresult';
			frmSearchResult[0].submit();
		}
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
    if ($j("#hidSort").val() == "1"){
        sortAddCss("#sortDesc","#sort");
    }
    else {
        sortAddCss("#sort","#sortDesc");
    }
}

/*
chanag page 
*/
function changePageAction(page, desc, asc)
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajax/slave/getgiftsearch';
	var keyWord = $j("#hidKeyWord").val();
	var sort = $j("#hidSort").val();
	
	$j("#pageIndex").val(page);
	
	try {
	    $j.ajax({
		    type: "GET",   
		    url: ajaxUrl,
		    dataType: "json",
		    data: "pageIndex=" + page + "&pageSize=" + CONST_DEFAULT_PAGE_SIZE +
		    	  "&sort=" + sort + "&keyWord=" + keyWord,
		    success: function(responseObject) {     
	            if (responseObject) {            	
	            	//show response array data to list table
	            	var strHtml = showGiftSearchResult(responseObject.info);
	            	
            		//show page Current Info
            		$j('#maxCount').html(responseObject.count == null ? 0 : responseObject.count);
			      	var numstart = (parseInt($j('#pageIndex').val()) - 1) * CONST_DEFAULT_PAGE_SIZE + 1;
			      	var numend = (numstart + CONST_DEFAULT_PAGE_SIZE - 1) > parseInt(responseObject.count) ? responseObject.count : (numstart + CONST_DEFAULT_PAGE_SIZE - 1);
			      	if (0 == responseObject.count) {
			      		numstart = 0;
			      		numend = 0;
			      	}
			      	$j('#lblNumS').html(numstart);
			      	$j('#lblNumB').html(numend);
			      	
            		$j('#searchResult').html('');
            		var nav = cm_showPagerNav(responseObject.count, parseInt($j("#pageIndex").val()), 10);
	            	$j('#searchResult').html(strHtml + nav);
	            	//sort asc css
                    sortAddCss(desc, asc);
                    if (!desc) {
                        if (null != cm_getCookie('app_top_url')) {
                            top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
                        }  
                    }
	            	adjustHeight();      	
				}
			}
		});
	}catch (e) {
		alert(e);
	}
}

/*
show gift Search info
*/
function showGiftSearchResult(array)
{
	var html = '';     
    if (null == array ||0 == array.length) {
    	html += '<p><p><span>「' + $j('#txtKeyWord').val() + '」</span>で検索した結果、何も見つかりませんでした。</p></p>';
    	html += '<p>別のキーワードを入力して、もう一度検索してみてください。</p>';
    	$j("#pInfo").hide();
    	return html;
    }
    var myCash = parseInt($j("#hidMyCash").val());
    
    var gIdStr = $j("#hidFavGidStr").val();
    gIdStr = gIdStr.substr(0 , gIdStr.length-1);
    var gIds = gIdStr.split("|");
    
    html += '<ul><!--';
    //for each row data
    for (var i = 0 ; i < array.length ; i++) { 
   		var isBuy = true;  
   		var isFav = true; 
    	if(myCash < array[i].gift_price){
    		isBuy = false;
    	}
    	
    	if( gIds != null || gIds.length != 0 ){
	    	for(var j = 0 ; j < gIds.length ; j++){
	    		if(gIds[j] == array[i].gid){
	    			isFav = false;
	    		}
	    	}
    	}
    	else{
    		var isFav = true; 
    	}
		html += '--><li>';
		html += '<p class="pic"><a style="cursor:default;background-image:url('+ array[i].gift_small_pic +')">'+ cm_escapeHtml(array[i].gift_name) + '</a></p>';
		html += '<p class="name">'+ cm_escapeHtml(array[i].gift_name) +'</p>';
		html += '<p class="price">価格：¥'+ array[i].gift_format_price+'<span></span></p>';
		
		html += '<ul class="actionList"><!--';
		if(isBuy){
			html += '--><li class="actionName"><a href="'+ UrlConfig.BaseUrl +'/slave/buygift?gid='+ array[i].gid +'">購入する</a></li><!--';
		}
		else{
			html += '--><li class="actionName"><a class="disable"">購入する</a></li><!--';
		}
		
		if(isFav){
			html += '--><li class="actionComment"><a href="'+ UrlConfig.BaseUrl +'/slave/addfav?gid='+ array[i].gid +'">お気に入りに追加</a></li><!--';
		}
		else{
			html += '--><li class="actionComment"><a class="disable">お気に入りに追加</a></li><!--';
		}
		//html += '--><li class="actionPoke"><a class="active" href="'+ UrlConfig.BaseUrl + '/slave/torakuten?gid='+ array[i].gid +'">楽天市場で購入する</a></li><!--';
		html += '--></ul><!--/.actionList-->';
		html += '</li><!--';		
	}
	html += '--></ul>';
	return html;
}
