
(function($) {

var APPLY_COUNT = 0;
var flag = 0;
var allianceList = null;

$.allianceapply = {

    //get alliance application list
    confirmDynamiteApplication : function(jsName, methodName, parm)
    {           
        jQuery.ajax({
            type : "POST",
            dataType : "json",
            url : UrlConfig.BaseUrl + '/ajax/dynamite/confirmdynamiteapplication',
            timeout : 5000,
            error : function() {
                jQuery.item.createAjaxTimeOutErrorWindow();
            },
            success : function(response){
                allianceList = response.allianceList;
                APPLY_COUNT = allianceList.length;
               
                flag = 0;
                jQuery.allianceapply.showResult(allianceList, flag, jsName, methodName, parm);
            }
        });
    },
    
    //show alliance application list
    showResult : function(allianceList, flag, jsName, methodName, parm)
    {   
        if (allianceList == null || allianceList == '') {
            APPLY_FLAG = true;
                
                if (parm) {
                    if (parm.length == '2') {
                        jQuery[jsName][methodName](parm, parm, 1);
                    }
                    else {
                        jQuery[jsName][methodName](parm, 1);
                    }
                }
                else {
                    jQuery[jsName][methodName](parm, 1);
                }
           
            //jQuery[jsName][methodName](parm, 1);
            return;
        }
        
        var html = '';
        for (var i = 0; i < allianceList.length; i++) {
            if (i == flag) {
                html += '<iframe></iframe>'
                      + '<div id="overlayBox" class="alliance select">'
  					  + '<div id="overlayBoxInner">'
                      + '   <h2>同盟の申請</h2>'
                      + '       <p class="pic_user"><a href="" style="background-image: url('+allianceList[i].thumbnailUrl+')">[[ユーザー名]]</a></p>'
                      + '       <p class="text">'
                      + '           '+allianceList[i].displayName+'組がアナタと同盟を結びたがっています。<br />'
                      + '           同盟を結んだ時点で、未使用のダイナマイトが2つ献上されます。<br />'
                      + '           同盟を結びますか？'
                      + '       </p>';
                 if (APPLY_COUNT > (flag+1)) {
                      html += '       <a href="javascript:void(0)" onclick="jQuery.allianceapply.agreeAllApply()">全部承認</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                 }
                 html += '   <ul class="btnList small">'
                       + '       <li id="btnCancel"><a href="javascript:void(0)" onclick="jQuery.allianceapply.refuseAllianceApply('+allianceList[i].uid+', \''+allianceList[i].thumbnailUrl+'\', &quot;'+allianceList[i].displayName+'&quot;);"></a></li>'
                       + '       <li id="btnAlliance"><a href="javascript:void(0);" onclick="jQuery.allianceapply.confirmAllianceSubmit('+allianceList[i].uid+', \''+allianceList[i].thumbnailUrl+'\', &quot;' +allianceList[i].displayName+ '&quot;);">同盟を結ぶ</a></li>'
                       + '   </ul>'
					   + '</div>'
                       + '</div>';
              }
        }
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    //agree alliance application
    confirmAllianceSubmit : function(aid, thumbnailUrl, displayName)
    {
        var url = UrlConfig.BaseUrl + '/ajax/dynamite/confirmallianceapply';

        $.ajax({
             type: "POST",
             url: url,
             data: {aid : aid},
             timeout : 5000,
             error : function() {
                 jQuery.item.createAjaxTimeOutErrorWindow();
             },
             success: function(response){
                 jQuery.allianceapply.confirmOver(response, thumbnailUrl, displayName, aid);
             }
        });
    },
    
    confirmOver : function(response, thumbnailUrl, displayName, aid)
    {   
        flag++;
        
        if (response == 1) {
            var html = '';
            html += '<iframe></iframe>'
                  + '<div id="overlayBox" class="alliance select">'
			      + '<div id="overlayBoxInner">'
                  + '   <h2>同盟の申請</h2>'
                  + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.allianceapply.closeConfirmWindow('+aid+')"/></p>'
                  + '   <p class="pic_user"><a href="" style="background-image: url('+thumbnailUrl+')">[[ユーザー名]]</a></p>'
                  + '   <p class="text">'
                  + '       '+displayName+'組と同盟を結びました。<br />'
                  + '           同盟関係は一方的に解消できますが、裏切ると恐ろしい報復が待っていますので、注意しましょう。'
                  + '   </p>';
            if (flag < APPLY_COUNT) {
                html += '   <a href="javascript:void(0)" onclick="jQuery.allianceapply.nextApply('+flag+')">次の申請</a>';
            }
            html += '</div></div>';
            $('#overlay').html(html);
            $('#overlay').show();
        }
        else {
            jQuery.item.createSystemErrorWindow();
        }     
    },
    
    closeConfirmWindow : function(aid)
    {
        $('#overlay').html('');
        $('#overlay').hide();
        jQuery.dynamite.updateBombBox($('#txtUid').val());
        jQuery.dynamite.goUserDynamite(aid);
    },
    
    //next application
    nextApply : function(flag)
    {   
        jQuery.allianceapply.showResult(allianceList, flag);
    },
    
    //agree all apply
    agreeAllApply : function()
    {
        var url = UrlConfig.BaseUrl + '/ajax/dynamite/agreeallapply';
        
        if (!AJAX_COMPLETE) {
            AJAX_COMPLETE = true;
            
            $.ajax({
                 type: "POST",
                 url: url,
                 timeout : 5000,
                 error : function() {
                     jQuery.item.createAjaxTimeOutErrorWindow();
                 },
                 success: function(response) {
                     jQuery.allianceapply.agreeAllApplyOver(response);
                     
                     AJAX_COMPLETE = false;
                 }
            });
        }
    },
    
    agreeAllApplyOver : function(response)
    {
        if (response == 1) {
            var html = '';
            html += '<iframe></iframe>'
                  + '<div id="overlayBox" class="alliance select">'
				  + '<div id="overlayBoxInner">'
                  + '   <h2>同盟の申請</h2>'
                  + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.allianceapply.closeWindow()"/></p>'
                  + '   <p class="text">'
                  + '       すべての同盟申請の処理が完了しました。<br />'
                  + '           同盟関係は一方的に解消できますが、裏切ると恐ろしい報復が待っていますので、注意しましょう。'
                  + '   </p>'
				  + '</div>'
                  + '</div>';
            $('#overlay').html(html);
            $('#overlay').show();
        }
        else {
            jQuery.item.createSystemErrorWindow();
        } 
    },
    
    //refuse application
    refuseAllianceApply : function(aid, thumbnailUrl, displayName)
    {
        var url = UrlConfig.BaseUrl + '/ajax/dynamite/refuseallianceapply';

        $.ajax({
             type: "POST",
             url: url,
             data: {aid : aid},
             timeout : 5000,
             error : function() {
                 jQuery.item.createAjaxTimeOutErrorWindow();
             },
             success: function(response){
                 jQuery.allianceapply.refuseApplyOver(response, aid);
             }
        });
    },
    
    refuseApplyOver : function(response, aid)
    {
        flag++;
       
        if (flag < APPLY_COUNT) {
            jQuery.allianceapply.nextApply(flag);
        }
        
        if (flag == APPLY_COUNT) {
            jQuery.dynamite.updateBombBox($('#txtUid').val());
            jQuery.dynamite.goUserDynamite(aid);
        }
        
        if (response == '-1'){
            jQuery.item.createSystemErrorWindow();
        }
        
    },
    
    closeWindow : function()
    {
        $('#overlay').html('');
        $('#overlay').hide();
        jQuery.dynamite.updateBombBox($('#txtUid').val());
    }
};

})(jQuery);

