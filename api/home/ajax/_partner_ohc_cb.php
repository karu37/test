<?
$g_ohc['unique_prefix'] = "ohc";		// 적립 결과에 Unique 키
$g_ohc['timeout_sec'] = 60;				// 시작 / 적립 요청시의 Timeout 초

/* ******************************************
	// callback base url : http://cb.aline-soft.kr/ajax-request.php?id=_partner_ohc_cb
	// "http://cb.aline-soft.kr/ajax-request.php?id=_partner_ohc_cb&appkey=ohcca56209ce2b04fc6e7288375b9f689bb&uniquekey=ohcuniquekey&userdata=" . base64_encode(json_encode(array('aid' => $userapp_id)))
**********************************************/

	// 전달된 파라미터 받아서 표준화 하기

	//## 현재 시간 $ar_time
	$ar_time = mysql_get_time($conn);
	
	//## m_key
	$m_key 			= $_REQUEST['eId'];
	
	//## unique_key
	$unique_key 	= $g_ohc['unique_prefix'] . $_REQUEST['ohvalue'];
	
	//## userdata ==> aid
	$cb_user_data 	= $_REQUEST['etc1'];
	$js_data = json_decode(base64_decode(str_replace('*','=',$cb_user_data)), true);
		
	//## userapp_id
	$userapp_id = $js_data['aid'];

	// ------------------------------------------------
	
	//## al_user_app_t 정보 로딩 및 m_key 값과 일치하는지 확인
	$db_userapp_id = mysql_real_escape_string($userapp_id);
	$sql = "SELECT a.*, 
					app.mkey,
					p.reward_percent,
					p.callback_url
			FROM al_user_app_t a 
					INNER JOIN al_publisher_t p ON a.pcode = p.pcode 
					INNER JOIN al_app_t app ON a.app_key = app.app_key
			WHERE a.id = '{$db_userapp_id}'";
	$arr_userapp = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$arr_userapp || $arr_userapp['mkey'] != $m_key) {
		echo_response('error-info', '앱정보가 일치하지 않습니다.');
		die();
	}
	//## pcode from db
	$g_log['pcode'] = $arr_userapp['pcode'];
	$g_log['adid'] = $arr_userapp['adid'];
	
	//## 적립하기
	$ar_reward = callback_reward($arr_userapp['pcode'], $arr_userapp['mcode'], $arr_userapp['app_key'], $arr_userapp['adid'], 
							$arr_userapp['merchant_fee'], $arr_userapp['tag_price'], $arr_userapp['publisher_fee'], $unique_key, 
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
		
		// MYSQL을 닫은 후 요청이 완료되면 dbPConn()으로 재 연결한다.
		mysql_close($conn);
		$response_data = post($req_base_url, $url_param, $g_ohc['timeout_sec']);
		$conn = dbConn();
		
		$ar_resp = json_decode($response_data, true);

		make_action_log("callback-pub-ohc", ifempty($ar_resp['result'], 'N'), $arr_userapp['pcode'], $arr_userapp['adid'], null, get_timestamp() - $start_tm, $req_base_url, $url_param, $response_data, $conn);
		
		// ----------------------------------------------------------------------
		// 리턴 데이터 구성 (리턴 불필요 -- 자체 해결해야 함)
		// 	 callback_done 결과를 al_user_app_t 에 기록하기 실패시 F 로 설정함.
		// ----------------------------------------------------------------------
		$db_callback_url = mysql_real_escape_string($req_base_url);
		$db_callback_post = mysql_real_escape_string(json_encode($url_param));
		$db_result = mysql_real_escape_string($ar_resp['result'] == 'Y' ? 'Y' : 'N');
		$db_response_data = mysql_real_escape_string($response_data);
		
		$sql = "UPDATE al_user_app_t SET callback_done = '{$db_result}', callback_url = '{$db_callback_url}', callback_post = '{$db_callback_post}', callback_resp = '{$db_response_data}', callback_time = '{$ar_time['now']}', callback_tried = callback_tried + 1 WHERE id = '{$userapp_id}'";
		mysql_query($sql, $conn);
	}
	echo_response('success');
	die();

function echo_response($type, $msg = null) 
{
	global $conn, $g_log;
	
	$add_msg = ($msg ? " (".$msg.")" : "");
/*	
	if ($type == 'success') $arResult = array('result' => 'Y', 'code' => 1, 'msg' => "success");
	else if ($type == 'error-sec') $arResult = array('result' => 'N', 'code' => 'E80', 'msg' => "Invalid signed value{$add_msg}");
	else if ($type == 'error-dup') $arResult = array('result' => 'N', 'code' => 'E81, 'msg' => "duplicate transaction{$add_msg}");
	else if ($type == 'error-user') $arResult = array('result' => 'N', 'code' => 'E82', 'msg' => "invalid user transaction{$add_msg}");
	else if ($type == 'error-info') $arResult = array('result' => 'N', 'code' => 'E83', 'msg' => "no matching transaction{$add_msg}");
	else if ($type == 'error-etc') $arResult = array('result' => 'N', 'code' => 'E84', 'msg' => "exception has occurred in processing callback{$add_msg}");
	if (!$arResult) $arResult = array('result' => 'Y', 'code' => 'E85', 'msg' => "Invalid response {$add_msg}");
*/	
	if ($type == 'success') $arResult = array('resCode' => 'E00', 'msg' => "정상처리");
	else if ($type == 'error-sec') $arResult = array('resCode' => 'E80', 'msg' => "Invalid signed value{$add_msg}");
	else if ($type == 'error-dup') $arResult = array('resCode' => 'E81', 'msg' => "duplicate transaction{$add_msg}");
	else if ($type == 'error-user') $arResult = array('resCode' => 'E82', 'msg' => "invalid user transaction{$add_msg}");
	else if ($type == 'error-info') $arResult = array('resCode' => 'E83', 'msg' => "no matching transaction{$add_msg}");
	else if ($type == 'error-etc') $arResult = array('resCode' => 'E84', 'msg' => "exception has occurred in processing callback{$add_msg}");
	if (!$arResult) $arResult = array('resCode' => 'E85', 'msg' => "Invalid response {$add_msg}");

	$result = json_encode($arResult);
	make_action_log("callback-ohc", ($type == 'success' ? 'Y' : 'N'), $g_log['pcode'], $g_log['adid'], null, null, null, null, $result, $conn);
	echo $result;
}

?>