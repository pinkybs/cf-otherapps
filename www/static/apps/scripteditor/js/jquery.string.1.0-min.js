/**
 * jquery.string - Prototype string functions for jQuery
 * (c) 2008 David E. Still (http://stilldesigning.com)
 * Original Prototype extensions (c) 2005-2008 Sam Stephenson (http://prototypejs.org)
 */
jQuery.__stringPrototype={JSONFilter:/^\/\*-secure-([\s\S]*)\*\/\s*$/,ScriptFragment:"<script[^>]*>([\\S\\s]*?)<\/script>",specialChar:{"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r","\\":"\\\\"},blank:function(A){return/^\s*$/.test(this.s(A)||" ")},camelize:function(C){var A=this.s(C).split("-"),B;C=[A[0]];for(B=1;B<A.length;B++){C.push(A[B].charAt(0).toUpperCase()+A[B].substring(1))}C=C.join("");return this.r(arguments,0,C)},capitalize:function(A){A=this.s(A);A=A.charAt(0).toUpperCase()+A.substring(1).toLowerCase();return this.r(arguments,0,A)},dasherize:function(A){A=this.s(A).split("_").join("-");return this.r(arguments,0,A)},empty:function(A){return this.s(A)===""},endsWith:function(B,A){A=this.s(A);var C=A.length-B.length;return C>=0&&A.lastIndexOf(B)===C},escapeHTML:function(A){A=this.s(A).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");return this.r(arguments,0,A)},evalJSON:function(sanitize,s){s=this.s(s);var json=this.unfilterJSON(false,s);try{if(!sanitize||this.isJSON(json)){return eval("("+json+")")}}catch(e){}throw new SyntaxError("Badly formed JSON string: "+s)},evalScripts:function(s){var scriptTags=this.extractScripts(this.s(s)),results=[];if(scriptTags.length>0){for(var i=0;i<scriptTags.length;i++){results.push(eval(scriptTags[i]))}}return results},extractScripts:function(C){var E=new RegExp(this.ScriptFragment,"img"),D=new RegExp(this.ScriptFragment,"im"),A=this.s(C).match(E)||[],F=[];if(A.length>0){for(var B=0;B<A.length;B++){F.push(A[B].match(D)[1]||"")}}return F},gsub:function(C,B,A){A=this.s(A);if(jQuery.isFunction(B)){A=this.sub(C,B,-1,A)}else{A=A.split(C).join(B)}return this.r(arguments,2,A)},include:function(B,A){return this.s(A).indexOf(B)>-1},inspect:function(B,C){C=this.s(C);var A;try{A=this.sub(/[\x00-\x1f\\]/,function(E){var F=jQuery.__stringPrototype.specialChar[E[0]];return F?F:"\\u00"+E[0].charCodeAt().toPaddedString(2,16)},-1,C)}catch(D){A=C}C=(B)?'"'+A.replace(/"/g,'\\"')+'"':"'"+A.replace(/'/g,"\\'")+"'";return this.r(arguments,1,C)},interpolate:function(F,E,C){C=this.s(C);if(!E){E=/(\#\{\s*(\w+)\s*\})/}var A=new RegExp(E.source,"g");var D=C.match(A),B;for(B=0;B<D.length;B++){C=C.replace(D[B],F[D[B].match(E)[2]])}return this.r(arguments,2,C)},isJSON:function(A){A=this.s(A);if(this.blank(A)){return false}A=A.replace(/\\./g,"@").replace(/"[^"\\\n\r]*"/g,"");return(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(A)},scan:function(C,B,A){A=this.s(A);this.sub(C,B,-1,A);return this.r(arguments,2,A)},startsWith:function(B,A){return this.s(A).indexOf(B)===0},strip:function(A){A=jQuery.trim(this.s(A));return this.r(arguments,0,A)},stripScripts:function(A){A=this.s(A).replace(new RegExp(this.ScriptFragment,"img"),"");return this.r(arguments,0,A)},stripTags:function(A){A=this.s(A).replace(/<\/?[^>]+>/gi,"");return this.r(arguments,0,A)},sub:function(H,D,E,C){C=this.s(C);if(H.source&&!H.global){var A=(H.ignoreCase)?"ig":"g";A+=(H.multiline)?"m":"";H=new RegExp(H.source,A)}var G=C.split(H),F=C.match(H);if(jQuery.browser.msie){if(C.indexOf(F[0])==0){G.unshift("")}if(C.lastIndexOf(F[F.length-1])==C.length-F[F.length-1].length){G.push("")}}E=(E<0)?(G.length-1):E||1;C=G[0];for(var B=1;B<G.length;B++){if(B<=E){if(jQuery.isFunction(D)){C+=D(F[B-1]||F)+G[B]}else{C+=D+G[B]}}else{C+=(F[B-1]||F)+G[B]}}return this.r(arguments,3,C)},succ:function(A){A=this.s(A);A=A.slice(0,A.length-1)+String.fromCharCode(A.charCodeAt(A.length-1)+1);return this.r(arguments,0,A)},times:function(D,C){C=this.s(C);var A="";for(var B=0;B<D;B++){A+=C}return this.r(arguments,1,A)},toJSON:function(A){return this.r(arguments,0,this.inspect(true,this.s(A)))},toQueryParams:function(F,C){C=this.s(C);var E=C.substring(C.indexOf("?")+1).split("#")[0].split(F||"&"),H={},B,A,D,G;for(B=0;B<E.length;B++){G=E[B].split("=");A=decodeURIComponent(G[0]);D=(G[1])?decodeURIComponent(G[1]):undefined;if(H[A]){if(typeof H[A]=="string"){H[A]=[H[A]]}H[A].push(D)}else{H[A]=D}}return H},truncate:function(C,A,B){B=this.s(B);C=C||30;A=(!A)?"...":A;B=(B.length>C)?B.slice(0,C-A.length)+A:String(B);return this.r(arguments,2,B)},underscore:function(A){A=this.sub(/[A-Z]/,function(B){return"_"+B.toLowerCase()},-1,this.s(A));if(A.charAt(0)=="_"){A=A.substring(1)}return this.r(arguments,0,A)},unescapeHTML:function(A){A=this.stripTags(this.s(A)).replace(/&amp;/g,"&").replace(/&lt;/g,"<").replace(/&gt;/g,">");return this.r(arguments,0,A)},unfilterJSON:function(C,B){B=this.s(B);C=C||this.JSONFilter;var A=B.match(C);B=(A!==null)?A[1]:B;return this.r(arguments,1,jQuery.trim(B))},r:function(A,B,C){if(A.length>B||this.str===undefined){return C}else{this.str=""+C;return this}},s:function(A){if(A===""||A){return A}if(this.str===""||this.str){return this.str}return this}};jQuery.__stringPrototype.parseQuery=jQuery.__stringPrototype.toQueryParams;jQuery.string=function(A){if(A===String.prototype){jQuery.extend(String.prototype,jQuery.__stringPrototype)}else{return jQuery.extend({str:A},jQuery.__stringPrototype)}}