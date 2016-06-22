<?	
$g_sucom['lib'] = 'SUCOMM';				// 해당 광고에 대한 처리 routine 명 (이 파일)
$g_sucom['mcode'] = 'msucomm';			// 가져온 광고를 해당 mcode 밑으로 연결
$g_sucom['aline-code'] = "aline_json";	// sucomm 연동 매체 코드
$g_sucom['appkey_prefix'] = "suc";		// 앱키 시작 문자열 ( + md5한 mkey 결과 뒤에 붙임 )

$g_sucom['unique_prefix'] = "suc";		// 적립 결과에 Unique 키
$g_sucom['timeout_sec'] = 60;				// 시작 / 적립 요청시의 Timeout 초

$g_sucom['list'] = "http://api.flexplatform.net/v1/get_ad_list_v2.php";
$g_sucom['start'] = "http://api.flexplatform.net/v1/connect.php";
$g_sucom['done'] = "http://api.flexplatform.net/v1/return_result.php";
$g_sucom['img'] = "http://api.flexplatform.net/v1/get_ad_image.php";

// http://api.aline-soft.kr/ajax-request.php?id=_partner_sucomm&dev=1
if ($_REQUEST['dev'] == 1 && $_REQUEST['id'] == "_partner_sucomm") {
	update_sucomm_app(true, $conn);
	exit;
}	

