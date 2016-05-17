// fn_name: function(){..} <== 형태 항상 확인하기
var util = {
	var_dump: function(obj) {
		var out = '';
    	for (var i in obj) {
        	out += i + ": " + obj[i] + "\n";
        }
    	return out;
	},
	popup_close: function(popup_id, callback_afterclose) {
		$('#'+popup_id).off('popupafterclose').on('popupafterclose', callback_afterclose).popup('close');	
	},
	request: function(sz_url, callback_func, error_func) {
			console.log('[__global.php] request: ' + sz_url);
			$.ajax({
				type:"GET",  
				url:sz_url,      
				success:function(sz_data){callback_func(sz_data);},   
				error:function(e){if (error_func) error_func(e.statusText); else alert(e.statusText + "\n" + sz_url);}  
			});		
	},
	post: function(sz_url, js_param, callback_func, error_func) {
			console.log('[__global.php] post: ' + sz_url);
			$.ajax({
				type:"POST",  
				url:sz_url,
				data: js_param,      
				success:function(sz_data){callback_func(sz_data);},
				error:function(e){if (error_func) error_func(e.statusText); else alert(e.statusText + "\n" + sz_url);}  
			});		
	},
	set_item_array: function(json_sel_map, json_data) {
		for (var key in json_sel_map) 
		{
			// console.log(key + ':' + json_data[json_sel_map[key]]);
			var elements = $(key);
			var sz_value = json_data[json_sel_map[key]];
			for (var j = 0; j < elements.length; j++) 
			{
				this.set_item_value(elements[j], sz_value);
			}
		}
	},
	set_item_value: function(item, sz_value) {
		var ar_item = $(item);
		if (ar_item.length == 0) return;
		var target_element = ar_item[0];
		
		if (target_element.tagName == "FIELDSET") // radio / checkbox
		{	
			var sub_nodes = $("input", target_element);
			for (var i = 0; i < sub_nodes.length; i++) 
			{
				// type=radio 의 값과 같으면 check
				if ($(sub_nodes[i]).attr('type') == "radio") {
					$("input[type='radio']:checked:not([value='"+sz_value+"'])", $(target_element)).prop('checked', false).checkboxradio("refresh");
					$("input[type='radio'][value='"+sz_value+"']", $(target_element)).prop('checked',true).checkboxradio("refresh");
					break;
				}
				// type=checkbox 의 값이 array값중 존재하면 check
				else if ($(sub_nodes[i]).attr('type') == "checkbox") {
					var b_prev_state = ($(sub_nodes[i]).attr('checked') == true) ? true : false;
					var b_checked = this.in_array($(sub_nodes[i]).val(), sz_value) ? true : false;	// array값 중 존재하면 true
					if (b_prev_state != b_checked) {
						$(sub_nodes[i]).prop('checked',b_checked).checkboxradio("refresh");
					}
				}
			}
		} 
		else if (target_element.tagName == "INPUT" && (this.in_array($(target_element).attr('type'), ["checkbox", "radio"])))
		{
			var b_prev_state = ($(target_element).attr('checked') == true) ? true : false;
			var b_checked = sz_value;		// sz_value => true / false
			if (b_prev_state != b_checked) $(target_element).attr('checked',b_checked).checkboxradio("refresh");
		}
		else
		{
			$(target_element).val(sz_value);
			$(target_element).trigger("change");
		}
	},
	get_item_value: function(item) {
		var ar_item = $(item);
		if (ar_item.length == 0) return "";
		var target_element = ar_item[0];
		
		if (target_element.tagName == "FIELDSET") {
			var sps_checked = $("input[type='radio']:checked", target_element);
			if (sps_checked) return $(sps_checked[0]).val();

			var ret_value = "";
			sps_checked = $("input[type='checkbox']:checked", target_element);
			for (var i = 0; i < sps_checked.length; i++) {
				if (ret_value != "") ret_value += ",";
				ret_value += $(sps_checked[i]).val();	
			};
			return ret_value;			
		}
		if (target_element.tagName == "INPUT" && $(target_element).attr('type') == 'checkbox') {
			var sp_label = $(target_element).parent().find("label[for='"+target_element.id+"']");
			if (sp_label) return $(sp_label).hasClass('ui-checkbox-on');
			return ($(target_element).attr('checked') != '') ? true : false;
		}
		return $(target_element).val();
	},
	list_reset_lastid: function(jq_item) {
		jq_item.data('last_id', -1);		
	},
	list_request_for_table: function(url, jq_item, sz_more_script, on_request)
	{
		if (sz_more_script === null || typeof(sz_more_script) == 'undefined') 
			sz_more_script = '<tr><td colspan=100 class="btn-more" style=\'cursor:pointer\'><t4 style="text-align:center">더보기</t4></td></tr>';	// 더보기 버튼 UI
		return util.list_request(url, jq_item, sz_more_script, on_request);
	},
	list_request: function(url, jq_item, sz_more_script, on_request) // function on_request(json_data){ ... }
	{
		if (sz_more_script === null || typeof(sz_more_script) == 'undefined') 
			sz_more_script = '<li data-icon="false" class="btn-more"><a href="#"><t4 style="text-align:center">더보기</t4></a></li>';	// 더보기 버튼 UI
			
		var last_id = jq_item.data("last_id");
		if (!last_id) last_id = -1;
		url = this.url_add_param(url, "last_id", last_id);	// set last id	
		console.log(url);
		this.request(url, function(sz_data)
		{
			var js_list = util.to_json(sz_data);
			jq_item.append(js_list['data']);
			jq_item.data("last_id", js_list['last_id']);

			// button more 처리
			if (sz_more_script != "") {
				var jq_btn_more = $('.btn-more', jq_item);
				if (js_list['has_more']) {
					if (jq_btn_more.length == 0) {
						jq_item.append(sz_more_script);
						jq_btn_more = $('.btn-more', jq_item);
						jq_btn_more.on('click', function() {
							util.list_request(url, jq_item, sz_more_script, on_request);
						});
					}
					else {
						jq_item.append(jq_btn_more);
					}
				} else {
					jq_btn_more.remove();
				}
			}

			// callback if valid
			if (on_request) on_request(js_list);
			if (jq_item.listview) jq_item.listview("refresh");
		});
	},
	to_json: function(sz_data) {
		return eval('(' + sz_data + ')');
	},
	to_html: function(sz_txt) {
		return util.htmlspecialchars(sz_txt).replace(/\n/g, "<br>");
	},
	from_json: function(js_data) {
		return JSON.stringify(js_data);
	},
	to_localphoneno: function(telno) {	// +821012341234 ==> 01012341234 로 변경, 외국 전화번호는 그대로 유지
		var new_no = telno.replace(/^\+82/, '0');
		if (new_no.match(/^01/)) return new_no;
		return telno;	
	},
	to_internationalphoneno: function(telno) {	// 01012341234 ==> +821012341234 로 변경
		if (!telno) return '';
		return telno.replace(/^0/, '+82');
	},
	to_phoneno: function(telno) {	// 전화번호에 \+\d+ 가 되도록 나머진 특수문자는 모두 제거
		if (telno.length <= 8) {
			var matched = telno.match(/^([\d]+?)([\d]{4})$/);
			if (matched) return matched[1] + '-' + matched[2];
			return telno;
		} else if (telno.length <= 11) {
			var matched = telno.match(/^(02)([\d]{3,4})([\d]{4})$/);
			if (matched) return matched[1] + '-' + matched[2] + '-' + matched[3];
			matched = telno.match(/^([\d]{3})([\d]{3,4})([\d]{4})$/);
			if (matched) return matched[1] + '-' + matched[2] + '-' + matched[3];				
			return telno;
		} else if (telno.substring(0, 3) == '+82') {
			var matched = telno.match(/^(\+822)([\d]{3,4})([\d]{4})$/);
			if (matched) return matched[1] + '-' + matched[2] + '-' + matched[3];
			matched = telno.match(/^(\+82\d{2})([\d]{3,4})([\d]{4})$/);
			if (matched) return matched[1] + '-' + matched[2] + '-' + matched[3];
			return telno;
		}
		var matched = telno.match(/^(.{2,}?)([\d]{3,4})([\d]{4})$/);
		if (matched) return matched[1] + '-' + matched[2] + '-' + matched[3];
		return telno;
	},
	from_phoneno: function(telno_disp) {
		return telno_disp.replace(/-/g, '');
	},
	to_date: function(sz_date) {
		return sz_date.replace(/\//ig, '-');
	},
	from_date: function(sz_date) {
		return sz_date.replace(/-/ig, '/');
	},
	get_now: function() {
		var now = new Date();
		return "%04d-%02d-%02d %02d:%02d:%02d".sprintf(now.getFullYear(), now.getMonth() + 1, now.getDate(), now.getHours(), now.getMinutes(), now.getSeconds());
	},
	get_today: function() {
		var now = new Date();
		return "%04d-%02d-%02d".sprintf(now.getFullYear(), now.getMonth() + 1, now.getDate());
	},
	get_date: function(sz_datetime) {
		if (!sz_datetime) return "";
		return sz_datetime.substring(0, 10);
	},
	get_time: function(sz_datetime) {
		if (!sz_datetime) return "";
		return sz_datetime.substring(11, 19);
	},
	is_undefined: function(obj) {
		if (typeof(obj) == 'undefined') return true;
		return false;
	},
	to_money: function(n_money) {
		if (!n_money) return "";
		return util.number_format(n_money) + ' 원';
	},
	from_money: function(sz_money) {
		if (!sz_money) return "";
		sz_money = sz_money.replace(/,/g, '');
		return parseInt(sz_money);
	},
	to_displaytime: function(sz_date){	// 7.13(월) 12:20
		var js_date =new Date(sz_date.replace(/-/g, '/'));
		var ar_week =["일","월","화","수","목","금","토"];
		var sz_week = ar_week[js_date.getDay()];
		return "%d.%d(%s) %d:%02d".sprintf(js_date.getMonth()+1, js_date.getDate(), sz_week, js_date.getHours(), js_date.getMinutes());
	},
	to_displaydate: function(sz_date, form){	// 7.13(월) 12:20
		var js_date =new Date(sz_date.replace(/-/g, '/'));
		var ar_week =["일","월","화","수","목","금","토"];
		var sz_week = ar_week[js_date.getDay()];
		
		if (!form || form == 0) return "%d.%d (%s)".sprintf(js_date.getMonth()+1, js_date.getDate(), sz_week);
		if (form == "YYYY.mm.dd") return "%04d.%02d.%02d".sprintf(js_date.getYear(), js_date.getMonth()+1, js_date.getDate());
	},
	json_to_urlparam: function(js_data) {
	    var parts = [];
	    for (var key in js_data) {
	        if (js_data.hasOwnProperty(key)) {
	        	if (js_data[key]) parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(js_data[key]));
	        }
	    }
	    return parts.join('&');
	},
	is_email: function(txt) {
		if (!txt) return false;
		return txt.match(/^[a-z0-9_+.-]+@([a-z0-9-]+\.)+[a-z0-9]{2,4}$/i);
	},
	intval: function(str_num) {
		if (!str_num) return 0;
		str_num = str_num.replace(/,/g, '');
		return parseInt(str_num);
	},
	number_format: function(num, empty_string){
		if (typeof empty_string == 'undefined') empty_string = 0;
		if (typeof num == 'undefined' || num === null || num === "") num = empty_string;
		else if (typeof num == 'string') {
			num = util.intval(num);
			num = (num).toFixed(0).replace(/(\d)(?=(\d{3})+$)/g, "$1,");
		}
		return num;
	},
	number_format_ex: function(num, empty_string){
		if (typeof empty_string == 'undefined') empty_string = 0;
		if (typeof num == 'undefined' || num === null || num === "") num = empty_string;
		else if (typeof num == 'string') {
			num = util.intval(num);
			num = (num).toFixed(0).replace(/(\d)(?=(\d{3})+$)/g, "$1,");
		}
		return num;
	},
	trim: function(txt) {
    	return txt.replace(/(^\s*)|(\s*$)/gi, "");
	},
	in_array: function(needle, haystack, argStrict)
	{
	  var key = '', strict = !! argStrict;
	  if (strict) {
	    for (key in haystack) {
	      if (haystack[key] === needle) {
	        return true;
	      }
	    }
	  } else {
	    for (key in haystack) {
	      if (haystack[key] == needle) {
	        return true;
	      }
	    }
	  }
	  return false;
	},
	set_event_for_input_number: function(sp_element, empty_string) {
		$(sp_element).attr('type', 'tel');
		$(sp_element).off('focusout').off('focusin');
		$(sp_element).val( util.number_format($(sp_element).val(), empty_string) );
		
		$(sp_element).on('focusout', function(){ $(sp_element).val( util.number_format($(sp_element).val(), empty_string)); });
		$(sp_element).on('focusin', function(){ 
			if (typeof empty_string != 'undefined' && empty_string == $(sp_element).val())
				$(sp_element).val("");
			else
				$(sp_element).val($(sp_element).val().replace(/,/gi,'')); 
		});
	},
	set_event_for_input_money: function(sp_element) {
		$(sp_element).attr('type', 'tel');	// 원을 사용하기 위해 tel타입으로 변경
		$(sp_element).off('focusout').off('focusin');
		$(sp_element).val( util.number_format($(sp_element).val()) );
		
		$(sp_element).on('focusout', function(){ $(sp_element).val( util.to_money($(sp_element).val())); });
		$(sp_element).on('focusin', function(){ $(sp_element).val( util.from_money($(sp_element).val()) ); });
	},
	set_event_for_input_phoneno: function(sp_element) {
		$(sp_element).off('focusout').off('focusin');
		$(sp_element).val( util.to_phoneno($(sp_element).val()) );
		
		$(sp_element).on('focusout', function(){ $(sp_element).val( util.to_phoneno($(sp_element).val())); });
		$(sp_element).on('focusin', function(){ $(sp_element).val( util.from_phoneno($(sp_element).val())); });
	},
	url_add_param: function(url, param_key, param_value) {
		var regex = new RegExp("([?;&])" + param_key + "=[^&;]*");
		if (url.match(regex)) return url.replace(regex, "$1" + param_key + '=' + encodeURIComponent(param_value));
		if (url.indexOf('?') >= 0) return url + '&' + param_key + '=' + encodeURIComponent(param_value);
		return url + '?' + param_key + '=' + encodeURIComponent(param_value);
	},
	url_add_params: function(url, ar_data) {
		for (var i = 0 ; i < ar_data.length / 2; i++) {
			url = util.url_add_param(url, ar_data[i*2], ar_data[i*2+1]);			
		}
		return url;
	},
	url_add_json_params: function(url, ar_data) {
		for (var i in ar_data) {
			url = util.url_add_param(url, i, ar_data[i]);			
		}
		return url;
	},
	urlencode: function(param) {
		return encodeURIComponent(param);
	},
	urldecode: function(param) {
		return decodeURIComponent(param);
	},	
	htmlspecialchars: function(text) {
	  var map = {
	    '&': '&amp;',
	    '<': '&lt;',
	    '>': '&gt;',
	    '"': '&quot;',
	    "'": '&#039;'
	  };
	  return text.replace(/[&<>\"\']/g, function(m) { return map[m]; });
	},
	load_js: function (file_url, func_ok){
		this.request(file_url, function(sz_data){
			eval(sz_data);
			if (func_ok) func_ok(true);
		});
	},
	load_js_local: function(filename){
        var fileref=document.createElement('script');
        fileref.setAttribute("type","text/javascript");
        fileref.setAttribute("src", filename);
	},
	Alert: function(title, msg, func_ok, btn_txt) {
		if (!msg) {
			msg = title;
			title = '알림';
		}
		
		btn_txt = btn_txt || '확인';
		if (navigator && navigator.notification) 
			navigator.notification.alert(msg, func_ok, title, btn_txt);
		else {
			alert(msg);
			if (func_ok) func_ok();
		}
	},
	MessageBox: function(title, msg, func_cb, js_ar_txt) {
		js_ar_txt = js_ar_txt || ['예','아니오'];
		if (navigator && navigator.notification) 
			navigator.notification.confirm(msg, func_cb, title, js_ar_txt);
		else
			if (confirm(msg)) 
				func_cb(1); 
			else 
				func_cb(2);
	},	
	initPage: function(page_root) {
		$(page_root).find('[init-script]').each(function(){ 
			if ($(this).attr('init-script')) eval($(this).attr('init-script'));
		});
		$(page_root).find('[init-html]').each(function(){ 
			$(this).html($(this).attr('init-html')); 
		});
		$(page_root).find('[init-value]').each(function(){ 
			var str_value = $(this).attr('init-value');
			str_value = str_value.replace(/\\n/g, '\n');
			util.set_item_value(this, str_value);
		 });
		$(page_root).find('[init-display]').each(function(){ 
			if ($(this).attr('init-display') == 'show') $(this).show(); 
			else if ($(this).attr('init-display') == 'hide') $(this).hide(); 
		});
		$(page_root).find('[init-readonly]').each(function(){ 
			if (eval($(this).attr('init-readonly'))) $(this).attr("readonly","readonly"); else $(this).removeAttr("readonly");
		});
		$(page_root).find('[init-background]').each(function(){ 
			if (eval($(this).attr('init-background'))) $(this).css("background","");
		});
		$(page_root).find('[init-enable]').each(function(){ 
			if (eval($(this).attr('init-enable'))) $(this).removeAttr("disabled"); else $(this).attr("disabled", "disabled");
		});
		$(page_root).find('[init-collapse]').each(function(){ 
			if (eval($(this).attr('init-collapse'))) $(this).collapsible( "option", "collapsed", true); else $(this).collapsible( "option", "collapsed", false);
		});
		$(page_root).find('[init-class]').each(function(){ 
			$(this).addClass($(this).attr('init-class'));
		});
		$(page_root).find('[init-removeclass]').each(function(){ 
			$(this).removeClass($(this).attr('init-removeclass'));
		});
		$(page_root).find('[init-data]').each(function(){ 
			var ar_datas = $(this).attr('init-data').split(" ");
			for(var i in ar_datas) { $(this).data(ar_datas[i], null); }
		});
	},
	to_money_korean: function(str) {
		var nString = ['','일','이','삼','사','오','육','칠','팔','구'];;
		var nbString = ['','','십','백','천','만 ','십','백','천','억 ','십','백','천','조 ','십','백','천'];

		str = str.replace(/(,|원)/g, '');
        var strCode = "";
        var codeStr = "";
        var nHan = "";
        var cnt = 0;
        
        /* 천조이상이면 */
        if (str.length > 16) {
            //alert("한글 표현은 천조 이하에 금액까지 가능합니다.");   
            //경고창 후 마지막 입력값 제거 필요 귀찮아서 안함!! ㅡㅡ;   
            return false;
        }
        /* 뒷자리부터 루프 */
        for (var i = str.length; i > 0; i--) {
            /* 유니코드 구하기 */
            strCode = str.charCodeAt(i - 1);
            /* 숫자가 맞다면 */
            if (strCode >= 48 && strCode <= 57) {
                cnt++; // 단위계산을 위해 카운팅   
                codeStr = Number(String.fromCharCode(strCode)); // Number형으로   
                if (codeStr != 1) {
                    if (codeStr == 0) {
                        if (cnt / 5 == 1) { // 만단위표현   
                            nHan = nbString[5] + nHan;
                        } else if (cnt / 9 == 1) { // 억단위표현   
                            nHan = nbString[9] + nHan;
                        } else if (cnt / 13 == 1) { // 조단위 표현   
                            nHan = nbString[13] + nHan;
                        }
                    } else {
                        /* 0이 아니면 입력값에 한글과 단위 */
                        nHan = nString[codeStr] + nbString[cnt] + nHan;
                    }
                } else if (codeStr == 1 && i == str.length) {
                    /* 1이고 마지막입력값이면 한글 일 표현 */
                    nHan = nString[codeStr] + nHan;
                } else {
                    if (codeStr == 1 && i == 1 && (cnt == 9 || cnt == 13)) {
                        /**  
                        *    입력값이 1이고 첫입력값이며 단위가 억이거나 조이면   
                        *    예) 일억 또는 일조   
                        *    억이하 단위에선 일을 표현안되기 때문에 일백만원을 백만원 일십만원을 십만원으로 표현되고  
                        *    억, 조 단위는 일억원 일조원 으로 표현하기 위해  
                        */
                        nHan = nString[codeStr] + nbString[cnt] + nHan;
                    } else {
                        nHan = nbString[cnt] + nHan;
                    }
                }
                /* 단위표현에서 억만, 조억에 두번째 단위 제거 (이거 때문에 삽질했네..) */
                nHan = nHan.replace('억만', '억').replace('조억', '조');
            } else {
                //alert("숫자로 입력하세요.");   
                //경고창 후 마지막 입력값 제거 필요 귀찮아서 안함!! ㅡㅡ;   
                return false;
            }
        }
        return nHan;
    },
    reload: function() {
    	if ($(document).scrollTop() > 1) localStorage.setItem('scroll-pos', $(document).scrollTop());	
    	document.location.reload();
    },
};

window.var_dump = util.var_dump;