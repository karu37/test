<div>
	<t3 style='height:40px; padding-top:20px'>새 업체 등록</t3>
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
	<col width=300px valign=top align=left></col>
	<tr class='should'>
		<th>업체 아이디</th>
		<td>
			<input style='width:200px' type="text" id="partner-id" name="partner-id" />
		</td>
	</tr>
	<tr class='should'>
		<th>비밀번호</th>
		<td>
			<div style='width: 200px'>
				<input type="text" id="partner-pw" name="partner-pw" />
			</div>
			(영문/숫자로만 작성해야 함 - 한글 절대 불가)
		</td>
	</tr>
	<tr class='should'>
		<th>이름</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="partner-name" name="partner-name" />
			</div>
		</td>
	</tr>
	
	<tr>
		<th>회사명</th>
		<td>
			<div style='min-height: 32px'>
				<input type="text" id="partner-company" name="partner-company" />
			</div>
		</td>
	</tr>
	<tr>
		<th>전화번호</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="partner-telno" name="partner-telno" />
			</div>
		</td>
	</tr>
	<tr>
		<th>메모</th>
		<td>
			<div style='min-height: 32px'>
				<textarea type="text" id="partner-memo" name="partner-memo" /></textarea>
			</div>
		</td>
	</tr>
	</table>
	<div style='padding-top: 20px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_addpartner()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >등록하기</a>
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
			on_btn_addpartner: function()
			{

				var ar_param = {
					'partnerid' : _$("#partner-id").val(),
					'partnerpw' : _$("#partner-pw").val(),
					'partnername' : _$("#partner-name").val(),
					'appkey' : _$("#partner-appkey").val(),
					'company' : _$("#partner-company").val(),
					'telno' : _$("#partner-telno").val(),
					'memo' : _$("#partner-memo").val()
				};
				util.post(get_ajax_url('admin-partner-new'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '등록되었습니다.', function() {
							window.location.href = "?id=partner-modify&partnerid=" + js_data['partnerid'];
						});	
					} else util.Alert(js_data['msg']);
				});
			},
		},
	};		
	
	function setEvents() {
		$(document).on("pageinit", function(){page.action.initialize();} );
		
		util.set_event_for_input_phoneno(_$("#partner-telno"));
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
	
		
		
		