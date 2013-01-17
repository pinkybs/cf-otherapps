
(function($) {

$().ready(function() {
    $.codepress.set_tab_indent_for_textareas();
});


$.codepress = {
    insertAtCursor: function(obj, txt) {
        obj.focus();
	    //IE support
	    if (document.selection) {
	       var sel = document.selection.createRange();
	       var r = obj.createTextRange()
		   if (sel.getClientRects().length > 1) {  
			   var code = sel.text;
			   var tmp = sel.duplicate();
			   tmp.moveToPoint(r.getBoundingClientRect().left, sel.getClientRects()[0].top);
			   sel.setEndPoint("startToStart", tmp);
			   sel.text = txt + sel.text.replace(/\r\n/g, "\r" + txt);
			   code = code.replace(/\r\n/g, "\r" + txt);
			   r.findText(code);
			   r.select(); 
			}  
			else {  
			   sel.text = txt;  
			   sel.select();
			}
	    }
	    //MOZILLA/NETSCAPE support
	    else {
	       var startPos = obj.selectionStart;
	       var scrollTop = obj.scrollTop;
	       var endPos = obj.selectionEnd;
	       var selectText = obj.value.substring(startPos, endPos);
	       var beforeText = obj.value.substring(0, startPos);
	       var afterText = obj.value.substring(endPos, obj.value.length);
	       var t = txt;
	       /*
           var s = selectText.match(/\n/g);
           var len = s ? (s.length + 1) : 0;
           if (len > 1) {
               t += selectText.replace(/\r\n/g, "\r" + txt);
               endPos += (len - 1) * (txt.length - 1);
           }
           */
           obj.value = beforeText + t + afterText;
           startPos += txt.length;
           obj.setSelectionRange(startPos, startPos);
           obj.scrollTop = scrollTop;
	    }
	    obj.focus();
    },
    
    getCaretPos: function(ctrl) {
        var caretPos = 0;
        if (document.selection) {
        // IE Support
        var range = document.selection.createRange();
        // We'll use this as a 'dummy'
        var stored_range = range.duplicate();
        // Select all text
        stored_range.moveToElementText( ctrl );
        // Now move 'dummy' end point to end point of original range
        stored_range.setEndPoint( 'EndToEnd', range );
        // Now we can calculate start and end points
        ctrl.selectionStart = stored_range.text.length - range.text.length;
        ctrl.selectionEnd = ctrl.selectionStart + range.text.length;
        caretPos = ctrl.selectionStart;
        } else if (ctrl.selectionStart || ctrl.selectionStart == '0')
            // Firefox support
            caretPos = ctrl.selectionStart;
        
        return (caretPos);
    },
    
    getCurrentLineBlanks: function(obj) {
        var pos = $.codepress.getCaretPos(obj);
        var str = obj.value;
        var i = pos-1;
        while (i>=0) {
            if (str.charAt(i) == '\n')
                break;
            i--;
        }
        i++;
        var blanks = "";
        while (i < str.length) {
            var c = str.charAt(i);
            if (c == ' ' || c == '\t')
                blanks += c;
            else
                break;
            i++;
        }
        
        return blanks;
    },
    
    set_tab_indent_for_textareas: function() {
	    /* set all the tab indent for all the text areas */
	    $("textarea").each(function() {
	       $(this).keydown(function(eve) {
	           if (eve.target != this) return;
	           if (eve.keyCode == 13) {
	               last_blanks = $.codepress.getCurrentLineBlanks(this);
	           }
	           else if (eve.keyCode == 9) {
	               eve.preventDefault();
	               $.codepress.insertAtCursor(this, "    ");
	               this.returnValue = false;
	           }
	       }).keyup(function(eve) {
	           /*
	           if (eve.target == this && eve.keyCode == 13) {
	               $.codepress.insertAtCursor(this, last_blanks);
	           }
	           */
	       });
	    });   
    
    }

};

$.coderun = {
    showDialog: function(keys, values) {
        var url = UrlConfig.BaseUrl + '/scripteditor/run';
        openWindowWithPost(url, 'runcode_popup_window',  684, 556, keys, values);
    }
};


$.util = {};

$.util.string = {
    parseBR: function(str) {
        return str.replace(/  /g, ' &nbsp;&nbsp;').replace(/\r\n|\r|\n/g,'<br/>');
    },
    escape: function(str){
        return str.replace(/\&/g, '&amp;').replace(/\"/g,'&quot;').replace(/\'/g,'&#039;');
    }
};

})(jQuery);

function extractIFrameBody(iFrameEl) {
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
    
    return doc;
}

function openWindowWithPost(url, name, width, height, keys, values) {
    if (codeRunWindow) {
        codeRunWindow.setActive();
        return;
    }
    var top = (window.screen.availHeight - 30- height)/2;
    var left = (window.screen.availWidth - 10 - width)/2;
    var options = 'width=' + width + ',innerWidth=' + width + ',height=' + height + ',innerHeight=' + height + ',top=' + top + ',left=' + left + ',toolbar=no,menubar=no,scrollbars=no,resizable=yes,location=no,status=no,directories=no';
    var codeRunWindow = window.open('', name, options);
    if (!codeRunWindow) return false;   
    codeRunWindow.focus();   

    var html = "<html><head></head><body><form id='formid' method='post' accept-charset='utf-8' action='" + url + "'>";
    if (keys && values && (keys.length == values.length))
    for (var i=0; i < keys.length; i++)
    html += "<input type='hidden' name='" + keys[i] + "' value='" + jQuery.util.string.escape(values[i]) + "'/>";
    html += "</form><script type='text/javascript'>document.getElementById(\"formid\").submit()</script></body></html>";
    //var doc = extractIFrameBody(codeRunWindow);
    var doc = codeRunWindow.document;
    if (doc) {
	    doc.open();
	    doc.write(html);
	    doc.close();
    }
    
    return codeRunWindow;
}


    