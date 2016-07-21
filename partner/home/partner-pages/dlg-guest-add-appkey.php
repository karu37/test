<div data-role="dialog" id="<?=$page_id?>" data-overlay-theme="a">
	<div data-role="header" data-theme="a">
    	<h1>광고주 광고 추가</h1>
	</div>
	<div>
		<table id='app-info' cellpadding=0 cellspacing=0>
		<style>
			#app-info	tr > * 			{border-bottom: 1px solid #ddd}
			#app-info	td				{padding: 5px 10px; text-align: left}
			#app-info	td:first-child	{padding-top: 10px; line-height:23px; vertical-align:top}
			#app-info	td > div		{text-align: left}	/* 2번째 컬럼의 div내의 text-align을 left로 */
			#app-info	.should			{background-color:#ffd}
		</style>
		<col width=100px valign=top></col>
		<col width=400px valign=top align=left></col>
		<tr>
			<th>광고주 광고</th>
			<td>
				<div style='min-height: 32px'>
					<div class='ui-grid-a'>
						<div class='ui-block-a' style='width:70%'>
							<input type="text" id="guest-apptitle" name="guest-apptitle" readonly style='background-color:#eff' />
							<input type="hidden" id="guest-appkey" name="guest-appkey" />
						</div>
						<div class='ui-block-b' style='width:30%'><a href="#" onclick="goPage('dlg-select-appkey', null, {caller:'{<?=$js_page_id?>}'})" data-role='button' data-mini='true' data-theme='b'>앱 선택</a></div>
					</div>
				</div>
			</td>
		</tr>
		</table>
		<div style='padding-top: 20px'>
			<a href='#' onclick='<?=$js_page_id?>.action.on_btn_add_appkey()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >광고 추가</a>
		</div>
	
	</div>
</div>	
			
<script type="text/javascript"> 

var <?=$js_page_id?> = function()
{
	// 외부에서 사용할 (Event Callback)함수 정의
	var _$ = function(selector) { if (!selector) return $("#<?=$page_id?>"); return $("#<?=$page_id?>").find(selector); };
	var page = 
	{			
		action: {
			initialize: function() 
			{
				util.initPage($('#page'));
				_$("div[data-role='popup']").on("popupbeforeposition", function(){ util.initPage($(this)); });
			},
			on_btn_add_appkey: function()
			{

				var ar_param = {
					'partnerid' : '<?=$partner_id?>',
					'guestid' : _$("#guest-id").val(),
					'appkey' : _$("#guest-appkey").val()
				};
				util.post(get_ajax_url('partner-guest-add-appkey'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '등록되었습니다.', function() {
							window.location.href = "?id=guest-modify&guestid=" + js_data['guestid'];
						});	
					} else util.Alert(js_data['msg']);
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
	
		
		
		