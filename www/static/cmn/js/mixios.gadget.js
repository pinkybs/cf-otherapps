/*
 * Mixi Opensocial JavaScript Library For Gadget
 * Version: 1.0.0
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

mixios.application.newGadget = function(host, appName) {
    return new mixios.Gadget(host, appName);
}

mixios.Gadget = function(host, appName) {
    this.setHost(host);
    this.appName_ = appName.toLowerCase();
    this.appId_ = 0;
    this.appSignature_ = '';
    this.logger = new mixios.Logger(false);
    this.height_ = 800;
    
    this.ownerId_ = 0;
    this.viewerId_ = 0;
    
    this.isGetOwnerData = false;
    this.isGetViewerData = false;
    this.isGetOwnerFriendsData = false;
    this.isGetViewerFriendsData = false;
    
    this.mixiHost_ = 'http://mixi.jp';
    this.checkUrl_ = this.host_ + '/callback/checkstatus';
    this.activityCallBackUrl_ = this.host_ + '/callback/activity';
    this.staticHost_ = 'http://static.mixi.communityfactory.net';
    
    this.loadingImgSrc_ = this.staticHost_ + '/cmn/img/loading/gadget_loader.gif';
    this.containerId_ = 'gadget_container';
    
    this.onLoadHandlers_ = [];
    this.postLoadDataHandlers_ = [];
        
    this.init_();
}

mixios.Gadget.prototype.getHost = function() {
    return this.host_;
}

mixios.Gadget.prototype.setHost = function(host) {
    if (host.charAt(host.length - 1) == '/') {
        host = host.substr(0, host.length - 1);
    }
    this.host_ = host;
    return this;
}

mixios.Gadget.prototype.getStaticHost = function() {
    return this.staticHost_;
}

mixios.Gadget.prototype.setStaticHost = function(host) {
    this.staticHost_ = host;
    return this;
}

mixios.Gadget.prototype.getHeight = function() {
    return this.height_;
}

mixios.Gadget.prototype.setHeight = function(height) {
    this.height_ = height;
    return this;
}

mixios.Gadget.prototype.getAppId = function() {
    return this.appId_;
}

mixios.Gadget.prototype.setAppSignature = function(signature) {
    this.appSignature_ = signature;
    return this;
}

mixios.Gadget.prototype.enableDebug = function(enabled) {
    this.logger.enabled = enabled;
    return this;
}

mixios.Gadget.prototype.getOwnerId = function() {
    return this.ownerId_;
}

mixios.Gadget.prototype.getViewerId = function() {
    return this.viewerId_;
}

mixios.Gadget.prototype.getMixiHost = function() {
    return this.mixiHost_;
}

mixios.Gadget.prototype.setLoadingImgSrc = function(src) {
    this.loadingImgSrc_ = src;
    return this;
}

mixios.Gadget.prototype.getContainerId = function() {
    return this.containerId_;
}

mixios.Gadget.prototype.setContainerId = function(id) {
    this.containerId_ = id;
    return this;
}

mixios.Gadget.prototype.getTimestamp = function() {
    return (new Date()).getTime();
}

mixios.Gadget.prototype.isGetData = function() {
    return this.isGetOwnerData || this.isGetViewerData || this.isGetOwnerFriendsData || this.isGetViewerFriendsData;
}

mixios.Gadget.prototype.getOwner = function() {
    return this.owner_;
}

mixios.Gadget.prototype.getViewer = function() {
    return this.viewer_;
}

mixios.Gadget.prototype.getOwnerFriends = function() {
    return this.ownerFriends_;
}

mixios.Gadget.prototype.getViewerFriends = function() {
    return this.viewerFriends_;
}

mixios.Gadget.prototype.registerOnLoadHandler = function(callback) {
    this.onLoadHandlers_.push(callback);
    return this;
}

mixios.Gadget.prototype.registerPostLoadDataHandler = function(callback) {
    this.postLoadDataHandlers_.push(callback);
    return this;
}

mixios.Gadget.prototype.runOnLoadHandlers = function() {
	for (var i = 0, j = this.onLoadHandlers_.length; i < j; ++i) {
	   this.onLoadHandlers_[i]();
	}
}

mixios.Gadget.prototype.runPostLoadDataHandlers = function() {
    for (var i = 0, j = this.postLoadDataHandlers_.length; i < j; ++i) {
       this.postLoadDataHandlers_[i]();
    }
}

mixios.Gadget.prototype.start = function() {
    gadgets.util.registerOnLoadHandler(mixios.hitch(this, this.onload_));
}

mixios.Gadget.prototype.checkStatus = function() {
    var params = {};
    params[gadgets.io.RequestParameters.METHOD] = gadgets.io.MethodType.POST;
    params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.JSON;
    params[gadgets.io.RequestParameters.AUTHORIZATION] = gadgets.io.AuthorizationType.SIGNED;
    var urlParams=gadgets.util.getUrlParameters();
    var post_data = {app_name: this.appName_, app_signature: this.appSignature_, view: urlParams.view};
    params[gadgets.io.RequestParameters.POST_DATA] = gadgets.io.encodeValues(post_data);
    var self = this;
    gadgets.io.makeRequest(this.checkUrl_, function(response) {
        var status = response.data;
        if (status) {
          if (status.code == 1) {
              if (status.parameters) {
                  self.appId_ = status.parameters.app_id;
                  self.ownerId_ = status.parameters.owner_id;
                  self.viewerId_ = status.parameters.viewer_id;
              }
              
              if (self.isGetData()) {
                  self.getData();
              }
              self.runOnLoadHandlers();
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
 * Parses the response to the data information request
 * @param {Object} response data information that was requested.
 */
