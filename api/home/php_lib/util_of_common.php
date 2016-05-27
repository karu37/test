<?
/*
// IF (IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NULL OR IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0), 'Y', 'N') as 'check_hour_executed',
// IF (IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NULL OR IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0), 'Y', 'N') as 'check_day_executed',
	pa�� �ð��� ������ NULL�̸� --> app�� ������ ����ϰ�, ��� NULL�̸� Y
	�ƴϸ� ���� ���� ���� ������ ũ�� Y (���Ƶ� �Ϸ�Ȱ��̹Ƿ� N �̵�)
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

// ����� �����ϱ� (����� ���� �� al_user_app_t ���� ���� ��� ó��)
/*
	* ���� �������� ���� (mactive, active��)�� üũ���� ���� ȣ�� ���� �̹� üũ ������ ��.
	
	user_app_t �� ���� ���¸� Ȯ�� (D, UniqkeyŰ �ߺ��� ��� �����ϱ�)

	- al_app_exec_stat_t �� �ǽð� ���� ���� �߰�
	- al_user_app_t �� ���� �Ϸ� �� unique_key ���� (���� ������ �ִ� ��� �Ϲ� �������� ����)
	- al_user_app_saving_t �� �����Ϸ� �α� ��� (merchant ����, publisher ���� Row���� ���)
	- al_user_saving_h_t �� mcode, merchant_fee, pcode, publisher_fee, cnt�� ����
	
	- ���� ����
		1. SQL ���� �� ����
		2. �̹� ���� �Ϸ�� ���� (Unique Ű üũ �Ǵ� ���°� �̹� D)
*/

// ���� ���� ó�� (���� ������ ���·� ó����)
// ���� $ar_return['callback_done'] == 'Y' �̸� �̹� �ݹ��� ȣ���� �ȵ�
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
		
			// al_user_app_t LOCK �ɱ�
			$sql = "SELECT count(*) cnt FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' FOR UPDATE";
			mysql_query($sql, $conn);
			
			// �̹� �ش� ��Ű�� ���ؼ� adid�� ����(������������) �������� �ִٸ� ����
			$sql = "SELECT id FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' and status = 'D' AND forced_done = 'N'";
			$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
			if ($row['id']) {		// �̹� �����ϸ� ���� ����
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
			
			// unique_key �ߺ��� ���� ó��
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
				// ���� ����
				//////////////////////////////////////////////////////
		
				// al_user_app_t ���� �Ϸ�� ����
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
				
				// al_user_app_saving_t ���� ���ڵ� �߰�
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
					// Merchant Fee�� 0���� ū��쿡 Merchant_cnt�� 1 ������Ų��.
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
						// ���� ������ ���� 
						//////////////////////////////////////////////////////
						
						// al_user_app_t ���� �Ϸ�� ���� <== [���������Ѱ��]���� publisher_fee�� �������� ����, ���� �����Ϸ�ð��� ���ݽð����� ��.
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
						
						// al_user_app_saving_t ���� <== [���������Ѱ��]���� ���� �������̺� merchant_fee�� unique_key, �׸��� m_reg_day, m_reg_date�� �����Ѵ�.
						$sql = "UPDATE al_user_app_saving_t
								SET merchant_fee = '{$merchant_fee}',
									unique_key = '{$db_unique_key}',
									m_reg_day = '{$ar_time['day']}',
									m_reg_date = '{$ar_time['now']}'
								WHERE user_app_id = '{$user_app_id}'";
						// echo $sql . "\n";
						mysql_execute($sql, $conn);
						
						
						// <== [���������Ѱ��]���� Merchant���� �� �����Ѵ�.
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
							// <== [���������Ѱ��]���� Merchant �� �����Ѵ�. (Publisher�� 0��, 0��)
							$sql = "INSERT al_summary_user_sales_h_t (mcode, pcode, adid, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
									VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_adid}', '{$db_appkey}', '1', '{$merchant_fee}', '0', '0', '{$ar_time['day']}', HOUR('{$ar_time['now']}'))
									ON DUPLICATE KEY UPDATE merchant_cnt = merchant_cnt + 1, 
															merchant_fee = merchant_fee + '{$merchant_fee}'";
							// echo $sql . "\n";
							mysql_execute($sql, $conn);
						}				
			}
			
			// al_app_exec_stat_t �� ���� ������ �߰��Ѵ�.
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

