<? session_start() ?>
function get_ajax_url(ajax_id, param) {
	var url = "";
	url = "http://<?=$_SERVER['HTTP_HOST']?>/ajax-request.php?id=" + ajax_id + '&partner_id=' + util.urlencode('<?=$_SESSION['partnerid']?>') + '&umcode=' + util.urlencode('<?=$_SESSION['umcode']?>');
		
	if (typeof param == "object") url += '&' + util.json_to_urlparam(param);
	else if (typeof param == "string") url += '&' + param;
	return url;
}
function get_popup_url(page_id, param) {
	var url = "";
	url = "http://<?=$_SERVER['HTTP_HOST']?>/partner_popup.php?id=" + page_id;
		
	if (typeof param == "object") url += '&' + util.json_to_urlparam(param);
	else if (typeof param == "string") url += '&' + param;
	return url;
}

var g_page_param = {};
function goPage(page_id, options, param, web_param) 
{
	// onclick으로 goPage바로 이동시 --> Pressed -> pressedup 이 되지 않아 push상태로 남아있는 현상 발생
	setTimeout(function(){

		g_page_param[page_id] = param; 
		if (page_id != $.mobile.activePage.attr('id')) 
		{
			var new_options = $.extend(options, {transition: 'none'});
			if ($('#'+page_id).length == 0)
			{
				var sz_webparam = '';
				if (typeof web_param == 'object') sz_webparam = "&" + $.param( web_param, true );
// alert("<?=$_SERVER['REQUEST_SCHEME']?>://<?=$_SERVER['HTTP_HOST']?>/partner_index_ajax.php?id="+util.urlencode(page_id) + sz_webparam);				
				// 처음 페이지 호출시
				util.request("<?=$_SERVER['REQUEST_SCHEME']?>://<?=$_SERVER['HTTP_HOST']?>/partner_index_ajax.php?id="+util.urlencode(page_id) + sz_webparam, function(sz_data) {
					try {
						$(sz_data).appendTo($.mobile.pageContainer);
					} catch(err) { 
						alert(page_id + " Error > " + err.name + " : " + err.message);
					}
					
					// 화면 반투명 처리하기 위해 별도 추가
					set_dialog_ready(page_id);
					
					if ($('#'+page_id).length == 0) toast(page_id + " 이동 실패");
					
					_changePage("#" + page_id, new_options, false);
				});
			} 
			else
			{
				// 이미 생성된 페이지를 재 호출시
				_changePage("#" + page_id, new_options, true);
			}
		}
		
		// trigger create (동일한 페이지를 호출했을 때 처리)
		$("#" + page_id).trigger('pagecreate');
	}, 10);
}

function _changePage(page_selector, option, b_call_triggercreate) {
	$.mobile.pageContainer.pagecontainer("change", page_selector, option);
	if (b_call_triggercreate) $(page_selector).trigger('pagecreate');
}

function set_dialog_ready(page_id) {
    $('#' + page_id + '[data-role="dialog"]').on('pagebeforeshow', function(e, ui) {
		ui.prevPage.addClass("ui-dialog-background ");
	});
    $('#' + page_id + '[data-role="dialog"]').on('pagehide', function(e, ui) {
		$(".ui-dialog-background ").removeClass("ui-dialog-background ");
	});
}
