/**!
 * Mixi Opensocial JavaScript Library For Canvas
 * Version: 1.1.0
 *
 * Copyright (c) 2009 CommunityFactory.com
 */
 
var mixios = mixios || {};

mixios.getType = function(obj) {
    var type = typeof(obj);
    if (type == 'object' && (obj instanceof Array)) {
       type = 'array';
    }
    
    return type;
}

mixios.hitch = function(thisObject, method) {  
    var fcn;
    if(typeof method == "string") {  
        fcn = thisObject[method];
    } else {  
        fcn = method;
    }  
    
    return function() {  
        return fcn.apply(thisObject, arguments);  
    }  
}

mixios.DateFormat = function(d, fmt)
{
    var o = {
        "M+" : d.getMonth() + 1,
        "d+" : d.getDate(),
        "h+" : d.getHours(),
        "m+" : d.getMinutes(),
        "s+" : d.getSeconds(),
        "q+" : Math.floor((d.getMonth() + 3)/3),
        "S"  : d.getMilliseconds()
    };
    
    if(/(y+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (d.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    
    for(var k in o) {
        if(new RegExp("("+ k +")").test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        }
    }
    
    return fmt; 
}

mixios.Logger = function(enabled) {
    this.enabled = enabled || true;
    var hasConsole = window.console && console.debug;
    
    this.debug = function(message) {
        if (this.enabled && hasConsole) {
            console.debug(message);
        }
        return this;
    }
    
    this.error = function(message) {
        if (this.enabled && hasConsole) {
            console.error(message);
        }
        return this;
    }
}

mixios.application = mixios.application || {};

mixios.application.newCanvas = function(host, appName) {
    return new mixios.Canvas(host, appName);
}

/**
 * Class Canvas
 */
mixios.Canvas = function(host, appName) {
    this.version = '1.1.0';
    this.setHost(host);
    this.appName_ = appName.toLowerCase();
    this.appId_ = 0;
    this.appSignature_ = '';
    this.logger = new mixios.Logger(false);
    this.size_ = {width: '100%', height: '800'};
    this.ownerId_ = 0;
    this.viewerId_ = 0;
    this.nonce_ = '';
    this.isUpdated_ = false;
    
    this.isGetViewer = true;
    this.isGetOwner = true;
    
    this.runUrl_ = this.host_ + '/index/run';
    this.staticHost_ = 'http://static.mixi.communityfactory.net';
    
    this.mixiHost_ = 'http://mixi.jp';
    this.checkUrl_ = this.host_ + '/callback/checkstatus';
    this.inviteCallBackUrl_ = this.host_ + '/callback/invite';
    this.activityCallBackUrl_ = this.host_ + '/callback/activity';
    
    this.loadingImgSrc_ = this.staticHost_ + '/cmn/img/loading/loader.gif';
    this.containerId_ = 'canvas_container';
    this.childIframeId_ = 'content_iframe';
    
    this.init_();
}

mixios.Canvas.prototype.getHost = function() {
    return this.host_;
}

mixios.Canvas.prototype.setHost = function(host) {
    if (host.charAt(host.length - 1) == '/') {
        host = host.substr(0, host.length - 1);
    }
    this.host_ = host;
    return this;
}

mixios.Canvas.prototype.getStaticHost = function() {
    return this.staticHost_;
}

mixios.Canvas.prototype.setStaticHost = function(host) {
    this.staticHost_ = host;
    return this;
}

mixios.Canvas.prototype.getAppName = function() {
    return this.appName_;
}

mixios.Canvas.prototype.getAppId = function() {
    return this.appId_;
}

mixios.Canvas.prototype.setAppSignature = function(signature) {
    this.appSignature_ = signature;
    return this;
}

mixios.Canvas.prototype.enableDebug = function(enabled) {
    this.logger.enabled = enabled;
    return this;
}

mixios.Canvas.prototype.getSize = function() {
    return this.size_;
}

mixios.Canvas.prototype.setSize = function(size) {
    this.size_ = size;
    return this;
}

mixios.Canvas.prototype.setHeight = function(height) {
    this.size_.height = '' + height;
    return this;
}

mixios.Canvas.prototype.getOwnerId = function() {
    return this.ownerId_;
}

mixios.Canvas.prototype.getViewerId = function() {
    return this.viewerId_;
}

mixios.Canvas.prototype.isOwner = function() {
    return this.viewerId_ == this.ownerId_;
}

mixios.Canvas.prototype.getNonce = function() {
    return this.nonce_;
}

mixios.Canvas.prototype.setRunUrl = function(url) {
    if (url.indexOf('http://') == -1) {
        url = this.host_ + url;
    }
    this.runUrl_ = url;
    return this;
}

mixios.Canvas.prototype.getMixiHost = function() {
    return this.mixiHost_;
}

mixios.Canvas.prototype.setLoadingImgSrc = function(src) {
    if (src.indexOf('http://') == -1) {
        src = this.host_ + src;
    }
    this.loadingImgSrc_ = src;
    return this;
}

mixios.Canvas.prototype.getContainerId = function() {
    return this.containerId_;
}

mixios.Canvas.prototype.setContainerId = function(id) {
    this.containerId_ = id;
    return this;
}

mixios.Canvas.prototype.getContainerUrl = function() {
    var href = window.location.href;
    return href.substr(0, href.indexOf('#'));
}

mixios.Canvas.prototype.isRpcMode = function() {
    var rpc_mode = this.getParam('rpc_mode');
    if (rpc_mode) {
        return true;
    }
    
    return false;
}

mixios.Canvas.prototype.getRunAppliUrl = function() {
    return document.referrer;
}

mixios.Canvas.prototype.getJoinUrl = function() {
    return this.mixiHost_ + '/join_appli.pl?id=' + this.appId_;
}

mixios.Canvas.prototype.preLoadImage = function(images) {
    var d = document; 
    if(d.images) {
        if (!d.MM_p) d.MM_p = new Array();
        var i, j = d.MM_p.length, a = images;
        for(i = 0; i < a.length; i++) {
            if (a[i].indexOf("#") != 0) {
                d.MM_p[j] = new Image;
                d.MM_p[j++].src = a[i];
            }
        }
    }
}

mixios.Canvas.prototype.start = function() {
    if (this.isRpcMode()) {
		var u = location.href, h = u.substr(u.indexOf('#') + 1).split('&'), t, r;
		try {
            t = h[0] === '..' ? parent.parent : parent.frames[h[0]];
            r = t.gadgets.rpc.receive;
		} catch (e) {
		}
		r && r(h);
    } else {
        this.preLoadImage([this.loadingImgSrc_]);
        gadgets.util.registerOnLoadHandler(mixios.hitch(this, this.onload_));
    }
}

mixios.Canvas.prototype.checkStatus = function() {
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var post_data = {version: this.version, app_name: this.appName_, app_signature: this.appSignature_, view: "canvas", request_nonce: 1};
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    var self = this;
    gadgets.io.makeRequest(this.checkUrl_, function(response) {
        var status = response.data;
        if (status) {
          if (status.code == 1) {
              self.nonce_ = status.nonce || status.html;
              if (status.parameters) {
                  self.appId_ = status.parameters.app_id;
                  self.ownerId_ = status.parameters.owner_id;
                  self.viewerId_ = status.parameters.viewer_id;
                  if (self.isOwner()) {
                      self.isGetOwner = false;
                  } 
              }
              if (status.isUpdated) {
                  self.isUpdated_ = (status.isUpdated == 'true');
              }
              
              if (self.isUpdated_) {
                  self.submit_(null, null);
              }
              else {
                  self.getData();
              }
          }
          else {
              var container = document.getElementById(self.getContainerId());
              if (container) {
                  container.innerHTML = status.html;
              }
              if (status.script) {
                  eval(status.script);
              }
          }
        }
        
    }, params);  
}

/**
 * Creates the html for form. The function add the hidden elements 
 * used to pass user and friends information to the application
 */
mixios.Canvas.prototype.createFormHtml = function(url, viewerInfo, ownerInfo) {
    // set accept-charset="utf-8", safari 3.2 has a bug
    // mixi charset=euc-jp, our utf-8
    var formHtml = '<form name="mainform" id="mainform" method="post" accept-charset="utf-8" action="' + url + '">';
        
    formHtml += '<input type="hidden" name="version" value="' + this.version + '"/>';
    formHtml += '<input type="hidden" name="nonce" value="' + this.getNonce() + '"/>';
    formHtml += '<input type="hidden" name="container_type" value="' + this.getContainerType() + '"/>';
    formHtml += '<input type="hidden" name="windomain" value="' + this.getParam('parent') + '"/>';
    formHtml += '<input type="hidden" name="top_url" value="' + this.getRunAppliUrl() + '"/>';
    formHtml += '<input type="hidden" name="mixi_platform_api_url" value="' + this.getContainerUrl() + '"/>';
    
    if (viewerInfo) {
        formHtml += '<input type="hidden" name="viewer_info" value=\'' + gadgets.json.stringify(viewerInfo) + '\'/>';
    }
    
    if (ownerInfo) {
        formHtml += '<input type="hidden" name="owner_info" value=\'' + gadgets.json.stringify(ownerInfo) + '\'/>';
    }  

    formHtml += '</form>';
    
    return formHtml;
}

/**
 * Gets an iframe body for a given iframe element
 */
mixios.Canvas.prototype.extractIFrameBody = function(iFrameEl) {
    var doc = null;
    if (iFrameEl.contentDocument) { // For NS6 and Mozilla
        doc = iFrameEl.contentDocument; 
    } else if (iFrameEl.contentWindow) { // For IE5.5 and IE6
        doc = iFrameEl.contentWindow.document;
    } else if (iFrameEl.document) { // For IE5
        doc = iFrameEl.document;
    } else {
        return null;
    }
    
    //this is a hack for IE. If the following if loop is removed,
    // the body will be returned as null. We need to write something
    // to the document, here writing the loading image element
    if (doc){
        doc.open();
        doc.write('<div align="center" style="margin-top:200px;"><img src="' + this.loadingImgSrc_ + '" id="loadingImg" /></div>');
        doc.close();
    }
    return doc.body;
}

/**
 * Parses the response to the data information request
 * @param {Object} response data information that was requested.
 */
mixios.Canvas.prototype.onLoadData_ = function(response) {
    var item;
    //get data from mixi response
    
    if (this.isGetViewer) {
        item = response.get('viewer_data');
        if(item.hadError()) {
            this.logger.error("get viewer data error: " + item.getErrorMessage());
            return;
        }
        this.viewer_ = item.getData();
        // if viewer has not joined this app, redirect to app join page
        var hasApp = this.viewer_.getField(opensocial.Person.Field.HAS_APP);
        if(hasApp != 'true') {
           window.top.location.href = this.getJoinUrl();
           return;
        }
        
        item = response.get('viewer_friends_data');
        if(item.hadError()) {
            this.logger.error("get viewer friends data error: " + item.getErrorMessage());
            return;
        }
        this.viewerFriends_ = item.getData();
    } 
    
    if (this.isGetOwner) {
	    item = response.get('owner_data');
	    if(item.hadError()) {
	        this.logger.error("get owner data error: " + item.getErrorMessage());
	        return;
	    }
	    this.owner_ = item.getData();
	    
        item = response.get('owner_friends_data');
        if(item.hadError()) {
            this.logger.error("get owner friends data error: " + item.getErrorMessage());
            return;
        }
        this.ownerFriends_ = item.getData();
    }
    
    var viewerInfo = null;
    if(this.isGetViewer) {
        //fixed 2009-09-27
        //if viewer != owner
        //we can not get the data of this viewer's friend that not installed this app
        //but if the friend user is also friend with owner, it can get data!
        //eg. viewer(100), his friends: 123,124,125,221,222 (all not installed, hasApp == 'false')
        //owner(200), his friends 123,221,229,321,147 (all not installed, hasApp == 'false')
        //we can get 123,221, 229,321,147 data info, and can not get 123,125,222
        //so we do not update friends when viewer != owner, hoho~
        var friends = [];
        if (this.isOwner()) {
            friends = this.getFriends(this.viewerFriends_);
        }
        viewerInfo = {
          'user': this.getUser(this.viewer_),
          'friends': friends
        };
    }
    
    var ownerInfo = null;
    if (this.isGetOwner) {
	    ownerInfo = {
	       'user': this.getUser(this.owner_),
	       'friends': this.getFriends(this.ownerFriends_)
	    };
    }

    this.submit_(viewerInfo, ownerInfo);
}

mixios.Canvas.prototype.submit_ = function(viewerInfo, ownerInfo) {
    //create the frame element dynamically. 
    //Note: This frame element cant be added in the simple 
    //html because the opensocial api load functions stops working
    var frameID = this.childIframeId_;
    var frameSize = this.getSize();
    var frameElem = this.createFrameElement(frameID, frameSize);
    
    //add the frame element to the container div
    var frameContainer = document.getElementById(this.containerId_);
    frameContainer.innerHTML = '';
    frameContainer.appendChild(frameElem);
    var frameObj = document.getElementById(frameID);
    var frameBody = this.extractIFrameBody(frameObj);
    
    var url = this.runUrl_;
    
    // append option other params
    var viewParams = gadgets.views.getParams();

    for (var p in viewParams) {
       if (viewParams.hasOwnProperty(p)) {
           url = this.appendParameterToURL(url, p, viewParams[p]);
       } 
    }
            
    //link frame and form element
    frameBody.innerHTML = this.createFormHtml(url, viewerInfo, ownerInfo);
    
    //submit the form
    var form = frameBody.firstChild;
    
    form.submit();
}

mixios.Canvas.prototype.getData = function() {
    // for more profile info
    // default only [ID, NAME(nickname), THUMBNAIL_URL]
    var p = {};
    p[opensocial.DataRequest.PeopleRequestFields.PROFILE_DETAILS] = [
       opensocial.Person.Field.PROFILE_URL,
       mixi.PersonField.BLOOD_TYPE,
       opensocial.Person.Field.HAS_APP,
       //profile info, maybe can not get
       opensocial.Person.Field.ADDRESSES,
       opensocial.Person.Field.AGE,
       opensocial.Person.Field.DATE_OF_BIRTH,
       opensocial.Person.Field.GENDER
    ];
    var req = opensocial.newDataRequest();
    
    if (this.isGetViewer) {
        // request for viewer info
        req.add(req.newFetchPersonRequest(opensocial.IdSpec.PersonId.VIEWER, p), 'viewer_data');
    }
    
    if (this.isGetOwner) {
        // request for owner info
        req.add(req.newFetchPersonRequest(opensocial.IdSpec.PersonId.OWNER, p), 'owner_data');
    }
 
    p[opensocial.DataRequest.PeopleRequestFields.MAX] = 1000;
    
    if (this.isGetViewer) {
        var params2 = {};
        params2[opensocial.IdSpec.Field.USER_ID]  = opensocial.IdSpec.PersonId.VIEWER;
        params2[opensocial.IdSpec.Field.GROUP_ID] = "FRIENDS";
        var idspecViewer = opensocial.newIdSpec(params2); 
        // request for viewer friends info
        req.add(req.newFetchPeopleRequest(idspecViewer, p), 'viewer_friends_data');
    }
    
    if (this.isGetOwner) {
	    var params1 = {};
	    params1[opensocial.IdSpec.Field.USER_ID]  = opensocial.IdSpec.PersonId.OWNER;
	    params1[opensocial.IdSpec.Field.GROUP_ID] = "FRIENDS";
	    var idspecOwner = opensocial.newIdSpec(params1);
	    // request for owner friends info
	    req.add(req.newFetchPeopleRequest(idspecOwner, p), 'owner_friends_data');
    }
    
    //opensocial.DataRequest.FilterType.HAS_APP
    /*
    p[opensocial.DataRequest.PeopleRequestFields.FILTER]  = opensocial.DataRequest.FilterType.HAS_APP;
    req.add(req.newFetchPeopleRequest (idspecOwner, p), 'owner_friends_app_data');
    req.add(req.newFetchPeopleRequest (idspecViewer, p), 'viewer_friends_app_data');
    */
    
    req.send(mixios.hitch(this, this.onLoadData_));
}
        
mixios.Canvas.prototype.getUser = function(user) {
    var data = {
        id: user.getId(), 
        displayName: user.getDisplayName().replace(/&/g, '&amp;'), 
        thumbnailUrl: user.getField(opensocial.Person.Field.THUMBNAIL_URL), 
        profileUrl: user.getField(opensocial.Person.Field.PROFILE_URL),
        hasApp: user.getField(opensocial.Person.Field.HAS_APP)
    };
    var bloodType = user.getField(mixi.PersonField.BLOOD_TYPE);
    var address = user.getField(opensocial.Person.Field.ADDRESSES);
    var age = user.getField(opensocial.Person.Field.AGE);
    var dateOfBirth = user.getField(opensocial.Person.Field.DATE_OF_BIRTH);
    var gender = user.getField(opensocial.Person.Field.GENDER);
    
    if (bloodType) {
        data['bloodType'] = bloodType;
    }
    if (address) {
        data['address'] = address[0].getField(opensocial.Address.Field.UNSTRUCTURED_ADDRESS);
    }
    if (age) {
        data['age'] = age;
    }
    if (dateOfBirth) {
        data['dateOfBirth'] = mixios.DateFormat(dateOfBirth, 'yyyy-MM-dd');
    }
    if (gender) {
        data['gender'] = gender.getKey();
    }
    return data;
}
        
mixios.Canvas.prototype.getFriends = function(friends) {
    var data = [];
    var i = 0;
    var id, displayName, bloodType, address, age, dateOfBirth, gender;
    friends.each(function(user) {
        displayName = user.getDisplayName();
        //has basic info
        if (displayName) {
            //basic info
	        data[i] = {
	            id: user.getId(), 
	            displayName: displayName.replace(/&/g, '&amp;'), 
	            thumbnailUrl: user.getField(opensocial.Person.Field.THUMBNAIL_URL), 
	            profileUrl: user.getField(opensocial.Person.Field.PROFILE_URL),
	            hasApp: user.getField(opensocial.Person.Field.HAS_APP)
	        };
	        //more profile info
		    bloodType = user.getField(mixi.PersonField.BLOOD_TYPE);
		    address = user.getField(opensocial.Person.Field.ADDRESSES);
		    age = user.getField(opensocial.Person.Field.AGE);
		    dateOfBirth = user.getField(opensocial.Person.Field.DATE_OF_BIRTH);
		    gender = user.getField(opensocial.Person.Field.GENDER);
		    
		    if (bloodType) {
		        data[i]['bloodType'] = bloodType;
		    }
		    if (address) {
		        data[i]['address'] = address[0].getField(opensocial.Address.Field.UNSTRUCTURED_ADDRESS);
		    }
		    if (age) {
		        data[i]['age'] = age;
		    }
		    if (dateOfBirth) {
		        data[i]['dateOfBirth'] = mixios.DateFormat(dateOfBirth, 'yyyy-MM-dd');
		    }
		    if (gender) {
		        data[i]['gender'] = gender.getKey();
		    }
		    i++;
	    }
    });
    return data;
}
        
mixios.Canvas.prototype.appendParameterToURL = function(url, name, value) {
    if(url.indexOf('?') == -1) {
        return url + '?' + name + '=' + encodeURIComponent(value);
    } else {
        return url + '&' + name + '=' + encodeURIComponent(value);
    }
}
        
/**
 * Creates the frame element dynamically. 
 * Takes as input the frame id, and size object
 * that contains the height and width data
 */
mixios.Canvas.prototype.createFrameElement = function(frameID, size) {
    var frameElem = document.createElement('iframe');
    frameElem.setAttribute('id', frameID);
    frameElem.setAttribute('name', frameID);
    frameElem.frameBorder = 0;             //frameElem.setAttribute('frameBorder', '0'); doesnt work in IE
    frameElem.setAttribute('scrolling', 'no');
    frameElem.setAttribute('align', 'middle');
    frameElem.style.height = size.height;
    frameElem.style.width = size.width;
    //frameElem.style.overflowY = "hidden";
    
    return frameElem;
}
        
/**
 * returns the opensocial container name
 */
mixios.Canvas.prototype.getContainerType = function() {
    var syndParam = this.getParam("container");   
    if (syndParam.indexOf("mixi") != -1){
        syndParam = "mixi";
    }
    return syndParam;
}
        
/**
 * gets the parameters from the container
 */
mixios.Canvas.prototype.getParam = function(key) {
    return _args()[key];
}

mixios.Canvas.prototype.createContainerContent = function() {
    var frameContainer = document.getElementById(this.containerId_);
    if (!frameContainer) {
        var divContainer = document.createElement('div');
        divContainer.setAttribute('id', this.containerId_);
        var divCenter = document.createElement('div');
        divCenter.setAttribute('align', 'center');
        divCenter.style.marginTop = 200;
        
        var loadingImg = document.createElement('img');
        loadingImg.setAttribute('id', 'loadingImg');
        loadingImg.setAttribute('src', this.loadingImgSrc_);
        
        divCenter.appendChild(loadingImg);
        
        divContainer.appendChild(divCenter);
        document.body.appendChild(divContainer);
    }
}

mixios.Canvas.prototype.onload_ = function() {
    //hidden iframe scroll bar
    if (document.all) { //IE
        document.body.setAttribute('scroll', 'no');
    }
    else { //OTHERS
        document.body.style.overflow = 'hidden';
    }
    this.createContainerContent();
    gadgets.window.adjustHeight(this.getSize().height);
    this.checkStatus();
}

mixios.Canvas.prototype.init_ = function() {
    gadgets.rpc.register("remote_adjustHeight", mixios.hitch(this, this.adjustHeight));
    gadgets.rpc.register("remote_requestExternalNavigateTo", mixios.hitch(this, this.requestExternalNavigateTo));
    gadgets.rpc.register("remote_postActivity", mixios.hitch(this, this.postActivity));
    gadgets.rpc.register("remote_postActivityWithPic", mixios.hitch(this, this.postActivityWithPic));
    gadgets.rpc.register("remote_invite", mixios.hitch(this, this.invite));
}

mixios.Canvas.prototype.adjustHeight = function(height) {
    gadgets.window.adjustHeight(height);
    
    this.size_.height = height;
    var frameElem = document.getElementById(this.childIframeId_);
    if (frameElem) {
        frameElem.style.height = height + 'px';
    }
}

mixios.Canvas.prototype.requestExternalNavigateTo = function(url) {
    mixi.util.requestExternalNavigateTo(url);
}

mixios.Canvas.prototype.postActivity = function(title, reciptents) {
    var params = {};
    params[opensocial.Activity.Field.TITLE] = title;
    if (reciptents) {
        var type = mixios.getType(reciptents);
        if(type == "string" || type == "array") {
            if (type == "string") {
                reciptents = reciptents.split(',');
            }
            params[mixi.ActivityField.RECIPIENTS] = reciptents;
        }
    }
    var activity = opensocial.newActivity(params);
    var self = this;
    opensocial.requestCreateActivity(activity, opensocial.CreateActivityPriority.HIGH, function(response) {
        if (response.hadError()) {
            self.logger.error("postActivity: [" + response.getErrorCode() + '] ' + response.getErrorMessage());
            return;
        }
        
        var recipientIds = reciptents ? reciptents.join(',') : '';
        self.activityCallBack(recipientIds);
    });
}

mixios.Canvas.prototype.postActivityWithPic = function(title, picUrl, mimeType, reciptents) {
    var img_params = {};  
    img_params[opensocial.MediaItem.Field.TYPE] = opensocial.MediaItem.Type.IMAGE;
    mimeType = mimeType || 'image/jpeg';
    var img = opensocial.newMediaItem(mimeType, picUrl, img_params);
    
    var params = {};
    params[opensocial.Activity.Field.TITLE] = title;
    params[opensocial.Activity.Field.MEDIA_ITEMS] = [img];
    if (reciptents) {
       var type = mixios.getType(reciptents);
       if(type == "string" || type == "array") {
           if (type == "string") {
               reciptents = reciptents.split(',');
           }
           params[mixi.ActivityField.RECIPIENTS] = reciptents;
       }
    }
    var activity = opensocial.newActivity(params);
    var self = this;
    opensocial.requestCreateActivity(activity, opensocial.CreateActivityPriority.HIGH, function(response) {
        if (response.hadError()) {
            self.logger.error("postActivity: [" + response.getErrorCode() + '] ' + response.getErrorMessage());
            return;
        }
        
        var recipientIds = reciptents ? reciptents.join(',') : '';
        self.activityCallBack(recipientIds);
    });
}

mixios.Canvas.prototype.activityCallBack = function(recipientIds) {
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.TEXT;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var post_data = {recipientIds: recipientIds};
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    var self = this;
    gadgets.io.makeRequest(this.activityCallBackUrl_, function(response) {
        //do something.
        self.logger.debug('activityCallBack: ' + response.text);
    }, params);
}

mixios.Canvas.prototype.invite = function(uids) {
    var recipients = uids || null;
    var reason = opensocial.newMessage('your friend would like you to intall this application.');
    var self = this;
    opensocial.requestShareApp(recipients, reason, function(response) {
        //callback done
        var code = response.getErrorCode();
        if(response.hadError() && code != 200) {
            self.logger.error("requestShareApp: [" + response.getErrorCode() + '] ' + response.getErrorMessage());
            return;
        }
        
        var data = response.getData();
        if (data) {
           var recipientIds = data.recipientIds;
           if (recipientIds && recipientIds.length > 0) {
               self.inviteCallBack(recipientIds.join(','));
           }
        }
    });
}

mixios.Canvas.prototype.inviteCallBack = function(recipientIds) {
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.TEXT;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var post_data = {recipientIds: recipientIds};
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    var self = this;
    gadgets.io.makeRequest(this.inviteCallBackUrl_, function(response) {
        //do something.
        self.logger.debug('inviteCallBack: ' + response.text);
    }, params);
}