function update_sucomm_app($force_reload, $conn) 
{
	global $g_sucom;

	// g_sucom의 lib와 mcode
	$db_lib = mysql_real_escape_string($g_sucom['lib']);
	$db_mcode = mysql_real_escape_string($g_sucom['mcode']);
	
	//////////////////////////////////////////////////////////////////////////
	// 기본 추가에 대한 is_delete 설정 : 
	//////////////////////////////////////////////////////////////////////////
	$flag_is_deleted = 'N';	// Aline 개발 완료후에 N 으로 변경
	//////////////////////////////////////////////////////////////////////////
	
	// 자동 Reload 여부 체크
	if (!$force_reload) 
	{
		// 1 분 주기로 갱신 대상 및 권한 가져오기
		begin_trans($conn);
		$sql = "SELECT id, IF(up_date is null OR up_date < date_sub(NOW(), interval 10 minute), 'Y', 'N') as 'flag_update' FROM merchant_update_t WHERE mcode = '{$db_mcode}' FOR UDPATE;";
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

		mysql_query("UPDATE merchant_update_t SET up_date=NOW() WHERE mcode = '{$db_mcode}'", $conn);
		commit($conn);
	}
	else 
	{
		// 갱신시간을 update 
		mysql_query("UPDATE merchant_update_t SET up_date=NOW() WHERE mcode = '{$db_mcode}'", $conn);
	}

	// 제공되는 숫자 금액에 대한 merchant_fee (우리쪽의 실제 지급되는 원)의 변환 율
	$sql = "SELECT exchange_fee_rate FROM al_merchant_t WHERE mcode = '{$db_mcode}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	$exchange_fee_rate = intval(ifempty($row['exchange_fee_rate'], 100));
	
	// ----------------------------------------------------------------------
	// 요청 URL생성하기
	$url_param = array();
	$url_param['media'] = $g_sucom['aline-code'];
	
	$url_param['checktime'] = time();
	$url_param['checkkey'] = md5($url_param['checktime'].$url_param['media']);
	// ----------------------------------------------------------------------
	
	$campain_url = $g_sucom['list'] . "?" . http_build_query($url_param);
	
	// 광고 목록 요청
	$start_tm = get_timestamp();
	$sz_camp_data = @file_get_contents($campain_url);
	if (intval($sz_camp_data)) return false;
	
	$js_camp_data = json_decode($sz_camp_data, true);
	$n_item_cnt = $js_camp_data['adcount'];

	// 현재 OHC 의 active되어 있는 항목의 flag_updating ==> "Y" (나중에 갱신되지 않은 것은 모두 inactive 로 설정함 (Lock 걸지 않고 함)
	if ($n_item_cnt > 0) {
		$sql = "UPDATE al_app_t SET flag_updating = 'Y' WHERE mcode = '{$db_mcode}' AND is_active = 'Y'";
		mysql_execute($sql, $conn);
	}

	// 가져온 항목들을 모두 추가/갱신한다.
	for ($i=0; $i < $n_item_cnt; $i++) {
		$item = $js_camp_data['list'][$i];
		
		/////////////////////////////////////////

		//## app_key & m_key 
		$app_key = $g_sucom['appkey_prefix'] . md5($item['ads']);
		$m_key = $item['ads'];

		//-------------------------------------
		
		//## app_title
		$app_title = $item['title'];
		
		//## app_content
		$app_content = $item['details'];
		
		//## app_iconurl
		$app_iconurl = $g_sucom['img'] . "?ads=" . $item['ads'];

		//## app_packageid
		$app_packageid = $item['ads_package'];
		
		//## app_execurl
		$app_execurl = "";
		
		//-------------------------------------

		//## app_gender
		$arr_item_sex = array("" => "", "mail" => "M", "female" => "F");
		$app_gender = $arr_item_sex[$item['sex']];
		
		//## app_agefrom, app_ageto
		$app_agefrom = "";
		$app_ageto = "";
		if (intval($item['age_from']) > 0 && intval($item['age_to']) > intval($item['age_from'])) {
			$app_agefrom = $item['age_from'];
			$app_ageto = $item['age_to'];
		}

		//## app_exec_type		
		$arr_exec_type = array('CPI' => 'I', 'CPE' => 'E', 'CPA' => 'W');
		$app_exec_type = ifempty($arr_exec_type[$item['ads_type']], 'W');	// 설치형 등...

		//-------------------------------------

		//## app_exec_desc
		$arr_exec_desc = array('CPI' => '설치형', 'CPE' => '실행형', 'CPA' => '회원가입형');
		$app_exec_desc = ifempty( ifempty($item['participation'] , $arr_exec_desc[$item['ads_type']]), '참여형');	// 실행 방법..
		
		//## app_platform
		$app_platform = 'A';
		if ($item['os_type']) {
			$arr_type = explode('|', $item['os_type']);
			if (in_array("android", $arr_type)) 
				$app_platform = 'A';
			else if (in_array("web", $arr_type))
				$app_platform = 'W';
			else continue;		// a타입도 없고,  w타입도 없는 경우엔 통과 (ios 만 있는 경우에는 통과)
		}

		//## app_market
		$arr_markets = explode('|', $item['market']);
		if (!$item['market']) $app_market = "P";							// 별도 지정이 없으면 PLAYSTORE
		else if (in_array("google_play", $arr_markets)) $app_market = "P";
		else if (in_array("tstore", $arr_markets)) $app_market = "T";
		else if (in_array("olleh", $arr_markets)) $app_market = "O";
		else if (in_array("uplus", $arr_markets)) $app_market = "U";
		else $app_market = "P";	// ELSE인 경우는 일단 P 로 처리

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
				$g_sucom['reactive-added'] ++;
				$g_sucom['reactive-added-title'][] = "{$app_exec_type} {$app_title}";
			}
			
			if ($row['flag_keep_modify'] == 'Y') 
			{
				$sql = "UPDATE al_app_t 
						SET
							app_iconurl = '{$db_app_iconurl}' , 
							app_merchant_fee = '{$db_app_merchant_fee}' , 
							
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
						app_exec_desc, app_platform, app_market, app_merchant_fee,
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
						
						IF('{$db_app_exec_stime}'<>'', '{$db_app_exec_stime}', NULL) , 
						IF('{$db_app_exec_etime}'<>'', '{$db_app_exec_stime}', NULL) , 
						IF('{$db_app_exec_edate}'<>'', '{$db_app_exec_edate}', NULL) , 
						NULL, 
						'100000000', 
						
						'Y', '{$flag_is_deleted}', NOW(), 'N', NOW(), NOW()
					);";
		}
		mysql_execute($sql, $conn);
		
		if ($_REQUEST['dev'] == 1) {
			echo "{$app_title}, {$m_key}, {$app_exec_type}, {$app_merchant_fee}<br>\n";
		}
	}

	// 갱신되지 못한 나머지 항목은 모두 in_active 로 설정
	$sql = "UPDATE al_app_t SET last_deactive_time = IF(is_active='Y' AND is_deleted='N',NOW(),last_deactive_time), is_active = 'N', up_date = NOW(), flag_updating = 'N' WHERE mcode = '{$db_mcode}' AND is_active = 'Y' AND flag_updating = 'Y'";
	mysql_execute($sql, $conn);

	return true;
}