// ���� ���� ó���ϱ� (���������� $merchant_fee, $unique_key�� ���޵��� ����)
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
						
								// al_user_app_t LOCK �ɱ�
								$sql = "SELECT count(*) cnt FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' FOR UPDATE";
								mysql_query($sql, $conn);
								
								// �̹� �ش� ��Ű�� ���ؼ� adid�� ����(������������) �������� �ִٸ� ����
								$sql = "SELECT id FROM al_user_app_t WHERE adid = '{$db_adid}' AND app_key = '{$db_appkey}' and status = 'D' AND forced_done = 'N'";
								$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
								if ($row['id']) {		// �̹� �����ϸ� ���� ����
									rollback($conn);
									return array('result' => 'N', 'code' => '-3001');
								}
								
								//// ���� �õ� ������ üũ�ϰ� <== [��������]�Ǿ� �ִ� ��� ���� ó���Ѵ�.
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
								
								// al_user_app_t ���� <== [��������] ���·� �����ϰ�, merchant_fee�� �������� �ʴ´�.
								$sql = "UPDATE al_user_app_t 
										SET action_dtime = '{$ar_time['now']}', 
											done_day = '{$ar_time['day']}', 
											status = 'D', 
											forced_done = 'Y',
											publisher_fee = '{$publisher_fee}'
										WHERE id = '{$user_app_id}'";
								// echo $sql . "\n";
								mysql_execute($sql, $conn);
								
								//// al_user_app_saving_t ���� ���ڵ� �߰� <== [��������]�� Merchant���� = 0, UniqueŰ�� �������� ����, ���� �Ʒ��� �ߺ�Ű�� �����ؼ��� �ȵ� (��¥�� ���� �ð� <-- ������ ������)
								$sql = "INSERT INTO al_user_app_saving_t (user_app_id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, m_reg_day, m_reg_date, p_reg_day, p_reg_date)
										SELECT id, mcode, pcode, app_key, adid, 0, publisher_fee, done_day, action_dtime, done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
								// echo $sql . "\n";
								mysql_execute($sql, $conn);
								
								$sql = "SELECT id FROM al_summary_user_sales_h_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
								$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
								if ($row['id']) {
									// <== [��������]�� Merchant ���� ������ �ǵ帮�� ����.
									$sql = "UPDATE al_summary_user_sales_h_t 
											SET publisher_cnt = publisher_cnt + 1,
												publisher_fee = publisher_fee + '{$publisher_fee}'
											WHERE id = '{$row['id']}'";
									// echo $sql . "\n";
									mysql_execute($sql, $conn);
								} else {
									// <== [��������]�� Merchant ����� �Ǽ��� 0����
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
	
	if ($arr_data['code'] == '-100') $arr_data['msg'] = '��ȿ���� ���� ��ü�ڵ��Դϴ�.';
	else if ($arr_data['code'] == '-101') $arr_data['msg'] = '�Ķ���� �����Դϴ�.  �Ϻ� �Ķ���Ͱ� �����ֽ��ϴ�.';
	else if ($arr_data['code'] == '-102') $arr_data['msg'] = '�Ķ���� �ڵ� �� �����Դϴ�.';
	else if ($arr_data['code'] == '-103') $arr_data['msg'] = '���� ���ų� ������ �� ���� �����Դϴ�.';
	else if ($arr_data['code'] == '-104') $arr_data['msg'] = '���� �ӽ� �ߴܵ� �����Դϴ�.';
	else if ($arr_data['code'] == '-105') $arr_data['msg'] = '�̹� ������ �����Դϴ�.';
	else if ($arr_data['code'] == '-106') $arr_data['msg'] = '�� �̻� ������ �� ���� �����Դϴ�.';	// permanent_fail = 'Y'
	
	else if ($arr_data['code'] == '-107') $arr_data['msg'] = '���� ������ ����� �����ϴ�.';
	else if ($arr_data['code'] == '-109') $arr_data['msg'] = '��ȿ���� ���� ��û�Դϴ�';			// DONE�� �������� �ƴѵ� ��û��.
	else if ($arr_data['code'] == '-110') $arr_data['msg'] = '���� �����Դϴ�.';					// ���� LIB�� ���� ó���� �������� ����
	
	else if ($arr_data['code'] == '-1001') $arr_data['msg'] = '���� �����Դϴ�. (no-packageid)';
	else if ($arr_data['code'] == '-1002') $arr_data['msg'] = '���� �����Դϴ�. (unknown-market)';
	else if ($arr_data['code'] == '-1003') $arr_data['msg'] = '���� �����Դϴ�. (no-url)';

	// DB�� ���� �� �߻� ����
	else if ($arr_data['code'] == '-3001') $arr_data['msg'] = '�ߺ� ���� ����';
	else if ($arr_data['code'] == '-3002') $arr_data['msg'] = '���� �õ� ����� ����';
	else if ($arr_data['code'] == '-3003') $arr_data['msg'] = 'UNIQUE Ű �ߺ� ����';
	else if ($arr_data['code'] == '-3004') $arr_data['msg'] = 'DB ó�� �� ���� �߻�';

}

?>