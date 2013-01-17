/*
----------------------------------------------
Millionminds top JavaScript

Created Date: 2009/07/27
Author: huch

----------------------------------------------
*/

(function($) {
    
categoryName = ['すべて','性格診断','政治・経済','社会生活','芸能・スポーツ','趣味・その他'];
categoryNameE = ['all','character','politics','life','entertainment','hobby'];
    
$.top = {
    /**
     * get question ajax
     *
     */
    getQuestion : function(type, field)
    {
        var id = (field == 1) ? 'divPopular' : 'divRecent';
        
        jQuery.ajax({
            type : "POST",
            url : UrlConfig.BaseUrl + '/ajax/millionminds/getquestion',
            dataType: "json",
            data : {
                    type : type,
                    field : field
                   },
            timeout : 10000,
            success : function(response) {
                jQuery.top.showBanner(type, id);
                
                if (response.length == 0) {
                    jQuery.top.showQuestionNull(id);
                }
                else {
                    jQuery.top.showQuestion(response, field, id);
                }
                
                adjustHeight();          
            },
            error : function(request, settings) {
                jQuery.top.showBanner(type, id);
                
                if (settings == 'timeout') {
                    error = '<p>通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください。<p>';
                }
                else {
                    error = '<p>システムエラー。<p>';
                }
                
                $('#' + id).append(error);
            }
        });
    },
    
    /**
     * show banner html
     *
     */
    showBanner : function(type, id)
    {        
        $('#' + id).find('table.list').remove().end().find('p').remove().end()
                   .find('ul.cat > li > a.active').removeClass().end().find('ul.cat > li.' + categoryNameE[type] + ' > a').addClass("active");
    },
    
    /**
     * show question
     *
     */
    showQuestion : function(question, field, id)
    {
        var html = "";
        
        html += '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="list">'
			  + '	<tr>'
			  + '		<th class="title"></th>'
			  + '		<th class="count descend"><a href="' + UrlConfig.BaseUrl + '/millionminds/archive?field=1">回答数▼</a></th>'
			  + '		<th class="date descend"><a href="' + UrlConfig.BaseUrl + '/millionminds/archive?field=2">作成日▼</a></th>'
			  + '	</tr>';

        var l = question.length;
        for (i = 0; i < l; i++) {
           html += '<tr class="section">'
                 + '   <td class="title stringCut"><a href="' + UrlConfig.BaseUrl + '/millionminds/question?qid=' + question[i].qid + '" class="ico">' + question[i].question.escapeHTML() + '</a></td>'
                 + '   <td class="count">' + question[i].answer.escapeHTML() + '</td>'
                 + '   <td class="date">' + question[i].create_time + '</td>'
                 + '</tr><!--/.section-->';
        }
			  
        html += '</table>'
        	  + '<p class="more">⇒<a href="' + UrlConfig.BaseUrl + '/millionminds/archive?field=' + field + '">人気のクエスチョンをもっと見る</a></p>';
        
        $('#' + id).append(html);
    },
    
    /**
     * when question is null,show image
     *
     */
    showQuestionNull : function(id)
    {
        var html = '<p class="null"><a href="' + UrlConfig.BaseUrl + '/millionminds/newquestion">クエスチョンを作成する</a></p>';
        $('#' + id).append(html);
    }
};

})(jQuery);