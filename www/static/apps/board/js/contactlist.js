/**
 * contact(/board/contact.js)
 *  get|delete board list
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/03/10    shenhw
 * @modify     2009/06/02    shenhw
 */
var contactHeight = 67; //px
var column = 3;
var row = 7;
var contactNumPerPage = column * row;
var columnWidth = 199; //px
var canMove = 1;

/**
 * windows load function
 * register funcion
 */
Event.observe(window, 'load', function()
{
    if ($('picListBox'))
	{
		//getContactList();
		
	}

    ZeroClipboard.setMoviePath(UrlConfig.StaticUrl + '/apps/board/swf/ZeroClipboard.swf');

    clip = new ZeroClipboard.Client();
    clip.setHandCursor( true );
    clip.setText($F('boardUrl'))
    
    
    clip.glue( 'clip_button' );


});

/**
 * get the select page's messageboard list
 *
 * @param page int
 * @return void
 */
function getContactList()
{   
    var requestObject = new Object();
    requestObject.contactnumperpage = contactNumPerPage;
    requestObject.viewerid = $F('uid');
    requestObject.ownerid = $F('bownerId');
    
    var jsonRequest = Object.toJSON(requestObject);

    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/board/getminicontactlist';

    new Ajax.Request(url, {
        method: 'get',
        parameters: 'request='+escape(jsonRequest)+'&r='+escape(rand),
        onTimeout: function() {
            $('picListBox').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
      //  onCreate : getDataFromServer4Contact,
        onComplete: renderResults4Contact});
}

/**
 * show processing info
 */
function getDataFromServer4Contact()
{
    $('picListBox').innerHTML = '<p class="loadingBar">' + '<img src="' + UrlConfig.StaticUrl + '/apps/board/img/loading.gif" alt="" />' + '</p>';
}

/**
 * callback funtion when success
 *  set the callback data to the showArea of the page
 *
 * @param string response
 * @return void
 */
function renderResults4Contact(response)
{
    var responseObject = response.responseText.evalJSON();
    
    if ( "" == responseObject.info ) {
        $('picListBox').innerHTML = 'まだコンタクトがありません';
    }
    else {
    
        var aryContactList = responseObject.info;
        var countContactList = aryContactList.length;
        var allContactCount = responseObject.maxCount;
        var contactMaxPager = responseObject.maxPage;
        var contactPageIndex = responseObject.pageindex;
        
        var bownerId = $F('bownerId');
        var viewerId = $F('uid');
        var activeUidNum = 0;
        if (1 == contactMaxPager) {
            row = Math.ceil(countContactList / column);
        }
        var picListBoxHeight = contactHeight * row;
        var picListBoxWidth = contactMaxPager * columnWidth;

	    var html = '';

	    html += '<div id="picListScroll" class="picListScroll" style="left:0px; height:' + picListBoxHeight + 'px;width:' + picListBoxWidth + 'px;">';
	    html += '<div style="height:' + picListBoxHeight + 'px;"><ul class="picList">';
	        
	    //contact list
	    for (i = 0 ; i < countContactList ; i++) {
            if ((i != 0) && (0 == i % contactNumPerPage)) {
				html += '</ul></div>';
				html += '<div style="height:' + picListBoxHeight + 'px;"><ul class="picList">';
            }
            
            if (bownerId == aryContactList[i].uid ) {
                html += '<li class="active">';
            } else {
                html += '<li id="li'+aryContactList[i].uid+'" class="">';
            }
	    
	        if ( "" != aryContactList[i].thumbnailUrl ) {
	           html += '<a href="javascript:void(0);" onclick="viewFriendBoard('+aryContactList[i].uid+', "li'+(i+3)+'")"><img width="61" height="61" alt="' + aryContactList[i].displayName + '" src="' + aryContactList[i].thumbnailUrl + '"/></a></li>';
	        }
	        else {
	           html += '<a href="javascript:void(0);" onclick="viewFriendBoard('+aryContactList[i].uid+', "li'+(i+3)+'")"><img width="61" height="61" alt="' + aryContactList[i].displayName + '" src="' + UrlConfig.StaticUrl + '/apps/board/img/user_pic.gif"/></a></li>';
	        }
	    }
	    
        html += '</ul></div>';
	    html += '</div><!--/#scrollable-->';

        $('picListBox').innerHTML = html;
        $('picListBox').setStyle('height: ' + picListBoxHeight + 'px');
        
        $('contactMaxPager').value = contactMaxPager;
        $('contactPageIndex').value = contactPageIndex;
        $('rightCount').value = 0;
        $('leftCount').value = 0;
        
        if (contactPageIndex < contactMaxPager) {
            $('nextPage').setStyle({display: 'block'});
        }
        if (contactPageIndex > 1) {
            $('prevPage').setStyle({display: 'block'});
        }
    }
}

/**
 * show pager
 *
 * @param int contactMaxPager
 * @param int contactPageIndex
 * @return void
 */
function showPager(contactMaxPager, contactPageIndex) {

	if (contactMaxPager > 1) {
	    if (contactPageIndex < contactMaxPager) {
            new Effect.Appear('nextPage', { duration: 0.8 });
	    } else {
            new Effect.Fade('nextPage', { duration: 0.8 });
	    }

        if (contactPageIndex > 1) {
            new Effect.Appear('prevPage', { duration: 0.8 });
        } else {
            new Effect.Fade('prevPage', { duration: 0.8 });
        }
	}
}

/**
 * paging
 *
 * @param int index
 * @return void
 */
function paging(index) {
    if (1 == canMove) {
        moveContact(index, $F('contactPageIndex'));
    }
}

/**
 * get the select page's messageboard list
 *
 * @param page int
 * @return void
 */
function getMoreContactInfo(type, contactPageIndex)
{
    canMove = 2;
    var requestObject = new Object();
    requestObject.contactnumperpage = contactNumPerPage;
    requestObject.viewerid = $F('uid');
    requestObject.type = type;
    requestObject.contactPageIndex = contactPageIndex;
    
    var jsonRequest = Object.toJSON(requestObject);

    var rand=Math.random();
    var url = UrlConfig.BaseUrl + '/ajax/board/getmorecontactlist';

    new Ajax.Request(url, {
        method: 'get',
     //   parameters: 'request='+escape(jsonRequest)+'&r='+escape(rand),
        parameters: {
            contactnumperpage: contactNumPerPage,
            viewerid: $F('uid'),
            type: type,
            contactPageIndex: contactPageIndex
        },
        onTimeout: function() {
            $('picListBox').update('通信の問題で処理を中断しました。しばらく経ってからもう一度お試しください');
        },
        onComplete: renderResults4MoreContact});
}

/**
 * callback funtion when success
 *  set the callback data to the showArea of the page
 *
 * @param string response
 * @return void
 */
function renderResults4MoreContact(response)
{
    var responseObject = response.responseText.evalJSON();
    
    if ( "" == responseObject.info ) {
        $('picListBox').innerHTML = 'まだコンタクトがありません';
    }
    else {
        var html = "";
    
        var aryContactList = responseObject.info;
        var countContactList = aryContactList.length;
        var rightCount = responseObject.rightCount;
        var leftCount = responseObject.leftCount;
        
        var bownerId = $F('bownerId');
        var viewerId = $F('uid');

        var picListBoxHeight = contactHeight * row;

        html += '<div style="height:' + picListBoxHeight + 'px;"><ul class="picList">';
            
        //contact list
        for (i = 0 ; i < countContactList ; i++) {
            if ((i != 0) && (0 == i % contactNumPerPage)) {
                html += '</ul></div>';
                html += '<div style="height:' + picListBoxHeight + 'px;"><ul class="picList">';
            }
            html += '<li id="li'+aryContactList[i].uid+'" class="">';
            if ( "" != aryContactList[i].thumbnailUrl ) {
               html += '<a href="javascript:void(0);" onclick="viewFriendBoard('+aryContactList[i].uid+')"><img width="61" height="61" alt="' + aryContactList[i].displayName + '" src="' + aryContactList[i].thumbnailUrl + '"/></a>';
            }
            else {
               html += '<a href="javascript:void(0);" onclick="viewFriendBoard('+aryContactList[i].uid+')"><img width="61" height="61" alt="' + aryContactList[i].displayName + '" src="' + UrlConfig.StaticUrl + '/apps/board/img/user_pic.gif"/></a>';
            }
            html += '</li>';
        }
        
        html += '</ul></div>';

        if (0 != rightCount) {
            new Insertion.Bottom('picListScroll', html);
            $('rightCount').value = rightCount;
            canMove = 1;
            moveContact(1, $F('contactPageIndex'));
        }
        else {
            $('leftCount').value = leftCount;
            var contactOffSet = -columnWidth * leftCount;
            $('picListScroll').setStyle({left:contactOffSet+'px'});  
            new Insertion.Top('picListScroll', html);
            canMove = 1;
            moveContact(-1, $F('contactPageIndex'));
        }
    }
}

/**
 * move contact
 *
 * @param int type  1:move right -1:move left
 * @param int  contactPageIndex
 * @return void
 */
function moveContact(type, contactPageIndex) {
    if (canMove == 1) {
		var move = 0;
		
		var rightCount = Number($('rightCount').value);
		var leftCount = Number($('leftCount').value);
		
		if (type == 1) {
		    //can move
		    if ( rightCount > 0 ) {
		        var move = -(columnWidth * type);
		        
		        $('rightCount').value = rightCount - 1;
		        $('leftCount').value = leftCount + 1;
		    }//get more
		    else {
		        getMoreContactInfo(type, contactPageIndex);
		        return;
		    }
		}//move left
		else if (type == -1) {
		    //can move
		    if ( leftCount > 0  ) {
		        var move = -(columnWidth * type);
		        
		        $('rightCount').value = rightCount + 1;
		        $('leftCount').value = leftCount - 1;
		    }
		    //get more
		    else {
		        getMoreContactInfo(type, contactPageIndex);
		        return;
		    }
		}
	
		try {
		    new Effect.Move ('picListScroll',
		        {x: move, y: 0, duration: 0.8,
		        beforeStart : function() { canMove = 2; },
		        afterFinish : function() {
                    canMove = 1; 
			        var contactPageIndex = parseInt($F('contactPageIndex')) + parseInt(type);
			        var contactMaxPager = $F('contactMaxPager');
			        $('contactPageIndex').value = contactPageIndex;
			
			        showPager(contactMaxPager, contactPageIndex);
		        }
		    });
		}
		catch (e) {
		}
	}
}

function viewFriendBoard(uid) {
    var oldUid = $F("oldUid");
    $("oldUid").value = uid;
    if (oldUid != null && oldUid != '' && oldUid != $F('uid')) {
        $('li'+oldUid+'').removeClassName('active');
    }
    $('li'+uid+'').addClassName('active');
    gotoboard(uid);
}