function sucomm_request_start($app_key, &$arr_data, &$conn) 
{
	global $g_sucom;
	
	$ar_app = $arr_data['ad'];
	$userapp_id = $arr_data['user_app_id'];
	
	// 광고 시작 URL 생성
	$url_param = array();
	$url_param['media'] = $g_sucom['aline-code'];
	$url_param['flexcode'] = $ar_app['mkey'];
	$url_param['rewardkey'] = base64_encode(json_encode(array('aid' => $userapp_id)));		// appkey와 adid 의 HashCode
	$url_param['adId'] = $arr_data['adid'];
	$url_param['go_type'] = "json";
	$url_param['real_ip'] = $arr_data['ip'];
	
	$campain_url = concat_url($g_sucom['start'], http_build_query($url_param));

	// 광고 URL 요청하기
	$start_tm = get_timestamp();
	$ctx = stream_context_create( array( 'http'=> array('timeout' => $g_sucom['timeout_sec']), 'https'=> array('timeout' => $g_sucom['timeout_sec']) ) );
	
	// MYSQL을 닫은 후 요청이 완료되면 dbPConn()으로 재 연결한다.
	mysql_close($conn);
	$result_data = @file_get_contents($campain_url, 0, $ctx);
	$conn = dbPConn();
	
	make_action_log("user-start-sucomm", ($result_data?'Y':'N'), $arr_data['pcode'], $arr_data['adid'], null, get_timestamp() - $start_tm, $campain_url, null, $result_data, $conn);
	if (!$result_data) return array('result' => 'N', 'code' => '-1003');
	
	//전달할 결과 조합
	$js_data = json_decode($result_data, true);
	$result_data = $js_data['return_code'];
	$result_msg = "";

	// error-handling
	if ($result_data != "0000") {
		return sucomm_code_mapping($result_data, $result_msg);
	}
	$arr_data['result'] = 'Y';
	$arr_data['url'] = $js_data['url'];
	return array('result' => 'Y');
}

function local_request_done($app_key, $arr_data, &$conn) 
{
	global $g_sucom;

	$ar_app = $arr_data['ad'];
	$userapp_id = $arr_data['user_app_id'];

	// 설치형 이외에는 완료요청을 할 필요가 없음 ==> 무조건 성공으로 전달
	if ($ar_app['app_exec_type'] && $ar_app['app_exec_type'] != 'I') return array('result' => 'N', 'code' => '-109');

	// 광고 적립 URL생성
	$url_param = array();
	$url_param['media'] = $g_sucom['aline-code'];
	$url_param['flexcode'] = $ar_app['mkey'];
	$url_param['adId'] = $arr_data['adid'];

	// 광고 적립 요청 보내기
	$start_tm = get_timestamp();
	
	// MYSQL을 닫은 후 요청이 완료되면 dbPConn()으로 재 연결한다.
	mysql_close($conn);
	$result_data = post($g_sucom['done'], $url_param, $g_sucom['timeout_sec']);
	$conn = dbPConn();	
	
	make_action_log("user-done-sucomm", ($result_data?'Y':'N'), $arr_data['pcode'], $arr_data['adid'], null, get_timestamp() - $start_tm, $g_sucom['done'], $url_param, $result_data, $conn);
	if (!$result_data) return array('result' => 'N', 'code' => '-1003');

	// 전달할 결과 조합
	$js_data = json_decode($result_data, true);
	$result_data = $js_data['return_code'];
	$result_msg = "";
	
	// error-handling
	if ($result_data != "0000") {
		return sucomm_code_mapping($result_data, $result_msg);
	}
	return array('result' => 'Y');
}

function sucomm_code_mapping($code, $msg) {

	$ar_err_msg = sucom_error_msg($code);
	return array('result' => 'N', 'code' => $ar_err_msg[0], 'msg' => $ar_err_msg[1] );
}

