<?	
/*
	이 후부터 APPANG 으로 연동 작업을 함.
*/
$g_appang['lib'] = 'APPANG';				// 해당 광고에 대한 처리 routine 명 (이 파일)
$g_appang['mcode'] = '31e9c6g39afd04d1';			// ALINE의 mcode 값
$g_appang['aline-code'] = "ecb32e0e17df83a5c79732436542dd02";	// Merchant의 매체 연동 코드
$g_appang['appkey_prefix'] = "apg";		// 앱키 시작 문자열 ( + md5한 mkey 결과 뒤에 붙임 )

$g_appang['unique_prefix'] = "apg";		// 적립 결과에 Unique 키
$g_appang['timeout_sec'] = 60;				// 시작 / 적립 요청시의 Timeout 초

$g_appang['list'] = "http://www.appang.kr/nas/api/list.json.asp";
$g_appang['start'] = "http://www.appang.kr/nas/api/join.json.asp";
$g_appang['done'] = "http://www.appang.kr/nas/api/complete.json.asp";

// ---------------------------------------
// 처음 가져온 광고에 대한 관리자 차단 여부 (실제 광고의 경우 Y로 해놓고 개발상태이므로 노출되지는 않으므로 문제 안됨)
// 필요할 경우에만 특별히 사용 (N => Y)
$g_appang['is_mactive'] = 'Y';
// ---------------------------------------

// http://api.aline-soft.kr/ajax-request.php?id=_partner_appang&dev=1
if ($_REQUEST['dev'] == 1 && $_REQUEST['id'] == "_partner_appang") {
	update_appang_app(true, $conn);
	exit;
}	

