/**
 * watch topic comment list(static/admin/js/aschool/watchtopiccomment.js)
 * watchtopiccomment list 
 * 
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/03/12    zhangxin   
 */
 
/**
 *  page load function
 *  register funcion and init page
 */
Event.observe(window, 'load', function() { 
    Event.observe('btnSubmit', 'click', doSubmit)
    changePageAction('1');
});

//define default page size
var CONST_DEFAULT_PAGE_SIZE = 50;

/**
 * submit to deal action
 * @param  null
 * @return void
 */
function doSubmit() 
{
    $('divMsg').hide();
    $('frmDeal').action = UrlConfig.BaseUrl + '/ajaxaschool/dealwatchenquirycomment';
    $('frmDeal').request({
         onCreate : function() {
             $('btnSubmit').disable();
         },
         timeout: 30000,
         onSuccess : function(response) {
            try {    
                if (response.responseText != '' && !isNaN(response.responseText)) {      
                    $('divMsg').update('選択した' + response.responseText + '件の処理が完了しました。');
                }
                else {
                    $('divMsg').update('Error:エラーが出ました。');                        
                }
                $('divMsg').show();
                new Effect.Fade('divMsg', { duration: 3.0 });                        
                $('divMsg').scrollTo();
                
                new PeriodicalExecuter(function(pe) {
                     pe.stop();
                     changePageAction($F('pageIndex'));
                     $('btnSubmit').enable();
                }, 3);
                
            } catch (e) {
                //alert(e);
            }
         }
    });
}

/**
 * change page ajax request
 * @param  integer page
 * @return void
 */
function changePageAction(page)
{   
    //ajax show list request
    $('pageIndex').value = page;
    var url = UrlConfig.BaseUrl + '/ajaxaschool/listwatchenquirycomment';
    new Ajax.Request(url, {
        parameters : {
            pageIndex : $F('pageIndex'),
            pageSize : CONST_DEFAULT_PAGE_SIZE,
            hidSrhStatus : $F('hidSrhStatus'),
            hidSrhKeyword : $F('hidSrhKeyword'),
            hidShowType : $F('hidShowType'),
            hidTypeId : $F('hidTypeId')
        },
        timeout: 30000,
        onTimeout: function() {
            $('divList').update('通信の問題で処理を中断しました。しばらくたってからもう一度お試し下さい。');
        },
        onCreate : getDataFromServer,
        onSuccess: renderResults
    });         
}

/**
 * change page show when ajax is request -ing
 * @param  null
 * @return void
 */
function getDataFromServer()
{
    var html = '読み込み中、少々お待ちください…………  ' + '<img src="' + UrlConfig.StaticUrl + '/admin/img/photoeffect/loading.gif">';
    $('divList').update(html);
    //$('mixiapps_admin').scrollTo();
} 
       
/**
 * response from application view ajax request
 * @param  object response
 * @return void
 */
function renderResults(response)
{ 
    try {    
        if (response.responseText != '' && response.responseText != 'false') {      
            var responseObject = response.responseText.evalJSON(); 
            //show response array data to list table
            if (responseObject && responseObject.info && responseObject.info.length > 0) {            
                var html = showInfo(responseObject.info);
                var nav = showPagerNav(responseObject.count, Number($F('pageIndex')), CONST_DEFAULT_PAGE_SIZE);        
                $('divList').update(html + nav);                                                
                
                //curnumber
                $('lblCount').update(responseObject.count == null ? 0 : responseObject.count);
                   var numstart = (parseInt($('pageIndex').value) - 1) * CONST_DEFAULT_PAGE_SIZE + 1;
                   var numend = (numstart + CONST_DEFAULT_PAGE_SIZE - 1) > parseInt(responseObject.count) ? responseObject.count : (numstart + CONST_DEFAULT_PAGE_SIZE - 1);
                   if (0 == responseObject.count) {
                       numstart = 0;
                       numend = 0;
                   }
                   $('lblNumS').update(numstart);
                   $('lblNumB').update(numend);
                   $('divHead').show();
                $('watchFoot').show();
            }
            else {
                $('divList').update('該当するコンテンツが見つかりませんでした。');
                $('divHead').hide();
                $('watchFoot').hide();                
            }
            
            //show type [0-search|1-ID別コンテンツ一覧｜2-監視対象別のコンテンツ一覧|3-監視担当者別のコンテンツ一覧]
            if ('2' == $F('hidSrhStatus')) {
                $('lblTitle').update('容疑コンテンツ一覧');
            }
            else if ('1' == $F('hidShowType')) {
                $('lblTitle').update('<strong>投稿者ID:' + $F('hidTypeId') + '</strong>のコンテンツ一覧');
            }
            else if ('2' == $F('hidShowType')) {
                $('lblTitle').update('<strong>対象ID:' + $F('hidTypeId') + '</strong>のコンテンツ一覧');
            }
            else if ('3' == $F('hidShowType')) {
                $('lblTitle').update(responseObject.name.escapeHTML() + 'さんが監視したコンテンツ一覧');
            }
            else {
                var strObj = 'すべて';
                if ('1' == $F('hidSrhStatus')) {
                    strObj = '未処理';
                }
                else if ('3' == $F('hidSrhStatus')) {
                    strObj = '問題なし';
                }
                else if ('4' == $F('hidSrhStatus')) {
                    strObj = '保留';
                }
                else if ('5' == $F('hidSrhStatus')) {
                    strObj = '違反';
                }
                var strKeyword = '';
                if (null != $F('hidSrhKeyword') && '' != $F('hidSrhKeyword')) {
                    strKeyword = '検索キーワード「' + $F('hidSrhKeyword').escapeHTML() + '」, ';
                }
                $('lblTitle').update('コンテンツ検索結果（' + strKeyword + '検索対象「' + strObj + '」）');
            }
        }
    } catch (e) {
        alert(e);
    }
}

