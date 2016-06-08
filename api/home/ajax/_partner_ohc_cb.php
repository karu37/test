<?
$g_ohc['unique_prefix'] = "ohc";		// 적립 결과에 Unique 키
$g_ohc['timeout_sec'] = 60;				// 시작 / 적립 요청시의 Timeout 초

/* ******************************************
	// "http://api.aline-soft.kr/ajax-request.php?id=_partner_ohc_cb&appkey=ohcca56209ce2b04fc6e7288375b9f689bb&uniquekey=ohcuniquekey&userdata=" . base64_encode(json_encode(array('aid' => $userapp_id)))
**********************************************/

	// 전달된 파라미터 받아서 표준화 하기

	//## 현재 시간 $ar_time
	$ar_time = mysql_get_time($conn);
	
	//## m_key
	$m_key 			= $_REQUEST['appkey'];
	
	//## unique_key
	$unique_key 	= $g_ohc['unique_prefix'] . $_REQUEST['uniquekey'];
	
	//## userdata ==> aid
	$cb_user_data 	= $_REQUEST['userdata'];
	$js_data = json_decode(base64_decode(str_replace('*','=',$cb_user_data)), true);
		
	//## userapp_id
	$userapp_id = $js_data['aid'];

	// ------------------------------------------------
	
	//## al_user_app_t 정보 로딩
	$db_aid = mysql_real_escape_string($aid);
	$sql = "SELECT a.*, 
					p.reward_percent,
					p.callback_url
			FROM al_user_app_t a 
					INNER JOIN al_publisher_t p ON a.pcode = p.pcode 
			WHERE a.id = '{$db_aid}'";
	$arr_userapp = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$arr_userapp || $arr_userapp['m_key'] != $m_key) {
		echo_response('error-info', '앱정보가 일치하지 않습니다.');
		die();
	}
	
	//## 적립하기
	$ar_reward = callback_reward($arr_userapp['pcode'], $arr_userapp['mcode'], $arr_userapp['app_key'], $arr_userapp['adid'], 
							$arr_userapp['merchant_fee'], $arr_userapp['publisher_fee'], $unique_key, 
							$ar_time, false, $conn);	

	//## 적립 실패 처리	
	if ($ar_reward['result'] == 'N') {
		$arr_type = array('-3001' => 'error-user', '-3002' => 'error-user', '-3003' => 'error-dup');
		echo_response(ifempty($arr_type[$ar_reward['code']], 'error-etc'), get_error_msg($ar_reward['code'], $ar_reward['msg']));
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
		$response_data = post($req_base_url, $url_param, $g_ohc['timeout_sec']);
		$ar_resp = json_decode($response_data, true);

		// MYSQL을 닫은 후 요청이 완료되면 dbPConn()으로 재 연결한다.
		mysql_close($conn);
		
		make_action_log("callback-pub-ohc", ifempty($ar_resp['result'], 'N'), $arr_userapp['adid'], null, get_timestamp() - $start_tm, $req_base_url, $url_param, $response_data, $conn);
		
		$conn = dbPConn();
	
		// ----------------------------------------------------------------------
		// 리턴 데이터 구성 (리턴 불필요 -- 자체 해결해야 함)
		// 	 callback_done 결과를 al_user_app_t 에 기록하기 실패시 F 로 설정함.
		// ----------------------------------------------------------------------
		if ($ar_resp['result'] == 'Y') {
			$sql = "UPDATE al_user_app_t SET callback_done = 'Y', callback_data = NULL, callback_time = '{$ar_time['now']}' WHERE id = '{$userapp_id}'";
			mysql_query($sql, $conn);
		} else {
			$db_response_data = mysql_real_escape_string($response_data);
			$sql = "UPDATE al_user_app_t SET callback_done = 'F', callback_data = '{$db_response_data}', callback_time = '{$ar_time['now']}' WHERE id = '{$userapp_id}'";
			mysql_query($sql, $conn);
		}
	}
	echo_response('success');
	die();

function echo_response($type, $msg = null) 
{
	$add_msg = ($msg ? " (".$msg.")" : "");
	if ($type == 'success') $arResult = array('result' => 'Y', 'code' => 1, 'msg' => "success");
	else if ($type == 'error-sec') $arResult = array('result' => 'N', 'code' => 1100, 'msg' => "Invalid signed value{$add_msg}");
	else if ($type == 'error-dup') $arResult = array('result' => 'N', 'code' => 3100, 'msg' => "duplicate transaction{$add_msg}");
	else if ($type == 'error-user') $arResult = array('result' => 'N', 'code' => 3200, 'msg' => "invalid user transaction{$add_msg}");
	else if ($type == 'error-info') $arResult = array('result' => 'N', 'code' => 3300, 'msg' => "no matching transaction{$add_msg}");
	else if ($type == 'error-etc') $arResult = array('result' => 'N', 'code' => 4000, 'msg' => "exception has occurred in processing callback{$add_msg}");
	
	if (!$arResult) $arResult = array('result' => 'Y', 'code' => 4001, 'msg' => "Invalid response {$add_msg}");
	echo json_encode($arResult);
}

?>