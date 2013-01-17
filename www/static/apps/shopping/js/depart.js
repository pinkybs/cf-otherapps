/**
 * depart(/shopping/depart.js)
 * shopping depart
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/08/14    zhangxin
 */

var CONST_DEFAULT_PAGE_SIZE = 10;
var CONST_TIME = 0;

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {			
	$j("#removeBack").click(function (){
	    $j('#overlay').hide();   
	});
	
	$j("#addBack").click(function (){
	   $j('#price').val('');
	   $j('#addImg').attr('src',UrlConfig.StaticUrl + '/apps/shopping/img/content/btn_add_s_d.png');
       $j('#addFinish').css('cursor','default');

       $j('#overlay').hide();
    });
    
    $j("#gameBack").click(function (){
       $j('#overlay').hide();   
    });
    
    if($j('#price').val() == null || $j('#price').val() == ''){
        $j('#addImg').attr('src',UrlConfig.StaticUrl + '/apps/shopping/img/content/btn_add_s_d.png');
        $j('#addFinish').css('cursor','default');
    }
    
    $j("#removeFinish").click(removeCartFinish);
    
    $j("#addFinish").click(addCartFinish);
   
    $j('#price').keyup(function() { 
        priceCss();
    });
    
	adjustHeight();
});


/**
 * change page ajax request
 * @param  integer page
 * @param  boolean isTop
 * @return void
 */
function changePageAction(page, isTop)
{   
    //ajax show list request
    $j("#pageIndex").val(page);
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/listpopitem';
    
    try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    dataType: "json",
		    data: "pageIndex=" + page + "&pageSize=" + CONST_DEFAULT_PAGE_SIZE + "&cid=" + $j("#cid").val(),
		    success: function(responseObject) {	            
	            if (responseObject) {
	            
	                if (responseObject.info == '' && responseObject.count > 0) {
                        $j("#pageIndex").val(page - 1);
                        changePageAction(Number(page - 1));
                        return;
                    }
                    
                    if (Number(responseObject.cartCount) >= 10) {
                        $j('#isEnd').html('<a href="javascript:void(0);" onclick="gameEnd();" id="toGameEnd"><img src="' + UrlConfig.StaticUrl + '/apps/shopping/img/content/ban_adjust.png" alt="" /></a>');
                        $j('#Ptips').hide();
                    }
                    else {
                        $j('#isEnd').html('<img src="' + UrlConfig.StaticUrl + '/apps/shopping/img/content/ban_adjust.png" alt="" />');
                        $j('#Ptips').show();
                    }
                                                    	
	            	//show response array data to list table
	            	var strHtml = showInfo(responseObject.info);
	           
	            	var nav = cm_showPagerNav($j("#cntPop").val(), parseInt($j("#pageIndex").val()), CONST_DEFAULT_PAGE_SIZE);            		
	            	//curnumber
		           	var numstart = (parseInt($j("#pageIndex").val()) - 1) * CONST_DEFAULT_PAGE_SIZE + 1;
		           	var numend = (numstart + CONST_DEFAULT_PAGE_SIZE - 1) > parseInt($j("#cntPop").val()) ? parseInt($j("#cntPop").val()) : (numstart + CONST_DEFAULT_PAGE_SIZE - 1);
		           	$j('#lblStart').html(numstart);
		           	$j('#lblEnd').html(numend);	
		           	$j("#maxCount").html(responseObject.count); 		      	
            		$j('#lstPopItem').html(strHtml);
            		$j('#navPopItem').html(nav);
            		
            		adjustHeight();
            		if (!isTop) {
            		    if (null != cm_getCookie('app_top_url')) {
                            top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
                        }   
            		}                                 	
				}
			}
		});
	}catch (e) {
		//alert(e);
	}  
}

/**
 * show pop item list
 * @param  object array
 * @return string
 */
