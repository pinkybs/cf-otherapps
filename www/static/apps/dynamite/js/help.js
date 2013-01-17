
(function($) {

$.help = {
    //show flash
    showHelpFlash : function()
    {
        if ( null != getCookie('app_top_url_dynamite') ) {
            top.location.href = getCookie('app_top_url_dynamite') +  '#pagetop';            
        }
        
     //   if (!APPLY_FLAG) {
     //       jQuery.allianceapply.confirmDynamiteApplication('help','showHelpFlash');
     //   }
    //    else {
    //        APPLY_FLAG = false;
            
            var html = '';
            html += '<iframe></iframe>'
                  + '<div id="overlayBox" class="help">'
                  + '<div id="overlayBoxInner">'
                  + '   <h2>どきどきダイナマイトパニックの遊び方</h2>'
                  + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.help.closeFlashWindow()"/></p>'
                  + '   <div id="helpFlash">Flash</div>'
                  + '</div>'
				  + '</div>';
            $('#overlay').html(html);
            $('#overlay').show(); 
    //    }
        
    },
    
    showOtherHelpContent : function()
    {
        var html = '';
        html += '<iframe></iframe>'
              + '<div id="overlayBox" class="help">'
              + '<div id="overlayBoxInner">'
              + '   <h2>どきどきダイナマイトパニックの遊び方</h2>'
              + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.help.closeWindow()"/></p>'
              + '   <div id="helpList">'
              + '       <div class="section">'
              + '           <h3>もくじ</h3>'
              + '           <ul>'
              + '               <li id="liflash"><a href="javascript:void(0)" onclick="jQuery.help.showHelpFlash()">基本ルール（Flash）</a></li>'
              + '               <li id="litarget"><a href="javascript:void(0)" onclick="jQuery.help.gameTargetHelp()">このゲームの目的</a></li>'
              + '               <li id="liinstall"><a href="javascript:void(0)" onclick="jQuery.help.bombInstallHelp()">ダイナマイトの設置</a></li>'
              + '               <li id="libomb"><a href="javascript:void(0)" onclick="jQuery.help.bombHelp()">ダイナマイトの爆破</a></li>'
              + '               <li id="lidelete"><a href="javascript:void(0)" onclick="jQuery.help.bombDeleteHelp()">ダイナマイトの撤去</a></li>'
              + '               <li id="liitem"><a href="javascript:void(0)" onclick="jQuery.help.aboutItem()">アイテムについて</a></li>'
              + '           </ul>'
              + '       </div>'
              + '   <input type="hidden" id="linkname" name="linkname" value="">'
              + '       <div class="section" id="helpContent">'
              + '           <h3>このゲームの目的</h3>'
              + '           <p>どきどきダイナマイトパニックは、自分以外のユーザーのアジトにダイナマイトを設置し、爆破させ、懸賞金を獲得するスリル満点のシミュレーションゲーム。'
              + '               自分のアジトが壊滅しないよう、懸賞金を稼いで行き、アジトを大きくして行くのが目的となります。'
              + '           </p>'
              + '           <p>どきどきダイナマイトパニックは、自分以外のユーザーのアジトにダイナマイトを設置し、'
              + '               爆破させ、懸賞金を獲得するスリル満点のシミュレーションゲーム。'
              + '               自分のアジトが壊滅しないよう、懸賞金を稼いで行き、アジトを大きくして行くのが目的となります。'
              + '           </p>'
              + '       </div>'
              + '   </div>'
			  + '</div>'
              + '</div>';
        

        $('#overlay').html(html);
        $('#overlay').show();
        $('#litarget').html('<strong>このゲームの目的</strong>');
        $('#linkname').val('litarget');
    },
    
    //このゲームの目的
    gameTargetHelp : function()
    {   
        jQuery.help.addStyle($('#linkname').val())
       
        $('#litarget').html('<strong>このゲームの目的</strong>');
        
        var html = '';
        html += '<h3>このゲームの目的</h3>'
              + '<p>どきどきダイナマイトパニックは、自分以外のユーザーのアジトにダイナマイトを設置し、爆破させ、懸賞金を獲得するスリル満点のシミュレーションゲーム。'
              + '自分のアジトが壊滅しないよう、懸賞金を稼いで行き、アジトを大きくして行くのが目的となります。'
              + '</p>'
              + '<p>どきどきダイナマイトパニックは、自分以外のユーザーのアジトにダイナマイトを設置し、'
              + '爆破させ、懸賞金を獲得するスリル満点のシミュレーションゲーム。'
              + '自分のアジトが壊滅しないよう、懸賞金を稼いで行き、アジトを大きくして行くのが目的となります。'
              + '</p>';
        
        $('#helpContent').html(html);
        $('#linkname').val('litarget');

    },
    
    //ダイナマイトの設置
    bombInstallHelp : function()
    {   
        
        jQuery.help.addStyle($('#linkname').val())
       
        $('#liinstall').html('<strong>ダイナマイトの設置</strong>');
        var html = '';
        html += '<h3>ダイナマイトの設置</h3>'
              + 'aaaaaaaaaaaaaaaa';
        $('#helpContent').html(html);
        $('#linkname').val('liinstall');
    },
    
    //ダイナマイトの爆破
    bombHelp : function()
    {   
        jQuery.help.addStyle($('#linkname').val())
        $('#libomb').html('<strong>ダイナマイトの爆破</strong>');
        var html = '';
        html += '<h3>ダイナマイトの爆破</h3>'
              + 'bbbbbbbbbbbbb';
        $('#helpContent').html(html);
        $('#linkname').val('libomb');
    },
    
    //ダイナマイトの撤去
    bombDeleteHelp : function()
    {
        jQuery.help.addStyle($('#linkname').val())
        $('#lidelete').html('<strong>ダイナマイトの撤去</strong>');
        var html = '';
        html += '<h3>ダイナマイトの撤去</h3>'
              + 'cccccccccccccc';
        $('#helpContent').html(html);
        $('#linkname').val('lidelete');
    },
    
    //アイテムについて
    aboutItem : function()
    {
        jQuery.help.addStyle($('#linkname').val())
        $('#liitem').html('<strong>アイテムについて</strong>');
        var html = '';
        html += '<h3>アイテムについて</h3>'
              + 'ddddddddddddd';
        $('#helpContent').html(html);
        $('#linkname').val('liitem');
    },
    
    addStyle : function(linkName)
    {
        switch (linkName) {
            case 'litarget' :
                $('#litarget').html('<a href="javascript:void(0)" onclick="jQuery.help.gameTargetHelp()">このゲームの目的</a>');
                break;
            case 'liinstall' :
                $('#liinstall').html('<a href="javascript:void(0)" onclick="jQuery.help.bombInstallHelp()">ダイナマイトの設置</a>');
                break;
            case 'libomb' :
                $('#libomb').html('<a href="javascript:void(0)" onclick="jQuery.help.bombHelp()">ダイナマイトの爆破</a>');
                break;
            case 'lidelete' :
                $('#lidelete').html('<a href="javascript:void(0)" onclick="jQuery.help.bombDeleteHelp()">ダイナマイトの撤去</a>');
                break;
            case 'liitem' :
                $('#liitem').html('<a href="javascript:void(0)" onclick="jQuery.help.aboutItem()">アイテムについて</a>');
                break;

        }
    },
    
    closeWindow : function()
    {
        $('#overlay').html('');
        $('#overlay').hide();
    },
    
    closeFlashWindow : function()
    {
        jQuery.help.showOtherHelpContent();
    }
    

    
};

})(jQuery);