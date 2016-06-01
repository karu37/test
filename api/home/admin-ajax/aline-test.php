<?
	// 요청 URL (pcode = aline)
	//	http://api.aline-soft.kr/ajax-request.php?id=get-list&pcode=aline&is_web=Y
	
	if (!$_REQUEST['pcode']) $_REQUEST['pcode'] = 'aline';
	
	$pub_mactive = get_publisher_info();
	if (!$pub_mactive || $pub_mactive == 'N' || $pub_mactive == 'D') return_die('N', array('code'=>'-100', 'type'=>'E-REQUEST'), '유효하지 않은 매체코드입니다.');

	$pcode = $_REQUEST['pcode'];
	$db_pcode = mysql_real_escape_string($pcode);

	$ar_time = mysql_get_time($conn);
	$sql = get_query_app_list($pcode, $ar_time, false, ($pub_mactive == 'T'), $conn);
	$result = mysql_query($sql, $conn);
	
?>	
<head>
	<title>A-Line 테스트 페이지</title>
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
	<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>	
	<style>
		* {font-size:12px; }
		td {padding: 0 2px}
		.mini-btn 	{padding:2px; font-size:12px}
	</style>
</head>
<body>
	<!-- 
	<?=$sql?>
	-->
	<div style='padding:10px'>
		* A-Line 관리자 : <a href='http://admin.aline-soft.kr/login.php' target='_blank'>새창으로 관리자 이동</a>
	</div>
	<table width=100% border=0 cellpadding=0 cellspacing=0>
		<tr>
			<form onsubmit='return page.on_btn_set_publisher()'>
				<td width=150px>
					Publisher Code : 
				</td>
				<td>
					<div style='float:left'>
						<input type="text" name="pcode" id="pcode" value="<?=$_REQUEST['pcode']?>" style='display: block; width: 100%' />
					</div>
					<a href='#' onclick='page.on_btn_set_publisher()' data-role='button' data-mini='true' data-inline='true' data-theme='b'>변경</a>
				</td>
			</form>
			
		</tr>
		<tr>
			<td colspan=2>
				
				<br>
				<table border=1 cellpadding=0 cellspacing=0>
				<tr>
					<th>Idx</th>
					<th>아이콘</th>
					<th>제목/광고키</th>
					<th>타입</th>
					<th>M가격</th>
					<th>P가격</th>
					<th>나이</th>
					<th>성별</th>
					<th>시간</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
				<?	
					$arr_inactive = array();
					$i = 0;
					while ($row = mysql_fetch_assoc($result)) {
						$i++;
						// exec_tot_max_cnt 가 초과한 대상은 is_active ==> "N" 으로 변경한다.
						// exec_edate 가 지난 경우에도 is_active ==> "N" 으로 변경
						if ($row['tot_not_complished'] != 'Y' || $row['edate_not_expired'] != 'Y') {
echo "<!-- \n";
// var_dump($row);							
echo "\n-->";
							$arr_inactive[] = "'" . $row['app_key'] . "'";
							continue;
						}
			
						// 표시 차단 대상 (위에서 필터링 안된 대상)
						// 금액이 0 인 경우
						if ( intval($row['publisher_fee']) <= 0 ) continue;	
						
						echo "<tr>";
						echo "<td>{$i}</td>\n";
						echo "<td><img src='{$row['app_iconurl']}' width=40px /></td>\n";
						echo "<td>{$row['app_title']}<br>{$row['app_key']}</td>\n";
						echo "<td>{$row['app_exec_type']}</td>\n";
						echo "<td>{$row['app_merchant_fee']}</td>\n";
						echo "<td>{$row['publisher_fee']}</td>\n";
						echo "<td>{$row['app_agefrom']} ~ {$row['app_ageto']}</td>\n";
						echo "<td>{$row['app_gender']}</td>\n";
						echo "<td>{$row['exec_stime']} ~ {$row['exec_etime']}</td>\n";
						echo "<td>{$row['edate_not_expired']}, {$row['tot_not_complished']}</td>\n";
						?>
						<td>
							<a class='mini-btn' href='#' onclick="page.on_btn_start('<?=$pcode?>', $('#adid').val(), '<?=$row['app_key']?>', 'UID:'+$('#adid').val(), 'USERDATA:'+$('#adid').val())" data-role='button' data-mini='true'>광고 참여</a>
						</td>
						<td>
							<? if ($row['app_exec_type'] == 'I') { ?>
							<a class='mini-btn' href='#' onclick="page.on_btn_done('<?=$pcode?>', $('#adid').val(), '<?=$row['app_key']?>')" data-role='button' data-mini='true'>참여 확인</a>
							<? } ?>
						</td>
						<td>
							<a class='mini-btn' href='#' onclick="page.on_btn_undone('<?=$pcode?>', $('#adid').val(), '<?=$row['app_key']?>')" data-role='button' data-mini='true'>적립완료 취소</a>
						</td>
						<?
						echo "<tr>";
						
					}
				
					// Expire되거나, 모두 달성된 광고 is_active ==> 'N' 시키기
					if (count($arr_inactive) > 0) {
						$sql = "UPDATE al_app_t SET is_active = 'N' WHERE is_active <> 'N' AND app_key IN ( " . implode(",", $arr_inactive) . ")";
						mysql_executE($sql, $conn);
					}
				?>	
				</table>					
				<br>	
				<a class='mini-btn' href='#' onclick="page.on_btn_deleteinfo('<?=$pcode?>')" data-role='button' data-theme='b' data-mini='true' data-inline='true'>적립 기록 모두 초기화 (수행 수는 전체 초기화됨)</a>
				
			</td>
		</tr>
		<tr>
			<td>
				테스트 사용자 ADID : 
			</td>
			<td>
				<input type="text" name="adid" id="adid" value=""  style='display: block; width: 100%'/>
			</td>
		</tr>
	</table>
	<iframe id='status' src='about:blank' style='width:100%; height:1000px' /></iframe>