mixios.Gadget.prototype.onLoadData_ = function(response) {
	var item;
	//get data from mixi response
	
	if (this.isGetOwnerData) {
		item = response.get('owner_data');
		if(item.hadError()) {
		    this.logger.error("get owner data error: " + item.getErrorMessage());
		    return;
		}
	    this.owner_ = item.getData();
	}
	
	if (this.isGetViewerData) {
		item = response.get('viewer_data');
		if(item.hadError()) {
		    this.logger.error("get viewer data error: " + item.getErrorMessage());
		    return;
		}
		this.viewer_ = item.getData();
	}
	
    if (this.isGetOwnerFriendsData) {
		item = response.get('owner_friends_data');
		if(item.hadError()) {
		    this.logger.error("get owner friends data error: " + item.getErrorMessage());
		    return;
		}
		this.ownerFriends_ = item.getData();
	}
	
	if (this.isGetViewerFriendsData) {
	    item = response.get('viewer_friends_data');
	    if(item.hadError()) {
	        this.logger.error("get viewer friends data error: " + item.getErrorMessage());
	        return;
	    }
	    this.viewerFriends_ = item.getData();
	}
	
    this.runPostLoadDataHandlers();
}

mixios.Gadget.prototype.getData = function() {
	// for profile other info
	// default only [ID, NAME(nickname), THUMBNAIL_URL]
	var p = {};
	p[opensocial.DataRequest.PeopleRequestFields.PROFILE_DETAILS] = [
	   opensocial.Person.Field.PROFILE_URL,
	   opensocial.Person.Field.HAS_APP
	];
	var req = opensocial.newDataRequest();
	if (this.isGetOwnerData) {
		// request for owner info
		req.add(req.newFetchPersonRequest(opensocial.IdSpec.PersonId.OWNER, p), 'owner_data');
	}
	if (this.isGetViewerData) {
		// request for viewer info
		req.add(req.newFetchPersonRequest(opensocial.IdSpec.PersonId.VIEWER, p), 'viewer_data');
	}
	    
	p[opensocial.DataRequest.PeopleRequestFields.MAX] = 1000;
	
	if (this.isGetOwnerFriendsData) {
		var params1 = {};
		params1[opensocial.IdSpec.Field.USER_ID]  = opensocial.IdSpec.PersonId.OWNER;
		params1[opensocial.IdSpec.Field.GROUP_ID] = "FRIENDS";
		var idspecOwner = opensocial.newIdSpec(params1);
		// request for owner friends info
		req.add(req.newFetchPeopleRequest(idspecOwner, p), 'owner_friends_data');
	}
	
	if (this.isGetViewerFriendsData) {
		var params2 = {};
		params2[opensocial.IdSpec.Field.USER_ID]  = opensocial.IdSpec.PersonId.VIEWER;
		params2[opensocial.IdSpec.Field.GROUP_ID] = "FRIENDS";
		var idspecViewer = opensocial.newIdSpec(params2); 
		// request for viewer friends info
		req.add(req.newFetchPeopleRequest(idspecViewer, p), 'viewer_friends_data');
	}
		
	req.send(mixios.hitch(this, this.onLoadData_));
}

mixios.Gadget.prototype.adjustHeight = function(height) {    
    gadgets.window.adjustHeight(height);
}

mixios.Gadget.prototype.createContainerContent = function() {
    var frameContainer = document.getElementById(this.containerId_);
    if (!frameContainer) {
        var divContainer = document.createElement('div');
        divContainer.setAttribute('id', this.containerId_);
        var divCenter = document.createElement('div');
        divCenter.setAttribute('align', 'center');
        divCenter.style.marginTop = 70;
        
        var loadingImg = document.createElement('img');
        loadingImg.setAttribute('id', 'loadingImg');
        loadingImg.setAttribute('src', this.loadingImgSrc_);
        
        divCenter.appendChild(loadingImg);
        
        divContainer.appendChild(divCenter);
        document.body.appendChild(divContainer);
    }
}

mixios.Gadget.prototype.onload_ = function() {
    this.createContainerContent();
	gadgets.window.adjustHeight(this.getHeight());
	this.checkStatus();
}

mixios.Gadget.prototype.init_ = function() {
    //do something
}

mixios.Gadget.prototype.postActivity = function(title, reciptents) {
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

mixios.Gadget.prototype.postActivityWithPic = function(title, picUrl, mimeType, reciptents) {
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

mixios.Gadget.prototype.activityCallBack = function(recipientIds) {
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

