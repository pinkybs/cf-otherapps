/**
 * add(/chat/add.js)
 * chat add
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/05/31    zhangxin
 */

var CONST_DEFAULT_RELOAD_SECONDS = 10;

var chatUseStaticID = {
	scrollFrame : '#memberFrame',
	innaerFrame : '#innaerFrame',
	navLeft : 'li#frameNavLeft a',
	navRight : 'li#frameNavRight a',
	frameWidth : 590
};

/**
 * windows load function
 * register funcion
 */
$j(document).ready(function() {
	
	//if (null != cm_getCookie('app_top_url')) {
	//	top.location.href = cm_getCookie('app_top_url') +  '#pagetop';
	//}	
	
	$j("#txtName").focus();
	
	//activity
	if (null != $j('#activity') && '' != $j('#activity').val()) {
		var tarActivity = '';
		if ($j('#activityUser').val() != null && $j('#activityUser').val() != '') {
			tarActivity = $j('#activityUser').val();
		}
		postActivity($j('#activity').val(), tarActivity);
	}
	
	if ('' != $j('#chkLastMem').val()) {
		$j("#chkLastMem").click(function() {checkLastSelectMember(this);});
	}
	
	setTimeout('walkclock()', 1000);
	
	//date panel
	$j("#datepicker").datepicker({
		dateFormat: 'yy-mm-dd'
	});
	
	$j("#alertClose").click(function() {
    	$j("#alertBox").fadeOut("normal");
    });
    
    $j('#memberSearchForm').keyup(function(event) {
    	if ( $j('#hidSearch').val() == $j.trim($j("#memberSearchForm").val()) ) {
    		return;
    	}
   		$j('#hidSearch').val($j.trim($j('#memberSearchForm').val()));    		
   		getFriends($j.trim($j('#memberSearchForm').val()));
    });        
            
	$j("#btnInvite").click(doInvite);
	getFriends('');
	adjustHeight();
	
});

function walkclock()
{
	$j('#curDate').val(parseInt($j('#curDate').val()) + 1);
	setTimeout('walkclock()', 1000);
}

/**
 * get the system content list
 *
 * @params filter
 * @return void
 */
