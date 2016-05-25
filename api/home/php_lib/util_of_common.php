<?

function get_query_publisher_app($pcode, $appkey, $ar_time, $conn)
{
	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	
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
				
	$ret = @mysql_fetch_assoc(mysql_query($sql, $conn));
	// var_dump($ret);
	return $ret;
}

// 사용자 적립하기 (사용자 적립 및 al_user_app_t 상태 변경 모두 처리)
/*
	* 광고 적립가능 상태 (mactive, active등)는 체크하지 않음 호출 전에 이미 체크 끝내야 함.
	
	user_app_t 에 현재 상태를 확인 (D, Uniqkey키 중복인 경우 제외하기)

	- al_app_exec_stat_t 에 실시간 적립 개수 추가
	- al_user_app_t 에 적립 완료 및 unique_key 설정 (강제 적립이 있는 경우 일반 적립으로 변경)
	- al_user_saving_t 에 적립완료 로그 기록 (merchant 매출, publisher 매출 Row단위 기록)
	- al_user_saving_h_t 에 mcode, merchant_fee, pcode, publisher_fee, cnt값 증가
	
	- 오류 종류
		1. SQL 쿼리 중 오류
		2. 이미 지급 완료된 상태 (Unique 키 체크 또는 상태가 이미 D)
*/
function callback_reward($pcode, $appkey, $adid, $ar_time, $ar_app, $unique_key, $conn) {
	
	echo "callback_reward($pcode, $appkey, $adid)<br>";
	
	$db_mcode = mysql_real_escape_string($ar_app['mcode']);
	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_adid = mysql_real_escape_string($adid);
	$db_unique_key = mysql_real_escape_string($unique_key);
	
	$merchant_fee = $ar_app['app_merchant_fee'];
	$publisher_fee = $ar_app['publisher_fee'];
	
	try {
		begin_trans($conn);
		
			// al_user_app_t LOCK 걸기
			$sql = "SELECT id FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' and (status = 'D' OR forced_done = 'Y') FOR UPDATE";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {		// 이미 존재하면 오류 리턴
				rollback($conn);
				return array('result' => 'N', 'code' => '-3001');
			}
			
			$sql = "SELECT id FROM al_user_app_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}'";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			
//echo $sql . "<br>";
//var_dump($row);

			if (!$row['id']) {
				rollback($conn);
				return array('result' => 'N', 'code' => '-3002');
			}
			$user_app_id = $row['id'];
			
			// unique_key 중복에 대한 처리
			$sql = "SELECT id FROM al_user_app_t WHERE unique_key = '{$db_unique_key}'";
			echo $sql . "\n";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {
				rollback($conn);
				return array('result' => 'N', 'code' => '-3003');
			}
		
			// al_user_app_t 상태 완료로 변경
			$sql = "UPDATE al_user_app_t 
					SET action_dtime = '{$ar_time['now']}', 
						done_day = '{$ar_time['day']}', 
						status = 'D', 
						forced_done = 'N',
						merchant_fee = '{$merchant_fee}', 
						publisher_fee = '{$publisher_fee}'";
			echo $sql . "\n";
			mysql_execute($sql, $conn);
			
			// al_user_saving_t 매출 레코드 추가
			$sql = "INSERT INTO al_user_saving_t (user_app_id, mcode, pcode, app_key, adid, ip, merchant_fee, publisher_fee, unique_key, reg_day, reg_date)
					SELECT id, mcode, pcode, app_key, adid, ip, merchant_fee, publisher_fee, unique_key, done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
			echo $sql . "\n";
			mysql_execute($sql, $conn);
			
			$sql = "SELECT id FROM al_summary_user_sales_h_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['day']}') FOR UPDATE";
			echo $sql . "\n";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {
				$sql = "UPDATE al_summary_user_sales_h_t 
						SET merchant_cnt = merchant_cnt + 1, 
							merchant_fee = merchant_fee + '{$merchant_fee}',
							publisher_cnt = publisher_cnt + 1,
							publisher_fee = publisher_fee + '{$publisher_fee}'
						WHERE id = '{$row['id']}'";
				echo $sql . "\n";
				mysql_execute($sql, $conn);
			} else {
				$sql = "INSERT al_summary_user_sales_h_t (mcode, pcode, adid, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
						VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_adid}', '{$db_appkey}', '1', '{$merchant_fee}', '1', '{$publisher_fee}, '{$ar_time['day']}', HOUR('{$ar_time['day']}'))
						ON DUPLICATE KEY UPDATE merchant_cnt = merchant_cnt + 1, 
												merchant_fee = merchant_fee + '{$merchant_fee}',
												publisher_cnt = publisher_cnt + 1,
												publisher_fee = publisher_fee + '{$publisher_fee}';";
				echo $sql . "\n";
				mysql_execute($sql, $conn);
			}
	
		commit($conn);
	} 
	catch(Exception $e) 
	{
		echo $e->getMessage();
		rollback($conn);
		return array('result' => 'N', 'code' => '-3004', 'msg' => $e->getMessage());
	}
	
	
	return array('result' => 'Y');
}

function set_error_msg(&$arr_data) {
	
	if ($arr_data['result'] != 'N' || $arr_data['msg']) return;
	
	if ($arr_data['code'] == '-100') $arr_data['msg'] = '유효하지 않은 매체코드입니다.';
	else if ($arr_data['code'] == '-101') $arr_data['msg'] = '파라미터 오류입니다.  일부 파라미터가 빠져있습니다.';
	else if ($arr_data['code'] == '-102') $arr_data['msg'] = '파라미터 코드 값 오류입니다.';
	else if ($arr_data['code'] == '-103') $arr_data['msg'] = '광고가 없거나 참여할 수 없는 상태입니다.';
	else if ($arr_data['code'] == '-104') $arr_data['msg'] = '광고가 임시 중단된 상태입니다.';
	else if ($arr_data['code'] == '-105') $arr_data['msg'] = '이미 참여한 광고입니다.';
	else if ($arr_data['code'] == '-106') $arr_data['msg'] = '더 이상 참여할 수 없는 광고입니다.';
	
	else if ($arr_data['code'] == '-1001') $arr_data['msg'] = '광고 오류입니다. (no-packageid)';
	else if ($arr_data['code'] == '-1002') $arr_data['msg'] = '광고 오류입니다. (unknown-market)';
	else if ($arr_data['code'] == '-1003') $arr_data['msg'] = '광고 오류입니다. (no-url)';

}

?>