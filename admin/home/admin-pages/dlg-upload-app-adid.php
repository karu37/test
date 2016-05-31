<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>ADID 등록</h1>
	</div>
	<style>
		#input-data	{overflow: scroll; min-height: 200px; max-height:400px}
	</style>
    <div data-role="main" id="content" style='padding:20px'>

    	<div id='app-title' style='font-size:20px; color:darkred; font-weight: bold' init-html=""></div>

    	<t5 id='adid-summary' style='height: 22px; padding-top:5px; text-align:left' init-html=""></t5>
		
		<textarea id='input-data' name='input-data' wrap="off" init-value=""></textarea>	
    	<div style='text-align:left;'>ADID값 예: dc2f080f-2c83-4459-962f-a336de1de7cc<br>1줄에 1개 ADID로 작성</div>
		<br>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_upload_adid()' data-role='button' data-inline='true' data-mini='true' data-theme='b'>ADID 등록</a>
		<a href='#' data-role='button' data-rel='back' data-inline='true' data-mini='true' data-theme='b'>닫기</a>
		<br>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_adid_download()' data-role='button' data-inline='true' data-mini='true'>업로드한 ADID 목록</a>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_reset_adid()' data-role='button' data-inline='true' data-mini='true'>ADID 초기화</a>
		
	</div>		
<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var _$ = function(selector) { if (!selector) return $("#<?=$page_id?>"); return $("#<?=$page_id?>").find(selector); };
	var param = {};
	var m_appkey = '';
	var page = 
	{			
		action: {
			initialize: function() 
			{
				util.initPage(_$());
				param = (g_page_param['<?=$page_id?>'] ? g_page_param['<?=$page_id?>'] : {});
				
				if (!param['appkey']) {
					alert('앱키가 없습니다.');
					$.mobile.back();
					return;
				}
				m_appkey = param['appkey'];
				
				page.action.update_status();
			},
			
			update_status: function() {
				
				var ar_param = {appkey: m_appkey};
				util.request(get_ajax_url('admin-uploaded-adid-info', ar_param), function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						
						_$("#app-title").html(js_data['app_title']);
						_$("#adid-summary").html("현재 업로드된 ADID 개수 : " + util.number_format(js_data['adid_cnt']));
						_$("#input-data").val("");
						
					} else {
						util.Alert(js_data['msg']);
						$.mobile.back();
						return;
					}
				});
			},

			on_btn_upload_adid: function() {

				var ar_param = {};
				ar_param['appkey'] = m_appkey;
				ar_param['text'] = _$("#input-data").val();
				
				$.mobile.loading('show');
				util.post(get_ajax_url('admin-uploaded-adid-add'), ar_param, function(sz_data) {
					$.mobile.loading('hide');
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', js_data['msg'], function() {
							page.action.update_status();
						});	
					} else util.Alert(js_data['msg']);
				});

			},
			
			on_btn_adid_download: function() {
				window.open("?id=uploaded-adid-download&appkey=" + m_appkey);
			},
			
			on_btn_reset_adid: function() {

				var ar_param = {appkey: m_appkey};
				util.MessageBox("알림", '업로드된 ADID 데이터를 모두 삭제합니다.\n\n진짜로 삭제를 진행합니까 ?', function(sel) {
					if (sel == 1) {
						util.request(get_ajax_url('admin-uploaded-adid-reset', ar_param), function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								util.Alert('알림', js_data['msg'], function() {
									page.action.update_status();
								});	
							} else util.Alert(js_data['msg']);
						});
					}
				});
				
			},
		},
	};		
	
	function setEvents() {
		$(document).on("pageinit", function(){page.action.initialize();} );
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
</div>