function getFriends(filter)
{
    var ajaxUrl = UrlConfig.BaseUrl + '/ajax/chat/getfriends';
	$j("#innaerFrame").html('<img id="ajaxImg" src="' + UrlConfig.StaticUrl + '/cmn/img/loading/ajax_loader.gif"/>');	
	//$j('#memberSearchForm').attr("disabled","disabled");
	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
			data: "filter=" + filter,
		    dataType: "json",
		    success: function(response) {
		    	var strHtml ='';
		    	var intSize = 20;
		    	var intRound = Math.ceil(response.count/intSize);
				$j("#innaerFrame").html('');
				if (0 == response.count) {
					$j('#divError').show();
		    		$j('#divMask').removeClass('hide');
		    	}
		    	else {
			    	for (var j=0; j<intRound; j++) {
			    		strHtml = '<ul><!--';
			    		for (var i=intSize*j; i<intSize*(j+1) && i<response.count; i++) {
							var friendData = response.info[i];
			    			strHtml += '--><li id="li_' + j + '_' + i + '" style="background-image:url(' + friendData.thumbnailUrl + ')"><span>' + friendData.displayName + '</span>' 
			    			        + '<a href="javascript:void(0);" onclick="doSelUser(\'' + j + '_' + i + '\');"><img src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/content/btn_invite_select.png" alt=""/></a>' 
			    			        + '<input type="hidden" value="' + friendData.uid + '" /></li><!--';
						            //+ '<input type="checkbox" name="chkMem[]" id="check' + i + '" value="' + friendData.uid + '" /><label for="check' + i + '"><span>' + friendData.displayName + '</span></label></li><!--';
						}//end for li
			    		strHtml += '--></ul>';		    			
			    		$j("#innaerFrame").append(strHtml);
			    		$j("#innaerFrame").css('left', 0);
			    		strHtml = '';
			    	}//end for ul		    	
			    	$j('#divError').hide();
			    	$j('#divMask').addClass('hide');
			    	initListMoveBar(chatUseStaticID);
		    	}
		    	//$j('#memberSearchForm').removeAttr("disabled");
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
 * do select user 
 * @params liid
 *
 * @return void
 */
function doSelUser(liid)
{
	var objLi = $j('#ulSelected > li.null')[0];
	if (objLi == undefined) {
		$j("#pMessage").html('人数の上限は5人です。');
		$j("#dialog").dialog({
			autoOpen: true,
			bgiframe: true,
			modal: true,
			resizable: false
		}).dialog( 'open' );
		return;
	}
	
	var isExist = false;
	//already add
	$j("#ulSelected > li.selected").find('input').each(function(){
		if ($j('#li_' + liid).find('input').val() == $j(this).val()) {
			isExist = true;
			return;
		}		
	});
	if (isExist) {
		alert('Already added!');
		return;
	}
	
	var selUser = '';
	var curliId = $j(objLi).attr('id');

	var bgImg = $j('#li_' + liid).css('background-image');
	selUser = '<p><span>' + $j('#li_' + liid + ' > span').html() + '</span>' 
	        + '<img src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/spacer.gif" width="76" height="76" alt="" style=\'background-image:' + bgImg + '\' />'
	        + '<a href="javascript:void(0);" onclick="doCancelSel(\'' + curliId + '\');" title="選択解除">選択解除</a></p>' 
	        + '<input name="chkMem[]" type="hidden" value="' + $j('#li_' + liid).find('input').val() + '" />';
       
	$j(objLi).removeClass();
	$j(objLi).addClass('selected');
	$j(objLi).html(selUser);
    return false;
}

/**
 * do cancel select user 
 * @params curliId
 *
 * @return void
 */
function doCancelSel(curliId)
{	
	$j('#' + curliId).removeClass();
	$j('#' + curliId).addClass('null');
	$j('#' + curliId).html('選択されていません');
    return false;
}

/**
 * do invite 
 *
 * @return void
 */
function doInvite()
{
	var msg = '';
	var cntChecked = 0;
	//check needed
	if ( '' == $j.trim($j("#txtName").val()) ) {
		msg = 'チャット名は必要です。';
	}
	else if ( '' == $j.trim($j("#datepicker").val()) ) {
		msg = '開催日時は必要です。';
	}
	else {
		
		$j("#ulSelected > li.selected").find('input').each(function() {
			cntChecked ++;
		})
		if (0 == cntChecked) {
			msg = '招待メンバーは必要です。';
		}
	}
	
	if (msg) {
		$j("#pMessage").html(msg);
		$j("#dialog").dialog({
			autoOpen: true,
			bgiframe: true,
			modal: true,
			resizable: false
		}).dialog( 'open' );
		return;
	}
	
	//check data validate and member limit
	var selDate = $j("#datepicker").val();
	var aryDate = selDate.split('-');
	var curDate = $j('#curDate').val();//new Date();
	selDate = new Date(aryDate[0],aryDate[1] - 1,aryDate[2],$j("#selHour").val(),$j("#selMinute").val());

	if (isNaN(selDate)) {
		msg = '入力内容(開催日時)に誤りがあります。';
	}
	else if (parseInt(curDate*1000) > Date.parse(selDate)) {
		msg = '開催日時が過去でした。';
	}
	else if (5 < cntChecked) {
		msg = '人数の上限は5人です。';
	}
	else if ( $j.trim($j("#txtMessage").val()).length > 400 ) {
		msg = 'メッセージは全角400文字以内で入力して下さい。';
	}
	
	if (msg) {
		$j("#pMessage").html(msg);
		$j("#dialog").dialog({
			autoOpen: true,
			bgiframe: true,
			modal: true,
			resizable: false
		}).dialog( 'open' );
		return;
	}

    $j("#frmInvite").submit();
    return false;
}

function checkLastSelectMember(obj) 
{
	if (obj.checked) {
		$j('#ulSelected > li').each(function(index) {
			$j('#' + this.id).removeClass();
			$j('#' + this.id).addClass('null');
			$j('#' + this.id).html('選択されていません');
		});
		var aryLastMem = $j.json.decode($j('#hidLastMem').val());
		for (var i=0; aryLastMem != null && i<aryLastMem.length; i++) {
			var objLi = $j('#ulSelected > li.null')[0];										
			var curliId = $j(objLi).attr('id');
			var selUser = '';		
				
			selUser = '<p><span>' + aryLastMem[i].displayName + '</span>' 
			        + '<img src="' + UrlConfig.StaticUrl + '/apps/chat/cmn/img/mainArea/spacer.gif" width="76" height="76" alt="" style="background-image:url(' + aryLastMem[i].thumbnailUrl + ');" />'
			        + '<a href="javascript:void(0);" onclick="doCancelSel(\'' + curliId + '\');" title="選択解除">選択解除</a></p>' 
			        + '<input name="chkMem[]" type="hidden" value="' + aryLastMem[i].uid + '" />';
			$j(objLi).removeClass();
			$j(objLi).addClass('selected');
			$j(objLi).html(selUser);	    
		}
	}
	else {
		$j('#ulSelected > li').each(function(index) {
			$j('#' + this.id).removeClass();
			$j('#' + this.id).addClass('null');
			$j('#' + this.id).html('選択されていません');
		});
	}

	return false;
	/*
	$j('#innaerFrame :checkbox').each(function(index) {
		for (var i=0; aryLastMem != null && i<aryLastMem.length; i++) {
			if (aryLastMem[i] == this.value) {
				this.checked = obj.checked;
			}
		}
	});	
	*/
}

/*
function checkCheckCnt(objCheck)
{
	var intCheckNum = 0;
	for (var i = 1; i <= parseInt($F('hidDCnt')); i++) {
		if ($F('chkItem_' + i) != null) {
			intCheckNum += 1;
		}
	}
	
	if (intCheckNum > parseInt($F('hidSType'))) {
		alert($F('hidSType') + '件まで制限する。');
		objCheck.checked = '';
	}
}
*/