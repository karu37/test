<?	
$g_local['unique_prefix'] = "a";
$g_local['callback'] = "http://app.autoring.kr/home/ajax/request.php?id=_adlocal_cb";
$g_local['callback_cpc'] = "http://app.autoring.kr/home/ajax/request.php?id=_adlocal_cpc_cb";
/*
	# 성공시 RESULT
		 $ar_data['result'] == true
		 $ar_data['url'] == 'market:// ~ '
	# 실패시 RESULT
		 $ar_data['result'] == false
		 $ar_data['error'] == -11
		 $ar_data['error_msg'] == '오류가 발생 ~ ..'
		 
	 // http://app.autoring.kr/home/ajax/request.php?id=_lib-partner-local
*/

function local_request_start($app_key, &$arr_data, $conn) 
{
	global $g_local;
	
	$db_appkey = mysql_real_escape_string($app_key);
	$user_unique_key = $arr_data['user_unique_key'];

	// 실행 URL : WEB형은 기본적으로 가지고 있어야 함. (자체적인 URL에는 referrer=[al_user_app_t.id] 를 전달하도록 한다.
	if (trim($arr_data['app_execurl'])) {
		$exec_url = concat_url($arr_data['app_execurl'], "referrer=");
	} else {
		if ( $arr_data['app_exec_type'] == 'I' )
			$exec_url = "market://details?id={$arr_data['app_packageid']}";
	}
	
	// generate execution url		
	if ($arr_data['app_market'] == 'P') {
		if (!$arr_data['app_packageid']) return array('result' => 'N', 'error' => '-1001', 'error_msg' => "no-packageid");
	} else if (	$arr_data['app_market'] == 'W' ) {
		// WEB 형
	} else {
		return array('result' => 'N', 'error' => '-1002', 'error_msg' => "not-supported-martket");
	}
	
	// ADID를 가져오고 비교한 후 이전과 다르면 비정상 사용자로 뱉어낸다.
	$db_device_id = mysql_real_escape_string($arr_data['user_device_id']);
	
	$sql = "SELECT id, device_adid FROM user_device_t WHERE user_id = '{$db_user_id}' AND id = '{$db_device_id}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row['id'] && $row['device_adid']) 
	{
		if ($row['device_adid'] != $arr_data['adid']) 
		{
			$sql = "SELECT id, IF(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(up_date) < 86400 * 2, 'N', 'Y') as 'allow_update' FROM user_adid_changed_t WHERE device_id = '{$db_device_id}'";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row && $row['allow_update'] == 'N') 
			{
				// ADID 바뀐지 48시간 이내에는 다시 바뀌지 않음
				return array('result' => 'N', 'error' => '-1000', 'error_msg' => "ADID 변경됨");
			} 
			else 
			{
				// 사용자의 지정 DeviceID의 ADID를 갱신
				$db_adid = mysql_real_escape_string($arr_data['adid']);
				$sql = "UPDATE user_device_t SET device_adid = '{$db_adid}' WHERE user_id = '{$db_user_id}' AND id = '{$db_device_id}'";
				mysql_execute($sql, $conn);
				
				// ADID바뀐 시간을 작성한다.
				if ($row['id']) 
					$sql = "UPDATE user_adid_changed_t SET cnt = cnt + 1, up_date = NOW() WHERE id = '{$row['id']}'";
				else 
					$sql = "INSERT user_adid_changed_t (device_id, cnt, up_date) VALUES ('{$db_device_id}', 1, NOW())";
				mysql_execute($sql, $conn);
			}
		}
	} 
	else 
	{
		$db_adid = mysql_real_escape_string($arr_data['adid']);
		$sql = "UPDATE user_device_t SET device_adid = '{$db_adid}' WHERE user_id = '{$db_user_id}' AND id = '{$db_device_id}'";
		mysql_execute($sql, $conn);
	}
	
	$arr_data['result'] = true;
	$arr_data['url'] = $exec_url;
	
	// $arr_data는 파라미터로 그대로 전달됨.
	return array('result' => 'Y', 'error' => '', 'error_msg' => "");
}

