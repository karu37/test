(function($) {
	if ( $.scroller ) {
		$.scroller.setDefaults({
			mode : 'scroller', // scroller, clickpick
			theme : 'android',  // sense-ui, ios, android
	   		showOnFocus : false,
	   		dateFormat 	: 'yyyymmdd',
	   		beforeShow : function(elm, obj, a) {
	   		}
		});
	}
	$.isMobile = function() {
    	return (/iphone|ipad|ipod|android|opera\smini|opera\smobi|symbian|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));
    }
	$.isApp = function() {
    	return (/HYUMobile/i.test(navigator.userAgent.toLowerCase()));
    }
	$.mobile.setStack = function( key, value ) {
		return $.mobile.urlHistory.getActive()[key] = value;
	}
	$.mobile.getStack = function( key ) {
		var $prev = $.mobile.urlHistory.getPrev();
		if ($prev) {
			return $prev[key] || '';
		}
		return '';
	}
	$.mobile.backButtonString = function( str ) {
		var result = str;
		if ( !result ) return;
		if ( result.length > 6) {
			result = result.substr(0, 6) + '...';
		}
		return result;
	}
	$.extend({
		getXML: function( url, data, callback ) {
			return $.get( url, data, callback, "xml" );
		},
		getApiJSON: function( url, data, callback ) {
			return $.getJSON("/serviceapi"+url, data, callback );
		},
		getApiJSONTest: function( url, data, callback ) {
			return $.getJSON(url, data, callback );
		},
		getApiXML: function( url , data, callback ) {
			return $.getXML(url, data, callback );
		},
		postJSON: function(url, params, callback) {
			return jQuery.ajax({
				type : 'POST',
				url : url,
				data : params,
				dataType : 'json',
				success : function(response) {
					if (callback)
						callback(response);
				}
			});
		}
	});
	$.fn.serializeObject = function() {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name]) {
				if (!o[this.name].push) {
					o[this.name] = [ o[this.name] ];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

	$('<div/>').ajaxSend(function(e, xhr, setting) {
		var url = setting.url;
		if (url.match(/\.json/)) {
			$.mobile.showPageLoadingMsg();
		}
		else {
			$.mobile.hidePageLoadingMsg();
		}
	});
	$('<div/>').ajaxStart(function(e, xhr, setting) {
		$.mobile.showPageLoadingMsg();
	});

	$('<div/>').ajaxComplete(function(e, xhr, setting) {
		var url = setting.url;
		if (url.match(/\.json/)) {
			//$.mobile.hidePageLoadingMsg();
		}
	});

	$('<div/>').ajaxSuccess(callback);
	$('<div/>').ajaxError(callback);
	function callback(e, xhr, setting) {
		setTimeout(function() { $.mobile.hidePageLoadingMsg(); }, 200);
		var rtext = xhr.responseText;
		if ( rtext ) {
			var contentType = xhr.getResponseHeader('Content-Type');
			if (contentType && contentType.indexOf("application/json") >= 0) {
				var jsonObj = JSON.parse(xhr.responseText);
				if(jsonObj.hasError && !jsonObj.errors) {
					setTimeout(function() {
						var exceptionMsg = jsonObj.exception;
						if ( exceptionMsg ) {
							alert( exceptionMsg );
							var url = location.href;
							if(jsonObj.referer && url.indexOf(jsonObj.referer) == -1){
								location.replace(jsonObj.referer);
							}
						}

					}, 300);
				}
				var openApiMessage = jsonObj.message;
				if ( openApiMessage ) {
					if(openApiMessage.code && openApiMessage.code != 200 && openApiMessage.code != 524) {
						alert(openApiMessage.code + ':' + openApiMessage.msg);
					}
				}
			}
		}
	}

})(jQuery);


/**************************************************************************
* name		: fullZero(str,icount)
* parameter	: str => String
*			: icount => 전체 문자 갯수
* sample	: fullZero("123",5);
* return	: String
* 설명		: 전체 문자 만큼 앞에 0을 채워준다
**************************************************************************/
function fullZero(str,icount) {
	var slength = (""+str).length;
	var s="";
	for(i=0; i < icount - slength; i++) {
		s = s + "0";
	}
	return s + str;
}


function getDateTimeNo() {
	var today = new Date();
	var dmyStr = today.getFullYear();
	dmyStr += fullZero(today.getMonth()+1,2);
	dmyStr += fullZero(today.getDate(),2);
	dmyStr += fullZero(today.getHours(),2);
	dmyStr += fullZero(today.getMinutes(),2);
	dmyStr += fullZero(today.getSeconds(),2);
	dmyStr += fullZero(today.getMilliseconds(),3);
	
	return dmyStr;
}