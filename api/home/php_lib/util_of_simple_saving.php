<?
function simple_user_app_saving_fail($userdata, $error_code, $error_msg, $conn)
{
	// db : status = 'F'
	// db : error = $error_code
	// db : error_msg = $error_msg

	//## userdata ==> aid
	$cb_user_data 	= $userdata;
	$js_data = json_decode(base64_decode(str_replace('*','=',$cb_user_data)), true);

	//## userapp_id
	$userapp_id = $js_data['aid'];

	//## al_user_app_t 정보 로딩 및 m_key 값과 일치하는지 확인
	$db_userapp_id = mysql_real_escape_string($userapp_id);
	$db_error_code = mysql_real_escape_string($error_code);
	$db_error_msg = mysql_real_escape_string($error_msg);

	//## 현재 시간 $ar_time
	$ar_time = mysql_get_time($conn);

	// permanent_fail 처리 ==> 이 후 참여 불가 (단 적립 성공인 경우에는 수정하면 안됨)
	$sql = "UPDATE al_user_app_t
			SET
				action_ftime = '{$ar_time['now']}',
				status = 'F',
				permanent_fail = 'Y',
				error = '{$db_error_code}',
				error_msg = '{$db_error_msg}'
			WHERE id = '{$db_userapp_id}' AND status <> 'D'";
	mysql_query($sql, $conn);

	return true;
}

// -------------------------------------------------------------------------------------------------------------------------------------------------
// 적립을 해주는 경우 호출
// -------------------------------------------------------------------------------------------------------------------------------------------------
function simple_user_app_saving($userdata, &$error_msg, $conn)
{
	$g_local['unique_prefix'] = "loc";		// 적립 결과에 Unique 키
	$g_local['timeout_sec'] = 60;			// 시작 / 적립 요청시의 Timeout 초

	// 전달된 파라미터 받아서 표준화 하기

	//## 현재 시간 $ar_time
	$ar_time = mysql_get_time($conn);

	//## unique_key
	$unique_key 	= $g_local['unique_prefix'] . md5($userdata);

	//## userdata ==> aid
	$cb_user_data 	= $userdata;
	$js_data = json_decode(base64_decode(str_replace('*','=',$cb_user_data)), true);

	//## userapp_id
	$userapp_id = $js_data['aid'];

	// ------------------------------------------------

	//## al_user_app_t 정보 로딩 및 m_key 값과 일치하는지 확인
	$db_userapp_id = mysql_real_escape_string($userapp_id);
	$sql = "SELECT a.*,
					p.reward_percent,
					p.callback_url
			FROM al_user_app_t a
					INNER JOIN al_publisher_t p ON a.pcode = p.pcode
					INNER JOIN al_app_t app ON a.app_key = app.app_key
			WHERE a.id = '{$db_userapp_id}'";
	$arr_userapp = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$arr_userapp) {
		$error_msg = '광고 정보가 유효하지 않습니다.(-100)';
		return false;
	}
	//## pcode from db
	$g_log['pcode'] = $arr_userapp['pcode'];
	$g_log['adid'] = $arr_userapp['adid'];
	$g_log['app_key'] = $arr_userapp['app_key'];

	//## 적립하기
	$ar_time = mysql_get_time($conn);
	$ar_reward = callback_reward($arr_userapp['pcode'], $arr_userapp['mcode'], $arr_userapp['app_key'], $arr_userapp['adid'],
							$arr_userapp['merchant_fee'], $arr_userapp['tag_price'], $arr_userapp['publisher_fee'], $unique_key,
							$ar_time, false, $conn);

	//## 적립 실패 처리
	if ($ar_reward['result'] == 'N') {
		if ( in_array($ar_reward['code'], array(-3003, -3001)) )
			$error_msg = "이미 적립된 광고입니다.";
		else
			$error_msg = "적립 오류로 적립이 중지되었습니다.({$ar_reward['code']})<br><br>오류 : " . get_error_msg($ar_reward['code'], "");
		return false;
	}

	// 강제적립된 대상을 적립한 경우 콜백호출하면 안됨 (Y 가 아닌 N 또는 F 인 경우에 호출함)
	if ($ar_reward['callback_done'] != 'Y') {

		// ----------------------------------------------------------------------
		// CALLBACK 파라미터 생성 후 Publisher 콜백 호출
		// ----------------------------------------------------------------------
		$url_param['ad'] = $arr_userapp['app_key'];
		$url_param['price'] = $arr_userapp['publisher_fee'];
		$url_param['reward'] = intval($arr_userapp['publisher_fee'] * $arr_userapp['reward_percent'] / 100);
		$url_param['uid'] = $arr_userapp['uid'];
		$url_param['userdata'] = $arr_userapp['userdata'];
		$url_param['unique'] = $unique_key;
		$req_base_url = $arr_userapp['callback_url'];

		// ----------------------------------------------------------------------
		$start_tm = get_timestamp();

		// MYSQL을 닫은 후 요청이 완료되면 dbPConn()으로 재 연결한다.
		mysql_close($conn);
		$response_data = post($req_base_url, $url_param, $g_local['timeout_sec']);
		$conn = dbConn();

		$ar_resp = json_decode($response_data, true);

		make_action_log("callback-pub-local", ifempty($ar_resp['result'], 'N'), $arr_userapp['pcode'], $arr_userapp['adid'], $arr_userapp['app_key'], null, get_timestamp() - $start_tm, $req_base_url, $url_param, $response_data, $conn);

		// ----------------------------------------------------------------------
		// 리턴 데이터 구성 (리턴 불필요 -- 자체 해결해야 함)
		// 	 callback_done 결과를 al_user_app_t 에 기록하기 실패시 F 로 설정함.
		// ----------------------------------------------------------------------

		if ($ar_resp['result'] == 'Y') $callback_result = 'Y';
		else if (trim($response_data) == "") $callback_result = 'R';		// 아무것도 응답하지 않은 경우 ==> 실패로 보고 재시도함.
		else $callback_result = 'N';

		$db_callback_url = mysql_real_escape_string($req_base_url);
		$db_callback_post = mysql_real_escape_string(json_encode($url_param));
		$db_result = mysql_real_escape_string($callback_result);
		$db_response_data = mysql_real_escape_string($response_data);

		$sql = "UPDATE al_user_app_t SET callback_done = '{$db_result}', callback_url = '{$db_callback_url}', callback_post = '{$db_callback_post}', callback_resp = '{$db_response_data}', callback_time = '{$ar_time['now']}', callback_tried = callback_tried + 1 WHERE id = '{$userapp_id}'";
		mysql_query($sql, $conn);
	}

	return true;
}

?>