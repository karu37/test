<?
	$appkey = ifempty($_REQUEST['appkey'], "");
?>
<div>
	<t3 style='height:40px; padding-top:20px'>새 광고주 계정</t3>
	<hr>
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
	<tr class='should'>
		<th>광고주 아이디</th>
		<td>
			<input style='width:200px' type="text" id="guest-id" name="guest-id" />
		</td>
	</tr>
	<tr class='should'>
		<th>비밀번호</th>
		<td>
			<div style='width: 200px'>
				<input type="text" id="guest-pw" name="guest-pw" />
			</div>
			영문/숫자와 밑줄("_") 만 가능<br>
			<span style='color:red; font-weight: bold'>한글은 절대 사용 불가</span>
		</td>
	</tr>
	<tr class='should'>
		<th>이름</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="guest-name" name="guest-name" />
			</div>
		</td>
	</tr>
	
	<tr>
		<th>광고주 광고</th>
		<td>
			<div style='min-height: 32px'>
				<div class='ui-grid-a'>
					<div class='ui-block-a' style='width:70%'>
						<input type="text" id="guest-apptitle" name="guest-apptitle" readonly style='background-color:#eff' />
						<input type="hidden" id="guest-appkey" name="guest-appkey" />
					</div>
					<div class='ui-block-b' style='width:30%'><a href="#" onclick="goPage('dlg-select-appkey', null, {caller_onselect: <?=$js_page_id?>.action.on_select_appkey})" data-role='button' data-mini='true' data-theme='b'>앱 선택</a></div>
				</div>
			</div>
			(2개 이상은 먼저 등록한 후 수정에서 등록)
		</td>
	</tr>
	<tr>
		<th>회사명</th>
		<td>
			<div style='min-height: 32px'>
				<input type="text" id="guest-company" name="guest-company" />
			</div>
		</td>
	</tr>
	<tr>
		<th>전화번호</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="guest-telno" name="guest-telno" />
			</div>
		</td>
	</tr>
	<tr>
		<th>메모</th>
		<td>
			<div style='min-height: 32px'>
				<textarea type="text" id="guest-memo" name="guest-memo" /></textarea>
			</div>
		</td>
	</tr>
	</table>
	<div style='padding-top: 20px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_addguest()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >광고주 계정 생성하기</a>
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
			on_select_appkey: function(appkey, apptitle)
			{
				_$("#guest-appkey").val(appkey);
				_$("#guest-apptitle").val(apptitle);
			},			
			on_btn_addguest: function()
			{

				var ar_param = {
					'partnerid' : '<?=$partner_id?>',
					'guestid' : _$("#guest-id").val(),
					'guestpw' : _$("#guest-pw").val(),
					'guestname' : _$("#guest-name").val(),
					'appkey' : _$("#guest-appkey").val(),
					'company' : _$("#guest-company").val(),
					'telno' : _$("#guest-telno").val(),
					'memo' : _$("#guest-memo").val()
				};
				util.post(get_ajax_url('partner-guest-add'), ar_param, function(sz_data) {
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
		
		util.set_event_for_input_phoneno(_$("#guest-telno"));
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
	
		
		
		