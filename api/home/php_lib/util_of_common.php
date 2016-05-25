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

// ����� �����ϱ� (����� ���� �� al_user_app_t ���� ���� ��� ó��)
/*
	* ���� �������� ���� (mactive, active��)�� üũ���� ���� ȣ�� ���� �̹� üũ ������ ��.
	
	user_app_t �� ���� ���¸� Ȯ�� (D, UniqkeyŰ �ߺ��� ��� �����ϱ�)

	- al_app_exec_stat_t �� �ǽð� ���� ���� �߰�
	- al_user_app_t �� ���� �Ϸ� �� unique_key ���� (���� ������ �ִ� ��� �Ϲ� �������� ����)
	- al_user_saving_t �� �����Ϸ� �α� ��� (merchant ����, publisher ���� Row���� ���)
	- al_user_saving_h_t �� mcode, merchant_fee, pcode, publisher_fee, cnt�� ����
	
	- ���� ����
		1. SQL ���� �� ����
		2. �̹� ���� �Ϸ�� ���� (Unique Ű üũ �Ǵ� ���°� �̹� D)
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
		
			// al_user_app_t LOCK �ɱ�
			$sql = "SELECT id FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' and (status = 'D' OR forced_done = 'Y') FOR UPDATE";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {		// �̹� �����ϸ� ���� ����
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
			
			// unique_key �ߺ��� ���� ó��
			$sql = "SELECT id FROM al_user_app_t WHERE unique_key = '{$db_unique_key}'";
			echo $sql . "\n";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {
				rollback($conn);
				return array('result' => 'N', 'code' => '-3003');
			}
		
			// al_user_app_t ���� �Ϸ�� ����
			$sql = "UPDATE al_user_app_t 
					SET action_dtime = '{$ar_time['now']}', 
						done_day = '{$ar_time['day']}', 
						status = 'D', 
						forced_done = 'N',
						merchant_fee = '{$merchant_fee}', 
						publisher_fee = '{$publisher_fee}'";
			echo $sql . "\n";
			mysql_execute($sql, $conn);
			
			// al_user_saving_t ���� ���ڵ� �߰�
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
	
	if ($arr_data['code'] == '-100') $arr_data['msg'] = '��ȿ���� ���� ��ü�ڵ��Դϴ�.';
	else if ($arr_data['code'] == '-101') $arr_data['msg'] = '�Ķ���� �����Դϴ�.  �Ϻ� �Ķ���Ͱ� �����ֽ��ϴ�.';
	else if ($arr_data['code'] == '-102') $arr_data['msg'] = '�Ķ���� �ڵ� �� �����Դϴ�.';
	else if ($arr_data['code'] == '-103') $arr_data['msg'] = '���� ���ų� ������ �� ���� �����Դϴ�.';
	else if ($arr_data['code'] == '-104') $arr_data['msg'] = '���� �ӽ� �ߴܵ� �����Դϴ�.';
	else if ($arr_data['code'] == '-105') $arr_data['msg'] = '�̹� ������ �����Դϴ�.';
	else if ($arr_data['code'] == '-106') $arr_data['msg'] = '�� �̻� ������ �� ���� �����Դϴ�.';
	
	else if ($arr_data['code'] == '-1001') $arr_data['msg'] = '���� �����Դϴ�. (no-packageid)';
	else if ($arr_data['code'] == '-1002') $arr_data['msg'] = '���� �����Դϴ�. (unknown-market)';
	else if ($arr_data['code'] == '-1003') $arr_data['msg'] = '���� �����Դϴ�. (no-url)';

}

?>