function update_appang_app($force_reload, $conn) 
{
	global $g_appang;

	// g_appang의 lib와 mcode
	$db_lib = mysql_real_escape_string($g_appang['lib']);
	$db_mcode = mysql_real_escape_string($g_appang['mcode']);

	//////////////////////////////////////////////////////////////////////////
	// 기본 추가에 대한 is_mactive 설정 : 
	//////////////////////////////////////////////////////////////////////////
	$flag_is_mactive = $g_appang['is_mactive'];
	//////////////////////////////////////////////////////////////////////////
	
	// 자동 Reload 여부 체크
	if (!$force_reload) 
	{
		try {
			// 1 분 주기로 갱신 대상 및 권한 가져오기
			begin_trans($conn);
			$sql = "SELECT id, IF(up_date is null OR up_date < date_sub(NOW(), interval 10 minute), 'Y', 'N') as 'flag_update' FROM al_merchant_update_t WHERE mcode = '{$db_mcode}' FOR UDPATE;";
			$result = mysql_query($sql, $conn);
			$row = @mysql_fetch_assoc($result);
			if (!$row['id']) {
				// 대상 자체가 없는 경우 자동 필드 추가
				mysql_execute("INSERT al_merchant_update_t (mcode, up_date) VALUES ('{$db_mcode}', '2000-01-01')", $conn);
			} else {
				// 대상이 있는데 업데이트 시간이 1분 경과 안한 경우 ==> 취소
				if ($row['flag_update'] == 'N') {
					rollback($conn);
					return false;	// no need to update
				}
			}
	
			mysql_query("UPDATE al_merchant_update_t SET up_date=NOW() WHERE mcode = '{$db_mcode}'", $conn);
			commit($conn);
		}
		catch(Exception $e) 
		{
			if ($_REQUEST['dev'] == 1) echo $e->getMessage();
			rollback($conn);
			return false;
		}			
	}
	else 
	{
		// 갱신시간을 update 
		mysql_query("UPDATE al_merchant_update_t SET up_date=NOW() WHERE mcode = '{$db_mcode}'", $conn);
	}

	// 제공되는 숫자 금액에 대한 merchant_fee (우리쪽의 실제 지급되는 원)의 변환 율
	$sql = "SELECT exchange_fee_rate FROM al_merchant_t WHERE mcode = '{$db_mcode}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	$exchange_fee_rate = intval(ifempty($row['exchange_fee_rate'], 100));
	
	// ----------------------------------------------------------------------
	// 요청 URL생성하기
	$url_param = array();
	$url_param['ap'] = $g_appang['aline-code'];
	// ----------------------------------------------------------------------
	
	$campain_url = $g_appang['list'] . "?" . http_build_query($url_param);
	if ($_REQUEST['dev'] == 1) {
		echo "URL : " . $campain_url . "<br>\n";
		echo "<table border=1 cellpadding=0 cellspacing=0><style>* {font-size:11px} .content {max-width:300px; max-height:13px; overflow:hidden} .desc {display:inline-block; color:lightblue; width:300px; max-height:15px; overflow: hidden}</style>";
	}
	
	// 광고 목록 요청
	$start_tm = get_timestamp();
	$sz_camp_data = @file_get_contents($campain_url);
	if (!$sz_camp_data) return false;
	
	$js_camp_data = json_decode($sz_camp_data, true);
	if ($js_camp_data['result'] != 0) return false;
	
	$n_item_cnt = count($js_camp_data['items']);

	// 현재 OHC 의 active되어 있는 항목의 flag_updating ==> "Y" (나중에 갱신되지 않은 것은 모두 inactive 로 설정함 (Lock 걸지 않고 함)
	if ($n_item_cnt > 0) {
		$sql = "UPDATE al_app_t SET flag_updating = 'Y' WHERE mcode = '{$db_mcode}' AND is_active = 'Y'";
		mysql_execute($sql, $conn);
	}

	// 가져온 항목들을 모두 추가/갱신한다.
	for ($i=0; $i < $n_item_cnt; $i++) {
		$item = $js_camp_data['items'][$i];
		
		/////////////////////////////////////////
		// 광고 상태가 start가 아니면 스킵		
		/*
		## APPANG에는 중지된 광고는 제공되지 않음
		if ($item['ads_open'] != 'start') {
			if ($_REQUEST['dev'] == 1) {
				echo "{$item['ads_open']} ======> {$item['title']}, {$item['ads']}<br>\n";
			}
			continue;
		}
		*/
		
		//## app_key & m_key 
		$app_key = $g_appang['appkey_prefix'] . md5($item['key']);
		$m_key = $item['key'];

		//-------------------------------------
		
		//## app_title
		$app_title = $item['title'];
		
		//## app_content (앱 소개내용)
		$app_content = $item['intro']; // $item['details'];
		
		//## app_iconurl
		$app_iconurl = $item['icon'];

		//## app_packageid
		$app_packageid = ($item['app'] ? $item['app']['package'] : "");
		
		//## app_scheme (아직 iOS 대상 처리 안 함)
		// $app_scheme = ($item['app'] ? $item['app']['scheme'] : "");
		
		//## app_execurl
		$app_execurl = "";
		
		//-------------------------------------

		//## app_gender
		$arr_item_sex = array("" => "", "m" => "M", "f" => "F");
		$app_gender = $arr_item_sex[($item['target'] ? $item['target']['target_sex'] : "")];
		
		//## app_agefrom, app_ageto
		$app_agefrom = "";
		$app_ageto = "";
		if ($item['target'] && intval($item['target']['age_from']) > 0 && intval($item['target']['age_to']) > intval($item['target']['age_from'])) {
			$app_agefrom = $item['target']['age_from'];
			$app_ageto = $item['target']['age_to'];
		}

		//## app_exec_type		
		$arr_exec_type = array(11 => 'I', 12 => 'E');
		if ($arr_exec_type[ $item['type'] ]) {
			$app_exec_type = $arr_exec_type[ $item['type'] ];
			
			// packageid 에 . 이 없으면 수행형으로 변경함.
			if ($app_packageid && strpos($app_packageid, '.') === false) $app_exec_type = 'W';
		}
		else
			$app_exec_type = 'W';		// 설치 / 실행형 외는 모두 참여형

		//-------------------------------------

		//## app_exec_desc (사용자 적립 수행 방법)
		$app_exec_desc = $item['description'];	// 적립 수행 방법..
		
		//## app_platform (수행할 Android / iOS / 전체 OS 기기 플롯폼 대상)
		$app_platform = "W";					// 기본 Web형 플랫폼 제한 없음
		if ($item['app'] && $item['app']['market']) 
		{
			if ($item['app']['market'] == '1') continue;					// 앱스토어 인경우엔 광고를 추가하지 않음 (통과)
			else if ($item['app']['market'] == '2') $app_platform = 'A';	// 안드로이드 마켓인 경우
			else if ($item['app']['market'] == '3') $app_platform = 'A';	// 안드로이드 마켓인 경우 (원스토어 ~ 내에버스토어는 모두 구글 Android 플랫폼)
			else if ($item['app']['market'] == '4') $app_platform = 'A';	// 안드로이드 마켓인 경우
			else if ($item['app']['market'] == '5') $app_platform = 'A';	// 안드로이드 마켓인 경우
			else if ($item['app']['market'] == '6') $app_platform = 'A';	// 안드로이드 마켓인 경우
		}
		
		//## app_market (수행시 이동할 마켓)
		$arr_markets = explode('|', ($item['app'] ? $item['app']['market'] : ""));
		$app_market = "P";						// 기본 PLAYSTORE
		if ($item['app'] && $item['app']['market']) 
		{
			if ($item['app']['market'] == '1') continue;					// 앱스토어 인경우엔 광고를 추가하지 않음 (통과)
			else if ($item['app']['market'] == '2') $app_market = "P";			// 구글스토어
			else if ($item['app']['market'] == '3') $app_market = "O";			// 티스토어 ~ 네이버 스토어 까지 모두 OneStore로 설정함.
			else if ($item['app']['market'] == '4') $app_market = "O";
			else if ($item['app']['market'] == '5') $app_market = "O";
			else if ($item['app']['market'] == '6') $app_market = "O";
		}
		
		//## app_merchant_fee
		$app_merchant_fee = intval($item['price'] * $exchange_fee_rate / 100);

		//-------------------------------------

		//## exec_stime
		$app_exec_stime = "";
		
		//## exec_etime
		$app_exec_etime = "";
		
		//## exec_edate
		$app_exec_edate = "";

		/////////////////////////////////////////

		// DB 에 추가한다.
		$db_app_key = mysql_real_escape_string($app_key);
		$db_m_key = mysql_real_escape_string($m_key);
		
		$db_app_title = mysql_real_escape_string($app_title);
		$db_app_content = mysql_real_escape_string($app_content);
		$db_app_iconurl = mysql_real_escape_string($app_iconurl);
		$db_app_packageid = mysql_real_escape_string($app_packageid);
		$db_app_execurl = mysql_real_escape_string($app_execurl);

		$db_app_gender = mysql_real_escape_string($app_gender);
		$db_app_agefrom = mysql_real_escape_string($app_agefrom);
		$db_app_ageto = mysql_real_escape_string($app_ageto);
		$db_app_exec_type = mysql_real_escape_string($app_exec_type);
		
		$db_app_exec_desc = mysql_real_escape_string($app_exec_desc);
		$db_app_platform = mysql_real_escape_string($app_platform);
		$db_app_market = mysql_real_escape_string($app_market);
		$db_app_merchant_fee = mysql_real_escape_string($app_merchant_fee);
		
		$db_app_exec_stime = mysql_real_escape_string($app_exec_stime);
		$db_app_exec_etime = mysql_real_escape_string($app_exec_etime);
		$db_app_exec_edate = mysql_real_escape_string($app_exec_edate);

		/////////////////////////////////////////
		
		$sql = "SELECT id, mkey, flag_keep_modify, is_active, error_stopped_time, last_deactive_time FROM al_app_t WHERE app_key = '{$db_app_key}'";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row['id']) 
		{
			// app_key가 있는데 mkey가 다른 경우 ==> 통과
			if ($row['mkey'] != $m_key) continue;
			$app_id = $row['id'];
			if ($row['last_deactive_time'] && $row['error_stopped_time'] != $row['last_deactive_time'] && 
				$row['is_active'] != 'Y' && in_array($app_exec_type, array('I', 'E')) ) {
				$g_appang['reactive-added'] ++;
				$g_appang['reactive-added-title'][] = "{$app_exec_type} {$app_title}";
			}
			
			if ($row['flag_keep_modify'] == 'Y') 
			{
				$sql = "UPDATE al_app_t 
						SET
							app_iconurl = '{$db_app_iconurl}' , 
							app_merchant_fee = '{$db_app_merchant_fee}' , 
							app_tag_price = '{$db_app_merchant_fee}' , 
							
							exec_stime = IF('{$db_app_exec_stime}'<>'', '{$db_app_exec_stime}', NULL) , 
							exec_etime = IF('{$db_app_exec_etime}'<>'', '{$db_app_exec_etime}', NULL) , 
							exec_edate = IF('{$db_app_exec_edate}'<>'', '{$db_app_exec_edate}', NULL) , 
							exec_day_max_cnt = NULL, 
							exec_tot_max_cnt = '100000000' , 
							
							last_active_time = IF(is_active = 'N', NOW(), last_active_time),
							is_active = 'Y' , 
							flag_updating = 'N',
							up_date = NOW()
						WHERE id = '{$app_id}';";
			}
			else 
			{
				$sql = "UPDATE al_app_t 
						SET
							app_title = '{$db_app_title}' , 
							app_content = '{$db_app_content}' , 
							app_iconurl = '{$db_app_iconurl}' , 
							app_packageid = '{$db_app_packageid}' , 
							app_execurl = '{$db_app_execurl}' , 
							
							app_gender = IF('{$db_app_gender}'<>'', '{$db_app_gender}', NULL) , 
							app_agefrom = IF('{$db_app_agefrom}'<>'', '{$db_app_agefrom}', NULL)  , 
							app_ageto = IF('{$db_app_ageto}'<>'', '{$db_app_ageto}', NULL) , 
							app_exec_type = '{$db_app_exec_type}' , 
							
							app_exec_desc = '{$db_app_exec_desc}' , 
							app_platform = '{$db_app_platform}',
							app_market = '{$db_app_market}', 
							app_merchant_fee = '{$db_app_merchant_fee}', 
							app_tag_price = '{$db_app_merchant_fee}', 
							
							exec_stime = IF('{$db_app_exec_stime}'<>'', '{$db_app_exec_stime}', NULL) , 
							exec_etime = IF('{$db_app_exec_etime}'<>'', '{$db_app_exec_etime}', NULL) , 
							exec_edate = IF('{$db_app_exec_edate}'<>'', '{$db_app_exec_edate}', NULL) , 
							exec_day_max_cnt = NULL , 
							exec_tot_max_cnt = '100000000' , 
							
							last_active_time = IF(is_active = 'N', NOW(), last_active_time),
							is_active = 'Y' , 
							flag_updating = 'N',
							up_date = NOW()
							
						WHERE id = '{$app_id}';";
			}
		} 
		else 
		{
			$sql = "INSERT INTO al_app_t (app_key, mkey, lib, mcode, 
						app_title, app_content, app_iconurl, app_packageid, app_execurl, 
						app_gender, app_agefrom, app_ageto, app_exec_type, 
						app_exec_desc, app_platform, app_market, app_merchant_fee, app_tag_price,
						exec_stime, exec_etime, exec_edate, exec_day_max_cnt, exec_tot_max_cnt, 
						is_active, is_mactive, last_active_time, flag_updating, up_date, reg_date) 
					VALUES (
						'{$db_app_key}', 
						'{$db_m_key}', 
						'{$db_lib}',
						'{$db_mcode}', 
						
						'{$db_app_title}', 
						'{$db_app_content}', 
						'{$db_app_iconurl}', 
						'{$db_app_packageid}', 
						'{$db_app_execurl}', 
						
						IF('{$db_app_gender}'<>'', '{$db_app_gender}', NULL) , 
						IF('{$db_app_agefrom}'<>'', '{$db_app_agefrom}', NULL) , 
						IF('{$db_app_ageto}'<>'', '{$db_app_ageto}', NULL) , 
						'{$db_app_exec_type}', 
						
						'{$db_app_exec_desc}', 
						'{$db_app_platform}',
						'{$db_app_market}', 
						'{$db_app_merchant_fee}', 
						'{$db_app_merchant_fee}', 
						
						IF('{$db_app_exec_stime}'<>'', '{$db_app_exec_stime}', NULL) , 
						IF('{$db_app_exec_etime}'<>'', '{$db_app_exec_stime}', NULL) , 
						IF('{$db_app_exec_edate}'<>'', '{$db_app_exec_edate}', NULL) , 
						NULL, 
						'100000000', 
						
						'Y', '{$flag_is_mactive}', NOW(), 'N', NOW(), NOW()
					);";
		}
		mysql_execute($sql, $conn);
		
		if ($_REQUEST['dev'] == 1) {
			echo "<tr><td><img src='{$app_iconurl}' width=25 /></td><td>{$app_title}</td><td><div class='content'>{$app_content}</div><div class='desc'>{$app_exec_desc}</div></td><td>{$m_key}</td><td>{$app_platform}</td><td>{$app_market}</td><td>{$app_packageid}</td><td>{$app_exec_type}</td><td>{$app_merchant_fee}</td><td>{$app_gender}</td><td>{$app_agefrom}~{$app_ageto}</td></tr>\n";
		}
	}
	
	if ($_REQUEST['dev'] == 1) {
		echo "</table>";
	}

	// 갱신되지 못한 나머지 항목은 모두 in_active 로 설정
	$sql = "UPDATE al_app_t SET last_deactive_time = IF(is_active='Y' AND is_mactive='Y',NOW(),last_deactive_time), is_active = 'N', up_date = NOW(), flag_updating = 'N' WHERE mcode = '{$db_mcode}' AND is_active = 'Y' AND flag_updating = 'Y'";
	mysql_execute($sql, $conn);

	return true;
}

