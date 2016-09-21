<?
$g_local['unique_prefix'] = "loc";		// 적립 결과에 Unique 키
$g_local['timeout_sec'] = 60;				// 시작 / 적립 요청시의 Timeout 초

/* ******************************************
	// callback url : http://cb.aline-soft.kr/adkey/result.json?adkey=[USERDATA]	* 중간에 adkey를 얻어주면 adkey로 userdata를 전달 받는다.
	// 	==> http://cb.aline-soft.kr/ajax-request.php?id=_partner_local_cb&pkey=adkey&adkey=[USERDATA]
**********************************************/

	// 전달된 파라미터 받아서 표준화 하기

	//## 현재 시간 $ar_time
	$ar_time = mysql_get_time($conn);
	
	//## ud parameter key
	$ud_param = ifempty($_REQUEST['pkey'], 'userdata');
	
	// 성공 실패 결과 출력 종류
	$resp_type = ifempty($_REQUEST['type'], 'typejs');
	
	//## unique_key
	$unique_key 	= $g_local['unique_prefix'] . md5($_REQUEST[$ud_param]);
	
	//## userdata ==> aid
	$cb_user_data 	= $_REQUEST[$ud_param];
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
		echo_response($resp_type, 'error-info', '앱정보가 일치하지 않습니다.');
		die();
	}
	//## pcode from db
	$g_log['pcode'] = $arr_userapp['pcode'];
	$g_log['adid'] = $arr_userapp['adid'];
	$g_log['app_key'] = $arr_userapp['app_key'];
	
	//## 적립하기
	$ar_reward = callback_reward($arr_userapp['pcode'], $arr_userapp['mcode'], $arr_userapp['app_key'], $arr_userapp['adid'], 
							$arr_userapp['merchant_fee'], $arr_userapp['tag_price'], $arr_userapp['publisher_fee'], $unique_key, 
							$ar_time, false, $conn);	

	//## 적립 실패 처리	
	if ($ar_reward['result'] == 'N') {
		$arr_type = array('-3001' => 'error-user', '-3002' => 'error-user', '-3003' => 'error-dup');
		echo_response($resp_type, ifempty($arr_type[$ar_reward['code']], 'error-etc'), get_error_msg($ar_reward['code'], $ar_reward['msg']));
		die();
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
	echo_response($resp_type, 'success');
	die();

function echo_response($resp_type, $type, $msg = null) 
{
	global $conn, $g_log;
	
	$add_msg = ($msg ? " (".$msg.")" : "");
	
	if ($resp_type == 'typejs') {

		if ($type == 'success') $arResult = array('result' => true, 'resCode' => 'E00', 'msg' => "정상처리");
		else if ($type == 'error-sec') $arResult = array('result' => false, 'resCode' => 'E80', 'msg' => "Invalid signed value{$add_msg}");
		else if ($type == 'error-dup') $arResult = array('result' => false, 'resCode' => 'E81', 'msg' => "duplicate transaction{$add_msg}");
		else if ($type == 'error-user') $arResult = array('result' => false, 'resCode' => 'E82', 'msg' => "invalid user transaction{$add_msg}");
		else if ($type == 'error-info') $arResult = array('result' => false, 'resCode' => 'E83', 'msg' => "no matching transaction{$add_msg}");
		else if ($type == 'error-etc') $arResult = array('result' => false, 'resCode' => 'E84', 'msg' => "exception has occurred in processing callback{$add_msg}");
		if (!$arResult) $arResult = array('result' => false, 'resCode' => 'E85', 'msg' => "Invalid response {$add_msg}");
		$result = json_encode($arResult);
		
	} else if ($resp_type == 'typesf') {
		
		if ($type == 'success') $result = 'S';
		else $result = 'F';
		
	}
	
	make_action_log("callback-local", ($type == 'success' ? 'Y' : 'N'), $g_log['pcode'], $g_log['adid'], $g_log['app_key'], null, null, null, null, $result, $conn);
	echo $result;
}

?>