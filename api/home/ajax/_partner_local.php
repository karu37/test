<?	
$g_local['unique_prefix'] = "uvl";
// 로컬은 광고주쪽 요청이 없이 바로 처리하기 때문에 callback없음
$g_local['callback'] = "";

/*
	# 성공시 RESULT
		 $ar_data['result'] == true
		 $ar_data['url'] == 'market:// ~ '
	# 실패시 RESULT
		 $ar_data['result'] == false
		 $ar_data['code'] == -11
		 $ar_data['msg'] == '오류가 발생 ~ ..'
		 
	 // http://app.autoring.kr/home/ajax/request.php?id=_lib-partner-local
*/

function local_request_start($app_key, &$arr_data, &$conn) 
{
	global $g_local;
	
	// al_app_t 정보
	$ar_app = $arr_data['ad'];
	
	$db_appkey = mysql_real_escape_string($app_key);
	$user_unique_key = $arr_data['user_unique_key'];

	// 실행 URL : WEB형은 기본적으로 가지고 있어야 함. (자체적인 URL에는 referrer=[al_user_app_t.id] 를 전달하도록 한다.
	$referrer = "uaid=".$arr_data['user_app_id']."&cc=".md5(sha1($arr_data['user_app_id']));
	
	if ( $ar_app['app_exec_type'] == 'I' ) {
		// 실행형
		if ($ar_app['app_execurl']) 
			$exec_url = $ar_app['app_execurl'];
		else
			$exec_url = "market://details?id={$ar_app['app_packageid']}";
	} else if ( $ar_app['app_exec_type'] == 'E' ) {
		// 실행형은 referrer를 덧붙인다.
		if ($ar_app['app_execurl']) 
			$exec_url = concat_url($ar_app['app_execurl'], "referrer=".urlencode($referrer));
		else
			$exec_url = "market://details?id={$ar_app['app_packageid']}&referrer=".urlencode($referrer);
	} else {
		// 그외
		if ($ar_app['app_execurl']) 
			$exec_url = $ar_app['app_execurl'];
		else
			return array('result' => 'N', 'code' => '-1003', 'msg' => "no-exec-url");
	}
	
	$arr_data['result'] = 'Y';
	$arr_data['url'] = $exec_url;
	
	// $arr_data는 파라미터로 그대로 전달됨.
	return array('result' => 'Y', 'code' => '', 'msg' => "");
}

function local_request_done($app_key, $arr_data, $b_forcedone, &$conn) 
{
	global $g_local, $dev_mode;

	$ar_app = $arr_data['ad'];
	$ar_userapp = $arr_data['userapp'];
	$unique_key = $g_local['unique_prefix'].$arr_data['user_app_id'];
	$ar_result = array();
	
	// ----------------------------------------------------------------------
	// 문제 없는지 한번 더 확인함. (호출 전에 체크한것 외에..)
	// ----------------------------------------------------------------------
	// 	return array('result' => 'N', 'code' => '-1003', 'msg' => "no-exec-url");

	/////////////////////////////////////////////////////////////////////////
	// MERCHANT CALLBACK 발생 영역과 동일
	/////////////////////////////////////////////////////////////////////////
	
		$ar_time = mysql_get_time($conn);

		$ar_reward['callback_done']	= 'N';
		
		// al_app_t.is_mactive 가 T인 경우에는 적립하지 않음
		if ($row_app['is_mactive'] != 'T') 
		{
			// ----------------------------------------------------------------------
			// 사용자 적립하기 (사용자 적립 및 al_user_app_t 상태 변경 모두 처리)
			// ----------------------------------------------------------------------
			if (!$b_forcedone) 
			{
				$ar_reward = callback_reward($arr_data['pcode'], $arr_data['mcode'], $app_key, $arr_data['adid'], 
										$ar_app['app_merchant_fee'], $ar_app['publisher_fee'], $unique_key, 
										$ar_time, ($ar_app['lib'] == 'LOCAL'), $conn);
			} 
			else 
			{
				$ar_reward = force_reward($arr_data['pcode'], $arr_data['mcode'], $app_key, $arr_data['adid'], 
										$ar_app['app_merchant_fee'], $ar_app['publisher_fee'], 
										$ar_time, ($ar_app['lib'] == 'LOCAL'), $conn);
			}

			if ($ar_reward['result'] == 'N') {
				$code = $ar_reward['code'];
				$msg = $ar_reward['msg'];
				return array('result' => 'N', 'code' => $code, 'msg' => $msg);
			}
		}

		// 강제적립된 대상을 적립한 경우 콜백호출하면 안됨 (Y 가 아닌 N 또는 F 인 경우에 호출함)
		if ($ar_reward['callback_done'] != 'Y') {
			
			// ----------------------------------------------------------------------
			// CALLBACK 파라미터 생성 후 Publisher 콜백 호출
			// ----------------------------------------------------------------------
			$ar_result['result'] = 'Y';
		
			$url_param['ad'] = $app_key;
			$url_param['price'] = $ar_app['publisher_fee'];
			$url_param['reward'] = intval($ar_app['publisher_fee'] * $arr_data['reward_percent'] / 100);
			$url_param['uid'] = $ar_userapp['uid'];
			$url_param['userdata'] = $ar_userapp['userdata'];
			$url_param['unique'] = $unique_key;
			$req_base_url = $arr_data['callback_url'];
			// echo "POST URL : " . concat_url($req_base_url, http_build_query($url_param));
		
			// ----------------------------------------------------------------------
			$start_tm = get_timestamp();
			$response_data = post($req_base_url, $url_param, 3);
			$ar_resp = json_decode($response_data, true);

			// MYSQL을 닫은 후 요청이 완료되면 dbPConn()으로 재 연결한다.
			mysql_close($conn);
			
			// echo "make_action_log({$ar_resp['result']}, {$response_data}, 0, 'local-cb-call', null, {$req_base_url}, {$url_param});";
			make_action_log($ar_resp['result'], $response_data, get_timestamp() - $start_tm, 'local-cb-call', null, $req_base_url, $url_param);
			
			$conn = dbPConn();
		
			// ----------------------------------------------------------------------
			// 리턴 데이터 구성 (리턴 불필요 -- 자체 해결해야 함)
			// 	 callback_done 결과를 al_user_app_t 에 기록하기 실패시 F 로 설정함.
			// ----------------------------------------------------------------------
			if ($ar_resp['result'] == 'Y') {
				$sql = "UPDATE al_user_app_t SET callback_done = 'Y', callback_data = NULL, callback_time = '{$ar_time['now']}' WHERE id = '{$arr_data['user_app_id']}'";
				mysql_query($sql, $conn);
			} else {
				$db_response_data = mysql_real_escape_string($response_data);
				$sql = "UPDATE al_user_app_t SET callback_done = 'F', callback_data = '{$db_response_data}', callback_time = '{$ar_time['now']}' WHERE id = '{$arr_data['user_app_id']}'";
				mysql_query($sql, $conn);
			}
		}
		
	/////////////////////////////////////////////////////////////////////////
	// MERCHANT CALLBACK END
	/////////////////////////////////////////////////////////////////////////
	
	return array('result' => 'Y');
}
?>