<?
	$guest_id = $_REQUEST['guestid'];
	$db_guest_id = mysql_real_escape_string($guest_id);
	
	$sql = "SELECT * FROM guest_user_t WHERE guest_id = '$db_guest_id'";
	$row_info = @mysql_fetch_assoc(mysql_query($sql,$conn));
	
	$sql = "SELECT b.app_title, b.app_packageid, b.app_exec_type, b.reg_date, a.* FROM guest_app_list a INNER JOIN al_app_t b ON a.app_key = b.app_key WHERE a.guest_id = '$db_guest_id'";
	$result = mysql_query($sql,$conn);
?>
<div>
	<t3 style='height:40px; padding-top:20px'>광고주 정보 변경</t3>
	<hr>
	<div style='line-height:20px; color: blue'>
	* 아래 내용 Copy해서 전달
<textarea>
실시간 정보 접속 정보
웹 URL : http://guest.aline-soft.kr
로그인 ID : <?=$row_info['guest_id'] . "\n" ?>비밀번호 : <?=$row_info['guest_pw']?>
</textarea>
	</div>
	<hr>
	<table id='app-info' cellpadding=0 cellspacing=0>
	<style>
		#app-info	tr > * 			{border-bottom: 1px solid #ddd}
		#app-info	td				{padding: 5px 10px; text-align: left}
		#app-info	td:first-child	{padding-top: 10px; line-height:23px; vertical-align:top}
		#app-info	td > div		{text-align: left}	/* 2번째 컬럼의 div내의 text-align을 left로 */
	</style>
	<col width=100px valign=top></col>
	<col width=300px valign=top align=left></col>
	<tr>
		<th>광고주 아이디</th>
		<td>
			<input style='width:200px' type="text" id="guest-id" name="guest-id" value="<?=$row_info['guest_id']?>"/>
		</td>
	</tr>
	<tr>
		<th>비밀번호</th>
		<td>
			<div style='width: 200px'>
				<input type="text" id="guest-pw" name="guest-pw"  value="<?=$row_info['guest_pw']?>"/>
			</div>
			(영문/숫자로만 작성해야 함 - 한글 절대 불가)
		</td>
	</tr>
	<tr>
		<th>이름</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="guest-name" name="guest-name"  value="<?=$row_info['guest_name']?>"/>
			</div>
		</td>
	</tr>
	<tr>
		<th>회사명</th>
		<td>
			<div style='min-height: 32px'>
				<input type="text" id="guest-company" name="guest-company"  value="<?=$row_info['company']?>"/>
			</div>
		</td>
	</tr>
	<tr>
		<th>전화번호</th>
		<td>
			<div style='width: 200px; min-height: 32px'>
				<input type="text" id="guest-telno" name="guest-telno" value="<?=$row_info['telno']?>" />
			</div>
		</td>
	</tr>
	<tr>
		<th>메모</th>
		<td>
			<div style='min-height: 32px'>
				<textarea type="text" id="guest-memo" name="guest-memo"  value="<?=$row_info['memo']?>"/></textarea>
			</div>
		</td>
	</tr>
	</table>
	<div style='padding-top: 20px'>
		<a href='#' onclick='goPage("dlg-guest-add-app", null, {guestid: "<?=$row_info['guest_id']?>"})' data-role='button' data-theme='b' data-inline='true' data-mini='true' >새 광고키 등록</a>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_modifyguest()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >정보 수정하기</a>
	</div>
	<hr>

	<table class='single-line' cellpadding=0 cellspacing=0>
	<thead>
		<tr>
			<th width=5%>앱타이틀</th>
			<th width=3%>광고키</th>
			<th width=3%>타입</th>
			<th width=4%>Package ID</th>
			<th width=2%>등록일</th>
			<th width=2%></th>
		</tr>	
	</thead>
	<tbody>
		<?
		$arr_app_exec_type = array('I' => '설치형', 'E' => '실행형', 'S' => '검색설치형', 'R' => '리뷰형');
		while ($row = mysql_fetch_assoc($result)) {
		?>
			<tr>
				<td><?=$row['app_title']?></td>
				<td><?=$row['app_key']?></td>
				<td><?=$arr_app_exec_type[$row['app_exec_type']]?></td>
				<td><?=$row['app_packageid']?></td>
				<td><span><?=admin_to_datetime($row['reg_date'])?></span></td>
				<td><a href='#' onclick='<?=$js_page_id?>.action.on_btn_delete_app("<?=$row['app_title']?>", "<?=$row['app_key']?>")' data-role='button' data-mini='true' data-inlnie='true'>삭제</a></td>
			</tr>
		<?
		}
		?>
	</tbody>
	</table>

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
			on_btn_modifyguest: function()
			{

				var ar_param = {
					'guestid' : _$("#guest-id").val(),
					'guestpw' : _$("#guest-pw").val(),
					'guestname' : _$("#guest-name").val(),
					'appkey' : _$("#guest-appkey").val(),
					'company' : _$("#guest-company").val(),
					'telno' : _$("#guest-telno").val(),
					'memo' : _$("#guest-memo").val()
				};
				util.post(get_ajax_url('admin-guest-modify'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '수정되었습니다.', function() {
							window.location.reload();
						});	
					} else util.Alert(js_data['msg']);
				});
			},
			on_btn_delete_app: function(apptitle, appkey)
			{
				var ar_param = {
					'guestid' : '<?=$guest_id?>',
					'appkey' : appkey,
				};
				util.MessageBox('알림', '"' + apptitle + '" 를 삭제하겠습니까 ?', function(sel) {
					if (sel == 1) {

						util.post(get_ajax_url('admin-guest-del-appkey'), ar_param, function(sz_data) {
							var js_data = util.to_json(sz_data);
							if (js_data['result']) {
								util.Alert('알림', '삭제되었습니다.', function() {
									window.location.reload();
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
		
		util.set_event_for_input_phoneno(_$("#guest-telno"));
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
	
		
		
		