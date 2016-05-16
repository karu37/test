<?
	$partner_id = $_REQUEST['partnerid'];
	$db_partner_id = mysql_real_escape_string($partner_id);
	
	$sql = "SELECT * FROM al_partner_t WHERE partner_id = '$db_partner_id'";
	$row_info = @mysql_fetch_assoc(mysql_query($sql,$conn));
	
?>
<div>
	<t3 style='height:40px; padding-top:20px'>업체 정보 변경</t3>
	<hr>
	<div style='line-height:20px; color: blue'>
	* 아래 내용 Copy해서 전달
<textarea>
업체 접속 정보
웹 URL : http://partner.aline-soft.kr
로그인 ID : <?=$row_info['partner_id'] . "\n" ?>비밀번호 : <?=$row_info['partner_pw']?>
</textarea>
	</div>
	<hr>
	<table id='app-info' width=100% cellpadding=0 cellspacing=0>
	<style>
		#app-info	tr > * 			{border-bottom: 1px solid #ddd}
		#app-info	td				{padding: 5px 10px; text-align: left}
		#app-info	td:first-child	{padding-top: 10px; line-height:23px; vertical-align:top}
		#app-info	td > div		{text-align: left}	/* 2번째 컬럼의 div내의 text-align을 left로 */
		#app-info	.should			{background-color:#ffd}
	</style>
	<col width=100px valign=top></col>
	<col width=300px valign=top></col>
	<col valign=top></col>
	<col valign=top align=left></col>
	<tr class='should'>
		<th>업체 아이디</th>
		<td>
			<input style='width:200px' type="text" id="partner-id" name="partner-id" value="<?=$row_info['partner_id']?>"/>
		</td>
		<td></td>
	</tr>
	<tr class='should'>
		<th>비밀번호</th>
		<td>
			<div style='width: 200px'>
				<input type="text" id="partner-pw" name="partner-pw"  value="<?=$row_info['partner_pw']?>"/>
			</div>
			(영문/숫자로만 작성해야 함 - 한글 절대 불가)
		</td>
		<td></td>
	</tr>
	<tr class='should'>
		<th>이름</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="partner-name" name="partner-name"  value="<?=$row_info['name']?>"/>
			</div>
		</td>
		<td></td>
	</tr>
	<tr>
		<th>회사명</th>
		<td>
			<div style='min-height: 32px'>
				<input type="text" id="partner-company" name="partner-company"  value="<?=$row_info['company']?>"/>
			</div>
		</td>
		<td></td>
	</tr>
	<tr>
		<th>전화번호</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="partner-telno" name="partner-telno" value="<?=$row_info['telno']?>" />
			</div>
		</td>
		<td></td>
	</tr>
	<tr>
		<th>메모</th>
		<td>
			<div style='min-height: 32px'>
				<textarea type="text" id="partner-memo" name="partner-memo"/><?=$row_info['memo']?></textarea>
			</div>
		</td>
		<td></td>
	</tr>
	</table>
	<div style='padding-top: 20px'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_modifypartner()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >업체 정보 저장하기</a>
	</div>
	<hr>
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
			on_btn_modifypartner: function()
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
				util.post(get_ajax_url('admin-partner-modify'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '수정되었습니다.', function() {
							window.location.reload();
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
	
		
		
		