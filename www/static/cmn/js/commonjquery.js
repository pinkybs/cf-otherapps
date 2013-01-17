/**
 * commonjquery(/commonjquery.js)
 *
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create     2009/07/07    Liz
 */

/**
 * eval json
 *
 * @return string
 */
function evalJSON(strJson)
{
    return eval( "(" + strJson + ")");
}

/**
* @see  将javascript数据类型转换为json字符串
* @param 待转换对象,支持object,array,string,function,number,boolean,regexp
* @return 返回json字符串
*/
function toJSON(object)
{
    var type = typeof object;
    if ('object' == type) {
        if (Array == object.constructor)
            type = 'array';
        else if (RegExp == object.constructor)
            type = 'regexp';
        else
            type = 'object';
    }
    
    switch(type)
    {
         case 'undefined':
         case 'unknown':
            return;
            break;
         case 'function':
         case 'boolean':
         case 'regexp':
            return object.toString();
            break;
         case 'number':
            return isFinite(object) ? object.toString() : 'null';
            break;
         case 'string':
            return '"' + object.replace(/(\\|\")/g,"\\$1").replace(/\n|\r|\t/g,
                function(){  
                     var a = arguments[0];                   
                     return  (a == '\n') ? '\\n':  
                           (a == '\r') ? '\\r':  
                           (a == '\t') ? '\\t': "" 
                 }) + '"';
            break;
         case 'object':
            if (object === null) return 'null';
            var results = [];
            for (var property in object) {
                var value = toJSON(object[property]);
                if (value !== undefined)
                results.push(toJSON(property) + ':' + value);
            }
            return '{' + results.join(',') + '}';
            break;
         case 'array':
            var results = [];
            for(var i = 0; i < object.length; i++)
            {
                var value = toJSON(object[i]);
                if (value !== undefined) results.push(value);
            }
            return '[' + results.join(',') + ']';
            break;
     }
}
  
/**
 * format to amount
 *
 * @return string
 */
function formatToAmount(str){
    str = Number(str);
    
    var tmp= '' + str;
    
    var signa = 0;
    var ll = tmp.length   
    if (ll % 3 == 1) {   
        tmp = "00" + tmp;
        signa = 2;
    }   
    
    if (ll % 3 == 2){   
        tmp = "0" + tmp;
        signa = 1;  
    }   
    
    var tt = tmp.length / 3   
    var mm = new Array();
    for (i = 0; i < tt; i++) {   
        mm[i] = tmp.substring(i * 3, 3 + i * 3);
    }   
    
    var vv = "";
    for (var i=0; i < mm.length; i++) {
        vv += mm[i] + ",";
    }
    
    vv = vv.substring(signa, vv.length -1);
    return vv;
}

/**
 * get cookie
 *
 * @return string
 */
function getCookie(name)
{
    var result = null;
    var myCookie = document.cookie + ";";
    var searchName = name + "=";
    var startOfCookie = myCookie.indexOf(searchName);
    var endOfCookie;
    if (startOfCookie != -1) {
        startOfCookie += searchName.length;
        endOfCookie = myCookie.indexOf(";",startOfCookie);
        result = unescape(myCookie.substring(startOfCookie, endOfCookie));
    }
    return result;
}

/**
 * get string length
 * Half-angle=0.5; Wide-angle=1
 *
 * @return len
 */
String.prototype.getLength = function() {   
    var str = this;
    var len = str.length;
    var reLen = 0;
    for (i = 0; i < len; i++) {
        if (str.charCodeAt(i) < 27 || str.charCodeAt(i) > 126) {
            reLen += 2;
        } 
        else {
            reLen++;
        }
    }
    return Math.ceil(reLen/2);
}