/*
	$arr_data (array object) 키
		now			// 요청 수행 초기에 받아놓은 NOW() 값
		day			// 요청 수행 초기에 받아놓은 DATE() 값
		
		sid			// publisher사의 Sub 매체 구별값 varchar(64)
		uid			// publisher사의 사용자 구별값 varchar(64)
		userdata	// publisher사의 사용자 context text
		
		pcode
		ad
		ip
		adid
		imei
		model
		mf			// manufacturer
		brand
		account (not encoded)
		
		user_app_id			// al_user_app_t 의 id 값
		ad (array object) 	// al_app_t 테이블 정보
			

*/
function appang_request_start($app_key, &$arr_data, &$conn) 
{
	global $g_appang;
	
	$ar_app = $arr_data['ad'];
	$userapp_id = $arr_data['user_app_id'];
	
	// 광고 시작 URL 생성
	$url_param = array();
	$url_param['os'] = 'a';
	$url_param['ap'] = $g_appang['aline-code'];
	$url_param['a'] = $ar_app['mkey'];
	
	if ($arr_data['imei']) {
		$url_param['u'] = md5($arr_data['imei']);
		$url_param['u2'] = base64_encode($arr_data['imei']);
	} else {
		return array('result' => false, 'error' => 'NEP', 'error_msg' => '시작요청 정보가 부족합니다');
	}
	
	$url_param['ua'] = $arr_data['adid'];
	$url_param['ud'] = base64_encode(json_encode(array('aid' => $userapp_id)));
	$url_param['ajip'] = $arr_data['ip'];

	$url_param['d_model'] = $arr_data['model'];
	$url_param['d_manu'] = $arr_data['mf'];
	$campain_url = concat_url($g_appang['start'], http_build_query($url_param));
	
	// 광고 URL 요청하기
	$start_tm = get_timestamp();
	$ctx = stream_context_create( array( 'http'=> array('timeout' => $g_appang['timeout_sec']), 'https'=> array('timeout' => $g_appang['timeout_sec']) ) );
	
	// MYSQL을 닫은 후 요청이 완료되면 재 연결한다.
	mysql_close($conn);
	$result_data = @file_get_contents($campain_url, 0, $ctx);
	$conn = dbConn();
	
	make_action_log("user-start-appang", ($result_data?'Y':'N'), $arr_data['pcode'], $arr_data['adid'], $app_key,  null, get_timestamp() - $start_tm, $campain_url, null, $result_data, $conn);
	if (!$result_data) return array('result' => 'N', 'code' => '-1003');
	
	// 전달할 결과 조합
	$js_data = json_decode($result_data, true);
	$result_data = $js_data['result'];		// APPANG은 result 가 0 이 아니면 오류임
	$result_msg = "";

	// error-handling
	if (intval($result_data) != 0) {
		return appang_code_mapping($result_data, $result_msg);
	}
	$arr_data['result'] = 'Y';
	$arr_data['url'] = $js_data['url'];
	return array('result' => 'Y', 'code' => '1');
}

