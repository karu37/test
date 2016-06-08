<?
/* 
	IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
		: pa에 지정된 가격이 있으면 그것을 사용하고
		: 그렇지 않고 pa에 지정된 율이 있으면 그 율로 계산
		: 그렇지 않으면 기본 계산법으로 계산

	1번 al_app_t.is_mactive						: [관리자]가 해당 광고 활성/중지/삭제 ( Y/N/D )
	2번 al_app_t.is_active						: [Merchant]가 해당 광고 활성/중지 ( Y/N )
	
	3번 al_merchant_t.is_mactive				: [관리자]가 Merchant Code 활성/테스트/중지/삭제 ( Y/T/N/D )
	4번 al_publisher_t.is_mactive				: [관리자]가 Publisher Code 활성/테스트/중지/삭제 ( Y/T/N/D )
	
	5번 al_publisher_app_t.is_mactive 			: [관리자]가 Publisher에게 app공급 활성/중지/삭제 ( Y/N/D )
	6번 al_publisher_app_t.publisher_disabled 	: [Publisher]가 광고 받기를 중지 ( Y )

	7번 al_app_t.publisher_level				: Publisher 공급 레벨 지정
			al_publisher_t.level				: 	app의 공급레벨보다 낮은 경우(숫자로는 높은경우) 공급 차단
		
	8번 al_app_t.is_public_mode					: [Merchant]의 public 모드 설정
			al_publisher_app_t.merchant_disabled: is_public_mode = Y인 경우 참고함 'N'이면 차단
			al_publisher_app_t.merchant_enabled	: is_public_mode = N인 경우 참고함 'Y'이면 차단

	-- 광고 자체 오픈 시간 조정 (아래조건은 모두 AND)

	 	al_app_t.exec_stime ~ exec_etime		: 광고에 설정된 광고 시작 시간
	
	 	al_publisher_app_t.active_time			: 광고 활성 시간 - 관리자가 설정함 - 해당 Publisher & 광고를 허용/금지
	 	
※※※ al_app_t.exec_edate					: end 시간보다 이전일 때에만 동작 (해당일 23:59:59 까지 동작 )
	 		IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'N', 'Y') as 'edate_expired' ## 오늘포함해서 미래까지모두 동작하도록 함 (어제날짜인경우 차단)
	 		==> 초과 체크해서 app.is_active 를 'N'
		
		※ exec개수 정보가 없는 경우 또는 NOT( exec 개수 체크해서 개수가 0 이상으로 설정되어 있으면서 개수 초과를 한 경우 표시하지 않음 )
			(
				# 시간및 일일 제한이 없는 경우 그냥 OK
				(
				  IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NULL 	# 시간당 제한이 없는 경우 
				   AND
				  IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NULL		# 일일 제한이 없는 경우
				)
				OR
				(
				 # 시간당 제한이 설정되어 있고, 시간당 개수가 초과하지 않은 경우
				 ( IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NOT NULL AND IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0) )
				  OR
				 # 일일 제한이 설정되어 있고, 일일 개수가 초과하지 않은 경우
				 ( IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NOT NULL AND IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0) )
				)
			)

	 	※ al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.exec_tot_cnt	
			==> 초과 체크해서 app.is_active 를 'N'

	# 쿼리 결과에서 ==> 아래 대상은 is_active = 'N' 변경 
		if ($row['tot_not_complished'] != 'Y' || $row['edate_not_expired'] != 'Y')

*/
function get_query_app_list($pcode, $ar_time, $b_hide_exhauseted, $b_test_publisher, $conn)
{
	$where_add = "";
	// 기간 종료 및 최대 개수 초과를 미리 제거할지 리턴시킬지 여부 ==> 이 광고는 is_active='N' 변경 대상 처리
	if ($b_hide_exhauseted) {
		$where_add = "AND IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'Y', 'N') = 'N'
					AND IF (s.app_key IS NULL OR IFNULL(pa.exec_tot_max_cnt, app.exec_tot_max_cnt) > s.exec_tot_cnt, 'Y', 'N') = 'N'";
	}
	
	$app_is_mactive = "Y";
	$p_is_mactive = "'Y'";
	// 테스트 모드는 app.is_mactive = 'T' 로 조회
	if ($b_test_publisher) {
		$app_is_mactive = "T";
		$p_is_mactive = "'Y', 'T'";
	}
	
	$db_pcode = mysql_real_escape_string($pcode);
	$sql = "SELECT app.*, 
				m.name AS 'merchant_name', 
				
				IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
				
				IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'Y', 'N') as 'edate_not_expired',
				
				IF (app.exec_tot_max_cnt > IFNULL(s.exec_tot_cnt, 0), 'Y', 'N') as 'tot_not_complished'
				
			FROM al_app_t app
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pa.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_app_exec_stat_t s ON app.app_key = s.app_key
				LEFT OUTER JOIN al_app_exec_pub_stat_t ps ON app.app_key = ps.app_key AND ps.pcode = '{$db_pcode}' 
			WHERE 1=1
				AND app.is_active = 'Y'
				AND app.is_mactive = '{$app_is_mactive}'

				AND m.is_mactive = 'Y'
				AND p.is_mactive IN ({$p_is_mactive})
				
				AND IFNULL(pa.is_mactive, 'Y') = 'Y'
				AND IFNULL(pa.publisher_disabled, 'N') = 'N'
				
				AND (app.publisher_level IS NULL OR p.level <= app.publisher_level)
				
				AND IF (app.is_public_mode = 'Y', 
					IF(IFNULL(pa.merchant_disabled,'N')='N','Y', 'N'),
					IF(IFNULL(pa.merchant_enabled,'N')='Y', 'Y', 'N')) = 'Y'
				
				AND (CASE 
					WHEN p.level = 1 AND (level_1_active_date IS NULL OR level_1_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level = 2 AND (level_2_active_date IS NULL OR level_2_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level = 3 AND (level_3_active_date IS NULL OR level_3_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level = 4 AND (level_4_active_date IS NULL OR level_4_active_date <= '{$ar_time['datehour']}') THEN 'Y'
					WHEN p.level >= 5 THEN 'Y'
					ELSE 'N' END) = 'Y'
					
				AND ( ( app.exec_stime IS NULL OR app.exec_etime IS NULL ) OR
				  	  IF ( app.exec_stime <= app.exec_etime, 
				  	 	 app.exec_stime <= '{$ar_time['hour']}' AND app.exec_etime > '{$ar_time['hour']}', 
				  	 	 app.exec_stime < '{$ar_time['hour']}' OR app.exec_etime >= '{$ar_time['hour']}' )
				)

				AND (pa.active_time IS NULL OR pa.active_time <= '{$ar_time['datehour']}')

				AND ( pa.exec_hour_max_cnt IS NULL OR pa.exec_hour_max_cnt > IF(ps.exec_time = '{$ar_time['datehour']}', ps.exec_hour_cnt, 0) )
				AND	( pa.exec_day_max_cnt IS NULL OR pa.exec_day_max_cnt > IF(DATE(ps.exec_time) = '{$ar_time['day']}', ps.exec_day_cnt, 0) )
				AND	( pa.exec_tot_max_cnt IS NULL OR pa.exec_tot_max_cnt > IFNULL(ps.exec_tot_cnt, 0) )

				AND ( app.exec_hour_max_cnt IS NULL OR app.exec_hour_max_cnt > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0) )
				AND	( app.exec_day_max_cnt IS NULL OR app.exec_day_max_cnt > IF(DATE(s.exec_time) = '{$ar_time['day']}', s.exec_day_cnt, 0) )

				{$where_add}
			";	
	return $sql;
}

/*
// IF (IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NULL OR IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0), 'Y', 'N') as 'check_hour_executed',
// IF (IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NULL OR IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0), 'Y', 'N') as 'check_day_executed',
	pa에 시간당 제한이 NULL이면 --> app의 제값을 사용하고, 모두 NULL이면 Y
	아니면 값이 현재 실행 수보다 크면 Y (같아도 완료된것이므로 N 이됨)
	
	check_xxxxxxxxxxxx 는 기본 DEFAULT는 Y 임
	check_tot_executed 값만 DEFAULT일 때 N 임
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
				
				IF (pa.exec_hour_max_cnt <= IF(ps.exec_time = '{$ar_time['datehour']}', ps.exec_hour_cnt, 0), 'N', 'Y') as 'check_ps_hour_executed',
				IF (pa.exec_day_max_cnt <= IF(DATE(ps.exec_time) = '{$ar_time['day']}', ps.exec_day_cnt, 0), 'N', 'Y') as 'check_ps_day_executed',
				IF (pa.exec_tot_max_cnt <= IFNULL(ps.exec_tot_cnt,0), 'N', 'Y') as 'check_ps_tot_executed',
				
				IF (app.exec_hour_max_cnt <= IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0), 'N', 'Y') as 'check_hour_executed',
				IF (app.exec_day_max_cnt <= IF(DATE(s.exec_time) = '{$ar_time['day']}', s.exec_day_cnt, 0), 'N', 'Y') as 'check_day_executed',
				IF (app.exec_tot_max_cnt > IFNULL(s.exec_tot_cnt,0), 'Y', 'N') as 'check_tot_executed'

			FROM al_app_t app
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pa.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_app_exec_stat_t s ON app.app_key = s.app_key
				LEFT OUTER JOIN al_app_exec_pub_stat_t ps ON app.app_key = ps.app_key AND ps.pcode = '{$db_pcode}' 
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
						$ar_time, $b_local, $conn) {
	
	// echo "callback_reward($pcode, $appkey, $adid)<br>";
	
	$db_mcode = mysql_real_escape_string($mcode);
	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_adid = mysql_real_escape_string($adid);
	$db_unique_key = mysql_real_escape_string($unique_key);

	if ($b_local) $is_local = 'Y'; else $is_local = 'N';
	
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
				mysql_execute($sql, $conn);
				
				// al_user_app_saving_t 매출 레코드 추가
				$sql = "INSERT INTO al_user_app_saving_t (user_app_id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, unique_key, m_reg_day, m_reg_date, p_reg_day, p_reg_date)
						SELECT id, mcode, pcode, app_key, adid, '{$merchant_fee}', '{$publisher_fee}', '{$db_unique_key}', done_day, action_dtime, done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
				mysql_execute($sql, $conn);

				////////////////////////////////////////////////////////////////////////////////////
				// 실시간 통계에 정보 추가
				////////////////////////////////////////////////////////////////////////////////////
				$sql = "SELECT id FROM al_summary_sales_h_t WHERE pcode = '{$db_pcode}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
				$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
				if ($row['id']) {
					$sql = "UPDATE al_summary_sales_h_t 
							SET merchant_cnt = merchant_cnt + 1, 
								merchant_fee = merchant_fee + '{$merchant_fee}',
								publisher_cnt = publisher_cnt + 1,
								publisher_fee = publisher_fee + '{$publisher_fee}'
							WHERE id = '{$row['id']}'";
					mysql_execute($sql, $conn);
				} else {
					// Merchant Fee가 0보다 큰경우에 Merchant_cnt를 1 증가시킨다.
					$sql = "INSERT al_summary_sales_h_t (mcode, pcode, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
							VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_appkey}', '1', '{$merchant_fee}', '1', '{$publisher_fee}', '{$ar_time['day']}', HOUR('{$ar_time['now']}'));";
					mysql_execute($sql, $conn);
				}
				////////////////////////////////////////////////////////////////////////////////////
				// merchant별 수행 총 수인 al_app_exec_stat_t 에 수행 개수를 추가한다
				$sql = "INSERT INTO al_app_exec_stat_t (app_key, exec_time, exec_hour_cnt, exec_day_cnt, exec_tot_cnt)
						VALUES ('{$db_appkey}', '{$ar_time['datehour']}', '1', '1', '1')
						ON DUPLICATE KEY UPDATE exec_hour_cnt = IF(exec_time = '{$ar_time['datehour']}', exec_hour_cnt + 1, 1),
												exec_day_cnt = IF(DATE(exec_time) = '{$ar_time['day']}', exec_day_cnt + 1, 1),
												exec_tot_cnt = exec_tot_cnt + 1,
												exec_time = '{$ar_time['datehour']}'";
				mysql_execute($sql, $conn);				
				
				// publisher별 수행수인 ==> al_app_exec_pub_stat_t 에 수행 개수를 추가한다
				$sql = "INSERT INTO al_app_exec_pub_stat_t (app_key, pcode, exec_time, exec_hour_cnt, exec_day_cnt, exec_tot_cnt)
						VALUES ('{$db_appkey}', '{$db_pcode}', '{$ar_time['datehour']}', '1', '1', '1')
						ON DUPLICATE KEY UPDATE exec_hour_cnt = IF(exec_time = '{$ar_time['datehour']}', exec_hour_cnt + 1, 1),
												exec_day_cnt = IF(DATE(exec_time) = '{$ar_time['day']}', exec_day_cnt + 1, 1),
												exec_tot_cnt = exec_tot_cnt + 1,
												exec_time = '{$ar_time['datehour']}'";
				mysql_execute($sql, $conn);				
				
			}
			else
			{
						//////////////////////////////////////////////////////
						// 강제 적립된 상태 
						// 	로컬은 이미 이전에 모두 적립이 된 상태이므로 변경사항은 FORCED_DONE만 N 로 변경하고 나무지는 수행하지 않음.
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
						mysql_execute($sql, $conn);

						if (!$b_local) 
						{
							// al_user_app_saving_t 매출 <== [강제적립한경우]에는 기존 매출테이블에 merchant_fee와 unique_key, 그리고 m_reg_day, m_reg_date만 갱신한다.
							$sql = "UPDATE al_user_app_saving_t
									SET merchant_fee = '{$merchant_fee}',
										unique_key = '{$db_unique_key}',
										m_reg_day = '{$ar_time['day']}',
										m_reg_date = '{$ar_time['now']}'
									WHERE user_app_id = '{$user_app_id}'";
							mysql_execute($sql, $conn);
							
							////////////////////////////////////////////////////////////////////////////////////
							// 실시간 통계에 정보 추가
							////////////////////////////////////////////////////////////////////////////////////
							// <== [강제적립한경우]에는 Merchant매출 만 갱신한다.
							$sql = "SELECT id FROM al_summary_sales_h_t WHERE pcode = '{$db_pcode}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
							$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
							if ($row['id']) {
								$sql = "UPDATE al_summary_sales_h_t 
										SET merchant_cnt = merchant_cnt + 1, 
											merchant_fee = merchant_fee + '{$merchant_fee}'
										WHERE id = '{$row['id']}'";
								mysql_execute($sql, $conn);
							} else {
								// <== [강제적립한경우]에는 Merchant 만 갱신한다. (Publisher는 0건, 0원)
								$sql = "INSERT al_summary_sales_h_t (mcode, pcode, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
										VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_appkey}', '1', '{$merchant_fee}', '0', '0', '{$ar_time['day']}', HOUR('{$ar_time['now']}'))";
								mysql_execute($sql, $conn);
							}
											
							////////////////////////////////////////////////////////////////////////////////////
							// al_app_exec_stat_t 에 수행 개수를 추가한다
							//	로컬 강제적립 후 적립은 변경 없고													<== 이미 강제적립시 적용완료
							//	외부 강제적립 후 적립은 al_app_exec_stat_t 증가, al_app_exec_pub_stat_t 유지		<== 이미 강제적립시 al_app_exec_pub_stat_t는 증가됨
							////////////////////////////////////////////////////////////////////////////////////
							$sql = "INSERT INTO al_app_exec_stat_t (app_key, exec_time, exec_hour_cnt, exec_day_cnt, exec_tot_cnt)
									VALUES ('{$db_appkey}', '{$ar_time['datehour']}', '1', '1', '1')
									ON DUPLICATE KEY UPDATE exec_hour_cnt = IF(exec_time = '{$ar_time['datehour']}', exec_hour_cnt + 1, 1),
															exec_day_cnt = IF(DATE(exec_time) = '{$ar_time['day']}', exec_day_cnt + 1, 1),
															exec_tot_cnt = exec_tot_cnt + 1,
															exec_time = '{$ar_time['datehour']}'";
							mysql_execute($sql, $conn);
							
						}

			}
			
				
	
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
		
	단, LOCAL광고는 merchant_fee, merchant_cnt 를 추가하고 (자체 광고는 추후 적립이 필요 없으므로).
		외부 연동 광고는 merchant_fee = NULL, merchant_cnt 는 유지 한다.
*/

function force_reward($pcode, $mcode, $appkey, $adid, 
						$merchant_fee, $publisher_fee, 
						$ar_time, $b_local, $conn) {
	
	// echo "callback_reward($pcode, $appkey, $adid)<br>";
	$db_mcode = mysql_real_escape_string($mcode);
	$db_pcode = mysql_real_escape_string($pcode);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_adid = mysql_real_escape_string($adid);

	if ($b_local) $is_local = 'Y'; else $is_local = 'N';
	
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
								
								////////////////////////////////////////////////////////////////////////////////////
								// al_user_app_t 상태 <== [강제적립] 상태로 변경하고, merchant_fee는 설정하지 않는다.
								////////////////////////////////////////////////////////////////////////////////////
								$sql = "UPDATE al_user_app_t 
										SET action_dtime = '{$ar_time['now']}', 
											done_day = '{$ar_time['day']}', 
											status = 'D', 
											forced_done = 'Y',
											merchant_fee = IF('{$is_local}'='Y','{$merchant_fee}',NULL),
											publisher_fee = '{$publisher_fee}'
										WHERE id = '{$user_app_id}'";
								mysql_execute($sql, $conn);
								
								////////////////////////////////////////////////////////////////////////////////////
								// al_user_app_saving_t 상태 <== [강제적립] 상태로 변경하고, merchant_fee는 설정하지 않는다.
								// 강제적립시 p에대한 매출건수,매출은 현재 시점
								//			  m에 대한 매출건수,매출은 ==> 로컬은 현재, 외부는 없음
								////////////////////////////////////////////////////////////////////////////////////
								//// al_user_app_saving_t 매출 레코드 추가 <== [강제적립]은 Merchant매출 = 0, Unique키는 존재하지 않음, 또한 아래의 중복키가 존재해서도 안됨 (날짜는 현재 시간 <-- 위에서 설정됨)
								$sql = "INSERT INTO al_user_app_saving_t (user_app_id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, m_reg_day, m_reg_date, p_reg_day, p_reg_date)
										SELECT id, mcode, pcode, app_key, adid, IF('{$is_local}'='Y','{$merchant_fee}',NULL), '{$publisher_fee}', 
											IF('{$is_local}'='Y',done_day,NULL),
											IF('{$is_local}'='Y',action_dtime,NULL),
											done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
								mysql_execute($sql, $conn);

								////////////////////////////////////////////////////////////////////////////////////
								// 실시간 통계에 정보 추가
								////////////////////////////////////////////////////////////////////////////////////
								$sql = "SELECT id FROM al_summary_sales_h_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
								$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
								if ($row['id']) {
									// <== [강제적립]은 Merchant 매출 정보를 건드리지 않음.
									$sql = "UPDATE al_summary_sales_h_t 
											SET merchant_cnt = merchant_cnt + IF('{$is_local}'='Y',1,0), 
												merchant_fee = merchant_fee + IF('{$is_local}'='Y','{$merchant_fee}',NULL),
												publisher_cnt = publisher_cnt + 1,
												publisher_fee = publisher_fee + '{$publisher_fee}'
											WHERE id = '{$row['id']}'";
									mysql_execute($sql, $conn);
								} else {
									// <== [강제적립]은 Merchant 매출및 건수를 0으로
									$sql = "INSERT al_summary_sales_h_t (mcode, pcode, adid, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
											VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_adid}', '{$db_appkey}', 
													IF('{$is_local}'='Y',1,0),
													IF('{$is_local}'='Y','{$merchant_fee}',NULL), 
													'1', 
													'{$publisher_fee}', 
													'{$ar_time['day']}', HOUR('{$ar_time['now']}'));";
									mysql_execute($sql, $conn);
								}

								////////////////////////////////////////////////////////////////////////////////////
								//	로컬 강제 적립 시점은 al_app_exec_stat_t 개수 증가, al_app_exec_pub_stat_t 개수 증가
								//	외부 강제 적립 시점은 al_app_exec_stat_t 유지, 		al_app_exec_pub_stat_t 개수 증가
								if ($b_local) 
								{
									$sql = "INSERT INTO al_app_exec_stat_t (app_key, exec_time, exec_hour_cnt, exec_day_cnt, exec_tot_cnt)
											VALUES ('{$db_appkey}', '{$ar_time['datehour']}', '1', '1', '1')
											ON DUPLICATE KEY UPDATE exec_hour_cnt = IF(exec_time = '{$ar_time['datehour']}', exec_hour_cnt + 1, 1),
																	exec_day_cnt = IF(DATE(exec_time) = '{$ar_time['day']}', exec_day_cnt + 1, 1),
																	exec_tot_cnt = exec_tot_cnt + 1,
																	exec_time = '{$ar_time['datehour']}'";
									mysql_execute($sql, $conn);
								}
								
								// publisher별 수행수인 ==> al_app_exec_pub_stat_t 에 수행 개수를 추가한다
								$sql = "INSERT INTO al_app_exec_pub_stat_t (app_key, pcode, exec_time, exec_hour_cnt, exec_day_cnt, exec_tot_cnt)
										VALUES ('{$db_appkey}', '{$db_pcode}', '{$ar_time['datehour']}', '1', '1', '1')
										ON DUPLICATE KEY UPDATE exec_hour_cnt = IF(exec_time = '{$ar_time['datehour']}', exec_hour_cnt + 1, 1),
																exec_day_cnt = IF(DATE(exec_time) = '{$ar_time['day']}', exec_day_cnt + 1, 1),
																exec_tot_cnt = exec_tot_cnt + 1,
																exec_time = '{$ar_time['datehour']}'";
								mysql_execute($sql, $conn);		
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
	if ($arr_data['result'] != 'N') return;
	$arr_data['msg'] = get_error_msg($arr_data['code'], $arr_data['msg']);
}

