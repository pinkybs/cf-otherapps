$(function(){

    var ajaxLoad = true;

    $().ready(function() {
        //adjustHeight(710);
    });
    
    $.charashop = {
        /**
         * auto refresh 
         */
        buyHitman : function()
        {
            if ( ajaxLoad ) {
                ajaxLoad = false;
                var hitmanType = $('#hitmanType').val();
                var url = UrlConfig.BaseUrl + '/ajax/dynamite/buyhitman';
        
                $.ajax({
                     type : "POST",
                     url: url,
                     dataType : "json",
                     data: {hitmanType : hitmanType},
                     timeout : 5000,
                     /*error : function () {
                        jQuery.charashop.showCharaShopMessage(-2);
                     },*/
                     success: function(response){ jQuery.charashop.renderResults(response); }
                });
            }
        },
        
        /**
         * callback funtion when success
         *  set the callback data to the showArea of the page
         *
         * @param string response
         * @return void
         */
        renderResults : function(response)
        {            
            if ( response.status == 1 ) {
                postActivityWithPic(response.activity, response.activity_pic, "image/gif");
            
                $('#selectComfirm').css({backgroundPosition : 'left bottom'}).addClass('finish');
                return false;
            }
            else if ( response.status == -2 ) {
                alert('所持金が足りません');
            }
            else {
                $.charashop.showCharaShopMessage(-1);
            }
            
            ajaxLoad = true;
        },
        
        /**
         * show Chara Shop message
         */
        showCharaShopMessage : function(status)
        {
            var message = '';
            var title = '';
            
            if ( status == -1 ) {
                message = 'システムエラーが発生しました。<br/>このページを再度読み込み直してから、実行してください。';
                title = 'ヒットマン紹介所';
            }
            else if ( status == -2 ) {
                message = '通信エラーが発生しました。<br />インターネット接続状況を確認し、このページを再度読み込み直してください。';
                title = 'ヒットマン紹介所';
            }
            else if ( status == -3 ) {
                message = 'You have not enough money!';
                title = 'ヒットマン紹介所';
            }
         
            var html = '<iframe></iframe>'
                     + '<div id="overlayBox" class="set">'
                     + '<div id="overlayBoxInner">'
                     + '    <h2>' + title + '</h2>'
                     + '    <p class="btnClose"><img src="' + UrlConfig.StaticUrl + '/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.charashop.removeOverlay()"/></p>'
                     + '    <p>' + message + '</p>'
                     + '</div>'
                     + '</div><!--/#overlayBox-->';
            
            $('#overlay').html(html);
            $('#overlay').show();
        },
        
        /**
         * remove overlay
         */
        checkCanBuy : function(id)
        {
            if (!fixID) {
                return ;
            }
            
            var userBonus = $('#userBonus').val();
            var userPic = $('#userPic').val();
            
            $('#selectChara').removeClass('disable');
            
            var selectDivId = id == 10 ? id : '0' + id;
            var selectName = 'charaSelect' + selectDivId;
            var selectID = '#' + selectName;
            
           switch ( id ) {
                case Number(userPic) :                    
                    jQuery.charashop.addClassDisable(selectID);
                    break;
                case 2 : 
                    if ( userBonus < 1000 ) {
                        jQuery.charashop.addClassDisable(selectID);
                    }
                    break;
                case 4 : 
                    if ( userBonus < 15000 ) {
                        jQuery.charashop.addClassDisable(selectID);
                    }
                    break;
                case 6 : 
                    if ( userBonus < 1000 ) {
                        jQuery.charashop.addClassDisable(selectID);
                    }
                    break;
                case 7 : 
                    if ( userBonus < 4000 ) {
                        jQuery.charashop.addClassDisable(selectID);
                    }
                    break;
                case 8 : 
                    if ( userBonus < 4000 ) {
                        jQuery.charashop.addClassDisable(selectID);
                    }
                    break;
                case 9 :
                    if ( userBonus < 50000 ) {
                        jQuery.charashop.addClassDisable(selectID);
                    }
                    break;
                case 10 : 
                    if ( userBonus < 100000 ) {
                        jQuery.charashop.addClassDisable(selectID);
                    }
                    break;
                default :
                    $('#selectChara').removeClass('disable');
                    break;
            }
        },
        
        addClassDisable : function(id)
        {
            $('#selectChara').addClass('disable');
            $(id).unbind('click');
        },
        
        /**
         * remove overlay
         */
        removeOverlay : function()
        {
            $('#overlay').html('');
            $('#overlay').hide();
        }
        
    };
    
    var defaultCharaID = '01';
	if ( $('#userPic').val() ) {
        defaultCharaID = $('#userPic').val()
    }
	
	var fixID = true;
    function defaultCharaSet(){
        $('#contentWrap').addClass('charaSelect' + defaultCharaID);
        $('#selectedChara').show();
    }
    defaultCharaSet();
	
    
    function selectMap(id){
        
        var selectName = 'charaSelect' + id;
        var selectID = '#' + selectName;
        $('map#selectCharaMap').find(selectID).mouseover(function(){
			if (fixID) {			    
				$('#contentWrap').removeClass().addClass(selectName);
				$('#selectedChara').show();
			}
        //}, function() {
			//if (fixID) {
				//$('#contentWrap').removeClass(selectName).addClass('charaSelect' + defaultCharaID);
			//}
        }).click(function() {
			fixID = false;
            $('#hitmanType').val(id);
            $('#selectComfirm').fadeIn('fast');
        });
    }
    
    $('#selectCharaMap').mouseout(function(){
        if (fixID) {
            $('#contentWrap').removeClass().addClass('charaSelect' + defaultCharaID);
            $('#selectChara').addClass('disable');
        }
    });
        
    selectMap('01');
    selectMap('02');
    selectMap('03');
    selectMap('04');
    selectMap('05');
    selectMap('06');
    selectMap('07');
    selectMap('08');
    selectMap('09');
    selectMap('10');
    
    $('#selectComfirm').find('.yes').click(function(){
		$('#selectComfirm').css({backgroundPosition : 'left bottom'}).addClass('finish');
        jQuery.charashop.buyHitman();
    });
    $('#selectComfirm').find('.no').click(function(){
		fixID = true;
        $('#selectComfirm').fadeOut('fast');
        return false;
    });
});