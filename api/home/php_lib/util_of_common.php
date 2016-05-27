<?
/*
// IF (IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NULL OR IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0), 'Y', 'N') as 'check_hour_executed',
// IF (IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NULL OR IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0), 'Y', 'N') as 'check_day_executed',
	pa에 시간당 제한이 NULL이면 --> app의 제값을 사용하고, 모두 NULL이면 Y
	아니면 값이 현재 실행 수보다 크면 Y (같아도 완료된것이므로 N 이됨)
*/
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
				IF (IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NULL OR IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0), 'Y', 'N') as 'check_hour_executed',
				IF (IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NULL OR IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(DATE(s.exec_time) = '{$ar_time['day']}', s.exec_day_cnt, 0), 'Y', 'N') as 'check_day_executed',
				IF (s.app_key IS NULL OR IFNULL(pa.exec_tot_max_cnt, app.exec_tot_max_cnt) > s.exec_tot_cnt, 'Y', 'N') as 'check_tot_executed'

			FROM al_app_t app
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pa.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_app_exec_stat_t s ON app.app_key = s.app_key
			WHERE
				app.app_key = '{$db_appkey}'";	
	// echo $sql;
	$ret = @mysql_fetch_assoc(mysql_query($sql, $conn));
	return $ret;
}

// 사용자 적립하기 (사용자 적립 및 al_user_app_t 상태 변경 모두 처리)
/*
	* 광고 적립가능 상태 (mactive, active등)는 체크하지 않음 호출 전에 이미 체크 끝내야 함.
	
	user_app_t 에 현재 상태를 확인 (D, Uniqkey키 중복인 경우 제외하기)

	- al_app_exec_stat_t 에 실시간 적립 개수 추가
	- al_user_app_t 에 적립 완료 및 unique_key 설정 (강제 적립이 있는 경우 일반 적립으로 변경)
	- al_user_app_saving_t 에 적립완료 로그 기록 (merchant 매출, publisher 매출 Row단위 기록)
	- al_user_saving_h_t 에 mcode, merchant_fee, pcode, publisher_fee, cnt값 증가
	
	- 오류 종류
		1. SQL 쿼리 중 오류
		2. 이미 지급 완료된 상태 (Unique 키 체크 또는 상태가 이미 D)
*/

