
(function($) {

$.invite = {
  
    invite : function()
    {
        var owner = $('#txtUid').val();
        
        var html = '';
        html += '<iframe></iframe>'
              + '<div id="overlayBox" class="invite">'
			  + '<div id="overlayBoxInner">'
              + '   <h2>友達を誘って、ダイナマイトをGET</h2>'
              + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.item.closeWindow()"/></p>'
              + '   <p>'
              + '       以下のURLを日記やメッセージに貼付け、あなたのお友達を誘ってください。<br />'
              + '       新しいアジトが作成されるたびに、ダイナマイトが3つプレゼントされます！'
              + '   </p>'
              + '   <form id="diaryForm" method="post" action="" target="_top" accept-charset="euc-jp">'
              + '   <label>'
              + '       招待用URL '
              //<input size="40" name="diary_title" type="text" value="http://mixi.jp/run_appli.pl?id=1325" />'
              + '   <input type="text" value="http://mixi.jp/join_appli.pl?id=3459&r=%2Frun_appli.pl%3Fid%3D3459%26invite%3D'+owner+'" />'
              + '   <input type="hidden" id="diaryBody" name="diary_body" value="'
              + '◇ダイナマイトＷＡＲＳ'
              + '       \n'
              + 'http://mixi.jp/join_appli.pl?id=3459&r=%2Frun_appli.pl%3Fid%3D3459%26invite%3D'+owner+''
              + '\n'
              + '◇インストール方法'
              + '\n'
              + 'http://mixi.jp/XXXXXXXXX ">'
              + '   </label>'
              + '   </form>'
              + '   <p class="btn"><a href="javascript:void(0)" onclick="jQuery.invite.addDiary('+owner+')"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/diary.png" width="584" height="45" alt="招待用URLを貼り付けて、ミクシィ日記を書く" /></a></p>'
              + '</div>'
			  + '</div>';
        $('#overlay').html(html);
        $('#overlay').show(); 
        
    },
    
    //invite submit
    addDiary : function(owner) 
    {
        
        if(owner == null) {
            return;
        }
        
        var url = 'http://mixi.jp/add_diary.pl?id=' + owner;
        var diaryForm = document.getElementById('diaryForm');
        if (diaryForm) {
            diaryForm.action = url;
            diaryForm.submit();
        }
    }
};

})(jQuery);