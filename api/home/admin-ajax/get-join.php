<?
	$os = $_REQUEST['os'];
	$pcode = $_REQUEST['pcode'];
	$appkey = $_REQUEST['ad'];
	$adid = $_REQUEST['adid'];
	$ip = $_REQUEST['ip'];
	$uid = $_REQUEST['uid'];		// publisher사의 사용자 구별값 varchar(64)
	$userdata = $_REQUEST['udata'];	// publisher사의 사용자 context text
	
	// $arr_param 기본 정보는 $_REQUEST 파라미터로 초기화
	$arr_param = $_REQUEST;
	
	if (!$os || !$pcode || !$appkey || !$uid || (!$adid && !$imei) || !$ip) return_die('N', array('code'=>'1001'), '파라미터 오류입니다..');
	
	if ( !in_array($os, array('I', 'A')) )  return_die(false, array('code'=>'1002'), '파라미터 코드 값 오류입니다. (os)');

	$db_os = mysql_real_escape_string($os);
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
	$sql = "SELECT app.*, 
				IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 

				m.is_mactive as 'm_mactive',
				p.is_mactive as 'p_mactive',

				IFNULL(pa.is_mactive, 'Y') as 'pa_mactive',
				IF(IFNULL(pa.publisher_disabled, 'N') = 'N', 'Y', 'N') AS 'pa_disabled',

				IF (app.publisher_level IS NULL OR p.level <= app.publisher_level, 'Y', 'N') as 'p_level_block',

				IF (app.is_public_mode = 'Y', 
					IF(IFNULL(pa.merchant_disabled,'N')='N','Y', 'N'),
					IF(IFNULL(pa.merchant_enabled,'N')='Y', 'Y', 'N')) as 'pa_merchant_disabled',

				(CASE 
					WHEN p.level = 1 AND (level_1_active_date IS NULL OR level_1_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level = 2 AND (level_2_active_date IS NULL OR level_2_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level = 3 AND (level_3_active_date IS NULL OR level_3_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level = 4 AND (level_4_active_date IS NULL OR level_4_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level >= 5 THEN 'Y'
					ELSE 'N' END) as 'p_level_active_date',

				IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'Y', 'N') as 'check_edate',
				IF ( ( app.exec_stime IS NULL OR app.exec_etime IS NULL ) OR
				  	  IF ( app.exec_stime <= app.exec_etime, 
				  	 	 app.exec_stime <= '{$ar_time['hour']}' AND app.exec_etime > '{$ar_time['hour']}', 
				  	 	 app.exec_stime < '{$ar_time['hour']}' OR app.exec_etime >= '{$ar_time['hour']}' )
					, 'Y', 'N') as 'check_time_period',

				IF (pa.active_time IS NULL OR pa.active_time <= '{$ar_time['datehour']}', 'Y', 'N') as 'check_open_time',
				IF (IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NOT NULL AND IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0), 'N', 'Y') as 'check_hour_executed',
				IF (IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NOT NULL AND IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0), 'N', 'Y' ) as 'check_day_executed',
				IF (s.id IS NULL OR IFNULL(pa.exec_tot_max_cnt, app.exec_tot_max_cnt) < s.exec_tot_cnt, 'Y', 'N') as 'check_tot_executed'

			FROM al_app_t app
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pa.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_app_exec_stat_t s ON app.app_key = s.app_key
			WHERE
				app.app_key = '{$db_appkey}'";

	$row_app = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	// $arr_param['ad'] 에 al_app_t 정보를 추가한다.
	$arr_param['ad'] = $row_app;
	
	// ----------------------------------------------------------------------------
	// 광고가 없거나, 참여할 수 없는 상태입니다.
	if (!$row_app || 
		$row_app['m_mactive'] != 'Y' || 
		$row_app['p_mactive'] != 'Y' || 
		$row_app['pa_mactive'] != 'Y' || 
		$row_app['pa_disabled'] != 'Y' || 
		$row_app['p_level_block'] != 'Y' || 
		$row_app['pa_merchant_disabled'] != 'Y' || 
		$row_app['p_level_active_date'] != 'Y' || 
		$row_app['check_edate'] != 'Y' || 
		$row_app['check_open_time'] != 'Y' || 
		$row_app['check_tot_executed'] != 'Y') 
	{
		return_die('N', array('code'=>'1003'), '광고가 없거나 참여할 수 없는 상태입니다.');
	}

	// 광고가 수량이 완료되어 임시 중단된 상태입니다.
	if (
		$row_app['check_time_period'] != 'Y' || 
		$row_app['check_hour_executed'] != 'Y' || 
		$row_app['check_day_executed'] != 'Y' ) 
	{
		return_die('N', array('code'=>'1004'), '광고가 임시 중단된 상태입니다.');
	}

	// ---------------------------------------------	
	// 해당 사용자의 참여 가능 여부를 확인한다.
	$sql = "SELECT id, status, permanent_fail FROM al_user_app_t WHERE app_key = '{$db_appkey}' AND adid = '{$db_adid}' AND ( status = 'D' OR permanent_fail = 'Y' )";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row) {
		if ($row['status'] == 'D') {
			return_die('N', array('code'=>'1005'), '이미 참여한 광고입니다.');
		} else if ($row['permanent_fail'] == 'Y') {
			return_die('N', array('code'=>'1006'), '더 이상 참여할 수 없는 광고입니다.');
		}
	} 
	
	// ---------------------------------------------	
	// pcode로 해당 사용자의 참여 기록을 찾고 없으면 추가한다.
	$sql = "SELECT id, status, permanent_fail FROM al_user_app_t WHERE pcode = '{$db_pcode}' AND app_key = '{$db_appkey}' AND adid = '{$db_adid}'";
	$row_userapp = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row_userapp) {
		$sql = "UPDATE al_user_app_t 
				SET ip = '{$db_ip}',
						puid = '{$db_uid}', 
						puserdata = '{$db_userdata}', 
						fee_merchant = '{$row_app['app_merchant_fee']}', 
						fee_publisher = '{$row_app['publisher_fee']}', 
						action_atime = '{$ar_time['now']}', 
						status = 'A'
				WHERE id = '{$row_userapp['id']}'";
		mysql_query($sql, $conn);
		$userapp_id = $row_userapp['id'];	
	} else {
		$sql = "INSERT al_user_app_t (mcode, pcode, app_key, adid, ip, puid, puserdata, fee_merchant, fee_publisher, action_atime, status, reg_day, reg_date)
				VALUES ('{$row_app['mcode']}', '{$db_pcode}', '{$db_appkey}', '{$db_adid}', '{$db_ip}', '{$db_uid}', '{$db_userdata}', '{$row_app['app_merchant_fee']}', '{$row_app['publisher_fee']}', '{$ar_time['now']}', 'A', '{$ar_time['day']}', '{$ar_time['now']}')";
		mysql_query($sql, $conn);
		$userapp_id = mysql_insert_id($conn);
	}
	
	// ## al_user_app_t.id 값을 저장
	$arr_param['userapp_id'] = $userapp_id;
	
	// --------------------------------------------------------
	include dirname(__FILE__)."/_partner_local.php";
	$ar_data = local_request_start($appkey, $arr_param, $conn);
	if ($ar_data['result'] == 'N') {
		
		
	}
	// --------------------------------------------------------
	
	// --------------------------------------------------------


	// --------------------------------------------------------
	
	return_die('Y', $ar_data);
	
?>