<script>

var basic_util = {
	request: function(sz_url, callback_func, error_func) {
		console.log('[__global.php] request: ' + sz_url);
		$.ajax({
			type:"GET",  
			url:sz_url,      
			success:function(sz_data){callback_func(sz_data);},   
			error:function(e){if (error_func) error_func(e.statusText); else alert(e.statusText + "\n" + sz_url);}  
		});		
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
	var_dump: function(obj) {
		var out = '';
    	for (var i in obj) {
        	out += i + ": " + obj[i] + "\n";
        }
    	return out;
	},
};
window.var_dump = basic_util.var_dump;
	
var page = function(){
	
	var fn = {
		init: function() {
			if (!localStorage.getItem('adid')) localStorage.setItem('adid', '0000000000000000-0000-0000-0000-0001');
			$("#adid").on('change', function(){
				localStorage.setItem('adid', $("#adid").val());
				$("#status").attr('src', "http://api.aline-soft.kr/ajax-request.php?id=aline-test-status&pcode=<?=$pcode?>&adid=" + $("#adid").val());
			});
			$("#adid").val(localStorage.getItem('adid'));
			
			$("#status").attr('src', "http://api.aline-soft.kr/ajax-request.php?id=aline-test-status&pcode=<?=$pcode?>&adid=" + localStorage.getItem('adid'));
		},
		
		on_btn_start: function(pcode, uadid, ad, uid, userdata) {
			// http://api.aline-soft.kr/ajax-request.php?id=get-join&pcode=aline&os=A&ad=LOC2&adid=0123456789012345-6789-0123-4567-8901&ip=127.0.0.1&uid=heartman@gmail.com&userdata=USERDATA
			var ar_param = {id: 'get-join',
							'pcode': pcode,
							'ad': ad,
							'adid': uadid,
							'ip': '127.0.0.1',
							'account': '1234567890123456789012345678901234567890',
							'imei': '123456789012345',
							'uid' : uid,
							'userdata': userdata};

			alert(var_dump(ar_param));
			basic_util.request('http://api.aline-soft.kr/ajax-request.php?' + basic_util.json_to_urlparam(ar_param), function(sz_data) {
				try {
					var js_data = JSON.parse(sz_data);
					if (js_data['result'] == 'Y') {
						alert("요청 성공\n\nUrl : " + js_data['url']);
					} else {
						alert("요청 실패\n\n code : " + js_data['code'] + "\n msg : " + js_data['msg']);
					}
				} catch(e) {alert(sz_data);}
					
				$("#status").attr('src', "http://api.aline-soft.kr/ajax-request.php?id=aline-test-status&pcode=<?=$pcode?>&adid=" + uadid);
			});
		},
		
		on_btn_done: function(pcode, uadid, ad) {
			// http://api.aline-soft.kr/ajax-request.php?id=get-join&pcode=aline&os=A&ad=LOC2&adid=0123456789012345-6789-0123-4567-8901&ip=127.0.0.1&uid=heartman@gmail.com&userdata=USERDATA
			var ar_param = {id: 'get-done',
							'pcode': pcode,
							'ad': ad,
							'adid': uadid};
							
			alert(var_dump(ar_param));
			basic_util.request('http://api.aline-soft.kr/ajax-request.php?' + basic_util.json_to_urlparam(ar_param), function(sz_data) {
				try {
					var js_data = JSON.parse(sz_data);
					if (js_data['result'] == 'Y') {
						alert("요청 성공");
					} else {
						alert("요청 실패\n\n code : " + js_data['code'] + "\n msg : " + js_data['msg']);
					}
				} catch(e) {alert(sz_data);}

				$("#status").attr('src', "http://api.aline-soft.kr/ajax-request.php?id=aline-test-status&pcode=<?=$pcode?>&adid=" + uadid);
					
			});
		},		
		on_btn_undone: function(pcode, uadid, ad) {
			// http://api.aline-soft.kr/ajax-request.php?id=get-join&pcode=aline&os=A&ad=LOC2&adid=0123456789012345-6789-0123-4567-8901&ip=127.0.0.1&uid=heartman@gmail.com&userdata=USERDATA
			var ar_param = {id: 'aline-test-status-undone',
							'pcode': pcode,
							'appkey': ad,
							'adid': uadid};
							
			alert(var_dump(ar_param));
			basic_util.request('http://api.aline-soft.kr/ajax-request.php?' + basic_util.json_to_urlparam(ar_param), function(sz_data) {
				try {
					var js_data = JSON.parse(sz_data);
					if (js_data['result'] == 'Y') {
						alert("요청 성공");
					} else {
						alert("요청 실패\n\n code : " + js_data['code'] + "\n msg : " + js_data['msg']);
					}
				} catch(e) {alert(sz_data);}

				$("#status").attr('src', "http://api.aline-soft.kr/ajax-request.php?id=aline-test-status&pcode=<?=$pcode?>&adid=" + uadid);
					
			});
		},	
		on_btn_deleteinfo: function(pcode, uadid, ad) {
			// http://api.aline-soft.kr/ajax-request.php?id=get-join&pcode=aline&os=A&ad=LOC2&adid=0123456789012345-6789-0123-4567-8901&ip=127.0.0.1&uid=heartman@gmail.com&userdata=USERDATA
			var ar_param = {id: 'aline-test-reset',
							'pcode': pcode};
							
			alert(var_dump(ar_param));
			basic_util.request('http://api.aline-soft.kr/ajax-request.php?' + basic_util.json_to_urlparam(ar_param), function(sz_data) {
				try {
					var js_data = JSON.parse(sz_data);
					if (js_data['result'] == 'Y') {
						alert("요청 성공");
					} else {
						alert("요청 실패\n\n code : " + js_data['code'] + "\n msg : " + js_data['msg']);
					}
				} catch(e) {alert(sz_data);}

				$("#status").attr('src', "http://api.aline-soft.kr/ajax-request.php?id=aline-test-status&pcode=<?=$pcode?>&adid=" + uadid);
					
			});
		},	
		on_btn_set_publisher: function() {
			window.location.href="?id=aline-test&pcode=" + $("#pcode").val();
			return false;
		},
	};
	
	fn.init();
	return fn;
}();

</script>

</body>
