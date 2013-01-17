(function($) {

$().ready(function() {
    //adjustHeight(1100);
});

$.specialrank = {
    otherTypeRank : function(rankName, rankType)
    {
        jQuery.ajax({
                    type : "POST",
                    url : UrlConfig.BaseUrl + '/ajax/dynamite/otherspecialtyperank',
                    dataType : "json",
                    data : {
                                rankName: rankName,
                                rankType:rankType
                           },
                    timeout : 5000,
                    error : function() {
                        jQuery.specialrank.createAjaxTimeOutErrorWindow();
                    },
                    success : function(response){
                        jQuery.specialrank.showOtherTypeRankResult(response, rankName, rankType);
                        
                    }
                }
           );
    },
    
    showOtherTypeRankResult : function(response, rankName, rankType)
    {
        var topHtml = '';
        var listHtml = '';
        var rankInfo = response.rankInfo;
        
        topHtml += '<p class="rank">No.01</p>'
                 + '<p class="pic"><a href="'+UrlConfig.BaseUrl+'/dynamite/index?uid='+rankInfo[0].uid+'" style="background-image: url('+rankInfo[0].largeThumbnailUrl+')">'+rankInfo[0].displayName+'</a></p>'
                 + '<p class="name"><a href="'+UrlConfig.BaseUrl+'/dynamite/index?uid='+rankInfo[0].uid+'">'+rankInfo[0].displayName+'</a></p>';
        if (rankName == 1) {
                topHtml += '<p class="price">'+rankInfo[0].bonus+'</p>';
            }
            else if (rankName == 2) {
                topHtml += '<p class="price">'+rankInfo[0].dead_number+'回</p>';
            }
        
        for (var i =1; i < rankInfo.length; i++) {
            listHtml += '<li>';
            if (i+1 >= 10) {
                listHtml += '<p class="rank">No.'+(i+1)+'</p>';
            }
            else {
                listHtml += '<p class="rank">No.0'+(i+1)+'</p>';
            }
            listHtml += '<p class="pic"><a href="'+UrlConfig.BaseUrl+'/dynamite/index?uid='+rankInfo[i].uid+'" style="background-image: url('+rankInfo[i].thumbnailUrl+')">'+rankInfo[i].displayName+'</a></p>'
                      + '<p class="name"><a href="'+UrlConfig.BaseUrl+'/dynamite/index?uid='+rankInfo[i].uid+'">'+rankInfo[i].displayName+'</a></p>';
            if (rankName == 1) {
                listHtml += '<p class="price">'+rankInfo[i].bonus+'</p>';
            }
            else if (rankName == 2) {
                listHtml += '<p class="price">'+rankInfo[i].dead_number+'回</p>';
            }
        }
        
        if (rankName == 1) {
            $('#priceRankFirst').html(topHtml);
            $('#priceRankList').html(listHtml);
            
            if (rankType == 1) {
                $('#priceNavMyMixi').addClass('active');
                $('#priceNavAll').removeClass('active');
            }
            else if (rankType == 2) {
                $('#priceNavAll').addClass('active');
                $('#priceNavMyMixi').removeClass('active');
            }
            
        }
        else if (rankName == 2) {
            $('#deadRankFirst').html(topHtml);
            $('#deadRankList').html(listHtml);
            
            if (rankType == 1) {
                $('#deadNavMyMixi').addClass('active');
                $('#deadNavAll').removeClass('active');
            }
            else if (rankType == 2) {
                $('#deadNavAll').addClass('active');
                $('#deadNavMyMixi').removeClass('active');
            }
           
        }
    },
    
    createAjaxTimeOutErrorWindow : function()
    {
        var html = '';
        html += '<iframe></iframe>'
              + '<div id="overlayBox" class="error communication">'
              + '<div id="overlayBoxInner">'
              + '   <h2>通信エラー</h2>'
              + '   <p class="btnClose"><img src="'+UrlConfig.StaticUrl+'/apps/dynamite/img/btn/close.png" width="36" height="36" alt="閉じる" onclick="jQuery.specialrank.closeWindow()"/></p>'
              + '   <p>'
              + '       通信エラーが発生しました。<br />'
              + '       インターネット接続状況を確認し、このページを再度読み込み直してください。'
              + '   </p>'
              + '</div>'
              + '</div>';
         
        $('#overlay').html(html);
        $('#overlay').show();
    },
    
    closeWindow : function()
    {
        $('#overlay').html('');
        $('#overlay').hide();
    }
};

})(jQuery);