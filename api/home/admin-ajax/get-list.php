<?
	$pub_mactive = get_publisher_info();
	if (!$pub_mactive || $pub_mactive == 'D') return_die(false, array('code'=>'1000'), '유효하지 않은 매체코드입니다.');

	$pcode = $_REQUEST['pcode'];
	
	$db_pcode = mysql_real_escape_string($pcode);

	/* 
		IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
			: pa에 지정된 가격이 있으면 그것을 사용하고
			: 그렇지 않고 pa에 지정된 율이 있으면 그 율로 계산
			: 그렇지 않으면 기본 계산법으로 계산
	
		app.is_active => Y : 광고 적립 가능 상태가 활성화
		app.is_mactive => Y : 관리자가 광고 활성화
		
		( app.exec_stime IS NULL OR app.exec_etime IS NULL ) OR
				  	  IF ( app.exec_stime <= app.exec_etime, 
				  	 	 a.exec_stime <= TIME(NOW()) AND a.exec_etime >= TIME(NOW()), 
				  	 	 a.exec_stime <= TIME(NOW()) OR a.exec_etime >= TIME(NOW()) ) ==> TRUE
				  	 	 
		m.is_mactive = 'Y' : 관리자가 해당 광고주를 활성화
		p.is_mactive = 'Y' : 관리자가 해당 매체사를 활성화
		app.publisher_level IS NULL OR p.level <= app.publisher_level : publisher의 레발값이 app에 지정된 매체사 레벨보다 같거나 작아야 함.

		-- pa.open_time IS NULL OR pa.open_time <= NOW() : 오픈일정이 없거나, 오픈일정이 지난 경우 <<<<<<< 사용 안함
		IFNULL(pa.publisher_disabled, 'N') => N : 매체사가 광고 수신 거부 안 함
		IFNULL(pa.is_mactive, 'Y') => Y : 매체사에 광고 공급을 관리자가 중지
		
		is_public_mode = Y : IFNULL(pa.merchant_disabled,'N')가 N 이면 표시(Y)
		is_public_mode = N : IFNULL(pa.merchant_enabled,'N')가 Y 이면 표시(Y)
		
		p.level, level_{n}_active_date 지정 안되어 있거나, 있는 경우 현재보다 이전인경우 표시
		
> pa에 지정된 시간당, 총 개수제한이 있으면 이를 사용하고
> 없는 경우에는 app에 있는 값을 사용한다.
		
		## 그리고 ##
		* exec_edate, exec_tot_max_cnt 초과는 그대록 목록으로 뽑은 다음 기간 초과 또는 수량 초과시 ==> is_active = 'N' 으로 변경한다.
	 */

	$sql = "SELECT app.*, 
				pa.exec_tot_max_cnt, 
				IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
				
				m.name AS 'merchant_name', 
				t.short_txt AS 'app_exec_type_name' 
			FROM al_app_t app
				INNER JOIN al_merchant_t m ON app.mcode = m.mcode 
				INNER JOIN al_publisher_t p ON p.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN al_publisher_app_t pa ON app.app_key = pa.app_key AND pa.pcode = '{$db_pcode}' 
				LEFT OUTER JOIN string_t t ON t.type = 'app_exec_type' AND app.app_exec_type = t.code 
			WHERE 
				app.is_active = 'Y' AND 
				app.is_mactive = 'Y' AND
				
				( ( app.exec_stime IS NULL OR app.exec_etime IS NULL ) OR
				  	  IF ( app.exec_stime <= app.exec_etime, 
				  	 	 app.exec_stime <= TIME(NOW()) AND app.exec_etime >= TIME(NOW()), 
				  	 	 app.exec_stime <= TIME(NOW()) OR app.exec_etime >= TIME(NOW()) )
				) AND
				
				m.is_mactive = 'Y' AND
				p.is_mactive = 'Y' AND
				(app.publisher_level IS NULL OR p.level <= app.publisher_level) AND
				
				IFNULL(pa.publisher_disabled, 'N') = 'N' AND
				IFNULL(pa.is_mactive, 'Y') = 'Y' AND
				
				IF (app.is_public_mode = 'Y', 
					IFNULL(pa.merchant_disabled,'N')='N',
					IFNULL(pa.merchant_enabled,'N')='Y') AND
				
				(CASE 
					WHEN p.level = 1 AND (level_1_active_date IS NULL OR level_1_active_date <= NOW()) THEN 'Y'
					WHEN p.level = 2 AND (level_2_active_date IS NULL OR level_2_active_date <= NOW()) THEN 'Y'
					WHEN p.level = 3 AND (level_3_active_date IS NULL OR level_3_active_date <= NOW()) THEN 'Y'
					WHEN p.level = 4 AND (level_4_active_date IS NULL OR level_4_active_date <= NOW()) THEN 'Y'
					WHEN p.level >= 5 THEN 'Y'
					ELSE 'N' END) = 'Y'
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
	
		echo "<tr>";
		echo "<td><img src='{$row['app_iconurl']}' width=40px /></td>";
		echo "<td>{$row['app_title']}</td>";
		echo "<td>{$row['app_exec_type']}</td>";
		echo "<td>{$row['app_merchant_fee']}</td>";
		echo "<td>{$row['publisher_fee']}</td>";
		echo "<td>{$row['app_agefrom']} ~ {$row['app_ageto']}</td>";
		echo "<td>{$row['app_gender']}</td>";
		echo "<td>{$row['exec_stime']} ~ {$row['exec_etime']}</td>";
		echo "<tr>";
		
	}
	?>	
	</table>	
<?		
	} else {
		
	}
	
	
	return_die(true, null, "ok");
	
?>