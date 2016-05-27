<?
	// SAMPLE URL for LOC2
	// 요청 초기화 쿼리
	/*
		DELETE FROM al_user_app_t WHERE app_key = 'LOC2' AND pcode = 'aline';
		DELETE FROM al_app_start_stat_t WHERE app_key = 'LOC2' AND pcode = 'aline';
	*/
	// 요청하기
	// http://api.aline-soft.kr/ajax-request.php?id=get-join&pcode=aline&os=A&ad=LOC2&adid=0123456789012345-6789-0123-4567-8901&ip=127.0.0.1&uid=heartman@gmail.com&userdata=USERDATA

	$pub_mactive = get_publisher_info();
	if (!$pub_mactive || $pub_mactive == 'D') return_die('N', array('code'=>'-100', 'type'=>'E-REQUEST'), '유효하지 않은 매체코드입니다.');

	$pcode = $_REQUEST['pcode'];
	$appkey = $_REQUEST['ad'];
	$adid = $_REQUEST['adid'];
	$ip = $_REQUEST['ip'];
	$uid = $_REQUEST['uid'];		// publisher사의 사용자 구별값 varchar(64)
	$userdata = $_REQUEST['userdata'];	// publisher사의 사용자 context text
	
	// $arr_param 기본 정보는 $_REQUEST 파라미터로 초기화
	$arr_param = $_REQUEST;
	
	if (!$pcode || !$appkey || !$uid || !$adid || !$ip) return_die('N', array('code'=>'-101', 'type'=>'E-REQUEST'), '파라미터 오류입니다..');
	
	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_adid = mysql_real_escape_string($adid);
	$db_ip = mysql_real_escape_string($ip);
	$db_uid = mysql_real_escape_string($uid);
	$db_userdata = mysql_real_escape_string($userdata);
	
	$ar_time = mysql_get_time($conn);
	
	// $arr_param 에 시간 정보 추가
	$arr_param['now'] = $ar_time['now'];
	$arr_param['day'] = $ar_time['day'];
	
	// ----------------------------------------------------------------------------
	// pcode와 광고에 대한 기본 정보를 로딩하면서, 광고 참여 여부 Flag도 체크한다.
	$row_app = get_query_publisher_app($pcode, $appkey, $ar_time, $conn);
	
	// $arr_param['ad'] 에 al_app_t 정보를 추가한다.
	$arr_param['ad'] = $row_app;

	// ----------------------------------------------------------------------------
	// 광고가 없거나, 참여할 수 없는 상태입니다. ( edate, tot_exec 에 대한 N 처리는 list에서 수행 )
	if (!$row_app || 
		$row_app['is_active'] != 'Y' || 
		$row_app['is_mactive'] != 'Y' || 
		$row_app['m_mactive'] != 'Y' || 
		$row_app['p_mactive'] != 'Y' || 
		$row_app['pa_mactive'] != 'Y' || 
		$row_app['pa_disabled'] != 'Y' || 
		$row_app['p_level_block'] != 'Y' || 
		$row_app['pa_merchant_disabled'] != 'Y' || 
		$row_app['p_level_active_date'] != 'Y' || 
		$row_app['check_open_time'] != 'Y' || 
		$row_app['check_edate'] != 'Y' || 
		$row_app['check_tot_executed'] != 'Y') 
	{
		$log = $row_app['is_active'] . $row_app['is_mactive'] . $row_app['m_mactive'] . $row_app['p_mactive'] . $row_app['pa_mactive'] . $row_app['pa_disabled'] .
				$row_app['p_level_block'] . $row_app['pa_merchant_disabled'] . $row_app['p_level_active_date'] . $row_app['check_open_time'] . $row_app['check_edate'] . $row_app['check_tot_executed'];
		return_die('N', array('code'=>'-103', 'type'=>'E-CLOSED'), '광고가 없거나 참여할 수 없는 상태입니다.' . $log);
	}

	// 광고가 수량이 완료되어 임시 중단된 상태입니다.
	if (
		$row_app['check_time_period'] != 'Y' || 
		$row_app['check_hour_executed'] != 'Y' || 
		$row_app['check_day_executed'] != 'Y' ) 
	{
		$log = $row_app['check_time_period'] . $row_app['check_hour_executed'] . $row_app['check_day_executed'];
		return_die('N', array('code'=>'-104', 'type'=>'E-PAUSED'), '광고가 임시 중단된 상태입니다.' . $log);
	}

	// ---------------------------------------------	
	// 해당 사용자의 참여 가능 여부를 확인한다.
	$sql = "SELECT id, status, permanent_fail FROM al_user_app_t WHERE app_key = '{$db_appkey}' AND adid = '{$db_adid}' AND ( status = 'D' OR permanent_fail = 'Y' )";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row) {
		if ($row['status'] == 'D') {
			return_die('N', array('code'=>'-105', 'type'=>'E-DONE'), '이미 참여한 광고입니다.');
		} else if ($row['permanent_fail'] == 'Y') {
			return_die('N', array('code'=>'-106', 'type'=>'E-DONE'), '더 이상 참여할 수 없는 광고입니다.');
		}
	} 
	
	// ---------------------------------------------	
	// # 시도 회수를 추가한다.
	// ---------------------------------------------	
	$sql = "INSERT al_app_start_stat_t (pcode, app_key, start_cnt, reg_day)
			VALUES ('{$db_pcode}', '{$db_appkey}', '1', '{$ar_time['day']}')
			ON DUPLICATE KEY UPDATE start_cnt = start_cnt + 1";
	mysql_query($sql, $conn);
	
	// ---------------------------------------------	
	// pcode로 해당 사용자의 참여 기록을 찾고 없으면 추가한다.
	$sql = "SELECT id, status, permanent_fail FROM al_user_app_t WHERE pcode = '{$db_pcode}' AND app_key = '{$db_appkey}' AND adid = '{$db_adid}'";
	$row_userapp = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row_userapp) {
		$sql = "UPDATE al_user_app_t 
				SET ip = '{$db_ip}',
						uid = '{$db_uid}', 
						userdata = '{$db_userdata}', 
						merchant_fee = '{$row_app['app_merchant_fee']}', 
						publisher_fee = '{$row_app['publisher_fee']}', 
						action_atime = '{$ar_time['now']}', 
						status = 'A'
				WHERE id = '{$row_userapp['id']}'";
		mysql_query($sql, $conn);
		$user_app_id = $row_userapp['id'];	
	} else {
		$sql = "INSERT al_user_app_t (mcode, pcode, app_key, adid, ip, uid, userdata, merchant_fee, publisher_fee, action_atime, status, reg_day, reg_date)
				VALUES ('{$row_app['mcode']}', '{$db_pcode}', '{$db_appkey}', '{$db_adid}', '{$db_ip}', '{$db_uid}', '{$db_userdata}', '{$row_app['app_merchant_fee']}', '{$row_app['publisher_fee']}', '{$ar_time['now']}', 'A', '{$ar_time['day']}', '{$ar_time['now']}')";
		mysql_query($sql, $conn);
		$user_app_id = mysql_insert_id($conn);
	}

	// ## al_user_app_t.id 값을 저장
	$arr_param['user_app_id'] = $user_app_id;
	