function sucom_error_msg($code) {
	
	// 오류 설명
	$arr_error_msg[1101] = array('-100', '정확하지 않는 정보로 접속 되었습니다. 다시 참여해 주십시오.');
	$arr_error_msg[1102] = array('-100', '정확하지 않는 정보로 접속 되었습니다. 다시 참여해 주십시오.');
	$arr_error_msg[1103] = array('-103', '이벤트가 일시 중지 되었거나 오픈 전 입니다.');
	$arr_error_msg[1104] = array('-103', '이벤트가 일시 중지 되었거나 오픈 전 입니다.');
	$arr_error_msg[1105] = array('-103', '이벤트가 일시 중지 되었거나 오픈 전 입니다.');
	$arr_error_msg[1106] = array('-103', '이벤트가 일시 중지 되었거나 오픈 전 입니다.');
	$arr_error_msg[1107] = array('-104', '일시 중지 되었거나 오픈 전 입니다.');
	$arr_error_msg[1108] = array('-104', '일시 중지 되었거나 오픈 전 입니다.');
	$arr_error_msg[1109] = array('-104', '일시 중지 되었습니다.');
	$arr_error_msg[1110] = array('-111', '광고 접속에 실패 하였습니다. 다시 참여해 주십시오.');
	$arr_error_msg[1111] = array('-105', '이미 참여한 이벤트 입니다.');
	$arr_error_msg[1112] = array('-105', '이미 참여한 이벤트 입니다.');
	$arr_error_msg[1113] = array('-105', '이미 참여한 이벤트 입니다.');
	$arr_error_msg[1114] = array('-105', '이미 참여한 이벤트 입니다.');
	$arr_error_msg[1115] = array('-105', '이미 참여한 이벤트 입니다.');
	$arr_error_msg[1116] = array('-106', '모바일웹 환경에서만 참여 가능합니다.');
	$arr_error_msg[1117] = array('-106', '참여하셨거나, 이벤트 참여 대상자가 대상자가 아닙니다.');
	$arr_error_msg[1118] = array('-103', '이벤트가 일시 중지 되었거나 오픈 전 입니다.');
	$arr_error_msg[1119] = array('-104', '당일 이벤트 참여가 일시 중지 되었습니다. 내일 다시 참여 해 주세요.');
	$arr_error_msg[1120] = array('-103', 'IMEI전용 광고 입니다');
	
	$arr_error_msg[1201] = array('-107', '정확하지 않는 정보로 접속 되었습니다. 다시 참여해 주십시오.');
	$arr_error_msg[1202] = array('-111', '광고 접속에 실패 하였습니다. 다시 참여해 주십시오');
	$arr_error_msg[1203] = array('-111', '광고 접속에 실패 하였습니다. 다시 참여해 주십시오');
	$arr_error_msg[1204] = array('-104', '이벤트가 일시 중지 되었거나 오픈 전 입니다');
	$arr_error_msg[1205] = array('-111', '광고 접속에 실패 하였습니다. 다시 참여해 주십시오');
	$arr_error_msg[1206] = array('-103', '이벤트가 일시 중지 되었거나 오픈 전 입니다');
	$arr_error_msg[1207] = array('-106', '참여하셨거나, 이벤트 참여 대상자가 아닙니다');
	$arr_error_msg[1208] = array('-104', '이벤트가 일시 중지 되었거나 오픈 전 입니다');
	$arr_error_msg[1209] = array('-104', '금일 광고 수량이 모두 소진되었습니다. 익일 참여하시기 바랍니다');
	$arr_error_msg[1210] = array('-111', '광고 접속에 실패 하였습니다. 다시 참여해 주십시오');
	$arr_error_msg[1211] = array('-111', '광고 접속에 실패 하였습니다. 다시 참여해 주십시오');
	$arr_error_msg[1212] = array('-100', '정확하지 않는 정보로 접속 되었습니다. 다시 참여해 주십시오');
	$arr_error_msg[1213] = array('-160', '참여하셨거나, 이벤트 참여 대상자가 아닙니다');

	if ($arr_error_msg[$code]) return $arr_error_msg[$code];
	return array('-1005', '알 수 없는 오류');
	
}
?>