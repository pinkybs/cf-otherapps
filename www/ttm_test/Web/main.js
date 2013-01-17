function thisMovie(movieName) {
	/*
     if (navigator.appName.indexOf("Microsoft") != -1) {
         return window[movieName];
     } else {
         return document[movieName];
     }
     */
    if (window.document[movieName]) {
	    return window.document[movieName];
	}
	if (navigator.appName.indexOf("Microsoft Internet") == -1) {
	    if (document.embeds && document.embeds[movieName])
	        return document.embeds[movieName];
	    }
	else {
	    return document.getElementById(movieName);
	}
 }
 
function FlashMessage(Str) {
  alert(Str);
}

function loadEnvironment() {  
    var env=opensocial.getEnvironment();  
    var domainname=env.domain;
    try{
    	if (thisMovie('ttmswf')) {
    		thisMovie('ttmswf').onJSLoadEnvironment(domainname);
    	}
    	else {
    		setTimeout(loadEnvironment, 500);
    	}
        
    }catch (e) { }   
}

function loadViewer() {
     var req = opensocial.newDataRequest();
     req.add(req.newFetchPersonRequest(opensocial.IdSpec.PersonId.VIEWER), "viewer");
     req.send(onLoadViewer);
}

function onLoadViewer(data) {
    var person = data.get("viewer").getData();
    var personname = person.getDisplayName();
    var personid = person.getId();
    var personpicurl = person.fields_.thumbnailUrl;
    try{
        thisMovie('ttmswf').onJSLoadViewer(personid, personname, personpicurl);  
    } catch (e) { } 
}

function loadFriend(userId) {
    var req = opensocial.newDataRequest();
    var opt_params={};
    opt_params[opensocial.DataRequest.PeopleRequestFields.MAX] = 1;
    req.add(req.newFetchPersonRequest(userId, opt_params), 'viewerFriend');
    req.send(onLoadFriend);     
}

function onLoadFriend(data) {
    var viewerFriend = data.get('viewerFriend').getData();   
    if(viewerFriend) {
      var friendid = viewerFriend.getId().toString();
      var friendname = viewerFriend.getDisplayName();
      var friendpicurl = viewerFriend.fields_.thumbnailUrl;    
      try{
          thisMovie('ttmswf').onJSLoadFriend(friendid, friendname, friendpicurl);  
      } catch (e) { }
    } 
}

function loadFriendOne(userId) {
    var req = opensocial.newDataRequest();
    var opt_params={};
    opt_params[opensocial.DataRequest.PeopleRequestFields.MAX] = 1;
    req.add(req.newFetchPersonRequest(userId, opt_params), 'viewerFriend');
    req.send(onLoadFriendOne);     
}

function onLoadFriendOne(data) {
    var viewerFriend = data.get('viewerFriend').getData();   
    if(viewerFriend) {
      var friendid = viewerFriend.getId().toString();
      var friendname = viewerFriend.getDisplayName();
      var friendpicurl = viewerFriend.fields_.thumbnailUrl;    
      try{
          thisMovie('ttmswf').onJSLoadFriendOne(friendid, friendname, friendpicurl);  
      } catch (e) { }
    } 
}

function loadFriends() {
    var req = opensocial.newDataRequest();
    var viewerFriendsIdSpec = opensocial.newIdSpec({ "userId" : "VIEWER", "groupId" : "FRIENDS" });
    var opt_params = {};
    opt_params[opensocial.DataRequest.PeopleRequestFields.PROFILE_DETAILS] = [
       opensocial.Person.Field.HAS_APP
    ];
    // opt_params[opensocial.DataRequest.PeopleRequestFields.MAX] = 100;
    opt_params[opensocial.DataRequest.PeopleRequestFields.MAX] = 1000;
    req.add(req.newFetchPeopleRequest(viewerFriendsIdSpec, opt_params), 'viewerFriends');
    req.send(onLoadFriends);
}

function onLoadFriends(data) {
    var viewerFriends = data.get('viewerFriends').getData();
    var FriendsList = new Array();
    viewerFriends.each(function(person) {
    	var displayName = person.getDisplayName();
    	if (displayName) {
    		var hasApp = person.getField(opensocial.Person.Field.HAS_APP) == 'true';
    		if (hasApp) {
		        var FriendObj=new Object();
		        FriendObj.PERSONID          = person.getId().toString();     
		        FriendObj.NAME              = gadgets.util.unescapeString(displayName);
		        FriendObj.THUMBNAILURL      = person.getField(opensocial.Person.Field.THUMBNAIL_URL)
		        FriendsList.push(FriendObj);
	        }
        }
    });
    
    try{
        thisMovie('ttmswf').onJSLoadFriends(FriendsList);
    }catch (e) {
        alert(e.name + ": " + e.message);
    } 
}

function mixi_invite() {
    var recipients = null;
    var reason = opensocial.newMessage('your friend would like you to intall this application.');
    opensocial.requestShareApp(recipients, reason, function(response) {
        //callback done
    });
}

function postActivity(score) {
	var params = {};
	params[opensocial.Activity.Field.TITLE] = "自己ベスト更新！" + score + "段 積み上げ、また伝説に1歩近づきました!";
	var activity = opensocial.newActivity(params);
	opensocial.requestCreateActivity(activity, opensocial.CreateActivityPriority.HIGH, function(response) {
		if (response.hadError()) {
			var code = response.getErrorCode();
		}
	});
}

function getFlashPlayerVersion() {
    var vsn = '';
    if( navigator.plugins && navigator.mimeTypes.length ) {     // not IE
    	if (undefined == navigator.plugins["Shockwave Flash"]) {
    		return 0;
    	}
        var tmp = navigator.plugins["Shockwave Flash"].description.match(/([0-9]+)/);
        vsn = tmp[0];
    } else {    // IE
    	try{
	    	var objF = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
	        var tmp = objF.GetVariable("$version").match(/([0-9]+)/);
	        vsn = tmp[0];
	    } catch (e) {
	    	return 0;
	    }
    }
    return vsn;
}