function showInfo(array)
{		
    //concat html tags to array data
    var html = '';
        					    				
    //for each row data
    for (var i = 0 ; i < array.length ; i++) {
    	var lnkCart = '<a href="javascript:void(0);" onclick="addNext(' + i + ');return false;"><span>ショッピングカートに追加する</span></a>';
    	if ('1' == array[i].is_in_cart) {
        	lnkCart = '<a href="javascript:void(0);" onclick="removeNext(' + i + ');return false;"><span>ショッピングカートからはずす</span></a>';
        }
       
		html += '<div class="section">';
		html += '  <dl class="itemBlock clearfix"><!--';
		html += '    --><dt class="pic" id="pic' + i + '"><a style="background-image:url(' + array[i].pic_small + ')"><img src="' + UrlConfig.StaticUrl + '/apps/shopping/img/spacer.gif" width="76"  height="76" alt="" /></a></dt><!--';
		html += '    --><dd class="name" id="name' + i + '">' + cm_escapeHtml(array[i].name) + '</dd><!--';
		html += '    --><dd class="price">価格：￥?????</dd><!--';
		html += '  --></dl>';
		html += '  <ul class="btnBlock clearfix"><!--';
		html += '    --><li class="remove">' + lnkCart + '</li><!--';
		html += '    --><li class="rakuten"><a href="'+ UrlConfig.BaseUrl +'/shopping/torakuten?iid=' + array[i].iid + '"><span>楽天市場で購入する</span></a></li><!--';
		html += '  --></ul>';
		html += '  <input type="hidden" id="hidIid' + i + '" value="' + array[i].iid + '" />';
		html += '  <input type="hidden" id="hidGuessPrice' + i + '" value="' + array[i].guess_price + '" />';
		html += '</div>';           
    } //end for
    
    return html;
}




//***************************************************************************
function removeCartFinish()
{
    if ($j("#removeFinish").attr('disabled') == 'disabled') {
        return;
    }
    removeCartAction();
}

function addCartFinish()
{
    if ($j("#addFinish").attr('disabled') == 'disabled') {
        return;
    }
    priceCss();
    addCartAction();
    return false;
}

/**
 * remove challenge cart
 *
 * @param integer key
 */
function removeNext(key)
{
	var pic = $j("#pic" + key).html();
	var name = $j("#name" + key).html();
	var iid = $j("#hidIid" + key).val();

    $j('#overlay').show();	                    
    $j('#addCart').hide();
    $j('#gameEndConfim').hide();
    $j('#removeCart').show();  
    $j('#btnList').show();
    
    $j("#itemPic").html(pic);
    $j('#itemName').html(name);
    $j('#hidIid').val(iid);
    $j('#lblPrice1').html($j("#hidGuessPrice" + key).val());
    
    $j('#overBox').css('top', '32%');    
    $j('#sBlankTop').focus();    
    
    $j('#removeMessage1').show();
    $j('#removeMessage2').hide();    
    $j('#removeMessage3').hide();
    
    $j('#goRakuten').attr('href', UrlConfig.BaseUrl + '/shopping/torakuten?iid=' + $j("#hidIid").val());
}

/**
 * add challenge cart
 *
 * @param integer key
 */
function addNext(key)
{
    var pic = $j("#pic" + key).html();
    var name = $j("#name" + key).html();
    var iid = $j("#hidIid" + key).val();
    
    $j('#overlay').show();    
    $j('#removeCart').hide();
    $j('#gameEndConfim').hide();
    $j('#addCart').show();
    $j('#btnList2').show();
    
    $j("#itemPic2").html(pic);
    $j('#itemName2').html(name);
    $j('#hidIid').val(iid);
    
	$j('#overBox').css('top', '32%');    
	$j('#sBlankTop').focus();    
	$j('#price').focus();
    
    $j('#addCartMessage1').show();
    $j('#addCartMessage2').hide();
    $j('#addCartMessage3').hide();
    
    $j('#goRakuten2').attr('href', UrlConfig.BaseUrl + '/shopping/torakuten?iid=' + $j("#hidIid").val());
    
    priceCss();
}

function priceCss()
{
     var strPre = $j('#price').val();  
    /* if(strPre == null || strPre == ''){
         $j('#addImg').attr('src',UrlConfig.StaticUrl + '/apps/shopping/img/content/btn_add_s_d.png');
         $j('#addFinish').css('cursor','default');
         $j("#addFinish").attr("disabled", "disabled");
         return;
     }
     else {  */
         var flag = isNaN(strPre);
         if (flag) {
             $j('#addImg').attr('src',UrlConfig.StaticUrl + '/apps/shopping/img/content/btn_add_s_d.png');
             $j('#addFinish').css('cursor','default');
             $j("#addFinish").attr("disabled", "disabled");
             return;
         }
         else {
            if (strPre < 0) {
                 $j('#addImg').attr('src',UrlConfig.StaticUrl + '/apps/shopping/img/content/btn_add_s_d.png');
	             $j('#addFinish').css('cursor','default');
	             $j("#addFinish").attr("disabled", "disabled");
	             return;
            }
            else {
	             $j('#addImg').attr('src',UrlConfig.StaticUrl + '/apps/shopping/img/content/btn_add_s.png');
	             $j('#addFinish').css('cursor','pointer');
	             $j("#addFinish").removeAttr("disabled");
             }
         }
    // }
}

