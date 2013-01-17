/**
 * edit(/board/edit.js)
 *  edit user board setting info  
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/03/11    Liz
 */


/**
 * edit setting info
 *
 * @param string content
 * @return void
 */
function editSetting()
{
    var txtTitle = $F('txtTitle');
    var txtIntroduce = $F('txtIntroduce');

    var url = UrlConfig.BaseUrl + '/ajax/board/edit';

    new Ajax.Request(url, {
        method: 'post',
        parameters : {
            txtTitle : txtTitle,
            txtIntroduce : txtIntroduce,
            designSkin : getRadioBoxValue('designSkin'),
            ddlBoardPublicType : 0,
            ddlCommentPublicType : 0
        },
        onTimeout: function() {
            $('inner').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        //onCreate : getDataFromServer_edit,
        onSuccess: renderResults_edit});
}

/**
 * show processing info
 */
function getDataFromServer_edit()
{
    $('inner').innerHTML = '<p class="loadingBar">' + '<img src="' + UrlConfig.StaticUrl + '/apps/board/img/loading.gif" alt="" />' + '</p>';
}

/**
 * callback funtion when success
 *  set the callback data to the showArea of the page
 *
 * @param string response
 * @return void
 */
function renderResults_edit(response)
{

    if (response.responseText) {
       gotoboard($F('uid'));
    }
	if ( null != getCookie('app_top_url_board') ) {
		top.location.href = getCookie('app_top_url_board') +  '#pagetop';            
	}
   //location.href = '#top';
}

/**
 * get radio box value
 */
function getRadioBoxValue(radioName)
{
    var obj = document.getElementsByName(radioName);

    for(i = 0; i < obj.length; i++) {
        if( obj[i].checked ) {
            return obj[i].value;
        }
    }
    return "undefined";
}

/**
 * show check  radio
 */
function showCheckRadio(radioName, checkRadio)
{   
    var radios=document.getElementsByName(radioName);
    var len=radios.length;
    for( var i=0; i<len; i++ ) {
        if( checkRadio == radios[i].value ) {
            radios[i].checked = true;
        }
    }
    if (Prototype.Browser.IE) {
        var txtSkin = $F('txtSkin');
        var hiddenSkin = txtSkin.substr(20, 1);
        $('hiddenSkin').value = hiddenSkin;
    }
}

function getUserSetInfo()
{
    var url = UrlConfig.BaseUrl + '/ajax/board/getusersetinfo';

    new Ajax.Request(url, {
        method: 'post',
        
        onTimeout: function() {
            $('inner').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        //onCreate : getDataFromServer_edit,
        onSuccess: showUserSet});
}

function showUserSet(response)
{
    var responseObject = response.responseText.evalJSON();
    
    var title = responseObject.title;
    var setInfo = responseObject.setInfo;
    var bownerId = responseObject.bownerId;
    var uid = responseObject.uid;
    var thumbnailUrl = responseObject.thumbnailUrl;
    var introduce = responseObject.introduce;
    
    html = '';
    html += '<div id="hdr">'
         +  '<p class="pic">'
         +  '<img src="'+thumbnailUrl+'" width="60" height="60" /></p>'
         +  '<h1>'+title+'</h1>'
         +  '<p id="copy">'+introduce+'</p>'
         +  '</div><!--/#hdr-->'
         +  '<ul id="gNav">'
         +  '   <li id="bbs"><a href="javascript:void(0);" onclick="gotoboard('+uid+');">あしあと帳</a></li>'
         +  '   <li id="posted"><a href="javascript:void(0);" onclick="getHistoryList();">投稿履歴</a></li>'
         +  '   <li id="edit" name="edit"><a href="javascript:void(0)" onclick="getUserSetInfo()">設定変更</a></li>'
         +  '</ul>'
         +  '<div id="content">'
         +  '<div id="inner" class="inner">'
         +  '   <div id="titleEdit" class="section">'
         +  '       <h2>タイトル</h2>'
         +  '       <div class="items"><div class="inner">'
         +  '           <form id="frmEdit" name="frmEdit" onsubmit="return false;">'
         +  '               <table class="editTable">'
         +  '                   <tr>'
         +  '                       <th>タイトル</th>'
         +  '                       <td><input type="text" name="txtTitle"  id="txtTitle"  value="'+title+'" onblur="whenBlur()" onfocus="whenFocus()" />'
         +  '                       <p class="note">※全角20文字以内</p></td>'
         +  '                   </tr>'
         +  '                   <tr>'
         +  '                       <th>説明</th>'
         +  '                       <td><textarea name="txtIntroduce" id="txtIntroduce" rows="5">'+setInfo.introduce+'</textarea>'
         +  '                       <p class="note">全角100文字以内</p></td>'
         +  '                   </tr>'
         +  '               </table>'
         +  '           </form>'
         +  '       </div></div>'
         +  '   </div><!--/#titleEdit-->'
         +  '   <div id="designEdit" class="section">'
         +  '       <h2>デザインスキン</h2>';
    if (Prototype.Browser.IE) {
    
    html += '       <div class="items"><div class="inner">'
         +  '           <ul id="skinList">'
         +  '               <li id="skin01" onmouseover="previewBoardSet(1)" onmouseout="deletePreview()">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(1)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin1_s.png" alt="" />'
     //    +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview01.png" alt="" /></span>'
                      
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin1.png" />ケーキ</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin02" onmouseover="previewBoardSet(2)" onmouseout="deletePreview()">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(2)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin2_s.png" alt=""/>'
    //     +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview02.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin2.png" />こうもり</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin03" onmouseover="previewBoardSet(3)" onmouseout="deletePreview()">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(3)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin3_s.png" alt=""/>'
   //      +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview03.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin3.png" />モスキート</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin04" onmouseover="previewBoardSet(4)" onmouseout="deletePreview()">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(4)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin4_s.png" alt=""/>'
  //       +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview04.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin4.png" />バイク</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin05" onmouseover="previewBoardSet(5)" onmouseout="deletePreview()">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(5)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin5_s.png" alt=""/>'
  //       +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview05.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin5.png" />薔薇の花</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin06" onmouseover="previewBoardSet(6)" onmouseout="deletePreview()">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(6)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin6_s.png" alt=""/>'
  //       +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview06.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin6.png" />えんぴつ</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '           </ul>'
         +  '       </div></div>'
         +  '   </div><!--/#designEdit-->';

    }
    else {
    html += '       <div class="items"><div class="inner">'
         +  '           <ul id="skinList">'
         +  '               <li id="skin01">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(1)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin1_s.png" alt="" />'
         +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview01.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin1.png" />ケーキ</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin02">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(2)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin2_s.png" alt=""/>'
         +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview02.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin2.png" />こうもり</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin03">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(3)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin3_s.png" alt=""/>'
         +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview03.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin3.png" />モスキート</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin04">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(4)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin4_s.png" alt=""/>'
         +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview04.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin4.png" />バイク</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin05">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(5)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin5_s.png" alt=""/>'
         +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview05.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin5.png" />薔薇の花</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '               <li id="skin06">'
         +  '                   <label>'
         +  '                       <a href="javascript:void(0)" onclick="selectSkin(6)">'
         +  '                       <img src="'+UrlConfig.StaticUrl+'/apps/board/img/skin6_s.png" alt=""/>'
         +  '                       <span class="preview"><img src="'+UrlConfig.StaticUrl+'/apps/board/img/preview06.png" alt="" /></span>'
         +  '                       </a>'
         +  '                       <span><input type="radio" name="designSkin" value="/apps/board/img/skin6.png" />えんぴつ</span>'
         +  '                   </label>'
         +  '               </li>'
         +  '           </ul>'
         +  '       </div></div>'
         +  '   </div><!--/#designEdit-->';

    }
    html += '   <input type="hidden" id="bownerId" name="bownerId" value="'+bownerId+'">'
         +  '   <input type="hidden" id="uid" name="uid" value="'+uid+'">'
         +  '   <input type="hidden" id="pageName" name="pageName" value="edit">'
         +  '   <input type="hidden" id="hiddenTitle" name="hiddenTitle" value="'+title+'">'
         +  '   <input type="hidden" id="hiddenSkin" name="hiddenSkin" value="">'
         +  '   <input type="hidden" name="txtSkin" id="txtSkin" value="'+setInfo.image_url+'" />'
         +  '   <p id="btnEdit"><input id="btnEditSub" type="image" src="'+UrlConfig.StaticUrl+'/apps/board/img/btn_change.png" alt="変更する" onclick="editSetting()"/></p>'
         +  '</div></div>';
    $('mainColumn').innerHTML = html;
    $('mainColumn').show();

    if (document.getElementById('bbsb') != null){
        document.getElementById('bbsb').id = 'editb';
    }
    if (document.getElementById('postedb') !=null){
        document.getElementById('postedb').id = 'editb';
    }
    showCheckRadio('designSkin', $F('txtSkin'));
    
    //if (Prototype.Browser.Gecko) {
        //adjustHeight(760);
    //}
    //else {
        adjustHeight();
    //}
}

function whenBlur()
{
    if ($F('txtTitle') == '') {
        $('txtTitle').value = $F('hiddenTitle');
    }
}

function whenFocus()
{

    if ($F('txtTitle') == $F('hiddenTitle')) {
        $('txtTitle').value = '';
    }
}

function selectSkin(selectedSkin)
{   
    
    if (selectedSkin == null || selectedSkin == '') {
        selectedSkin = Number($F('hiddenSkin'));
    }

    var skin = "/apps/board/img/skin" + selectedSkin + ".png";
    var allSkin = document.getElementsByName("designSkin");
    var len = allSkin.length;
    for( var i=0; i<len; i++ ) {
        if( skin == allSkin[i].value ) {
            allSkin[i].checked = true;
        }
    }
}


function previewBoardSet(skinName)
{  
    if ($F('pageName') == 'edit') {
        if (skinName == null || skinName == '') {
            skinName = Number($F('hiddenSkin'));
        }
    }
    $('priviewDiv').setStyle({
        left: '22px'
    });
    
    var leftSize = null;
    if (skinName > 1) {
        leftSize = 130 + (skinName - 2) * 110;
    }
    
    var skin = "/apps/board/img/preview0" +skinName+ ".png";
    var html = '<a href="javascript:void(0)"><img src="'+UrlConfig.StaticUrl+skin+'" alt=""/></a>';
    
    $('priviewDiv').innerHTML = html;
    $('priviewDiv').show();
    
    $('priviewDiv').setStyle({
        display: 'block'
    });
    
    if (skinName > 1) {
        $('priviewDiv').setStyle({
            left: ''+leftSize+'px'
        });
    }
    
    if ($F('pageName') == 'edit') {
        $('hiddenSkin').value = skinName;
    }
}

function deletePreview()
{    
    $("priviewDiv").hide();
}

