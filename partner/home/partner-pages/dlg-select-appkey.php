<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>광고 선택</h1>
	</div>
    <div data-role="main" id="content" style='padding: 0 10px'>
	<hr>
		<form onsubmit='return <?=$js_page_id?>.action.on_btn_search()'>
			<div style='width:300px; padding-top:10px; text-align: left'>
				<fieldset id="search-for" class='td-2-item' data-role="controlgroup" data-type="horizontal" style='margin-top: 3px;' data-mini=true init-value="title" >
			        <input name="search-for" id="search-for-title" value="title" type="radio" />
			        <label for="search-for-title">제목</label>
			        <input name="search-for" id="search-for-packageid" value="packageid" type="radio" />
			        <label for="search-for-packageid">패키지ID</label>
			    </fieldset>
			    <div class='ui-grid-a' style='padding:2px 0px; width: 300px; margin: 0 0 0 auto'>
			    	<div class='ui-block-a' style='width:200px'><input type=text name=search id=search data-clear-btn='true' value=""  style='line-height: 25px;'/></div>
					<div class='ui-block-b' style='width:100px'><a href='#' onclick='<?=$js_page_id?>.action.on_btn_search()' data-role='button' data-mini='true'>검색</a></div>
				</div>
			</div>
		</form>	
		<div style='padding: 10px 10px'>
			<iframe id="sub_frame" src="about:blank" style='width:100%; height:400px; border: 1px solid #ddd'></iframe>
		</div>
	</div>
</div>

<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var _$ = function(selector) { if (!selector) return $("#<?=$page_id?>"); return $("#<?=$page_id?>").find(selector); };
	var param = {};
	
	var caller_id = "";
	var caller_ok = null;
	var page = { 
		page_common_init: function(){
			util.initPage(_$()); 
			param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
		},
		page_event: {
			on_create: function() {
				caller_id = param['caller'];
				caller_ok = param['caller_ok'];
				_$("#sub_frame").attr('src', get_popup_url("iframe-search-app-lists"));
			}	
		},
		action: {
			on_btn_search: function() {
				var ar_param = {searchfor: util.get_item_value(_$("#search-for")), search: _$("#search").val()};
				_$("#sub_frame").attr('src', get_popup_url("iframe-search-app-lists", ar_param));
				return false;
			},
			on_window_message: function(e){
				var msg = e.data;
				if (msg.cmd == "onselect") {
					
					// input이 있다면 input입력
					$("#"+caller_id+" #guest-appkey").val(msg.app_key);
					$("#"+caller_id+" #guest-apptitle").val(msg.app_title);
					
					// 함수가 있다면 함수 호출
					if (typeof caller_ok == 'function') {
						caller_ok(msg.app_key, msg.app_title);
					}
					
					$.mobile.back();
					
					setTimeout(function() { _$("#sub_frame").attr('src', 'about:blank'); }, 100);
				}	
			}
		}
	};
	
	function setEvents() {
		_$().on("pagecreate", function(){page.page_common_init(); page.page_event.on_create();});
		window.addEventListener("message", page.action.on_window_message, false);
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
