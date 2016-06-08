<?
/* 
	IFNULL(pa.app_offer_fee, FLOOR(app.app_merchant_fee * IFNULL(pa.app_offer_fee_rate, p.offer_fee_rate) / 100) ) AS 'publisher_fee', 
		: pa�� ������ ������ ������ �װ��� ����ϰ�
		: �׷��� �ʰ� pa�� ������ ���� ������ �� ���� ���
		: �׷��� ������ �⺻ �������� ���

	1�� al_app_t.is_mactive						: [������]�� �ش� ���� Ȱ��/����/���� ( Y/N/D )
	2�� al_app_t.is_active						: [Merchant]�� �ش� ���� Ȱ��/���� ( Y/N )
	
	3�� al_merchant_t.is_mactive				: [������]�� Merchant Code Ȱ��/�׽�Ʈ/����/���� ( Y/T/N/D )
	4�� al_publisher_t.is_mactive				: [������]�� Publisher Code Ȱ��/�׽�Ʈ/����/���� ( Y/T/N/D )
	
	5�� al_publisher_app_t.is_mactive 			: [������]�� Publisher���� app���� Ȱ��/����/���� ( Y/N/D )
	6�� al_publisher_app_t.publisher_disabled 	: [Publisher]�� ���� �ޱ⸦ ���� ( Y )

	7�� al_app_t.publisher_level				: Publisher ���� ���� ����
			al_publisher_t.level				: 	app�� ���޷������� ���� ���(���ڷδ� �������) ���� ����
		
	8�� al_app_t.is_public_mode					: [Merchant]�� public ��� ����
			al_publisher_app_t.merchant_disabled: is_public_mode = Y�� ��� ������ 'N'�̸� ����
			al_publisher_app_t.merchant_enabled	: is_public_mode = N�� ��� ������ 'Y'�̸� ����

	-- ���� ��ü ���� �ð� ���� (�Ʒ������� ��� AND)

	 	al_app_t.exec_stime ~ exec_etime		: ���� ������ ���� ���� �ð�
	
	 	al_publisher_app_t.active_time			: ���� Ȱ�� �ð� - �����ڰ� ������ - �ش� Publisher & ���� ���/����
	 	
�ءء� al_app_t.exec_edate					: end �ð����� ������ ������ ���� (�ش��� 23:59:59 ���� ���� )
	 		IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'N', 'Y') as 'edate_expired' ## ���������ؼ� �̷�������� �����ϵ��� �� (������¥�ΰ�� ����)
	 		==> �ʰ� üũ�ؼ� app.is_active �� 'N'
		
		�� exec���� ������ ���� ��� �Ǵ� NOT( exec ���� üũ�ؼ� ������ 0 �̻����� �����Ǿ� �����鼭 ���� �ʰ��� �� ��� ǥ������ ���� )
			(
				# �ð��� ���� ������ ���� ��� �׳� OK
				(
				  IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NULL 	# �ð��� ������ ���� ��� 
				   AND
				  IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NULL		# ���� ������ ���� ���
				)
				OR
				(
				 # �ð��� ������ �����Ǿ� �ְ�, �ð��� ������ �ʰ����� ���� ���
				 ( IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) IS NOT NULL AND IFNULL(pa.exec_hour_max_cnt, app.exec_hour_max_cnt) > IF(s.exec_time = '{$ar_time['datehour']}', s.exec_hour_cnt, 0) )
				  OR
				 # ���� ������ �����Ǿ� �ְ�, ���� ������ �ʰ����� ���� ���
				 ( IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) IS NOT NULL AND IFNULL(pa.exec_day_max_cnt, app.exec_day_max_cnt) > IF(s.exec_time = CURRENT_DATE, s.exec_day_cnt, 0) )
				)
			)

	 	�� al_publisher_app_t.exec_tot_max_cnt or al_app_t.exec_tot_max_cnt <vs> al_app_exec_stat_t.exec_tot_cnt	
			==> �ʰ� üũ�ؼ� app.is_active �� 'N'

	# ���� ������� ==> �Ʒ� ����� is_active = 'N' ���� 
		if ($row['tot_not_complished'] != 'Y' || $row['edate_not_expired'] != 'Y')

*/
function get_query_app_list($pcode, $ar_time, $b_hide_exhauseted, $b_test_publisher, $conn)
{
	$where_add = "";
	// �Ⱓ ���� �� �ִ� ���� �ʰ��� �̸� �������� ���Ͻ�ų�� ���� ==> �� ����� is_active='N' ���� ��� ó��
	if ($b_hide_exhauseted) {
		$where_add = "AND IF (app.exec_edate IS NULL OR DATE(app.exec_edate) >= CURRENT_DATE, 'Y', 'N') = 'N'
					AND IF (s.app_key IS NULL OR IFNULL(pa.exec_tot_max_cnt, app.exec_tot_max_cnt) > s.exec_tot_cnt, 'Y', 'N') = 'N'";
	}
	
	$app_is_mactive = "Y";
	$p_is_mactive = "'Y'";
	// �׽�Ʈ ���� app.is_mactive = 'T' �� ��ȸ
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
	pa�� �ð��� ������ NULL�̸� --> app�� ������ ����ϰ�, ��� NULL�̸� Y
	�ƴϸ� ���� ���� ���� ������ ũ�� Y (���Ƶ� �Ϸ�Ȱ��̹Ƿ� N �̵�)
	
	check_xxxxxxxxxxxx �� �⺻ DEFAULT�� Y ��
	check_tot_executed ���� DEFAULT�� �� N ��
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
				mysql_execute($sql, $conn);
				
				// al_user_app_saving_t ���� ���ڵ� �߰�
				$sql = "INSERT INTO al_user_app_saving_t (user_app_id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, unique_key, m_reg_day, m_reg_date, p_reg_day, p_reg_date)
						SELECT id, mcode, pcode, app_key, adid, '{$merchant_fee}', '{$publisher_fee}', '{$db_unique_key}', done_day, action_dtime, done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
				mysql_execute($sql, $conn);

				////////////////////////////////////////////////////////////////////////////////////
				// �ǽð� ��迡 ���� �߰�
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
					// Merchant Fee�� 0���� ū��쿡 Merchant_cnt�� 1 ������Ų��.
					$sql = "INSERT al_summary_sales_h_t (mcode, pcode, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
							VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_appkey}', '1', '{$merchant_fee}', '1', '{$publisher_fee}', '{$ar_time['day']}', HOUR('{$ar_time['now']}'));";
					mysql_execute($sql, $conn);
				}
				////////////////////////////////////////////////////////////////////////////////////
				// merchant�� ���� �� ���� al_app_exec_stat_t �� ���� ������ �߰��Ѵ�
				$sql = "INSERT INTO al_app_exec_stat_t (app_key, exec_time, exec_hour_cnt, exec_day_cnt, exec_tot_cnt)
						VALUES ('{$db_appkey}', '{$ar_time['datehour']}', '1', '1', '1')
						ON DUPLICATE KEY UPDATE exec_hour_cnt = IF(exec_time = '{$ar_time['datehour']}', exec_hour_cnt + 1, 1),
												exec_day_cnt = IF(DATE(exec_time) = '{$ar_time['day']}', exec_day_cnt + 1, 1),
												exec_tot_cnt = exec_tot_cnt + 1,
												exec_time = '{$ar_time['datehour']}'";
				mysql_execute($sql, $conn);				
				
				// publisher�� ������� ==> al_app_exec_pub_stat_t �� ���� ������ �߰��Ѵ�
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
						// ���� ������ ���� 
						// 	������ �̹� ������ ��� ������ �� �����̹Ƿ� ��������� FORCED_DONE�� N �� �����ϰ� �������� �������� ����.
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
						mysql_execute($sql, $conn);

						if (!$b_local) 
						{
							// al_user_app_saving_t ���� <== [���������Ѱ��]���� ���� �������̺� merchant_fee�� unique_key, �׸��� m_reg_day, m_reg_date�� �����Ѵ�.
							$sql = "UPDATE al_user_app_saving_t
									SET merchant_fee = '{$merchant_fee}',
										unique_key = '{$db_unique_key}',
										m_reg_day = '{$ar_time['day']}',
										m_reg_date = '{$ar_time['now']}'
									WHERE user_app_id = '{$user_app_id}'";
							mysql_execute($sql, $conn);
							
							////////////////////////////////////////////////////////////////////////////////////
							// �ǽð� ��迡 ���� �߰�
							////////////////////////////////////////////////////////////////////////////////////
							// <== [���������Ѱ��]���� Merchant���� �� �����Ѵ�.
							$sql = "SELECT id FROM al_summary_sales_h_t WHERE pcode = '{$db_pcode}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
							$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
							if ($row['id']) {
								$sql = "UPDATE al_summary_sales_h_t 
										SET merchant_cnt = merchant_cnt + 1, 
											merchant_fee = merchant_fee + '{$merchant_fee}'
										WHERE id = '{$row['id']}'";
								mysql_execute($sql, $conn);
							} else {
								// <== [���������Ѱ��]���� Merchant �� �����Ѵ�. (Publisher�� 0��, 0��)
								$sql = "INSERT al_summary_sales_h_t (mcode, pcode, app_key, merchant_cnt, merchant_fee, publisher_cnt, publisher_fee, reg_day, hr)
										VALUES ('{$db_mcode}', '{$db_pcode}', '{$db_appkey}', '1', '{$merchant_fee}', '0', '0', '{$ar_time['day']}', HOUR('{$ar_time['now']}'))";
								mysql_execute($sql, $conn);
							}
											
							////////////////////////////////////////////////////////////////////////////////////
							// al_app_exec_stat_t �� ���� ������ �߰��Ѵ�
							//	���� �������� �� ������ ���� ����													<== �̹� ���������� ����Ϸ�
							//	�ܺ� �������� �� ������ al_app_exec_stat_t ����, al_app_exec_pub_stat_t ����		<== �̹� ���������� al_app_exec_pub_stat_t�� ������
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

// ���� ���� ó���ϱ� (���������� $merchant_fee, $unique_key�� ���޵��� ����)
/*
	al_user_app_saving_t
		merchant_fee : 0
		unique_key 	: NULL
	al_user_app_t
		unique_key	: NULL
		forced_done	: Y	
		
	��, LOCAL����� merchant_fee, merchant_cnt �� �߰��ϰ� (��ü ����� ���� ������ �ʿ� �����Ƿ�).
		�ܺ� ���� ����� merchant_fee = NULL, merchant_cnt �� ���� �Ѵ�.
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
								
								////////////////////////////////////////////////////////////////////////////////////
								// al_user_app_t ���� <== [��������] ���·� �����ϰ�, merchant_fee�� �������� �ʴ´�.
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
								// al_user_app_saving_t ���� <== [��������] ���·� �����ϰ�, merchant_fee�� �������� �ʴ´�.
								// ���������� p������ ����Ǽ�,������ ���� ����
								//			  m�� ���� ����Ǽ�,������ ==> ������ ����, �ܺδ� ����
								////////////////////////////////////////////////////////////////////////////////////
								//// al_user_app_saving_t ���� ���ڵ� �߰� <== [��������]�� Merchant���� = 0, UniqueŰ�� �������� ����, ���� �Ʒ��� �ߺ�Ű�� �����ؼ��� �ȵ� (��¥�� ���� �ð� <-- ������ ������)
								$sql = "INSERT INTO al_user_app_saving_t (user_app_id, mcode, pcode, app_key, adid, merchant_fee, publisher_fee, m_reg_day, m_reg_date, p_reg_day, p_reg_date)
										SELECT id, mcode, pcode, app_key, adid, IF('{$is_local}'='Y','{$merchant_fee}',NULL), '{$publisher_fee}', 
											IF('{$is_local}'='Y',done_day,NULL),
											IF('{$is_local}'='Y',action_dtime,NULL),
											done_day, action_dtime FROM al_user_app_t WHERE id = '{$user_app_id}'";
								mysql_execute($sql, $conn);

								////////////////////////////////////////////////////////////////////////////////////
								// �ǽð� ��迡 ���� �߰�
								////////////////////////////////////////////////////////////////////////////////////
								$sql = "SELECT id FROM al_summary_sales_h_t WHERE pcode = '{$db_pcode}' AND adid = '{$db_adid}' AND app_key = '{$db_appkey}' AND reg_day = '{$ar_time['day']}' AND hr = HOUR('{$ar_time['now']}') FOR UPDATE";
								$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
								if ($row['id']) {
									// <== [��������]�� Merchant ���� ������ �ǵ帮�� ����.
									$sql = "UPDATE al_summary_sales_h_t 
											SET merchant_cnt = merchant_cnt + IF('{$is_local}'='Y',1,0), 
												merchant_fee = merchant_fee + IF('{$is_local}'='Y','{$merchant_fee}',NULL),
												publisher_cnt = publisher_cnt + 1,
												publisher_fee = publisher_fee + '{$publisher_fee}'
											WHERE id = '{$row['id']}'";
									mysql_execute($sql, $conn);
								} else {
									// <== [��������]�� Merchant ����� �Ǽ��� 0����
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
								//	���� ���� ���� ������ al_app_exec_stat_t ���� ����, al_app_exec_pub_stat_t ���� ����
								//	�ܺ� ���� ���� ������ al_app_exec_stat_t ����, 		al_app_exec_pub_stat_t ���� ����
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
								
								// publisher�� ������� ==> al_app_exec_pub_stat_t �� ���� ������ �߰��Ѵ�
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
		case '-100': return '��ȿ���� ���� ��ü�ڵ��Դϴ�.';
		case '-101': return '�Ķ���� �����Դϴ�.  �Ϻ� �Ķ���Ͱ� �����ֽ��ϴ�.';
		case '-102': return '�Ķ���� �ڵ� �� �����Դϴ�.';
		case '-110': return '���� �����Դϴ�.';
		
		case '-103': return '���� ���ų� ������ �� ���� �����Դϴ�.';
		case '-104': return '���� �ӽ� �ߴܵ� �����Դϴ�.';
		case '-105': return '�̹� ������ �����Դϴ�.';
		case '-106': return '�� �̻� ������ �� ���� �����Դϴ�.';
		
		case '-107': return '���� ������ ����� �����ϴ�.';
		case '-108': return '���� ������ ����� �����ϴ�.';
		case '-109': return '��ȿ���� ���� ��û�Դϴ�';
		
		case '-1001': return '���� �����Դϴ�. (no-packageid)';
		case '-1002': return '���� �����Դϴ�. (unknown-market)';
		case '-1003': return '���� �����Դϴ�. (no-url)';
		case '-1004': return '��ü�� ��û �Ķ���� �����Դϴ�.';
		
		case '-2001': return '�������� �Դϴ�.';
		
		case '-3001': return '�ߺ� ���� ����';
		case '-3002': return '���� �õ� ����� ����';
		case '-3003': return 'UNIQUE Ű �ߺ� ����';
		case '-3004': return 'DB ó�� �� ���� �߻�';
		case '-3101': return '���� �ߺ� ���� ����';
	}
	return 'E-UNKNOWN';
}

?>