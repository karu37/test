<?
	$pub_mactive = get_publisher_info("");
	if (!$pub_mactive || $pub_mactive == 'D') return_die(false, array('code'=>'1000'), '유효하지 않은 매체코드입니다.');

	$pcode = $_REQUEST['pcode'];

	$db_pcode = mysql_real_escape_string($pcode);

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
		 	
		 	@ al_app_t.exec_edate					: end 시간보다 이전일 때에만 동작 (해당일 23:59:59 까지 동작 )
		 		IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'N', 'Y') as 'edate_expired' ## 오늘포함해서 미래까지모두 동작하도록 함 (어제날짜인경우 차단)
		 		==> 초과 체크해서 app.is_active 를 'N'
			
			@ exec개수 정보가 없는 경우 또는 NOT( exec 개수 체크해서 개수가 0 이상으로 설정되어 있으면서 개수 초과를 한 경우 표시하지 않음 )
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

		 	@ al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.exec_tot_cnt	
				==> 초과 체크해서 app.is_active 를 'N'

	 */

	$ar_time = mysql_get_time($conn);

	$sql = "SELECT app.*, 
				m.name AS 'merchant_name', 
				
				IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
				
				IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'N', 'Y') as 'edate_expired',
				IF (s.id IS NULL OR IFNULL(pa.exec_tot_max_cnt, app.exec_tot_max_cnt) < s.exec_tot_cnt, 'N', 'Y') as 'tot_complished'
				
			FROM al_app_t app
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pa.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_app_exec_stat_t s ON app.app_key = s.app_key
			WHERE 1=1
				AND app.is_active = 'Y'
				AND app.is_mactive = 'Y'

				AND m.is_mactive = 'Y'
				AND p.is_mactive = 'Y'
				
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
				
				AND (
					(
					 IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NULL
					  AND
					 IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NULL
					)
					OR
					(
					 ( IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NOT NULL AND IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0) )
					  OR
					 ( IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NOT NULL AND IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0) )
					)
				)
				
			";
	$result = mysql_query($sql, $conn);
	
	if ($_REQUEST['is_web'] == 'Y') {
	?>	
	<style>
		* {font-size:12px; }
		td {padding: 0 2px}
	</style>
	<table border=1 cellpadding=0 cellspacing=0>
	<?	
		while ($row = mysql_fetch_assoc($result)) {
			
			// exec_tot_max_cnt 가 초과한 대상은 is_active ==> "N" 으로 변경한다.
			// exec_edate 가 지난 경우에도 is_active ==> "N" 으로 변경
			if ($row['tot_complished'] == 'Y' || $row['edate_expired'] == 'Y') {
				continue;
			}

			// 표시 차단 대상 (위에서 필터링 안된 대상)
			// 금액이 0 인 경우
			if ( intval($row['publisher_fee']) <= 0 ) continue;	
			
			echo "<tr>";
			echo "<td><img src='{$row['app_iconurl']}' width=40px /></td>";
			echo "<td>{$row['app_title']}</td>";
			echo "<td>{$row['app_exec_type']}</td>";
			echo "<td>{$row['app_merchant_fee']}</td>";
			echo "<td>{$row['publisher_fee']}</td>";
			echo "<td>{$row['app_agefrom']} ~ {$row['app_ageto']}</td>";
			echo "<td>{$row['app_gender']}</td>";
			echo "<td>{$row['exec_stime']} ~ {$row['exec_etime']}</td>";
			echo "<td>{$row['edate_expired']}, {$row['tot_complished']}</td>";
			echo "<tr>";
			
		}
	?>	
	</table>	
	<?		
	} else {
		
	}
	
	return_die(true, null, "ok");
	
?>