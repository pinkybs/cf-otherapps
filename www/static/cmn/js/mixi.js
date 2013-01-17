/*
try {
    document.domain = LINNO_DOMAIN ? LINNO_DOMAIN : 'linno.jp';
}
catch(e) {
}

try {
    top.location.href.indexOf(LINNO_DOMAIN ? LINNO_DOMAIN : 'linno.jp');
}
catch(e) {
	try {
	   top.location = self.location;
	}
	catch(e) {
	}
}
*/

/*---------------------------------------------------------------------------------------------------
    validation_jp.js   

/**
 * modified by badqiu (badqiu@gmail.com)
 */

/*
 * Really easy field validation with Prototype
 * http://tetlaw.id.au/view/blog/really-easy-field-validation-with-prototype
 * Andrew Tetlaw
 * Version 1.5.3 (2006-07-15)
 * 
 * Copyright (c) 2006 Andrew Tetlaw
 * http://www.opensource.org/licenses/mit-license.php
 */
Validator = Class.create();

Validator.messagesSourceEn = [
	['validation-failed' , 'Validation failed.'],
	['required' , 'This is a required field.'],
	['validate-number' , 'Please enter a valid number in this field.'],
	['validate-digits' , 'Please use numbers only in this field. please avoid spaces or other characters such as dots or commas.'],
	['validate-alpha' , 'Please use letters only (a-z) in this field.'],
	['validate-alphanum' , 'Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'],
	['validate-alphanum-hyphen' , 'Please use only letters (a-z) or numbers (0-9) or hyphen(-) only in this field. No spaces or other characters are allowed.'],
	['validate-date' , 'Please enter a valid date.'],
	['validate-email' , 'Please enter a valid email address.<br/>For example somename@domain.com'],
	['validate-multi-email' , 'Please enter a valid email address.<br/>For example somename@domain.com'],
	['validate-pcemail' , 'Please enter a valid pc email address<br/>For example somename@domain.com<br/>Mobile email address not supported.'],
	['validate-ktaiemail' , 'Please enter a valid mobile email address<br/>For example somename@docomo.ne.jp<br/>PC email address not supported.'],
	['validate-url' , 'Please enter a valid URL.'],
	['validate-date-au' , 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'],
	['validate-currency-dollar' , 'Please enter a valid $ amount. For example $100.00 .'],
	['validate-one-required' , 'Please select one of the above options.'],
	['validate-jpdate' , 'Please use this date format: yyyy-mm-dd. For example 2006-03-16.'],
	['validate-yyyymmdd-date' , 'Please use this date format: yyyymmdd. For example 20060316.'],
	['validate-integer' , 'Please enter a valid integer in this field'],
	['min-value' , 'min value is %s.'],
	['max-value' , 'max value is %s.'],
	['min-length' , 'min length is %s,current length is %s.'],
	['max-length' , 'max length is %s,current length is %s.'],
	['validate-int-range' , 'Please enter integer value between %s and %s'],
	['validate-float-range' , 'Please enter number between %s and %s'],
	['validate-length-range' , 'Please enter value length between %s and %s,current length is %s'],
	['validate-length-equals' , 'Please enter value length with %s,current length is %s'],
	['validate-file' , 'Please enter file type in [%s]'],
	['validate-image-size' , 'Please select a image file that size less than [%s]'],
	['validate-pattern' , 'Validation failed.'],
	['validate-chinese','Please enter chinese'],
	['validate-ip','Please enter a valid IP address'],
	['validate-phone','Please enter a valid phone number,current length is %s.'],
	['validate-mobile-phone','Please enter a valid mobile phone,For example 13910001000.current length is %s.'],
	['validate-equals','Conflicting with above value.']
	['less-than','Input value must be less than above value.'],
	['great-than','Input value must be great than above value.'],
	['jpdate-not-less-than','Input value must be less than above value.'],
	['validate-selection','Please make a selection'],
	//学号检查
	['validate-keio-regnumber','Please enter a valid register school number'],
	['validate-waseda-regnumber','Please enter a valid register school number'],
	['validate-utokyo-regnumber','Please enter a valid register school number'],
	['validate-chuou-regnumber','Please enter a valid register school number。'],
	['validate-hosei-regnumber','Please enter a valid register school number。'],
	['validate-meiji-regnumber','Please enter a valid register school number。'],
	['validate-rikkyo-regnumber','Please enter a valid register school number。'],
	['validate-aoyama-regnumber','Please enter a valid register school number。'],
	['validate-sophia-regnumber','Please enter a valid register school number。'],
	['validate-seijo-regnumber','Please enter a valid register school number。']
];

Validator.messagesSourceJp = [
	['validation-failed' , '検証が失敗しました.'],
	['required' , '値を入力してください.'],
	['required-email' , 'メールアドレスを入力してください。'],
	['required-password' , 'パスワードを入力して下さい.'],
	['validate-number' , '有効な数字を入力してください.'],
	['validate-digits' , '数字を入力してください（空欄、読点、セミコロンなどを入れないでください）. '],
	['validate-alpha' , 'アルファベッドを入力してください.'],
	['validate-alphanum-hyphen' , 'アルファベッドと数字とハイフンのみ入力してください。(先頭に使用できるのはアルファベッドと数字だけである)'],
	['validate-alphanum-hyphen' , 'アルファベッドと数字とハイフンのみ入力してください。'],
	['validate-date' , '有効な日付を入力してください.'],
	['validate-email' , '有効なメールアドレスを入力してください。<br/>例えば username@example.com'],
	['validate-multi-email' , '有効なメールアドレスを入力してください。<br/>例えば username@example.com'],
	['validate-pcemail' , '有効なPCメールアドレスを入力してください。<br/>例えば username@example.com<br/>※携帯メールアドレスは使用できません。'],
	['validate-ktaiemail' , '有効な携帯メールアドレスを入力してください。<br/>例えば username@docomo.ne.jp<br/>※PCメールアドレスは使用できません。'],
	['validate-url' , '有効なURLアドレスを入力してください.'],
	['validate-date-au' , 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'],
	['validate-currency-dollar' , '有効な金額を入力してください. 例えば $100.00 .'],
	['validate-one-required' , '上記のオプションで一つ以上を選択してください.'],
	['validate-jpdate' , 'このような日付フォーマットを使うようにしてください: yyyy-mm-dd. 例えば2006年3月17日なら 2006-03-17.'],
	['validate-yyyymmdd-date' , 'このような日付フォーマットを使うようにしてください: yyyymmdd. 例えば2006年3月17日なら 20060317.'],
	['validate-integer' , '正しい整数を入力してください'],
	['min-value' , '最小の値は%s'],
	['max-value' , '最大の値は%s'],
	['min-length' , '最小の長さは%s,いまの長さは%s.'],
	['max-length', '最大の長さは%s,いまの長さは%s.'],
	['validate-int-range' , '入力値は%s～%sの整数であるべきです'],
	['validate-float-range' , '入力値は%s～%sの数字であるべきです'],
	['validate-length-range' , '%s字以上%s字以下で入力してください。'],
	['validate-length-equals' , 'Please enter value length with %s,current length is %s'],
	['validate-file' , 'アップ出来るファイル形式は[%s]です'],
	['validate-image-size' , 'Please select a image file that size less than [%s]'],
	['validate-pattern' , '入力値が合いません'],
	['validate-chinese','请输入中文'],
	['validate-ip','正しいIPアドレスを入力してください'],
	['validate-phone','正しい電話番号を入力してください,例えば 0920-29392929,いまの長さは%s.'],
	['validate-mobile-phone','正しい携帯電話の番号を入力してください,いまの長さは%s.'],
	['validate-equals','上記と異なります。再び入力して下さい。'],
	['less-than','前の値より小さいべきです'],
	['great-than','前の値より大きいべきです'],
	['jpdate-not-less-than','Input value must be not less than above value.'],
	['validate-selection','サービスを選択して下さい。'],
	//学号检查
	['validate-keio-regnumber','学籍番号の形式が正しくありません。'],
	['validate-waseda-regnumber','学籍番号の形式が正しくありません。'],
	['validate-utokyo-regnumber','学籍番号の形式が正しくありません。'],
	['validate-chuou-regnumber','学籍番号の形式が正しくありません。'],
	['validate-hosei-regnumber','学籍番号の形式が正しくありません。'],
	['validate-meiji-regnumber','学籍番号の形式が正しくありません。'],
	['validate-rikkyo-regnumber','学籍番号の形式が正しくありません。'],
	['validate-aoyama-regnumber','学籍番号の形式が正しくありません。'],
	['validate-sophia-regnumber','学籍番号の形式が正しくありません。'],
	['validate-seijo-regnumber','学籍番号の形式が正しくありません。']
];

Validator.validTlds = [
    'ac', 'ad', 'ae', 'aero', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao',
    'aq', 'ar', 'arpa', 'as', 'asia', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb',
    'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'biz', 'bj', 'bm', 'bn', 'bo',
    'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cat', 'cc', 'cd',
    'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'com', 'coop',
    'cr', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do',
    'dz', 'ec', 'edu', 'ee', 'eg', 'er', 'es', 'et', 'eu', 'fi', 'fj',
    'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh',
    'gi', 'gl', 'gm', 'gn', 'gov', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu',
    'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il',
    'im', 'in', 'info', 'int', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm',
    'jo', 'jobs', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw',
    'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu',
    'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mil', 'mk', 'ml', 'mm',
    'mn', 'mo', 'mobi', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'museum', 'mv',
    'mw', 'mx', 'my', 'mz', 'na', 'name', 'nc', 'ne', 'net', 'nf', 'ng',
    'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'org', 'pa', 'pe',
    'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'pro', 'ps', 'pt',
    'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd',
    'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr',
    'st', 'su', 'sv', 'sy', 'sz', 'tc', 'td', 'tel', 'tf', 'tg', 'th', 'tj',
    'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'travel', 'tt', 'tv', 'tw',
    'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've',
    'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'yu', 'za', 'zm',
    'zw'
];

Validator.messagesSource = Validator.messagesSourceJp;
Validator.messages = {};
//init Validator.messages
Validator.messagesSource.each(function(ms){
	Validator.messages[ms[0]] = ms[1];
});

Validator.format = function(str,args) {
	args = args || [];
	Validation.assert(args.constructor == Array,"Validator.format() arguement 'args' must is Array");
	var result = str
	for (var i = 0; i < args.length; i++){
		result = result.replace(/%s/, args[i]);	
	}
	return result;
}

Validator.prototype = {
	initialize : function(className, error, test, options) {
		this.options = Object.extend({}, options || {});
		this._test = test ? test : function(v,elm){ return true };
		this._error = error ? error : Validator.messages['validation-failed'];
		this.className = className;
	},
	test : function(v, elm) {
		if(this.options.depends && this.options.depends.length > 0) {
			var dependsResult = $A(this.options.depends).all(function(depend){
				return Validation.get(depend).test(v,elm);
			});
			if(!dependsResult) return dependsResult;
		}
		if(!elm) elm = {}
		return this._test(v,elm,Validation.getArgumentsByClassName(this.className,elm.className),this);
	},
	error : function(v,elm,useTitle) {
		var dependError = null;
		$A(this.options.depends).any(function(depend){
			var validation = Validation.get(depend);
			if(!validation.test(v,elm))  {
				dependError = validation.error(v,elm,useTitle)
				return true;
			}
			return false;
		});
		if(dependError != null) return dependError;

		var args  = Validation.getArgumentsByClassName(this.className,elm.className);
		var error = this._error;
		if(typeof error == 'string') {
			if(v) args.push(v.length);
			error = Validator.format(this._error,args);
		}else if(typeof error == 'function') {
			error = error(v,elm,args,this);
		}else {
			alert('error must type of string or function');
		}
		if(!useTitle) useTitle = elm.className.indexOf('useTitle') >= 0;
		return useTitle ? ((elm && elm.title) ? elm.title : error) : error;
	}
}

var Validation = Class.create();

Validation.prototype = {
	initialize : function(form, options){
		this.options = Object.extend({
			onSubmit : true,
			stopOnFirst : false,
			immediate : false,
			focusOnError : true,
			useTitles : false,
			onFormValidate : function(result, form) {},
			onElementValidate : function(result, elm) {}
		}, options || {});
		this.form = $(form);
		var id =  Validation.getElmID(this.form);
		Validation.validations[id] = this;
		if(this.options.onSubmit) Event.observe(this.form,'submit',this.onSubmit.bind(this),false);
		if(this.options.immediate) {
			var useTitles = this.options.useTitles;
			var callback = this.options.onElementValidate;
			Form.getElements(this.form).each(function(input) { // Thanks Mike!
				Event.observe(input, 'blur', function(ev) { Validation.validate(Event.element(ev),{useTitle : useTitles, onElementValidate : callback}); });
			});
		}
	},
	onSubmit :  function(ev){
		if(!this.validate()) Event.stop(ev);
	},
	validate : function() {
		var result = false;
		var useTitles = this.options.useTitles;
		var callback = this.options.onElementValidate;
		if(this.options.stopOnFirst) {
			result = Form.getElements(this.form).all(function(elm) { return Validation.validate(elm,{useTitle : useTitles, onElementValidate : callback}); });
		} else {
			result = Form.getElements(this.form).collect(function(elm) { return Validation.validate(elm,{useTitle : useTitles, onElementValidate : callback}); }).all();
		}
		if(!result && this.options.focusOnError) {
			var first = Form.getElements(this.form).findAll(function(elm){return $(elm).hasClassName('validation-failed')}).first();
			if(first.select) first.select();
			first.focus();
		}
		this.options.onFormValidate(result, this.form);
		return result;
	},
	reset : function() {
		Form.getElements(this.form).each(Validation.reset);
	}
}

Object.extend(Validation, {
	validate : function(elm, options){
		options = Object.extend({
			useTitle : false,
			onElementValidate : function(result, elm) {}
		}, options || {});
		elm = $(elm);
		var cn = elm.classNames();
		return result = cn.all(function(value) {
			var test = Validation.test(value,elm,options.useTitle);
			options.onElementValidate(test, elm);
			return test;
		});
	},
	_getInputValue : function(elm) {
		var elm = $(elm);
		if(elm.type.toLowerCase() == 'file') {
			return elm.value;
		}else {
			return $F(elm);
		}
	},
	_getErrorMsg : function(useTitle,elm,validation) {
		return validation.error(Validation._getInputValue(elm),elm,useTitle);
	},
	test : function(name, elm, useTitle) {
		var v = Validation.get(name);
		var prop = '__advice' + name.camelize();
		if(Validation.isVisible(elm) && !v.test(Validation._getInputValue(elm),elm)) {
			if(!elm[prop]) {
				var advice = Validation.getAdvice(name, elm);
				if(advice == null){
					var errorMsg = Validation._getErrorMsg(useTitle,elm,v);
					advice = '<div class="validation-advice" id="advice-' + name + '-' + Validation.getElmID(elm) +'" style="display:none">' + errorMsg + '</div>'
					switch (elm.type.toLowerCase()) {
						case 'checkbox':
						case 'radio':
							var p = elm.parentNode;
							if(p) {
								new Insertion.Bottom(p, advice);
							} else {
								new Insertion.After(elm, advice);
							}
							break;
						default:
							new Insertion.After(elm, advice);
				    }
					advice = $('advice-' + name + '-' + Validation.getElmID(elm));
				}
				if(typeof Effect == 'undefined') {
					advice.style.display = 'block';
				} else {
					new Effect.Appear(advice, {duration : 1 });
				}
			}
			var advice = Validation.getAdvice(name, elm);

			//if advice has title(error message), just show it
			//edit at 2007-8-21
			advice.innerHTML = advice.title?advice.title:Validation._getErrorMsg(useTitle,elm,v);
			
			elm[prop] = true;
			elm.removeClassName('validation-passed');
			elm.addClassName('validation-failed');
			return false;
		} else {
			var advice = Validation.getAdvice(name, elm);
			if(advice != null) {
				if(typeof Effect == 'undefined') {
					advice.hide();
				}
				else {
					new Effect.Fade(advice, {duration : 1 });
				}
			}
			
			elm[prop] = '';
			elm.removeClassName('validation-failed');
			elm.addClassName('validation-passed');
			return true;
		}
	},
	isVisible : function(elm) {
		while(elm && elm.tagName != 'BODY') {
			if(!$(elm).visible()) return false;
			elm = elm.parentNode;
		}
		return true;
	},
	getAdvice : function(name, elm) {
		/*
		return Try.these(
			function(){ return $('advice-' + name + '-' + Validation.getElmID(elm)) },
			function(){ return $('advice-' + Validation.getElmID(elm)) }
		);
		*/
		$adviceElm = $('advice-' + name + '-' + Validation.getElmID(elm));
		if (!$adviceElm) {
			$adviceElm = $('advice-' + Validation.getElmID(elm));
		}
		
		return $adviceElm;
	},
	getElmID : function(elm) {
		return elm.id ? elm.id : elm.name;
	},
	reset : function(elm) {
		elm = $(elm);
		var cn = elm.classNames();
		cn.each(function(value) {
			var prop = '__advice'+value.camelize();
			if(elm[prop]) {
				var advice = Validation.getAdvice(value, elm);
				advice.hide();
				elm[prop] = '';
			}
			elm.removeClassName('validation-failed');
			elm.removeClassName('validation-passed');
		});
	},
	add : function(className, error, test, options) {
		var nv = {};
		nv[className] = new Validator(className, error, test, options);
		Object.extend(Validation.methods, nv);
	},
	addAllThese : function(validators) {
		var nv = {};
		$A(validators).each(function(value) {
				nv[value[0]] = new Validator(value[0], value[1], value[2], (value.length > 3 ? value[3] : {}));
			});
		Object.extend(Validation.methods, nv);
	},
	get : function(name) {
		var resultMethodName;
		for(var methodName in Validation.methods) {
			if(name == methodName) {
				resultMethodName = methodName;
				break;
			}
			if(name.indexOf(methodName) >= 0) {
				resultMethodName = methodName;
			}
		}
		return Validation.methods[resultMethodName] ? Validation.methods[resultMethodName] : new Validator();
		//return  Validation.methods[name] ? Validation.methods[name] : new Validator();
	},
	// 通过classname传递的参数必须通过'-'分隔各个参数
	getArgumentsByClassName : function(prefix,className) {
		if(!className || !prefix)
			return [];
		var pattern = new RegExp(prefix+'-(\\S+)');
		var matchs = className.match(pattern);
		if(!matchs)
			return [];
		var results = [];
		var args =  matchs[1].split('-');
		for(var i = 0; i < args.length; i++) {
			if(args[i] == '') {
				if(i+1 < args.length) args[i+1] = '-'+args[i+1];
			}else{
				results.push(args[i]);
			}
		}
		return results;
	},
	assert : function(condition,message) {
		var errorMessage = message || ("assert failed error,condition="+condition);
		if (!condition) {
			alert(errorMessage);
			throw new Error(errorMessage);
		}else {
			return condition;
		}
	},
	methods : {}
});

Validation.add('IsEmpty', '', function(v) {
				return  ((v == null) || (v.length == 0)); // || /^\s+$/.test(v));
			});

Validation.addAllThese([
	['required', Validator.messages['required'], function(v) {
				return !(Validation.get('IsEmpty').test(v) || /^\s+$/.test(v));
			}],
	['required-email', Validator.messages['required-email'], function(v) {
				return !Validation.get('IsEmpty').test(v);
			}],
	['required-password', Validator.messages['required-password'], function(v) {
				return !(Validation.get('IsEmpty').test(v) || /^\s+$/.test(v));
			}],
	['validate-number', Validator.messages['validate-number'], function(v) {
				return Validation.get('IsEmpty').test(v) || (!isNaN(v) && !/^\s+$/.test(v));
			}],
	['validate-digits', Validator.messages['validate-digits'], function(v) {
				return Validation.get('IsEmpty').test(v) ||  !/[^\d]/.test(v);
			}],
	['validate-alpha', Validator.messages['validate-alpha'], function (v) {
				return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z]+$/.test(v)
			}],
	['validate-alphanum', Validator.messages['validate-alphanum'], function(v) {
				return Validation.get('IsEmpty').test(v) ||  !/\W/.test(v)
			}],
	['validate-alphanum-hyphen', Validator.messages['validate-alphanum-hyphen'], function(v) {
				return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z0-9]+[-\w]*$/.test(v)
			}],
	['validate-date', Validator.messages['validate-date'], function(v) {
				var test = new Date(v);
				return Validation.get('IsEmpty').test(v) || !isNaN(test);
			}],

	/**
	 * Usage : validate-yyyymmdd-date
	 * Example : 
	 */
	['validate-yyyymmdd-date', Validator.messages['validate-yyyymmdd-date'], function(v) {
				if(Validation.get('IsEmpty').test(v)) return true;
				var regex = /^(\d{4})(\d{2})(\d{2})$/;
				if(!regex.test(v)) return false;
				var d = new Date(v.replace(regex, '$1/$2/$3'));
				return ( parseInt(RegExp.$2, 10) == (1+d.getMonth()) ) && 
							(parseInt(RegExp.$3, 10) == d.getDate()) && 
							(parseInt(RegExp.$1, 10) == d.getFullYear() );
			}],
	['validate-email', Validator.messages['validate-email'], function (v) {
				// return Validation.get('IsEmpty').test(v) || /^\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(v)
				//var rst = Validation.get('IsEmpty').test(v) || /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(v);
				var rst = Validation.get('IsEmpty').test(v) || /^\w+[-.\w]*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(v);
				
				if(!rst){
					return false;
				}
        
			    var v_tld = v.substr(v.lastIndexOf('.') + 1);
			    
			    return Validator.validTlds.indexOf(v_tld) != -1;
        	
			}],
	['validate-multi-email', Validator.messages['validate-multi-email'], function (v) {
				var aryEmail = v.split(",");
				var blnFlag = true;
				
				// check each email address
				for(var i = 0; i < aryEmail.length ; i++){
				
					var rst = Validation.get('IsEmpty').test(aryEmail[i]) || /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(aryEmail[i]);
					
					if(!rst){
						return false;
					}
        
				    var v_tld = aryEmail[i].substr(aryEmail[i].lastIndexOf('.') + 1);
				    
				    blnFlag = Validator.validTlds.indexOf(v_tld) != -1;
				    
				    if (!blnFlag) {
				    	return blnFlag;
				    }
				}
				
				return blnFlag;
        	
			}],
	['validate-college-email', Validator.messages['validate-college-email'], function (v) {
				// return Validation.get('IsEmpty').test(v) || /^\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(v)
				//var rst = Validation.get('IsEmpty').test(v) || /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(v);
				var rst = Validation.get('IsEmpty').test(v) || /^\w+[-.\w]*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(v);
				
				if(!rst){
					return false;
				}
        
			    var v_tld = v.substr(v.lastIndexOf('.') + 1);
			    
			    return Validator.validTlds.indexOf(v_tld) != -1;
        	
			}],	
	['validate-pcemail', Validator.messages['validate-pcemail'], function (v) {
				if(Validation.get('IsEmpty').test(v))
					return true;

				if(!Validation.get('validate-email').test(v))
					return false;
				
				var m_emails_domain = ['docomo.ne.jp','ezweb.ne.jp','yy.ezweb.ne.jp','disney.ne.jp',
'softbank.ne.jp','d.vodafone.ne.jp','h.vodafone.ne.jp','t.vodafone.ne.jp','c.vodafone.ne.jp',
'r.vodafone.ne.jp','k.vodafone.ne.jp','n.vodafone.ne.jp','s.vodafone.ne.jp','q.vodafone.ne.jp',
'pdx.ne.jp','yy.pdx.ne.jp'];
					
				var v_domain = (v.split('@'))[1];
				
				for(var i=0,len=m_emails_domain.length;i<len;i++){
					if(m_emails_domain[i] == v_domain)
						return false;
				}
				
				return true;
			}],
	['validate-college-email', Validator.messages['validate-college-email'], function (v,elm,args,metadata) {
				if(Validation.get('IsEmpty').test(v))
					return true;
					
				v = v.toLowerCase();
				var domain = $F(args[0]);				
				var temp = domain.split(';');
				
				for (p=0; p < temp.length; p++){
					var i = v.lastIndexOf(temp[p].toLowerCase());
					if(i>0 && temp[p].toLowerCase() == v.substr(i))
						return true;
				}
				
				return false;
			}],
	['validate-ktaiemail', Validator.messages['validate-ktaiemail'], function (v) {
				if(Validation.get('IsEmpty').test(v))
					return true;
		
				if(!Validation.get('validate-email').test(v))
					return false;
				
				var m_emails_domain = ['docomo.ne.jp','ezweb.ne.jp','yy.ezweb.ne.jp','disney.ne.jp',
'softbank.ne.jp','d.vodafone.ne.jp','h.vodafone.ne.jp','t.vodafone.ne.jp','c.vodafone.ne.jp',
'r.vodafone.ne.jp','k.vodafone.ne.jp','n.vodafone.ne.jp','s.vodafone.ne.jp','q.vodafone.ne.jp',
'pdx.ne.jp','yy.pdx.ne.jp'];
					
				var v_domain = (v.split('@'))[1];
				
				var isKtai = false;
				for(var i=0,len=m_emails_domain.length;i<len;i++){
					if(m_emails_domain[i] == v_domain) {
						isKtai = true;
						break;
					}
				}
				
				return isKtai;
			}],						
	['validate-url', Validator.messages['validate-url'], function (v) {
				if(Validation.get('IsEmpty').test(v)) return true;
				
				//if(v.match(/['"<>']/g) != null) return false;
				if(v.match(/['"<>']/) != null) return false;
				
				return /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i.test(v);
	
			}],
	['validate-date-au', Validator.messages['validate-date-au'], function(v) {
				if(Validation.get('IsEmpty').test(v)) return true;
				var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
				if(!regex.test(v)) return false;
				var d = new Date(v.replace(regex, '$2/$1/$3'));
				return ( parseInt(RegExp.$2, 10) == (1+d.getMonth()) ) && 
							(parseInt(RegExp.$1, 10) == d.getDate()) && 
							(parseInt(RegExp.$3, 10) == d.getFullYear() );
			}],
	['validate-currency-dollar', Validator.messages['validate-currency-dollar'], function(v) {
				// [$]1[##][,###]+[.##]
				// [$]1###+[.##]
				// [$]0.##
				// [$].##
				return Validation.get('IsEmpty').test(v) ||  /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v)
			}],
	['validate-one-required', Validator.messages['validate-one-required'], function (v,elm) {
				var p = elm.parentNode;
				var options = p.getElementsByTagName('INPUT');
				return $A(options).any(function(elm) {
					return $F(elm);
				});
			}]
]);


