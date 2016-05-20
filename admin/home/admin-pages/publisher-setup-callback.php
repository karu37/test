<?
	$pcode = $_REQUEST['pcode'];
	$db_pcode = mysql_real_escape_string($pcode);
	
	$sql = "SELECT * FROM al_publisher_t WHERE pcode = '{$db_pcode}'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
?>
<div>
	<t3 style='height:40px; padding-top:20px'>연동 설정</t3>
	<hr>
	<table class='app-info' cellpadding=0 cellspacing=0>
	<style>
		.app-info	tr > * 			{border-bottom: 1px solid #ddd}
		.app-info	td				{padding: 5px 10px; text-align: left}
		.app-info	td:first-child	{padding-top: 10px; line-height:23px; vertical-align:top}
		.app-info	td > div		{text-align: left}	/* 2번째 컬럼의 div내의 text-align을 left로 */
		.app-info	.should			{background-color:#ffd}
	</style>
	<col width=100px valign=top></col>
	<col width=500px valign=top align=left></col>
	<tr class='should'>
		<th>업체 아이디</th>
		<td>
			<p3><?=$row['name']?></p3>
		</td>
	</tr>
	<tr class='should'>
		<th>Reward 환율</th>
		<td>
			<div style='width: 80px;float:left'>
				<input type="number" id="txt-reward-percent" name="txt-reward-percent" placeholder='100' value='<?=$row['reward_percent']?>' />
			</div>
			<div style='width: 300px;float:left; padding-top: 7px; padding-left: 10px'>
				%
			</div>
			<div style='clear:both'></div>
			* <b>50</b>% 이면, <b>price</b>(매체가격)이 <b>250</b>일 때, <b>reward</b>(사용자 보상 금액) <b>125</b>으로 제공됨
		</td>
	</tr>
	<tr style='display:none'>
		<th>Reward 단위</th>
		<td>
			<div style='width: 80px'>
				<input type="text" id="txt-reward-unit" name="txt-reward-unit" placeholder='원' value='<?=$row['reward_unit']?>' />
			</div>
			* 충전소에서 금액 표시할 때 사용되는 단위
		</td>
	</tr>
	<tr class='should'>
		<th valign=top><div style='padding-top:15px'>콜백 URL설정</div></th>
		<td>
			<input type="text" id="txt-callback-url" name="txt-callback-url" placeholder='http:// 로 시작하는 매체사 URL' value='<?=$row['callback_url']?>' />
			* 광고 적립이 완료되었을 때, A-Line이 호출할 매체사 URL<br>
				&nbsp; 예) http://www.partner.com/callback/aline.jsp
<pre>
<hr>
<b>콜백 URL의 POST 전달 파라미터</b>
	
	ad={광고 키}
	uid={매체사 사용자 ID 또는 사용자 구별자 - 최대 32자}
	userdata={매체사 사용 데이터 - 최대 1024 자}
	reward={사용자 보상 금액}
	price={매체사 제공 금액}
	unique={적립단위의 고유 키}
	
	* uid, userdata 는 광고참여 요청시 설정한 값을 그대로 전달함.
	* unique 는 적립에 대한 고유한 값으로 이미 받은 적이 있으면, 중복으로 처리해야 함.
</pre>
		</td>
	</tr>
	</table>
	<div style='padding-top: 10px;'>
		<a href='#' onclick='<?=$js_page_id?>.action.on_btn_set_callbackinfo()' data-role='button' data-theme='b' data-inline='true' data-mini='true' >등록하기</a>
	</div>
	
	<div class='app-info'  style='background-color:#fff; width:600px; padding-top: 30px'>
		<b3>콜백 호출 테스트</b3>
		<hr>
		<br>
		<table border=0 cellpadding=0 cellspacing=0 width=100%>
			<tr><td>광고키</td><td><div style='width:200px'><input type=text id='test-ad' name='test-ad' value='' /></div></td>
			<tr><td>제공 금액</td><td><div style='width:200px'><input type=text id='test-price' name='test-price' value='' /></div></td>
			<tr><td>uid</td><td><div style='width:200px'><input type=text id='test-uid' name='test-uid value='' /></div></td>
 			<tr><td>userdata</td><td width=500px><input type=text id='test-userdata' name='test-userdata value='' /></td>
 			<tr><td>unique</td><td><div style='width:200px'><input type=text id='test-unique' name='test-unique' value='' /></div></td>
			</tr>
		</table>
		
		<div style='background: #eff; padding:10px'>		
 			<b>콜백 URL</b>
 			<div id='sample-url' style='border: 1px solid #ddd; min-height:20px; color: darkblue; padding: 5px'></div>
 			<br>
 			<b>콜백 POST 파라미터</b>
 			<div id='sample-postparam' style='border: 1px solid #ddd; min-height:80px; color: darkblue; padding: 5px'></div>
 			<div style='padding-top:10px'>
	 			<a href='#' onclick='<?=$js_page_id?>.action.on_btn_send_sampleurl()' data-role='button' data-mini='true' data-inline='true'>콜백 URL 호출 테스트</a>
	 		</div>
	 		<div id='url-result-area' init-display='hide'>
	 			<br>
	 			<b>요청 결과</b>
	 			<div id='sample-result' style='border: 1px solid #ddd; min-height:100px; color: darkblue; padding: 5px'></div>
	 		</div>
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

				// restore data from localstorage				
				_$("#test-ad").val( util.ifempty(localStorage.getItem('test-ad'), 'TEST-000000000000000000001') );
				_$("#test-price").val( util.ifempty(localStorage.getItem('test-price'), '250') );
				_$("#test-uid").val( util.ifempty(localStorage.getItem('test-uid'), 'user1234') );
				_$("#test-userdata").val( util.ifempty(localStorage.getItem('test-userdata'), 'CUSTOM-USER-DATA') );
				_$("#test-unique").val( util.ifempty(localStorage.getItem('test-unique'), 'UNIQUE-000000000000001') );
				
				setTimeout( function() { page.action.on_update_callbacktest_url(); page.action.on_update_callbacktest_param(); } , 100);
			},
			// 보상율과 CallbackURL을 서버에 저장한다.
			on_btn_set_callbackinfo: function()
			{
				var ar_param = {
					'pcode' : "<?=$pcode?>",
					'callbackurl' : $("#txt-callback-url").val(),
					'rewardpercent' : _$("#txt-reward-percent").val()
				};
				
				if (!ar_param.callbackurl.match(/^https?\:\/\/[a-z_\-\.]+\/.*$/)) {
					alert("URL 형식이 올바르지 않습니다.");
					return;	
				}
								
				util.post(get_ajax_url('admin-publisher-set-callback'), ar_param, function(sz_data) {
					var js_data = util.to_json(sz_data);
					if (js_data['result']) {
						util.Alert('알림', '등록되었습니다.', function() {
							window.location.reload();
						});	
					} else util.Alert(js_data['msg']);
				});
			},
			// input으로부터 Post Parameter를 구성한다.
			gen_post_parameter: function() {
				var param = {ad: $("#test-ad").val(), 
							price: $("#test-price").val(), 
							reward: Math.floor($("#test-price").val() * util.ifempty($("#txt-reward-percent").val(),100) / 100),
							uid: $("#test-uid").val(),
							userdata: $("#test-userdata").val(),
							unique: $("#test-unique").val()};
				return param;
			},
			// CallbackURL이 변경되면 정보를 갱신한다.
			on_update_callbacktest_url: function() {
				_$("#sample-url").html( $("#txt-callback-url").val() );
			},
			// input 값이 변경되면 새로운 Post값으로 갱신한다.
			on_update_callbacktest_param: function() {
						
				var param = page.action.gen_post_parameter();
				_$("#sample-postparam").html( util.json_to_urlparam(param).replace(/\&/g, "&<br>") );
				
				// store data to localstorage
				localStorage.setItem('test-ad', _$("#test-ad").val());
				localStorage.setItem('test-price', _$("#test-price").val());
				localStorage.setItem('test-uid', _$("#test-uid").val());
				localStorage.setItem('test-userdata', _$("#test-userdata").val());
				localStorage.setItem('test-unique', _$("#test-unique").val());
			},
			// 전송 테스트를 호출한다.
			on_btn_send_sampleurl: function() {
				
				var url = $("#txt-callback-url").val();
				var param = page.action.gen_post_parameter();
				if (!url.match(/^https?\:\/\/[a-z_.-]+\/.*$/)) {
					alert("URL 형식이 올바르지 않습니다.");
					return;	
				}
				
				// alert(url + '?' + util.json_to_urlparam(param));
				util.post(url, param, function(result) {
					$("#url-result-area").show();
					$("#sample-result").html(result);
				}, function(err) {
					alert(err);
				});
			},
		},
	};		
	
	function setEvents() {
		$(document).on("pageinit", function(){page.action.initialize();} );
		
		$("#txt-callback-url").on('change', page.action.on_update_callbacktest_url);
		$("#txt-reward-percent").on('change', page.action.on_update_callbacktest_param);
		
		$("#test-ad").on('change', page.action.on_update_callbacktest_param);
		$("#test-price").on('change', page.action.on_update_callbacktest_param);
		$("#test-uid").on('change', page.action.on_update_callbacktest_param);
		$("#test-userdata").on('change', page.action.on_update_callbacktest_param);
		$("#test-unique").on('change', page.action.on_update_callbacktest_param);
	}		

	setEvents(); // Event Attaching		
	return page;
}();

</script>
	
		
		
		