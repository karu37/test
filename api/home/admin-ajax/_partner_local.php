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

function local_request_start($app_key, &$arr_data, $conn) 
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

function local_request_done($app_key, $arr_data, $conn) 
{
	global $g_local, $dev_mode;

	$ar_app = $arr_data['ad'];
	$ar_userapp = $arr_data['user_app'];

	$ar_result = array();
	
	// ----------------------------------------------------------------------
	// 문제 없는지 한번 더 확인함. (호출 전에 체크한것 외에..)
	// ----------------------------------------------------------------------
	// 	return array('result' => 'N', 'code' => '-1003', 'msg' => "no-exec-url");

	/////////////////////////////////////////////////////////////////////////
	// MERCHANT CALLBACK 발생 영역과 동일
	/////////////////////////////////////////////////////////////////////////
		$ar_time['now'] = $arr_data['now'];
		$ar_time['day'] = $arr_data['day'];

		// ----------------------------------------------------------------------
		// 사용자 적립하기 (사용자 적립 및 al_user_app_t 상태 변경 모두 처리)
		// ----------------------------------------------------------------------
		$ar_reward = callback_reward($arr_data['pcode'], $app_key, $arr_data['adid'], $ar_time, $ar_app, $conn);
var_dump($ar_reward);		
		if ($ar_reward['result'] == 'N') {
			$code = $ar_reward['code'];
			$msg = $ar_reward['msg'];
			return array('result' => 'N', 'code' => $code, 'msg' => $msg);
		}
echo "OK";
exit;		
		// ----------------------------------------------------------------------
		// CALLBACK 파라미터 생성 후 Publisher 콜백 호출
		// ----------------------------------------------------------------------
		$ar_result['result'] = 'Y';
	
		$url_param['ad'] = $app_key;
		$url_param['price'] = $ar_app['publisher_fee'];
		$url_param['reward'] = intval($ar_app['publisher_fee'] * $arr_data['reward_percent'] / 100);
		$url_param['uid'] = $ar_userapp['uid'];
		$url_param['userdata'] = $ar_userapp['userdata'];
		$url_param['unique'] = $g_local['unique_prefix'].$arr_data['user_app_id'];
		$req_base_url = $arr_data['callback_url'];
		// echo "POST URL : " . concat_url($req_base_url, http_build_query($url_param));
	
		// ----------------------------------------------------------------------
		$start_tm = get_timestamp();
		$response_data = post($req_base_url, $url_param, 3);
		make_action_log(get_timestamp() - $start_tm, basename(__FILE__), "p-callback", $arr_data['adid'], $req_base_url, http_build_query($url_param), $response_data, $conn);
		$ar_resp = json_decode($response_data, true);
	
		// ----------------------------------------------------------------------
		// 리턴 데이터 구성 (리턴 불필요 -- 자체 해결해야 함)
		// 	 callback_done 결과를 al_user_app_t 에 기록하기
		// ----------------------------------------------------------------------
		if ($ar_resp['result'] == 'Y') {
			$sql = "UPDATE al_user_app_t SET callback_done = 'Y', callback_code = NULL, callback_time = '{$ar_time['now']}' WHERE id = '{$arr_data['user_app_id']}'";
			mysql_query($sql, $conn);
		} else {
			$db_code = mysql_real_escape_string($ar_resp['code']);
			$sql = "UPDATE al_user_app_t SET callback_done = 'N', callback_code = '{$db_code}', callback_time = '{$ar_time['now']}' WHERE id = '{$arr_data['user_app_id']}'";
			mysql_query($sql, $conn);
		}
	
	/////////////////////////////////////////////////////////////////////////
	// MERCHANT CALLBACK END
	/////////////////////////////////////////////////////////////////////////
	
	return array('result' => 'Y');
}

?>	