//custom validate start
Validation.addAllThese([
	['validate-jpdate', Validator.messages['validate-jpdate'], function(v) {
				if(Validation.get('IsEmpty').test(v)) return true;
				var regex = /^(\d{4})-(\d{2})-(\d{2})$/;
				if(!regex.test(v)) return false;
				var d = new Date(v.replace(regex, '$1/$2/$3'));
				return ( parseInt(RegExp.$2, 10) == (1+d.getMonth()) ) && 
							(parseInt(RegExp.$3, 10) == d.getDate()) && 
							(parseInt(RegExp.$1, 10) == d.getFullYear() );
			}],

	['validate-integer', Validator.messages['validate-integer'], function(v) {
				return Validation.get('IsEmpty').test(v) || (/^[-+]?[\d]+$/.test(v));
			}],

	['validate-chinese', Validator.messages['validate-chinese'], function(v) {
				return Validation.get('IsEmpty').test(v) || (/^[\u4e00-\u9fa5]+$/.test(v));
			}],

	['validate-ip', Validator.messages['validate-ip'], function(v) {
				return Validation.get('IsEmpty').test(v) || (/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(v));
			}],

	['validate-phone', Validator.messages['validate-phone'], function(v) {
				return Validation.get('IsEmpty').test(v) || /^((0[1-9]{3})?(0[12][0-9])?[-])?\d{6,8}$/.test(v);
			}],

	['validate-mobile-phone', Validator.messages['validate-mobile-phone'], function(v) {
				return Validation.get('IsEmpty').test(v) || (/(^0?[1][35][0-9]{9}$)/.test(v));
			}],
	/**
	 * Usage : validate-equals-otherInputId
	 * Example : validate-equals-username or validate-equals-email etc..
	 */
	['validate-equals',Validator.messages['validate-equals'], function(v,elm,args,metadata) {
				//return Validation.get('IsEmpty').test(v) || $F(args[0]) == v;
				return $F(args[0]) == v;
			}],
	/**
	 * Usage : less-than-otherInputId
	 */
	['less-than',Validator.messages['less-than'], function(v,elm,args,metadata) {
				if(Validation.get('validate-number').test(v) && Validation.get('validate-number').test($F(args[0])))
					return Validation.get('IsEmpty').test(v) || parseFloat(v) < parseFloat($F(args[0]));
				return Validation.get('IsEmpty').test(v) || v < $F(args[0]);
			}],
	/**
	 * Usage : great-than-otherInputId
	 */
	['great-than',Validator.messages['great-than'], function(v,elm,args,metadata) {
				if(Validation.get('validate-number').test(v) && Validation.get('validate-number').test($F(args[0])))
					return Validation.get('IsEmpty').test(v) || parseFloat(v) > parseFloat($F(args[0]));
				return Validation.get('IsEmpty').test(v) || v > $F(args[0]);
			}],
	/**
	 * Usage : date-great-than-otherdateInputId
	 */
	['jpdate-not-less-than',Validator.messages['jpdate-not-less-than'], function(v,elm,args,metadata) {
				if(Validation.get('validate-jpdate').test(v) && Validation.get('validate-jpdate').test($F(args[0])))
					return Validation.get('IsEmpty').test(v) || v.toDate().getTime() >= $F(args[0]).toDate().getTime();
				return false;
			}],			
			
	/*
	 * Usage: min-length-number
	 * Example: min-length-10
	 */
	['min-length',Validator.messages['min-length'],function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || v.length >= parseInt(args[0]);
	}],
	/*
	 * Usage: max-length-number
	 * Example: max-length-10
	 */
	['max-length',Validator.messages['max-length'],function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || v.length <= parseInt(args[0]);
	}],
	/*
	 * Usage: validate-file-type1-type2-typeX
	 * Example: validate-file-png-jpg-jpeg
	 */
	['validate-file', function(v,elm,args,metadata) {
		return Validator.format(Validator.messages['validate-file'],[args.join(',')]);
	},function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || $A(args).any(function(extentionName) {
			return new RegExp('\\.'+extentionName+'$','i').test(v);
		});
	}],	
	/*
	 * Usage: validate-float-range-minValue-maxValue
	 * Example: -2.1 to 3 = validate-float-range--2.1-3
	 */
	['validate-float-range', Validator.messages['validate-float-range'],function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || (parseFloat(v) >= parseFloat(args[0]) && parseFloat(v) <= parseFloat(args[1]))
	},{depends : ['validate-number']}],
	/*
	 * Usage: validate-int-range-minValue-maxValue
	 * Example: -10 to 20 = validate-int-range--10-20
	 */
	['validate-int-range',Validator.messages['validate-int-range'],function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || (parseInt(v) >= parseInt(args[0]) && parseInt(v) <= parseInt(args[1]))
	},{depends : ['validate-integer']}],
	/*
	 * Usage: validate-length-range-minLength-maxLength
	 * Example: 10 to 20 = validate-length-range-10-20
	 */
	['validate-length-range', Validator.messages['validate-length-range'],function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || (v.length >= parseInt(args[0]) && v.length <= parseInt(args[1]));
	}],
	/*
	 * Usage: validate-length-number
	 * Example: validate-length-8
	 */
	['validate-length-equals', Validator.messages['validate-length-equals'],function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || (v.length == parseInt(args[0]));
	}],	
	/*
	 * Usage: max-value-number
	 * Example: max-value-10
	 */
	['max-value',Validator.messages['max-value'] ,function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || parseFloat(v) <= parseFloat(args[0]);
	},{depends : ['validate-number']}],
	/*
	 * Usage: min-value-number
	 * Example: min-value-10
	 */
	['min-value',Validator.messages['min-value'],function(v,elm,args,metadata) {
		return Validation.get('IsEmpty').test(v) || parseFloat(v) >= parseFloat(args[0]);
	},{depends : ['validate-number']}],
	/*
	 * Usage: validate-pattern-RegExp
	 * Example: <input id='sex' class='validate-pattern-/^[fm]$/i'>
	 */
	['validate-pattern',Validator.messages['validate-pattern'],function(v,elm,args,metadata) {
		var extractPattern = /validate-pattern-\/(\S*)\/(\S*)?/;
		Validation.assert(extractPattern.test(elm.className),"invalid validate-pattern expression,example: validate-pattern-/a/i");
		elm.className.match(extractPattern);
		return Validation.get('IsEmpty').test(v) || new RegExp(RegExp.$1,RegExp.$2).test(v);
	}],
	['validate-selection', Validator.messages['validate-selection'], function(v,elm){
		return elm.options ? elm.selectedIndex > 0 : !Validation.get('IsEmpty').test(v);
	}],
	/*
	 * Example: <input id='email' class='validate-ajax' validateUrl='http://localhost:8080/validate-email.jsp' validateFailedMessage='email already exists'>
	 */
	['validate-ajax',function(v,elm,args,metadata) {
		return elm.getAttribute('validateFailedMessage') || Validator.messages['validation-failed'];
	},function(v,elm,args,metadata) {
		Validation.assert(elm.getAttribute('validateUrl'),'element validate by ajax must has "validateUrl" attribute');
		//Validation.assert(elm.getAttribute('validateFailedMessage'),'element validate by ajax must has "validateFailedMessage" attribute');
		
		if(Validation.get('IsEmpty').test(v))
		    return true;
		
		if((elm._value == v) && elm._hasAjaxValidateResult) {
			return elm._ajaxValidateResult;
		}
		else {
			elm._value = v;
			elm._hasAjaxValidateResult = false;
		}
		
		var test = true;
		var sendRequest = function() {
			new Ajax.Request(elm.getAttribute('validateUrl'),{
				parameters : {txtEmail: elm._value},
				onCreate : function() {
					elm.readOnly = true;
				},
				onSuccess : function(response) {
					if('true' != response.responseText.strip()  && 'false' != response.responseText.strip())
						Validation.assert(false,'validate by ajax,response.responseText must equals "true" or "false",actual='+response.responseText);
					elm._ajaxValidateResult = eval(response.responseText);
					elm._hasAjaxValidateResult = true;
					test = elm._ajaxValidateResult;
					elm.readOnly = false;
					if (!test) {
						Validation.test('validate-ajax',elm,true);
						//Validation.validate(elm,{useTitle:true});
					}
				}
			});
		}
		
		sendRequest();
		return test;
		
		/*
		if(elm._ajaxValidating && elm._hasAjaxValidateResult) {
			elm._ajaxValidating = false;
			elm._hasAjaxValidateResult = false;
			return elm._ajaxValidateResult;
		}

		var sendRequest = function() {
			new Ajax.Request(elm.getAttribute('validateUrl'),{
				parameters : Form.Element.serialize(elm),
				onSuccess : function(response) {
					if('true' != response.responseText.strip()  && 'false' != response.responseText.strip())
						Validation.assert(false,'validate by ajax,response.responseText must equals "true" or "false",actual='+response.responseText);
					elm._ajaxValidateResult = eval(response.responseText);
					elm._hasAjaxValidateResult = true;
					Validation.test('validate-ajax',elm);
				},
				onFailure : function() {
					elm._ajaxValidating = false;
					elm._hasAjaxValidateResult = false;
				}
			});
			elm._ajaxValidating = true;
			return true;
		}

		return elm._ajaxValidating || Validation.get('IsEmpty').test(v) || sendRequest();
		
		*/
	}],
	['validate-ajax-ktaiaddress',Validator.messages['validate-ajax-ktaiaddress'],function(v,elm,args,metadata) {
		Validation.assert(elm.getAttribute('validateUrl'),'element validate by ajax must has "validateUrl" attribute');
		
		if(Validation.get('IsEmpty').test(v))
		    return true;
		
		if((elm._value == v) && elm._hasAjaxValidateResult) {
			return elm._ajaxValidateResult;
		}
		else {
			elm._value = v;
			elm._hasAjaxValidateResult = false;
		}
		
		var test = true;
		var sendRequest = function() {
			new Ajax.Request(elm.getAttribute('validateUrl'),{
				parameters : {txtKtaiAddress: elm._value + "@" + $F('ddlKtaiHost')},
				onCreate : function() {
					elm.disabled = true;
				},
				onSuccess : function(response) {
					if('true' != response.responseText.strip()  && 'false' != response.responseText.strip())
						Validation.assert(false,'validate by ajax,response.responseText must equals "true" or "false",actual='+response.responseText);
					elm._ajaxValidateResult = eval(response.responseText);
					elm._hasAjaxValidateResult = true;
					test = elm._ajaxValidateResult;
					elm.disabled = false;
					if (!test) {
						return Validation.test('validate-ajax-ktaiaddress',elm,true);
					}
				}
			});
		}
		
		sendRequest();
		return test;
	}],	
	/**
	 * Usage : validate-keio-regnumber-institute-entraceyear-length
	 */
	['validate-keio-regnumber',Validator.messages['validate-keio-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-number').test(v) 
				&& v.length==parseInt(args[2])
				&& (new RegExp(arr[0]+$F(args[1]).substr(2,2)+'\\d+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-waseda-regnumber-institute-entraceyear-length
	 */
	['validate-waseda-regnumber',Validator.messages['validate-waseda-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v.substr(4,5))
				&& v.length==parseInt(args[2])
				&& (new RegExp(arr[0]+$F(args[1]).substr(2,2)+'\\d+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-utokyo-regnumber-institute-entraceyear-length
	 */
	['validate-utokyo-regnumber',Validator.messages['validate-utokyo-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v)
				&& v.length==parseInt(args[2])
				&& !(Validation.get('IsEmpty').test($F(args[1]).substr(3,1)))
				&& (new RegExp('\^'+$F(args[1]).substr(3,1)+'\\d+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-chuou-regnumber-institute-entraceyear-length
	 */
	['validate-chuou-regnumber',Validator.messages['validate-chuou-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v.substr(3,7)) 
				&& v.length==parseInt(args[2])
				&& (Validation.get('validate-alphanum').test(v.substr(10,1)))
				&& (new RegExp($F(args[1]).substr(2,2)+arr[0]+'\\w+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-hosei-regnumber-institute-entraceyear-length
	 */
	['validate-hosei-regnumber',Validator.messages['validate-hosei-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');	
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-alphanum').test(v.substr(3,2)) 
				&& (Validation.get('validate-digits').test(v.substr(5,2))) 
				&& v.length==parseInt(args[2])
				&& (new RegExp($F(args[1]).substr(2,2)+arr[0]+'\\w+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-rikkyo-regnumber-subject-entraceyear-length
	 */
	['validate-rikkyo-regnumber',Validator.messages['validate-rikkyo-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		if( arr[0] == ''||  arr[0]== 'null'){
			arr[0] = '\[a-zA-Z]{2}';
		}	
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v.substr(4,3))
				&& (Validation.get('validate-alphanum').test(v.substr(6,1)))
				&& v.length==parseInt(args[2])
				&& (new RegExp($F(args[1]).substr(2,2)+arr[0]+'\\w+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-meiji-regnumber-subject-entraceyear-length
	 */
	['validate-meiji-regnumber',Validator.messages['validate-meiji-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		var result = false;

		if (arr[0].substr(0,2)== '14') {
			arr[0]='14\\d';
		}
		if( arr[0] == ''||  arr[0]== 'null'){
			arr[0] = '\[0-9]{3}';
		}
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v) 
				&& v.length == parseInt(args[2])
				&& (new RegExp(arr[0] + '\\d'+$F(args[1]).substr(2,2)+'\\d+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-aoyama-regnumber-subject-entraceyear-length
	 */
	['validate-aoyama-regnumber',Validator.messages['validate-aoyama-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		if( arr[0] == ''||  arr[0]== 'null'){
			arr[0] = '[1]{1}([0-9]{2})';
		}	
		var a1 = (new RegExp(arr[0]+$F(args[1]).substr(2,2)+'\\d+$','i').test(v));
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v) 
				&& v.length==parseInt(args[2])
				&& (new RegExp('\^'+arr[0]+$F(args[1]).substr(2,2)+'\\d+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-sophia-regnumber-subject-entraceyear-length
	 */
	['validate-sophia-regnumber',Validator.messages['validate-sophia-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		if(new RegExp('\^1|2{1}','i').test(arr[0])){
			arr[0] = '\[1-2]{1}';
		}
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v.substr(4,5))
				&& v.length==parseInt(args[2])
				&& (new RegExp(arr[0]+$F(args[1]).substr(2,2)+'\\d+$','i').test(v))
			);
	}],
	/**
	 * Usage : validate-seijo-regnumber-subject-entraceyear-length
	 */
	['validate-seijo-regnumber',Validator.messages['validate-seijo-regnumber'], function(v,elm,args,metadata) {
		var arr = $F(args[0]).split('-');
		var year = '\^[a]{1}' + $F(args[1]).substr(3,1);
		return Validation.get('IsEmpty').test(v) || 
			(Validation.get('validate-digits').test(v.substr(3,4))
				&& v.length==parseInt(args[2])
				&& (new RegExp(year + arr[0] + '\\d+$','i').test(v))
			);
	}]
]);

Validation.validations = {};
Validation.autoBind = function() {
	 //var forms = document.getElementsByClassName('required-validate');
	 var forms = $$('[class="required-validate"');
	 
	 $A(forms).each(function(form){
		var validation = new Validation(form,{immediate:true});
		Event.observe(form,'reset',function() {validation.reset();},false);
	 });
};

Validation.$ = function(id) {
	return Validation.validations[id];
}

Event.observe(window,'load',Validation.autoBind,false);




/*---------------------------------------------------------------------------------------------------
    namespace.js   

/*jslint evil: true */
// 声明一个全局对象Namespace，用来注册命名空间
Namespace = new Object();

// 全局对象仅仅存在register函数，参数为名称空间全路径，如"Grandsoft.GEA"
Namespace.register = function(fullNS)
{
    // 将命名空间切成N部分, 比如Grandsoft、GEA等
    var nsArray = fullNS.split('.');
    var sEval = '';
    var sNS = '';
    for (var i = 0; i < nsArray.length; i++)
    {
        if (i != 0) sNS += '.';
        sNS += nsArray[i];
        // 依次创建构造命名空间对象（假如不存在的话）的语句
        // 比如先创建Grandsoft，然后创建Grandsoft.GEA，依次下去
        sEval += 'if (typeof(' + sNS + ') == "undefined") ' + sNS + ' = new Object();';
    }
    if (sEval != '') eval(sEval);
}






/*---------------------------------------------------------------------------------------------------
    alphafilter.js   

/*jslint evil: true */
/*--------------------------------------------------------------------------*
 *  
 *  alphafilter JavaScript Library beta5
 *  
 *  MIT-style license. 
 *  
 *  2007 Kazuma Nishihata 
 *  http://www.webcreativepark.net
 *  
 *--------------------------------------------------------------------------*/

new function(){

	if(window.addEventListener){
		window.addEventListener('load',alphafilter,false);
	}else if(window.attachEvent){
		window.attachEvent('onload',alphafilter);
	}
	
	function alphafilter(){
		if (typeof document.body.style.maxHeight == "undefined") {//for old ie
			var elements = getElementsByClassName("alphafilter"); 
			for (var i=0; i<elements.length; i++) {
				var element = elements[i];
				if(element.nodeName=="IMG"){
					var newimg           = document.createElement("b");
					for(var key in element.currentStyle){
						newimg.style[key]=element.currentStyle[key];
					}
					newimg.className     = element.className;
					newimg.style.display = "inline-block";
					newimg.style.width   = element.width;
					newimg.style.height  = element.height;
					//newimg.style.float   = element.align;
					newimg.style.cssFloat   = element.align;
					newimg.style.filter  = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src="+element.src+",sizingMethod='scale')";
					element.parentNode.replaceChild(newimg,element);
				}else{
					var anchors = element.getElementsByTagName("a");
					for (var j=0; j<anchors.length; j++) {
						var anchor = anchors[j];
						anchor.style.position="relative";
					}
					var iputs = element.getElementsByTagName("input");
					for (var j=0; j<iputs.length; j++) {
						var iput = iputs[j];
						iput.style.position="relative";
					}
					var iputs = element.getElementsByTagName("textarea");
					for (var j=0; j<iputs.length; j++) {
						var iput = iputs[j];
						iput.style.position="relative";
					}
					var iputs = element.getElementsByTagName("select");
					for (var j=0; j<iputs.length; j++) {
						var iput = iputs[j];
						iput.style.position="relative";
					}
					var  newimg = element.currentStyle.backgroundImage || element.style.backgroundImage;
					newimg.match(/^url[("']+(.*\.png)[)"']+$/i)  //'
					var newimg = RegExp.$1;
					element.style.filter ="progid:DXImageTransform.Microsoft.AlphaImageLoader(src="+newimg+",sizingMethod='image')";
					element.style.background = "none";
				}
			}
		}
	}
	
	function getElementsByClassName(className){
		var i, j, eltClass;
		var objAll = document.getElementsByTagName ? document.getElementsByTagName("*") : document.all;
		var objCN = new Array();
		for (i = 0; i < objAll.length; i++) {
			eltClass = objAll[i].className.split(/\s+/);
			for (j = 0; j < eltClass.length; j++) {
				if (eltClass[j] == className) {
					objCN.push(objAll[i]);
					break;
				}
			}
		}
		return objCN;
	}
}



/*---------------------------------------------------------------------------------------------------
	extends.js

jslint evil: true '*/ 

//去除多余空格（半角、全角）
String.prototype.trimAll = function(){
	return this.replace(/^[\s　]+|[\s　]+$/, '').replace(/[\s　]+/g, ' ');
}

//去除字符串中全部空格（半角、全角）
String.prototype.clearAllSpace = function(){
	return this.replace(/^[\s　]+|[\s　]+$/, '').replace(/[\s　]+/g, '');
}

//过滤标点符号，转为空格
String.prototype.escapePunctuation = function(){
	return this.replace(/[~`!@#\$%\^&\*\(\)\-\+=\{\[\}\]:;"'<,>\.\?\/\\\|～·！◎＃￥％…※×（）—＋＝『』【】；‘：“”，‘’。、《》？÷§・「」－￥＿＋｜｛｝＜＞]+/g, ' ');
}

//把\n转为<br/>,空格' '转为&nbsp;
String.prototype.parseBR = function(){
	return this.replace(/  /g, ' &nbsp;&nbsp;').replace(/\r\n|\r|\n/g,'<br/>');
}

//把<br/>转为\n,空格&nbsp转为' ';
String.prototype.reParseBR = function(){
	return this.replace(/<br>/g, '\n').replace(/<br\/>/g, '\n').replace(/&nbsp;/g,' ');
}

String.prototype.unEscape = function(){	
	return this.replace(/&lt;/g,"<").replace(/&gt;/g,">").replace(/&amp;/g,"&");
}

//把 " 转为&quot;,空格 ' 转为&nbsp;
String.prototype.quoteEscape = function(){
	return this.replace(/&/g,"&amp;").replace(/\"/g,"&quot;").replace(/\'/g,"&#039;");
}

//把\n转为<br/>
String.prototype.nl2br = function(){
	return this.replace(/\r\n|\r|\n/g,'<br/>');
}

String.prototype.truncate2 = function(len,sep){
	if(len==null) len=2;
	if(sep==null) sep='';
	
	var a=0;
	
	for(var i=0;i<this.length;i++){
		if (this.charCodeAt(i)>255)
			a+=2;
		else
			a++;
		
		if(a>=len)
			return this.substr(0,i+1) + sep;
	}
	
	return this;
}

String.prototype.truncateEmoji = function(len, separator){

	var mixedStr = String(this);
	var moji_pattern = /\[([ies]:[0-9]{1,3})\]/ig;
	var matches = this.match(moji_pattern);
	matches = (matches == null) ? new Array() : matches;
	mixedStr = mixedStr.replace(moji_pattern, '�');

    len = len || 30;
    separator = Object.isUndefined(separator) ? '...' : separator;
    mixedStr = (mixedStr.length > len) ? (mixedStr.slice(0, len - separator.length) + separator) : mixedStr;

    var moji_pattern2 = /�/i;
    
    for (var i = 0; i < matches.length; i++) {
        mixedStr = mixedStr.replace(moji_pattern2, matches[i]);
    }
	
	return mixedStr;
}

String.prototype.formatToDate = function(format){
	if (!format) format = 'yyyy年MM月dd日 hh:mm';
	return new Date(this.replace(/-/g,"/")).Format(format);
}

String.prototype.toDate = function(){
	return new Date(this.replace(/-/g,"/"));
}

String.prototype.toDateLocalString = function(){
	var temp = new Date(this.replace(/-/g,"/"));
	
	var monthStr = '' + (temp.getMonth()+1); 
	if (monthStr.length<2) monthStr = '0' + monthStr; 
	
	var dateStr = '' + (temp.getDate()); 
	if (dateStr.length<2) dateStr = '0' + dateStr;
	
	return '' + temp.getFullYear() + '-' + monthStr + '-' + dateStr; 
}

String.prototype.formatToDateShort = function(format){
	if (!format) format = 'yyyy年MM月dd日';
	return new Date(this.replace(/-/g,"/")).Format(format);
}

String.prototype.formatToAmount = function(){
	return Number(this).formatToAmount();
}

Number.prototype.formatToAmount = function() {
	var tmp= '' + this;
    
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


function Hashtable() 
{ 
    this._hash  = new Object(); 
    this.add  = function(key,value){ 
         if(typeof(key)!="undefined"){ 
             if(this.contains(key)==false){ 
                 this._hash[key]=typeof(value)=="undefined"?null:value; 
                      return true; 
             }
             else { 
             	return false; 
             } 
         }
         else { 
         	return false; 
         } 
    } 
    this.remove = function(key){
    	delete this._hash[key];
    } 
    this.count = function(){
    	var i=0;
    	var obj = new this._hash.constructor();
    	for(var k in this._hash){
    		if(obj[k] !== this._hash[k])
    			i++;
    	}
    	return i;
    } 
    this.items = function(key){
    	if(this.contains(key))
    		return this._hash[key];
    	else
    		return null;
    } 
    this.contains = function(key){
    	return typeof(this._hash[key])!="undefined";
    } 
    this.clear = function(){
    	var obj = new this._hash.constructor();
    	for(var k in this._hash){
    		if(obj[k] !== this._hash[k])
    			delete this._hash[k];
    	}
    }
    this.keys = function(){
	    var keys = new Array();
	    var obj = new this._hash.constructor();
	    for(var prop in this._hash)
	    {
	     	if (obj[prop] !== this._hash[prop])
	    		keys.push(prop);
	    }
	    return keys;
    }
    this.values = function(){
	    var values = new Array();
	    var obj = new this._hash.constructor();
	    for(var prop in eval(this._hash))
	    {
	    	if (obj[prop] !== this._hash[prop])
	    		values.push(this._hash[prop]);
	    }
	    return values;   	
    }
}

//hashtable in javascript
var Collections = new Object();

Collections.Base = Class.create();
Collections.Base.prototype = {
 initialize:function()
 {
  this.count = 0 ;
  this.container = new Object();
 }
}
Collections.Hashtable = Class.create();
Collections.Hashtable.prototype = Object.extend(new Collections.Base(),
  { 
   add:function(key ,value)
   {
    if(!this.containsKey(key))
    {
     this.count++;
    }
    this.container[key] = value;
   },
   get:function(key)
   {
    if(this.containsKey(key))
    {
     return this.container[key];
    }
   else
    {
     return null;
    }
   },
   containsKey:function(key)
   {
    return (key in this.container);
   },
   containsValue:function(value)
   {
    for(var prop in this.container)
    {
     if(this.container[prop]==value)
     {
      return true;
     }
    }
    return false;
   },
   keys:function()
   {
    var keys = new Array();
    for(var prop in this.container)
    {
     keys.push(prop);
    }
    return keys;
   },
   values:function()
   {
    var values = new Array();
    for(var prop in this.container)
    {
     values.push(this.container[prop]);
    }
    return values;
   },
   remove:function()
   {
    if(this.containsKey(key))
    {
     delete this.container[key];
     this.count--;
    }
   }
    
  }

)

function StringBuilder(value) {
    this.strings = new Array("");
    this.append(value);
}

// Appends the given value to the end of this instance.
StringBuilder.prototype.append = function (value) {
    if (value != null) {
        this.strings.push(value);
    }
    return this;
}

// Clears the string buffer
StringBuilder.prototype.clear = function () {
    this.strings.length = 1;
    return this;
}

// Converts this instance to a String.
StringBuilder.prototype.toString = function () {
    return this.strings.join("");
}

StringBuilder.prototype.isEmpty = function () {
    return this.strings.length==0;
}

function DateSelector(ddlYear, ddlMonth, ddlDay)
{
    this.ddlYear = ddlYear;
    this.ddlMonth = ddlMonth;
    this.ddlDay = ddlDay;
    this.InitYearSelect();
    this.InitMonthSelect();
}

// 增加一个最大年份的属性
DateSelector.prototype.MinYear = 1970;

// 增加一个最大年份的属性
DateSelector.prototype.MaxYear = (new Date()).getFullYear();

// 初始化年份
DateSelector.prototype.InitYearSelect = function()
{
    // 循环添加OPION元素到年份select对象中
    for(var i = this.MaxYear; i >= this.MinYear; i--)
    {
        // 新建一个OPTION对象
        var op = window.document.createElement("OPTION");
        
        // 设置OPTION对象的值
        op.value = i;
        
        // 设置OPTION对象的内容
        op.innerHTML = i;
        
        // 添加到年份select对象
        this.ddlYear.appendChild(op);
    }
}

// 初始化月份
DateSelector.prototype.InitMonthSelect = function()
{
    // 循环添加OPION元素到月份select对象中
    for(var i = 1; i < 13; i++)
    {
        // 新建一个OPTION对象
        var op = window.document.createElement("OPTION");
        
        // 设置OPTION对象的值
        op.value = i;
        
        // 设置OPTION对象的内容
        op.innerHTML = i;
        
        // 添加到月份select对象
        this.ddlMonth.appendChild(op);
    }
}

Date.prototype.Format = function(fmt) 
{ //author: meizz 
	var o = { 
		"M+" : this.getMonth()+1, //月份 
		"d+" : this.getDate(), //日 
		"h+" : this.getHours(), //小时 
		"m+" : this.getMinutes(), //分 
		"s+" : this.getSeconds(), //秒 
		"q+" : Math.floor((this.getMonth()+3)/3), //季度 
		"S" : this.getMilliseconds() //毫秒 
	}; 
	if(/(y+)/.test(fmt)) 
	fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length)); 
	for(var k in o) 
	if(new RegExp("("+ k +")").test(fmt)) 
	fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length))); 
	return fmt; 
} 

// 根据年份与月份获取当月的天数
DateSelector.DaysInMonth = function(year, month)
{
    var date = new Date(year, month, 0);
    return date.getDate();
}

Date.prototype.dateDiff  =  function(interval,o)
{
	//判断o是否为日期对象
	if(o&&o.constructor==Date){
		//判断是否interval是否是字符串对象
		if  (interval&&interval.constructor==String){
		
			var  _start=  this.getTime();
			var  _end=  o.getTime();
			
			var  number=  _end  -  _start;
			
			var  iOut  =  -1;
			      
			switch  (interval.charAt(0)){
				case  'y':case  'Y'://year
				iOut  =    o.getFullYear()  -  this.getFullYear();
				break;
				case  'm':case  'M'://month
				iOut  =  (o.getFullYear()  -  this.getFullYear())  *  12  +  (o.getMonth()-this.getMonth());
				break;
				case  'q':case  'Q'://quarter
				iOut  =  ((o.getFullYear()  -  this.getFullYear())  *  12  +  (o.getMonth()-this.getMonth()))/3;
				break;
				case  'd':case  'D'://day
				iOut  =  parseInt(number  /  86400000)  ;
				break;
				case  'w':case  'W'://week
				iOut  =  parseInt(number  /  86400000/7)  ;
				break;
				case  'h':case  'H'://hour
				iOut  =  parseInt(number  /  3600000  )  ;
				break;
				case  'n':case  'N'://minute
				iOut  =  parseInt(number  /  60000  )  ;
				break;
				case  's':  case  'S'://second
				iOut  =  parseInt(number  /  1000  )  ;
				break;
				case  't':case  'T'://microsecond
				iOut  =  parseInt(number);
				break;
				default:
				iOut  =  -1;
			}
			
			return  iOut;
		}
	}	
	return  -1;
}

Date.prototype.dateAdd  =  function(interval,number)
{
	var  date  =  this;

    switch(interval)
    {
        case  "y"  :  
			date.setFullYear(date.getFullYear()+number);
			return  date;
        case  "q"  :  
			date.setMonth(date.getMonth()+number*3);
			return  date;
        case  "m"  :  
			date.setMonth(date.getMonth()+number);
			return  date;
        case  "w"  :  
			date.setDate(date.getDate()+number*7);
			return  date;
        case  "d"  :  
			date.setDate(date.getDate()+number);
			return  date;
        case  "h"  :  
			date.setHours(date.getHours()+number);
			return  date;
		case  "m"  :  
			date.setMinutes(date.getMinutes()+number);
			return  date;
		case  "s"  :  
			date.setSeconds(date.getSeconds()+number);
			return  date;
        default  :  
			date.setDate(d.getDate()+number);
			return  date;
    }
}