function appang_request_done($app_key, $arr_data, &$conn) 
{
	global $g_appang;

	$ar_app = $arr_data['ad'];
	$userapp_id = $arr_data['user_app_id'];

	// 설치형 이외에는 완료요청을 할 필요가 없음 ==> 무조건 성공으로 전달
	if ($ar_app['app_exec_type'] && $ar_app['app_exec_type'] != 'I') return array('result' => 'N', 'code' => '-109');

	// 광고 적립 URL생성
	$url_param = array();
	$url_param['os'] = 'a';
	$url_param['ap'] = $g_appang['aline-code'];
	$url_param['a'] = $ar_app['mkey'];
	
	if ($arr_data['imei']) {
		$url_param['u'] = md5($arr_data['imei']);
		$url_param['u2'] = base64_encode($arr_data['imei']);
	} else {
		return array('result' => false, 'error' => 'NEP', 'error_msg' => '시작요청 정보가 부족합니다');
	}
	
	$url_param['ua'] = $arr_data['adid'];
	$url_param['ajip'] = $arr_data['ip'];
	$campain_url = concat_url($g_appang['done'], http_build_query($url_param));

	// 광고 적립 요청 보내기
	$start_tm = get_timestamp();
	
	// MYSQL을 닫은 후 요청이 완료되면 재 연결한다.
	$ctx = stream_context_create( array( 'http'=> array('timeout' => $g_appang['timeout_sec']), 'https'=> array('timeout' => $g_appang['timeout_sec']) ) );
	
	// MYSQL을 닫은 후 요청이 완료되면 재 연결한다.
	mysql_close($conn);
	$result_data = @file_get_contents($campain_url, 0, $ctx);
	$conn = dbConn();

	make_action_log("user-done-appang", ($result_data?'Y':'N'), $arr_data['pcode'], $arr_data['adid'], $app_key, null, get_timestamp() - $start_tm, $g_appang['done'], $url_param, $result_data, $conn);
	if (!$result_data) return array('result' => 'N', 'code' => '-1003');

	// 전달할 결과 조합
	$js_data = json_decode($result_data, true);
	$result_data = $js_data['result'];
	$result_msg = "";
	
	// error-handling
	if (intval($result_data) != 0) {
		return appang_code_mapping($result_data, $result_msg);
	}
	return array('result' => 'Y', 'code' => '1');
}

function appang_code_mapping($code, $msg) {

	$ar_err_msg = appang_error_msg($code);
	return array('result' => 'N', 'code' => $ar_err_msg[0], 'msg' => $ar_err_msg[1] );
}

function appang_error_msg($code) {
	
	// 오류 설명
	$arr_error_msg[-99999] = array('-1004', '정확하지 않는 정보로 접속 되었습니다. 다시 참여해 주십시오.');
	$arr_error_msg[-99995] = array('-111', '사용하시는 기기로는 광고에 참여할 수 없습니다.');
	$arr_error_msg[-10001] = array('-104', '광고가 일시 중지 또는 종료되었습니다.');
	$arr_error_msg[-20001] = array('-106', '참여하셨거나, 이벤트 참여 대상자가 대상자가 아닙니다.');
	$arr_error_msg[-30001] = array('-119', '광고 설정 오류 (CB-URL 미등록).');
	$arr_error_msg[-40001] = array('-100', 'API 연동 권한 없음.');

	if ($arr_error_msg[$code]) return $arr_error_msg[$code];
	return array('-1005', '알 수 없는 오류');
	
}
?>