// 정상 적립 처리 (강제 적립된 상태로 처리함)
// 만약 $ar_return['callback_done'] == 'Y' 이면 이미 콜백을 호출함 안됨
function callback_reward($pcode, $mcode, $appkey, $adid, 
						$merchant_fee, $publisher_fee, $unique_key, 
						$ar_time, $conn) {
	
	// echo "callback_reward($pcode, $appkey, $adid)<br>";
	
	$db_mcode = mysql_real_escape_string($mcode);
	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_adid = mysql_real_escape_string($adid);
	$db_unique_key = mysql_real_escape_string($unique_key);
	
	$is_forceddone = 'N';
	try {
		begin_trans($conn);
		
			// al_user_app_t LOCK 걸기
			$sql = "SELECT count(*) cnt FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' FOR UPDATE";
			mysql_query($sql, $conn);
			
			// 이미 해당 앱키에 대해서 adid가 적립(강제적립제외) 받은적이 있다면 오류
			$sql = "SELECT id FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' and status = 'D' AND forced_done = 'N'";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {		// 이미 존재하면 오류 리턴
				rollback($conn);
				return array('result' => 'N', 'code' => '-3001');
			}
			
			$sql = "SELECT id, forced_done, callback_done FROM al_user_app_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}'";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if (!$row['id']) {
				rollback($conn);
				return array('result' => 'N', 'code' => '-3002');
			}
			$user_app_id = $row['id'];
			$is_forceddone = ifempty($row['forced_done'], 'N');
			$callback_done = $row['callback_done'];
			
			// unique_key 중복에 대한 처리
			$sql = "SELECT id FROM al_user_app_saving_t WHERE unique_key = '{$db_unique_key}'";
			// echo $sql . "\n";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {
				rollback($conn);
				return array('result' => 'N', 'code' => '-3003');
			}
		
			if ($is_forceddone == 'N')
			{
				//////////////////////////////////////////////////////
				// 최초 적립
				//////////////////////////////////////////////////////
		
				// al_user_app_t 상태 완료로 변경
				$sql = "UPDATE al_user_app_t 
						SET action_dtime = '{$ar_time['now']}', 
							done_day = '{$ar_time['day']}', 
							status = 'D', 
							forced_done = 'N',
							unique_key = '{$db_unique_key}',
							merchant_fee = '{$merchant_fee}', 
							publisher_fee = '{$publisher_fee}'
						WHERE id = '{$user_app_id}'";
				// echo $sql . "\n";
				mysql_execute($sql, $conn);
				
				// al_user_app_saving_t 매출 레코드 추가
				$sql = "INSERT INTO al_user_app_saving_t (user_app_id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, unique_key, m_reg_day, m_reg_date, p_reg_day, p_reg_date)
						SELECT id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, '{$db_unique_key}', done_day, action_dtime, done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
				// echo $sql . "\n";
				mysql_execute($sql, $conn);
				
				$sql = "SELECT id FROM al_summary_user_sales_h_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
				//echo $sql . "\n";
				$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
				if ($row['id']) {
					$sql = "UPDATE al_summary_user_sales_h_t 
							SET merchant_cnt = merchant_cnt + 1, 
								merchant_fee = merchant_fee + '{$merchant_fee}',
								publisher_cnt = publisher_cnt + 1,
								publisher_fee = publisher_fee + '{$publisher_fee}'
							WHERE id = '{$row['id']}'";
					// echo $sql . "\n";
					mysql_execute($sql, $conn);
				} else {
					// Merchant Fee가 0보다 큰경우에 Merchant_cnt를 1 증가시킨다.
					$sql = "INSERT al_summary_user_sales_h_t (mcode, pcode, adid, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
							VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_adid}', '{$db_appkey}', '1', '{$merchant_fee}', '1', '{$publisher_fee}', '{$ar_time['day']}', HOUR('{$ar_time['now']}'))
							ON DUPLICATE KEY UPDATE merchant_cnt = merchant_cnt + 1, 
													merchant_fee = merchant_fee + '{$merchant_fee}',
													publisher_cnt = publisher_cnt + 1,
													publisher_fee = publisher_fee + '{$publisher_fee}';";
					// echo $sql . "\n";
					mysql_execute($sql, $conn);
				}
				
			}
			else
			{
						//////////////////////////////////////////////////////
						// 강제 적립된 상태 
						//////////////////////////////////////////////////////
						
						// al_user_app_t 상태 완료로 변경 <== [강제적립한경우]에는 publisher_fee를 설정하지 않음, 실제 적립완료시간은 지금시간으로 함.
						$sql = "UPDATE al_user_app_t 
								SET action_dtime = '{$ar_time['now']}', 
									done_day = '{$ar_time['day']}', 
									status = 'D', 
									forced_done = 'N',
									unique_key = '{$db_unique_key}',
									merchant_fee = '{$merchant_fee}'
								WHERE id = '{$user_app_id}'";
						// echo $sql . "\n";
						mysql_execute($sql, $conn);
						
						// al_user_app_saving_t 매출 <== [강제적립한경우]에는 기존 매출테이블에 merchant_fee와 unique_key, 그리고 m_reg_day, m_reg_date만 갱신한다.
						$sql = "UPDATE al_user_app_saving_t
								SET merchant_fee = '{$merchant_fee}',
									unique_key = '{$db_unique_key}',
									m_reg_day = '{$ar_time['day']}',
									m_reg_date = '{$ar_time['now']}'
								WHERE user_app_id = '{$user_app_id}'";
						// echo $sql . "\n";
						mysql_execute($sql, $conn);
						
						
						// <== [강제적립한경우]에는 Merchant매출 만 갱신한다.
						$sql = "SELECT id FROM al_summary_user_sales_h_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
						$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
						if ($row['id']) {
							$sql = "UPDATE al_summary_user_sales_h_t 
									SET merchant_cnt = merchant_cnt + 1, 
										merchant_fee = merchant_fee + '{$merchant_fee}'
									WHERE id = '{$row['id']}'";
							// echo $sql . "\n";
							mysql_execute($sql, $conn);
						} else {
							// <== [강제적립한경우]에는 Merchant 만 갱신한다. (Publisher는 0건, 0원)
							$sql = "INSERT al_summary_user_sales_h_t (mcode, pcode, adid, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
									VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_adid}', '{$db_appkey}', '1', '{$merchant_fee}', '0', '0', '{$ar_time['day']}', HOUR('{$ar_time['now']}'))
									ON DUPLICATE KEY UPDATE merchant_cnt = merchant_cnt + 1, 
															merchant_fee = merchant_fee + '{$merchant_fee}'";
							// echo $sql . "\n";
							mysql_execute($sql, $conn);
						}				
			}
			
			// al_app_exec_stat_t 에 수행 개수를 추가한다.
			$sql = "INSERT INTO al_app_exec_stat_t (app_key, exec_time, exec_hour_cnt, exec_day_cnt, exec_tot_cnt)
					VALUES ('{$db_appkey}', '{$ar_time['datehour']}', '1', '1', '1')
					ON DUPLICATE KEY UPDATE exec_hour_cnt = IF(exec_time = '{$ar_time['datehour']}', exec_hour_cnt + 1, 1),
											exec_day_cnt = IF(DATE(exec_time) = '{$ar_time['day']}', exec_day_cnt + 1, 1),
											exec_tot_cnt = exec_tot_cnt + 1,
											exec_time = '{$ar_time['datehour']}'";
			// echo $sql;											
			mysql_execute($sql, $conn);
				
	
		commit($conn);
	} 
	catch(Exception $e) 
	{
		echo $e->getMessage();
		rollback($conn);
		return array('result' => 'N', 'code' => '-3004', 'msg' => $e->getMessage());
	}
	
	
	return array('result' => 'Y', 'callback_done' => $callback_done);
}

