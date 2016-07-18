<?

	// SAMPLE URL for LOC2
	// 요청 초기화 쿼리
	/*
		// 참여 테스트		
		DELETE FROM al_user_app_t WHERE app_key = 'LOC2' AND pcode = 'aline';
		DELETE FROM al_app_start_stat_t WHERE app_key = 'LOC2' AND pcode = 'aline';
		DELETE FROM al_user_saving_t WHERE app_key = 'LOC2' AND pcode = 'aline';
		DELETE FROM al_summary_user_sales_h_t WHERE pcode = 'aline' AND reg_day = CURRENT_DATE;

	*/

	// 완료 테스트 초기화
/*
	$test_adid = '0123456789012345-6789-0123-4567-8901';
	$test_pcode = 'aline';
	$test_appkey = 'LOC2';
	mysql_query($sql = "UPDATE al_user_app_t SET status = 'A', permanent_fail = 'N', action_dtime = NULL, done_day = NULL, unique_key = NULL WHERE app_key = '{$test_appkey}' AND pcode = '{$test_pcode}' AND adid = '{$test_adid}';", $conn);
	mysql_query($sql = "DELETE FROM al_user_app_saving_t WHERE app_key = '{$test_appkey}' AND pcode = '{$test_pcode}' AND adid = '{$test_adid}';", $conn);
	mysql_query($sql = "DELETE FROM al_summary_user_sales_h_t WHERE pcode = '{$test_pcode}' AND reg_day = CURRENT_DATE;", $conn);
	
	// die("INITILIZED AND EXIT");
*/
	// 요청하기
	// http://api.aline-soft.kr/ajax-request.php?id=get-done&pcode=aline&ad=LOC2&adid=0123456789012345-6789-0123-4567-8901

	$pub_mactive = get_publisher_info("reward_percent, callback_url", $ar_publisher);
	if (!$pub_mactive || $pub_mactive == 'N' || $pub_mactive == 'D') return_die('N', array('code'=>'-100'), '유효하지 않은 매체코드입니다.');

	$pcode = $_REQUEST['pcode'];
	$appkey = $_REQUEST['ad'];
	$adid = $_REQUEST['adid'];
	
	// $arr_param 기본 정보는 $_REQUEST 파라미터로 초기화
	$arr_param = $_REQUEST;
	
	if (!$pcode || !$appkey || !$adid) return_die('N', array('code'=>'-101'), '파라미터 오류입니다.');

	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_adid = mysql_real_escape_string($adid);
	
	$ar_time = mysql_get_time($conn);
	
	// $arr_param 에 시간 정보 추가
	$arr_param['now'] = $ar_time['now'];
	$arr_param['day'] = $ar_time['day'];
	
	// Reward Percent 추가
	$arr_param['reward_percent'] = $ar_publisher['reward_percent'];
	$arr_param['callback_url'] = $ar_publisher['callback_url'];
	
	// ----------------------------------------------------------------------------
	// pcode와 광고에 대한 기본 정보를 로딩하면서, 광고 참여 여부 Flag도 체크한다.
	$row_app = get_query_publisher_app($pcode, $appkey, $ar_time, $conn);
	
	// $arr_param['ad'] 에 al_app_t 정보를 추가한다.
	$arr_param['ad'] = $row_app;

	// ----------------------------------------------------------------------------
	// 광고가 없거나, 참여할 수 없는 상태입니다. ( edate, tot_exec 에 대한 N 처리는 list에서 수행 )
	if (!$row_app || 
		$row_app['is_active'] != 'Y' || 
		($pub_mactive == 'Y' && $row_app['is_mactive'] != 'Y') || 
		$row_app['m_mactive'] != 'Y' || 
		($pub_mactive == 'Y' && $row_app['p_mactive'] != 'Y') || 
		$row_app['pa_mactive'] != 'Y' || 
		$row_app['pa_disabled'] != 'Y' || 
		$row_app['p_level_block'] != 'Y' || 
		$row_app['pa_merchant_disabled'] != 'Y' || 
		$row_app['p_level_active_date'] != 'Y' || 
		$row_app['check_open_time'] != 'Y' || 
		$row_app['check_edate'] != 'Y' || 
		$row_app['check_tot_executed'] != 'Y') 
	{
		return_die('N', array('code'=>'-103'), '광고가 없거나 참여할 수 없는 상태입니다.');
	}

	// 광고가 수량이 완료되어 임시 중단된 상태입니다.
	if (
		$row_app['check_time_period'] != 'Y' || 
		$row_app['check_hour_executed'] != 'Y' || 
		$row_app['check_day_executed'] != 'Y' ) 
	{
		return_die('N', array('code'=>'-104'), '광고가 임시 중단된 상태입니다.');
	}

	// 실행형이 아니면 요청 불가함	
	if ($row_app['app_exec_type'] != 'I') {
		return_die('N', array('code'=>'-109'), '유효하지 않은 요청입니다.');
	}

	// ---------------------------------------------	
	// 해당 사용자의 업로드된 ADID에서의 참여 가능 여부 체크
	$sql = "SELECT adid FROM al_app_adid_uploaded_t WHERE app_key = '{$db_appkey}' AND adid = '{$db_adid}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row) {
		return_die('N', array('code'=>'-106'), '더 이상 참여할 수 없는 광고입니다.');
	}	

	// ---------------------------------------------	
	// 해당 사용자의 참여 가능 여부를 확인한다.
	$sql = "SELECT id, status, permanent_fail FROM al_user_app_t WHERE app_key = '{$db_appkey}' AND adid = '{$db_adid}' AND ( (status = 'D' AND forced_done = 'N') OR permanent_fail = 'Y' )";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row) {
		if ($row['status'] == 'D') {
			return_die('N', array('code'=>'-105'), '이미 참여한 광고입니다.');
		} else if ($row['permanent_fail'] == 'Y') {
			return_die('N', array('code'=>'-106'), '더 이상 참여할 수 없는 광고입니다.');
		}
	} 

	// ---------------------------------------------	
	// pcode로 해당 사용자의 참여 기록을 찾고 없으면 추가한다.
	$sql = "SELECT * FROM al_user_app_t WHERE pcode = '{$db_pcode}' AND app_key = '{$db_appkey}' AND adid = '{$db_adid}'";
	$row_userapp = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row_userapp && $row_userapp['status'] != 'F') {
		$sql = "UPDATE al_user_app_t 
				SET action_btime = '{$ar_time['now']}', 
					status = 'B'
				WHERE id = '{$row_userapp['id']}'";
		mysql_query($sql, $conn);
		$user_app_id = $row_userapp['id'];	
	} else {
		return_die('N', array('code'=>'-107'), '광고 참여한 기록이 없습니다.');
	}

	
	// user_app의 정보를 그대로 저장함.
	$arr_param['userapp'] = $row_userapp;
	
	// ## al_user_app_t.id 값을 저장
	$arr_param['user_app_id'] = $user_app_id;
	
	// --------------------------------------------------------
	// 각 Library사에 Done을 요청함
	// --------------------------------------------------------
	// x_requests_done에서는
	/* 
		1. 광고 적립에 대한 오류가 없는 지 확인 (Abusing, FlowCheck 등)
		2. 적립에 대한 Merchant사 적립확인 요청 URL을 생성한 후 ==> 요청하기 
			요청이 실패난 경우에만 status, action_ftime을 설정한다.
			( 관련 요청 로그는 site_action_log에 기록 )
			
		3. 요청완료가 되고 Merchant로부터 Callback을 받게 되면 적립, 상태 완료 후 Publisher Callback을 호출한다.
			Publisher Callback이 실패한 경우 callback_done => 'F' 로 설정한다
			( 관련 요청 로그는 site_action_log에 기록 )
	*/
	/* --------------------------------------------------------
		$arr_param
			pcode
			ad ==> al_app_t.*
			userapp ==> al_user_app_t.* (imei 등의 정보 존재) <-- get-join 과 차이
			ip
			adid
			
			user_app_id
			now
			day
	*/
	// --------------------------------------------------------

 	// var_dump($arr_param);
	if ($row_app['lib'] == 'LOCAL') {

		include dirname(__FILE__)."/_partner_local.php";
		$ar_result = local_request_done($appkey, $arr_param, false, $conn);
		
	} else if ($row_app['lib'] == 'OHC') {
		
		include dirname(__FILE__)."/_partner_ohc.php";
		$ar_result = ohc_request_done($appkey, $arr_param, $conn);
		
	} else if ($row_app['lib'] == 'SUCOMM') {
		
		include dirname(__FILE__)."/_partner_sucomm.php";
		$ar_result = sucomm_request_done($appkey, $arr_param, $conn);
		
	} else {
		
		return_die('N', array('code'=>'-110'), '광고 오류입니다.');
		
	}

	// --------------------------------------------------------
	// 광고 오류상태를 저장 (단 status가 B인 경우에만 갱신함 - 요청시에 바로 답이 오는 경우 변경이 되므로 이 경우 손대지 않도록 함)
	if ($ar_result['result'] == 'N') {
		$sql = "UPDATE al_user_app_t 
				SET action_ftime = '{$ar_time['now']}', status = 'F', error = '{$ar_data['code']}' 
				WHERE id = '{$user_app_id}' AND status = 'B'";
		return_die('N', $ar_result);
	}
	$ar_result['code'] = '1';
	return_die('Y', $ar_result);
	
?>