/*
	# 성공시 RESULT
		 $ar_data['result'] == true
	# 실패시 RESULT
		 $ar_data['result'] == false
		 $ar_data['error'] == -11
		 $ar_data['error_msg'] == '오류가 발생 ~ ..'
*/
// user-ad-done.php 에서 호출
function local_request_done($uid, $user_id, $app_key, $arr_data, $conn) 
{
	global $g_local, $dev_mode;

	if (!in_array($arr_data['app_exec_type'], array('I', 'E', 'S', 'R', 'F', 'C'))) {
		return array('result' => 'N', 'error' => '-999', 'error_msg' => "잘못된 app-exec-type 요청");
	}

	$db_user_id = mysql_real_escape_string($user_id);
	$db_appkey = mysql_real_escape_string($app_key);

	// Review 형의 경우 Review 정보를 사용한거면 사용으로 변경함
	if ($arr_data['app_exec_type'] == 'R') {	
		$sql = "UPDATE app_review_template_t SET used = 'Y' WHERE app_key = '{$db_appkey}' AND user_id = '{$db_user_id}'";
		mysql_execute($sql, $conn);
	}
	
	// ----------------------------------------------------------------------
	// LOCAL의 UNIQUE PREFIX : A
	// "A" . md5(appkey + adid) => UNIQUE 키
	// ----------------------------------------------------------------------
	$unique_key = $arr_data['user_app_id'];
	
	// 리뷰는 구글 계정당 1회 제한 ==> UserId & app_key로 UniqueKey를 조합을 한다.
	if ($arr_data['app_exec_type'] == 'R') $unique_key = $uid."#".$app_key;		
	
	if ($arr_data['app_exec_type'] == 'F') {
		// user-ad-start 에서는 unique_key 가 없으면 오류 발생
		$user_unique_key = $arr_data['user_unique_key'];
		if (!$user_unique_key) {
			return array('result' => 'N', 'error' => '-2001', 'error_msg' => "has-unique-key");
		}
		// 실제 DB에는 _cb에서 LOC을 더 붙여 들어감.
		$unique_key = md5($user_unique_key."#".$app_key);
	}
	
	// CPC는 uid + 현재 시간으로 ( 최대 64자 )
	if ($arr_data['app_exec_type'] == 'C') $unique_key = $uid."#".$app_key."#".date("YmdHi");
	
	// ----------------------------------------------------------------------

	if ($dev_mode) $url_param['dev'] = '1';
	
	$url_param['appkey'] = $app_key;
	$url_param['uniquekey'] = $unique_key;
	$url_param['useruniquekey'] = $user_unique_key;
	$url_param['is_autolaunch'] = $arr_data['is_autolaunch'];
	$url_param['auto_eventkey'] = $arr_data['auto_eventkey'];
	
	$url_param['userdata'] = base64_encode(json_encode(array('uid' => $uid, 'did' => $arr_data['user_device_id'])));
	
	$req_base_url = $g_local['callback'];
	
	// CPC 타입은 callback_cpc호출을 한다.
	if ($arr_data['app_exec_type'] == 'C') $req_base_url = $g_local['callback_cpc'];
	
	$req_url = $req_base_url . '&' . http_build_query($url_param);
	$start_tm = get_timestamp();
	$result_data = @file_get_contents($req_url);
	make_visit_log_url(get_timestamp() - $start_tm, basename(__FILE__), "local-request-callback", $req_url, $result_data, $conn);
	
	$js_obj = json_decode($result_data, true);
	if (!$js_obj['Result']) {
		return array('result' => 'N', 'error' => $js_obj['ResultCode'], 'error_msg' => $js_obj['ResultMsg']);
	}

	return array('result' => 'Y');
}

?>	