/**
 * show application table
 * @param  object array
 * @return string
 */
function showInfo(array)
{
    //concat html tags to array data
    var html = '';
                        
    html += '<table width="100%" cellpadding="0" cellspacing="0" border="0" id="dataGrid">'
            + '<thead>'
            + '<tr class="head">'
            + '<th>投稿日時</th>';
    if ('1' != $F('hidShowType')) {
        html += '<th>投稿者ID</th>';
    }
    if ('2' != $F('hidShowType')) {
        html += '<th>対象ID</th>';
    }
    html += '<th>監視対象</th>'
            + '<th>状態</th>';
    if ('3' != $F('hidShowType')) {
        html += '<th>担当者</th>';
    }
    html += '</tr>'
            + '</thead>'
            + '<tbody>';
                        
    //for each row data
    for (var i = 0 ; i < array.length ; i++) {        
        var cssClass = 'a';
        if (1 == i % 2) {
            cssClass = 'b';
        }
        
        var selected1 = '';
        var selected3 = '';
        var selected4 = '';
        var selected5 = '';
        if (1 == array[i].status || 2 == array[i].status) {
            selected1 = ' selected="selected" ';
        }
        else if (3 == array[i].status) {
            selected3 = ' selected="selected" ';
        }
        else if (4 == array[i].status) {
            selected4 = ' selected="selected" ';
        }
        else if (5 == array[i].status) {
            selected5 = ' selected="selected" ';
        }
        
        var strDisabled = '';
        var strHidInput = '<input type="hidden" id="commentId' + i + '" name="commentId[]" value="' + array[i].comment_id + '" />';
        if ($F('hidIsWatcher') && 3 <= array[i].status) {
             strDisabled = 'disabled="disabled"';
             strHidInput = '';
        }
        var strSelect = '<select id="selStatus' + i + '" name="selStatus[]" ' + strDisabled + '>' 
                      + '<option value="1"' + selected1 + '>未処理</option>' 
                      + '<option value="3"' + selected3 + '>問題なし</option>' 
                      + '<option value="4"' + selected4 + '>保留</option>' 
                      + '<option value="5"' + selected5 + '>違反</option></select>'
                      + strHidInput;
        var strCommentor = array[i].uid;//'<a href="javascript:void(0);" onclick="showType(1,\'' + array[i].uid + '\');return false;">' + array[i].uid + '</a>';
        var strObjector = array[i].comment_id;//'<a href="javascript:void(0);" onclick="showType(2,\'' + array[i].uid + '\');return false;">' + array[i].comment_id + '</a>';
        var strAdminor = array[i].admin_id == null ? '' : array[i].admin_name.escapeHTML();//'<a href="javascript:void(0);" onclick="showType(3,\'' + array[i].admin_id + '\');return false;">' + array[i].admin_name.escapeHTML() + '</a>';
                
        html += '<tr class="' + cssClass + '">'
              + '    <td>' + array[i].format_time + '</td>';
        if ('1' != $F('hidShowType')) {
            html += '    <td>' + strCommentor + '</td>';
        }
        if ('2' != $F('hidShowType')) {
            html += '    <td>' + strObjector + '</td>';
           }
           //TODO:光る雲を突き抜け<span style="background:#ffff99;">フライアウェイ</span>…体中に広がるパノラマ。
           var strContent = '';
           if (null != array[i].comment && '' != array[i].comment) {
               strContent = array[i].comment.escapeHTML().replace($F('hidSrhKeyword'), '<span style="background:#ffff99;">' + $F('hidSrhKeyword') + '</span>');
           }
        html += '    <td width="50%">' + strContent + '</td>'
              + '    <td width="150">' + strSelect + '</td>';
        if ('3' != $F('hidShowType')) {
            html += '    <td width="100">' + strAdminor + '</td>';
        }
        html += '</tr>';
    } //end for
    
    html += '</tbody>'
            + '</table>';
    
    return html;
}

/**
 * show application table
 * @param  object array
 * @return string
 */
function showType(type, id)
{
    if (null == type || '' == type || null == id || '' == id) {
        return false;
    }
    $('showType').value = type;
    $('typeId').value = id;
    $('frmList').submit();
}