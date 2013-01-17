/*
----------------------------------------------
Script Editor archives JavaScript

Created Date: 2009/05/20
Author: liz

----------------------------------------------
*/

(function($) {

var canConfirm1 = "1";
var canConfirm2;

$().ready(function() {
    jQuery.config.checkValue('input#txtNickname', 'p#errNickname');
    $.config.showFeatureInfo();
    
      $("#frmConfig").validate({ 
          rules: {
              blogUrl: {
                  url: true
              }
          },
          messages: {
              blogUrl: {
                  url: '<p class="note" style="text-align:left;"><strong>※正しいURLを入力してください</strong></p>'
              }
          }
      });
    
});

$.config = {

    validateUrl : function(u)
    {
        var checkurl = UrlConfig.BaseUrl + '/ajax/common/validateurl';
    
        $j.ajax({
             type: "POST",
             url: checkurl,
             data: {url : u},
             success: function(data) {
                if (data == 'true') {
                    $j('#lblBlogUrl').html('<a href="javascript:void(0);" onclick="mixiNavigateTo(\'' + u + '\')" tabindex="_blank" rel="nofollow" >' + u + '</a>');
                }
                else {
                    $j('#lblBlogUrl').html('<label style="color:red;">不正なURLです</label>');
                    $j('#blogUrl').val('');
                }
             } 
        });
    },
    
    /**
     * confirm Value
     *
     */
    confirmValue : function()
    {
        var blogUrl = $j('#blogUrl').val();
        if (blogUrl == 'http://') {
            blogUrl = '';
        }
        
        $j('#lblBlogUrl').html('');
        if (blogUrl != '') {
            $.config.validateUrl(blogUrl);
        }
        
        $.config.doThumbPreview();
                
        var nickname = $j('input#txtNickname').val();
        $j('td#lblNickname').text(nickname);
        
        //get job content
        var jobArr = $j('select#dllJob').val();
        if ( jobArr ) {
            var job = jobArr[0];
            $j('#txtJob').val(job);
            $.config.getJobContentById(job);
        }
        
        var level = $j('input#txtLevel').val();
        $j('td#lblLevel').text(level);
        
        var introduce = $j('input#txtIntroduce').val();
        $j('td#lblIntroduce').text(introduce);
                
        //get language
        var language = $j('input#defaultLang').val();
        if ( language == '1' ) {
            language = 'PHP';
        }
        $j('td#lblLanguage').text(language);
        
        //get mixi prof
        var mixiProf = $j('input:checked[name=mixiProf]').val();
        
        if ( mixiProf == '1' ) {
            var mixiProf = '公開する';
        }
        else {
            var mixiProf = '公開しない';
        }
        $j('td#lblMixiProf').text(mixiProf);
        
        //show checked feature
        $.config.getFeatureInfo();
        
        $j('div#configStep1').css({ display: "none" });
        $j('div#configStep2').css({ display: "" });
        $j.loadPage();
        
        return true;
    },
    
    /**
     * submit data
     *
     * @param  null
     * @return void
    */
    submit : function()
    {
        var isAgree = $('input:checked[name="agreement"]').val();
 
        if ( isAgree != '1' ) {
            return;
        }
        
        var blogUrl = $j('input#blogUrl');
        if (blogUrl.val() == 'http://') {
            blogUrl.val('');
        }
        
        var frmConfig = $j('#frmConfig');
        frmConfig.attr("action", UrlConfig.BaseUrl + '/ajax/scripteditor/config');
        frmConfig[0].submit();
        
        $j("form#frmConfig").ajaxSubmit( function(data) { $.config.renderResultsSubmit(data); } );
    },
    
    /**
     * renderResultsSubmit
     *
     */
    renderResultsSubmit : function(response)
    {
        if ( response ) {
            $j.loadPage();
            var nextUrl = UrlConfig.BaseUrl + '/scripteditor/profile?submit=1';
            countDown(1, nextUrl);
        }
        else {
            $j.loadPage();
            var nextUrl = UrlConfig.BaseUrl + '/scripteditor/config';
            countDown(1, nextUrl);
        }
    },
    
    /**
     * check the input value is number
     *
     * @param  string divId
     * @param  string errDivId
     * @return void
     */
    checkIsNumber : function(divId, errDivId)
    {
        var divContent = $j( divId ).val();
        
        if ( divContent ) {
            if ( divContent == parseInt(divContent) && divContent > 0 && divContent < 10000 ) {
                $j( errDivId ).css({ display: "none" });
                canConfirm1 = '1';
            }
            else {
                $j( errDivId ).css({ display: "" });
                canConfirm1 = '0';
            }
        }
        $.config.checkCanConfirm(canConfirm1, canConfirm2);
    },
    
    /**
     * check the input value is null
     *
     * @param  string divId
     * @param  string errDivId
     * @return void
     */
    checkValue : function(divId, errDivId)
    {
        var divContent = $j( divId ).val();
        if ( !divContent ) {
            $j( errDivId ).css({ display: "" });
            canConfirm2 = '0';
        }
        else {
            $j( errDivId ).css({ display: "none" });
            canConfirm2 = '1';
        }
        $.config.checkCanConfirm(canConfirm1, canConfirm2);
    },
    
    /**
     * check can Confirm
     *
     */
    checkCanConfirm : function(canConfirm1, canConfirm2)
    {
        if ( canConfirm1 == '1' && canConfirm2 == '1' ) {
            $j('a#btnConfirm2').css({ display: "none" });
            $j('a#btnConfirm1').css({ display: "" });
        }
        else {
            $j('a#btnConfirm2').css({ display: "" });
            $j('a#btnConfirm1').css({ display: "none" });
        }
    },
    
    /**
     * get job content by job id
     *
     * @param  integer id
     * @return string
     */
    getJobContentById : function(id)
    {
        var url = UrlConfig.BaseUrl + '/ajax/scripteditor/getjobcontent';
    
        $j.ajax({
             type: "POST",
             url: url,
             data: {id : id},
             success: function(msg){ $.config.renderResultsJobContent(msg); } 
        });
    },
    
    /**
     * renderResultsJobContent
     *
     */
    renderResultsJobContent : function(response)
    {
        var responseObject = $j.evalJSON(response);
        if ( responseObject.content ) {
            $j('td#lblJob').text(responseObject.content);
        }
    },
    
    /**
     * get feature info
     *
     * @return void
     */
    getFeatureInfo : function()
    {
        //get feature list info
        var featureList1 = $j.evalJSON( $j('input#arrFeature1').val() );
        var featureList2 = $j.evalJSON( $j('input#arrFeature2').val() );
        
        var html = '<dl>';
                
        var isFirstType1 = "1";
        var isFirstType2 = "1";
        var feature = "";
        
        for ( i=0,count=featureList1.length; i<count; i++ ) {
            featureList1[i].status = $('input:checked[name="' + featureList1[i].content + '"]').val();
            
            feature += featureList1[i].status;
            
            if ( featureList1[i].status != '0' ) {
                if ( isFirstType1 == "1" ) {
                    html += '<dt>＜プログラミング＞</dt>';
                    isFirstType1 = "0";
                }
                
                featureList1[i].record = $.config.getFeatureStatus(featureList1[i].status);
                
                html += '<dd><front style="width:80px;float:left">・' + featureList1[i].content + '</front>' + featureList1[i].record + '</dd>';
            }
        }
            
        for ( i=0,count=featureList2.length; i<count; i++ ) {
            featureList2[i].status = $('input:checked[name="' + featureList2[i].content + '"]').val();
            
            feature += featureList2[i].status;
            
            if ( featureList2[i].status != '0' ) {
                if ( isFirstType2 == "1" ) {
                    html += '<dt>＜データーベース＞</dt>';
                    isFirstType2 = "0";
                }
                
                featureList2[i].record = $.config.getFeatureStatus(featureList2[i].status);
 
                html += '<dd><front style="width:80px;float:left">・' + featureList2[i].content + '</front>' + featureList2[i].record + '</dd>';
            }
        }
        
        html += '</dl>';
        
        $j('#txtFeature').val(feature);
        $j('td#lblFeature').html(html);
    },
    
    /**
     * upload photo
     * @param  null
     * @return void
     */
    doThumbPreview : function()
    {
        var html = '';

        if ( !$j('#upPhoto').val() ) {
            return;
        }
    
        var upPhoto = $j('input#upPhoto').val();
        $j('input#picValue').val(upPhoto);
        
        
        var frmConfig = $j('form#frmConfig');
        frmConfig[0].action = UrlConfig.BaseUrl + '/ajax/common/thumbpreview/section/1';
        frmConfig[0].submit();
        
        $j("form#frmConfig").ajaxSubmit( function(data) { $.config.thumbPreviewResults(data); } );
    },
    
    /**
     * photo preview
     * @param Boolean result
     * @return void
     */
    thumbPreviewResults : function(result)
    {
        var html = '';
        if(result == null || result == '' || result == 'false') {
            html = '<span style="color:#FF0000">サムネイルの生成に失敗しました。</span>';
        }
        else {
            html = '<img id="imgPhoto" src="' + UrlConfig.BaseUrl + '/ajax/common/viewthumb/post_key/' + result + '" width="170" alt="サムネイル" />';
        }
        $j('div#divThumbPreview').html(html);
    },
    
    showFeatureInfo : function()
    {
        var feature = $j('input#userFeature').val();
        if ( feature ) {   

            var featureList1 = $j.evalJSON( $j('input#arrFeature1').val() );
            var featureList2 = $j.evalJSON( $j('input#arrFeature2').val() );
            var allFeatureList = featureList1.concat(featureList2); 

            for ( i=0,count=feature.length; i<count; i++ ) {
                allFeatureList[i].status = feature.substring(i, i+1);
                //set radio value
                $("input[type=radio][name=" + allFeatureList[i].content + "][value=" + allFeatureList[i].status + "]").attr("checked", "checked");
            }
        }
    },
    
    getFeatureStatus : function(status)
    {
        var record;
        
        switch (status) {
            case '1' :
                record = '独学';
                break;
            case '2' :
                record = '実務 〜 1年';
                break;  
            case '3' :
                record = '実務 1〜2年';
                break;
            case '4' :
                record = '実務 2年〜';
                break; 
        }
        
        return record;
    }

};

})(jQuery);

    