//remove challenge cart
function removeCartAction()
{
    $j("#removeFinish").attr("disabled", "disabled");    
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/removecart';
    try {
        $j.ajax({
            type: "GET",   
            url: ajaxUrl,
            data: "iid=" + $j('#hidIid').val(),
            dataType: "text",
            success: function(responseText) {
                $j("#removeFinish").removeAttr("disabled");
                
                if (responseText != 'false') {
                    $j('#removeMessage2').show();
                    $j('#removeMessage1').hide();
                    $j('#removeMessage3').hide();
                    
				    $j('#btnList').hide();
				    $j('#lblPrice1').html(responseText);

				    //refresh page
				    changePageAction($j("#pageIndex").val(), 'false');
				    
				    //予想金額
				    var tolalGuess = parseInt($j('#SpTolGuessPrice').html().replace(/,/g, '')) - parseInt($j('#lblPrice1').html().replace(/,/g, ''));
				    $j('#SpTolGuessPrice').html(toFarmat(tolalGuess));
				    
				    //目標金額
				    var moery = parseInt($j('#ChallengeMoery').html().replace(/,/g, ''));
                    var diff = tolalGuess - moery;
                    
                    var strPre = '';                    
                    if (diff > 0) {
                        strPre = '+¥';
                    }
                    else if (diff < 0) {
                        strPre = '-¥';
                    }       
                    
                    //残高
                    diff = Math.abs(diff);
                    $j('#SpDiff').html(strPre + toFarmat(diff));
				    
				    setTimeout(
				                function(){                     
                                    $j('#overlay').hide();            
                                },3000);                        
                }
                else {
                    $j('#removeMessage3').show();
                    $j('#removeMessage2').hide();
                    $j('#removeMessage1').hide();
                }
            }
        });
    }catch (e) {
        //alert(e);
    }
}
//add challenge cart
function addCartAction()
{
    $j("#addFinish").attr("disabled", "disabled");
    var guess_price = Number($j('#price').val());
    if(guess_price == null || guess_price == ''){
        guess_price = 0 ;
    }
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/shopping/addcart';
    try {
        $j.ajax({
            type: "GET",   
            url: ajaxUrl,
            data: "iid=" + $j('#hidIid').val() + "&guess_price=" + guess_price,
            dataType: "text",
            success: function(responseText) {
                $j("#addFinish").removeAttr("disabled");
                
                if (responseText == 'false' || responseText == null) {
                    $j('#addCartMessage3').show();
                    $j('#addCartMessage1').hide();
                    $j('#addCartMessage2').hide();	                               
                }
                else {
                    $j('#addCartMessage2').show();
                    $j('#addCartMessage1').hide();
                    $j('#addCartMessage3').hide();
                    
                    $j('#btnList2').hide();
                    var strPrice = $j('#price').val();
                    if (strPrice == null || strPrice == '') {
                        strPrice = 0 ;
                    } else {
                        strPrice = toFarmat(strPrice);
                    }
                    $j('#itemPrice2').html('予想価格：' + '￥' + strPrice); 

                    //予想金額
                    var tolalGuess = parseInt($j('#SpTolGuessPrice').html().replace(/,/g, '')) + guess_price;
                    $j('#SpTolGuessPrice').html(toFarmat(tolalGuess));
                    
                    //残高
                    var moery = parseInt($j('#ChallengeMoery').html().replace(/,/g, ''));
                    var diff = tolalGuess - moery;
                    
		            var strPre = '';		            
		            if (diff > 0) {
		                strPre = '+¥';
		            }
		            else if (diff < 0) {
		                strPre = '-¥';
		            }       
		            diff = Math.abs(diff);
                    $j('#SpDiff').html(strPre + toFarmat(diff));
                    
                    //refresh page
                    changePageAction($j("#pageIndex").val(), 'false');
                    
                    //5秒間自動遷移
                    setTimeout(
                         function(){                     
                             $j('#overlay').hide();        
                             $j('#itemPrice2').html('予想価格：<input type="text" id="price" onkeyup="priceCss();" />');     
                         },3000);                                      
                }

            }
        });
    }catch (e) {
        //alert(e);
    }
}

//farmat price
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