// 강제 적립 처리하기 (강제적립은 $merchant_fee, $unique_key가 전달되지 않음)
/*
	al_user_app_saving_t
		merchant_fee : 0
		unique_key 	: NULL
	al_user_app_t
		unique_key	: NULL
		forced_done	: Y	
*/

function force_reward($pcode, $mcode, $appkey, $adid, 
						$publisher_fee, 
						$ar_time, $conn) {
	
	// echo "callback_reward($pcode, $appkey, $adid)<br>";
	$db_mcode = mysql_real_escape_string($mcode);
	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_adid = mysql_real_escape_string($adid);
	
	try {
		begin_trans($conn);
						
								// al_user_app_t LOCK 걸기
								$sql = "SELECT count(*) cnt FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' FOR UPDATE";
								mysql_query($sql, $conn);
								
								// 이미 해당 앱키에 대해서 adid가 적립(강제적립제외) 받은적이 있다면 오류
								$sql = "SELECT id FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' and status = 'D' AND forced_done = 'N'";
								$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
								if ($row['id']) {		// 이미 존재하면 오류 리턴
									rollback($conn);
									return array('result' => 'N', 'code' => '-3001');
								}
								
								//// 적립 시도 정보를 체크하고 <== [강제적립]되어 있는 경우 오류 처리한다.
								$sql = "SELECT id, forced_done FROM al_user_app_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}'";
								$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
								if (!$row['id']) {
									rollback($conn);
									return array('result' => 'N', 'code' => '-3002');
								}
								if ($row['forced_done'] == 'Y') {
									rollback($conn);
									return array('result' => 'N', 'code' => '-3101');
								}
								$user_app_id = $row['id'];
								
								// al_user_app_t 상태 <== [강제적립] 상태로 변경하고, merchant_fee는 설정하지 않는다.
								$sql = "UPDATE al_user_app_t 
										SET action_dtime = '{$ar_time['now']}', 
											done_day = '{$ar_time['day']}', 
											status = 'D', 
											forced_done = 'Y',
											publisher_fee = '{$publisher_fee}'
										WHERE id = '{$user_app_id}'";
								// echo $sql . "\n";
								mysql_execute($sql, $conn);
								
								//// al_user_app_saving_t 매출 레코드 추가 <== [강제적립]은 Merchant매출 = 0, Unique키는 존재하지 않음, 또한 아래의 중복키가 존재해서도 안됨 (날짜는 현재 시간 <-- 위에서 설정됨)
								$sql = "INSERT INTO al_user_app_saving_t (user_app_id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, m_reg_day, m_reg_date, p_reg_day, p_reg_date)
										SELECT id, mcode, pcode, app_key, adid, 0, publisher_fee, done_day, action_dtime, done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
								// echo $sql . "\n";
								mysql_execute($sql, $conn);
								
								$sql = "SELECT id FROM al_summary_user_sales_h_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
								$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
								if ($row['id']) {
									// <== [강제적립]은 Merchant 매출 정보를 건드리지 않음.
									$sql = "UPDATE al_summary_user_sales_h_t 
											SET publisher_cnt = publisher_cnt + 1,
												publisher_fee = publisher_fee + '{$publisher_fee}'
											WHERE id = '{$row['id']}'";
									// echo $sql . "\n";
									mysql_execute($sql, $conn);
								} else {
									// <== [강제적립]은 Merchant 매출및 건수를 0으로
									$sql = "INSERT al_summary_user_sales_h_t (mcode, pcode, adid, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
											VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_adid}', '{$db_appkey}', '0', '0', '1', '{$publisher_fee}', '{$ar_time['day']}', HOUR('{$ar_time['now']}'))
											ON DUPLICATE KEY UPDATE publisher_cnt = publisher_cnt + 1,
																	publisher_fee = publisher_fee + '{$publisher_fee}';";
									// echo $sql . "\n";
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
	else if ($arr_data['code'] == '-106') $arr_data['msg'] = '더 이상 참여할 수 없는 광고입니다.';	// permanent_fail = 'Y'
	
	else if ($arr_data['code'] == '-107') $arr_data['msg'] = '광고 참여한 기록이 없습니다.';
	else if ($arr_data['code'] == '-109') $arr_data['msg'] = '유효하지 않은 요청입니다';			// DONE을 실행형이 아닌데 요청함.
	else if ($arr_data['code'] == '-110') $arr_data['msg'] = '광고 오류입니다.';					// 광고 LIB에 대한 처리가 존재하지 않음
	
	else if ($arr_data['code'] == '-1001') $arr_data['msg'] = '광고 오류입니다. (no-packageid)';
	else if ($arr_data['code'] == '-1002') $arr_data['msg'] = '광고 오류입니다. (unknown-market)';
	else if ($arr_data['code'] == '-1003') $arr_data['msg'] = '광고 오류입니다. (no-url)';

	// DB에 적립 중 발생 오류
	else if ($arr_data['code'] == '-3001') $arr_data['msg'] = '중복 적립 오류';
	else if ($arr_data['code'] == '-3002') $arr_data['msg'] = '적립 시도 기록이 없음';
	else if ($arr_data['code'] == '-3003') $arr_data['msg'] = 'UNIQUE 키 중복 오류';
	else if ($arr_data['code'] == '-3004') $arr_data['msg'] = 'DB 처리 중 오류 발생';

}

?>