// var_dump($arr_param);
	
	// --------------------------------------------------------
	if ($row_app['lib'] == 'LOCAL') {
		include dirname(__FILE__)."/_partner_local.php";
		$ar_data = local_request_start($appkey, $arr_param, $conn);
	} else {
		return_die('N', array('code'=>'-110', 'type'=>'E-CONFIG'), '광고 오류입니다.');
	}
	
	// --------------------------------------------------------
	if ($ar_data['result'] == 'N') {

		// 광고 오류		
		$ar_data['type'] = 'E-AD';
		
		// E-DONE인 경우 더이상 재 시도 하지 않도록 함.
		if ( in_array($ar_data['type'], array('E-DONE')) ) $err_nomorejoin = 'Y'; else $err_nomorejoin = 'N';
		
		// 광고 오류상태를 저장
		$sql = "UPDATE al_user_app_t 
				SET 
					action_ftime = '{$ar_time['now']}',
					status = 'F',
					permanent_fail = '{$err_nomorejoin}',
					error = '{$ar_data['code']}'
				WHERE id = '{$user_app_id}' AND status = 'A'";
		return_die('N', $ar_data);
		
	}
	
	// 광고 시작상태를 저장 (A 상태는 transaction걸지 않고 되는 데로 그때 그때 로깅한다.)
	$sql = "UPDATE al_user_app_t SET status = 'A' WHERE id = '{$user_app_id}'";	
	$ar_data['url'] = $arr_param['url'];
	
// var_dump(@mysql_fetch_assoc(mysql_query("SELECT * FROM al_app_start_stat_t WHERE app_key = '{$db_appkey}'", $conn)));
// var_dump(@mysql_fetch_assoc(mysql_query("SELECT * FROM al_user_app_t WHERE id = '{$user_app_id}'", $conn)));

	return_die('Y', $ar_data);
	
?>
