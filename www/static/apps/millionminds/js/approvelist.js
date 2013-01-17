var CONST_DEFAULT_PAGE_SIZE = 30;
(function($) {

categoryNameE = ['all','character','politics','life','entertainment','hobby'];
categoryName = ['すべて','性格診断','政治・経済','社会生活','芸能・スポーツ','趣味・その他'];
orderCss = ['','ascend','descend'];

$().ready(function() {
    //output format pager
    var nav = $.approve.showPagerNav($('input#questionCnt').val(), Number($('input#pageIndex').val()),CONST_DEFAULT_PAGE_SIZE, 10, '$.approve.changePageAction');
    $('div#pageList').html(nav);
    
    // bind click functions 
    $("#dayButton").bind( "click", function() { $.approve.changeDayOrder(); } );
    $('#typeall').bind ("click",function() { $.approve.changeArea('all'); } );
    $('#typepolitics').bind ("click",function() { $.approve.changeArea('politics'); } ); 
    $('#typelife').bind ("click",function() { $.approve.changeArea('life'); } ); 
    $('#typeentertainment').bind ("click",function() { $.approve.changeArea('entertainment'); } ); 
    $('#typehobby').bind ("click",function() { $.approve.changeArea('hobby'); } ); 
});

$.approve = {

    getAreaNum : function(area){
        for (i=0;i<6;i++) {
            if (categoryNameE[i] == area) {
                return i;
            }
        }
    },
    
    /**
    * change page
    *
    * @param integer page current page index
    */
    changePageAction : function(page)
    {
        $('input#pageIndex').val(page);
        var page = $('input#pageIndex').val();
        var dayOrder = $('input#dayOrder').val();
        var url = UrlConfig.BaseUrl + '/ajax/millionminds/approvelist';
        var area = $('input#hidArea').val();
        area = $.approve.getAreaNum(area);
        $.ajax({
             type: "POST",
             url: url,
             data: { dayOrder : dayOrder,
                     pageIndex : page,
                     type : area,
                     pageSize  : CONST_DEFAULT_PAGE_SIZE                
                   },
             dataType: "json",
             timeout : 10000,
             success: function(msg){ $.approve.renderResults(msg); },
             error: function(request, settings) {
                 if (settings == 'timeout') {
                     error = '通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください。';
                 }
                 else {
                     error = 'システムエラー。';
                 }
                 var alertHtm = '';
                 alertHtm += '<div id="content">'
                          +'<div id="mainColumn">'
                          +'<div id="add">'
                          +'<div class="content">'
					      +'<div class="alert">'
					      +'<ul>'
						  +'<li>' + error + '</li>'
						  +'<ul>'
						  +'</div>'
						  +'</div>'
						  +'</div>'
						  +'</div>'
						  +'</div>';
                 $('#questionList').html(alertHtm);
                 $('#pageList').html('');
                 
                 //bind click function again
                 $("#dayButton").bind( "click", function() { $.approve.changeDayOrder(); } );
                 $('#typeall').bind ("click",function() { $.approve.changeArea('all'); } );
                 $('#typepolitics').bind ("click",function() { $.approve.changeArea('politics'); } ); 
                 $('#typelife').bind ("click",function() { $.approve.changeArea('life'); } ); 
                 $('#typeentertainment').bind ("click",function() { $.approve.changeArea('entertainment'); } ); 
                 $('#typehobby').bind ("click",function() { $.approve.changeArea('hobby'); } );
    	     }
        });
    },
    
    /**
    * process data from server
    *
    * @param string response
    */
    renderResults : function(response)
    { 
        if (response) {   
            var aryInfo = response.info;
    	    var cntInfo = response.count;
            var htm="";
        	htm += '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="list">'
	       	htm +=     '<tr>'
			htm +=         '<th class="title"></th>'
			htm +=         '<th class="count ascend"><a href="javascript:void(0)">回答数▼</a></th>'
			htm +=         '<th class="date ' + orderCss[$("input#dayOrder").val()] + '"><a href="javascript:void(0)" id="dayButton">作成日▼</a></th>'
			htm +=     '</tr>';
            if (cntInfo > 0) {
    			for (i = 0 ; i < aryInfo.length ; i++)
                { 
                    htm += '<tr class="section">'
                    htm +=     '<td class="title stringCut"><a href="/millionminds/approve/id/' + aryInfo[i].id + '" class="ico">' + aryInfo[i].question.escapeHTML() + '</a></td>'
                    htm +=     '<td class="count">---</td>'
                    htm +=     '<td class="date">' + aryInfo[i].create_time + '</td>'
                    htm += '</tr>';
                }
            }
            else if (cntInfo == 0) {
                htm += '<tr class="section">'
                htm +=     '<td class="title stringCut">このカテゴリに未承認クエスチョンがありません。</td>'
                htm +=     '<td class="count">---</td>'
                htm +=     '<td class="date">---</td>'
                htm += '</tr>'; 
            }
            htm += '</table>';
            var nav = $.approve.showPagerNav(response.count, Number($('input#pageIndex').val()),CONST_DEFAULT_PAGE_SIZE, 10, '$.approve.changePageAction');

            $('#questionList').html(htm);
            $('#pageList').html(nav);
            
            //bind click function again
            $("#dayButton").bind( "click", function() { $.approve.changeDayOrder(); } );
            $('#typeall').bind ("click",function() { $.approve.changeArea('all'); } );
            $('#typepolitics').bind ("click",function() { $.approve.changeArea('politics'); } ); 
            $('#typelife').bind ("click",function() { $.approve.changeArea('life'); } ); 
            $('#typeentertainment').bind ("click",function() { $.approve.changeArea('entertainment'); } ); 
            $('#typehobby').bind ("click",function() { $.approve.changeArea('hobby'); } );
        }
    },
    
    /**
    * @see  将json字符串转换为对象
    * @param   json字符串
    * @return 返回object,array,string等对象
    */
    evalJSON : function(strJson)
    {
        return eval( "(" + strJson + ")");
    },
    
    /**
     * show page nav
     *
     * @param integer count
     * @param integer pageindex
     * @param integer pagesize
     * @param integer pagecount
     * @return string
     */
    showPagerNav : function(count,pageindex,pagesize,pagecount,action)
    {
        if (!pagecount) {
            pagecount = 10;
        }
    
        if (!action) {
            action = '$.approve.changePageAction';
        }
    
        if (count <= pagesize) {
            return '';
        }
    
        var nav = '';
    
        var forward = '';
        var pagerleft = '';
        var pagercurrent = '';
        var pagerright = '';
        var next = '';
        var maxpage = Math.ceil(count/pagesize);
        
        if (pageindex > 1) {
            forward += '<li><a href="javascript:' + action + '(' + (pageindex - 1) + ');">&laquo; 前へ</a></li>';
        }
    
        if (maxpage > pageindex) {
            next = '<li><a href="javascript:' + action + '(' + (pageindex + 1) + ');">次へ &raquo;</a></li>';
        }
    
        var page = Math.ceil(pagecount/2);
    
        //all page count
        var i = 1;
    
        //left nav
        var left = 0;
        for (left = pageindex - 1; left > 0 && left > pageindex - page; left--) {
            i++;
            pagerleft = '<li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
        }
    
        //current nav number
        pagercurrent = '<li><strong>' + pageindex + '</strong></li>';
    
        //right nva
        var right = 0;
        for (right = pageindex + 1; right <= maxpage && right < pageindex + page ; right++) {
            i++;
            pagerright = pagerright + '<li ><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
        }
    
        //If right side is not enough, show the page number for left until the page number number is up to 1
        if (i < pagecount && left >= 1) {
            for (; left > 0 && i < pagecount; left--,i++) {
                pagerleft = '<li><a href="javascript:' + action + '(' + left + ');">' + left + '</a></li>' + pagerleft;
            }
        }
    
        //If left side is not enough, showthe page number for right until the page number number is up to max
        if (i < pagecount && right <= maxpage) {
            for (; right <= maxpage && i < pagecount; right++,i++) {
                pagerright = pagerright + '<li><a href="javascript:' + action + '(' + right + ');">' + right + '</a></li>';
            }
        }
    
        nav = '<ul class="pager">' + forward + pagerleft + pagercurrent + pagerright + next + '</ul>';
        
        return nav;
    },
    
    changeArea : function(area)
    {
        if ( area != $("#hidArea").val() ) {
            $("#dayButton").unbind("click");
            for ( i=0; i<6; i++ ) {
                $("#type" + categoryNameE[i]).unbind("click");
            }
            $('#type' + area).addClass("active");
            $('#type' + $("#hidArea").val()).removeClass();
            $("#hidArea").val(area);
            $.approve.changePageAction(1);
        }
    },
    
    changeDayOrder : function ()
    {
        $("#dayButton").unbind("click");
        for ( i=0; i<6; i++ ) {
                $("#type" + categoryNameE[i]).unbind("click");
        }
        
        if ( $("input#dayOrder").val() == 1 ) {
            $("input#dayOrder").val(2);
        }
        else if ( $("input#dayOrder").val() == 2 ) {
            $("input#dayOrder").val(1);
        }
       
        $.approve.changePageAction(1);
    }
};

})(jQuery);