function get_error_type($code) {
	
	switch ($code) {
		case '-100':
		case '-101':
		case '-102': return 'E-REQUEST';
		case '-110': return 'E-CONFIG';
		
		case '-103': return 'E-CLOSED';
		case '-104': return 'E-PAUSED';
		case '-105':
		case '-106': return 'E-DONE';
		
		case '-107':
		case '-108':
		case '-109': return 'E-FLOW';
		
		case '-1001':
		case '-1002':
		case '-1003':
		case '-1004': return 'E-AD';
		
		case '-2001':
		
		case '-3001':
		case '-3002':
		case '-3003':
		case '-3004':
		case '-3101': return 'E-REWARD';
	}
	return 'E-UNKNOWN';
}

function get_error_msg($code, $msg) {
	
	if ($msg) return $msg;
	switch ($code) {
		case '-100': return '유효하지 않은 매체코드입니다.';
		case '-101': return '파라미터 오류입니다.  일부 파라미터가 빠져있습니다.';
		case '-102': return '파라미터 코드 값 오류입니다.';
		case '-110': return '광고 오류입니다.';
		
		case '-103': return '광고가 없거나 참여할 수 없는 상태입니다.';
		case '-104': return '광고가 임시 중단된 상태입니다.';
		case '-105': return '이미 참여한 광고입니다.';
		case '-106': return '더 이상 참여할 수 없는 광고입니다.';
		
		case '-107': return '광고 참여한 기록이 없습니다.';
		case '-108': return '광고 참여한 기록이 없습니다.';
		case '-109': return '유효하지 않은 요청입니다';
		
		case '-1001': return '광고 오류입니다. (no-packageid)';
		case '-1002': return '광고 오류입니다. (unknown-market)';
		case '-1003': return '광고 오류입니다. (no-url)';
		case '-1004': return '매체사 요청 파라미터 오류입니다.';
		
		case '-2001': return '적립오류 입니다.';
		
		case '-3001': return '중복 적립 오류';
		case '-3002': return '적립 시도 기록이 없음';
		case '-3003': return 'UNIQUE 키 중복 오류';
		case '-3004': return 'DB 처리 중 오류 발생';
		case '-3101': return '강제 중복 적립 오류';
	}
	return 'E-UNKNOWN';
}

?>