<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');

	$publisher_code = trim($_REQUEST['pcode']);
	$appkey = trim($_REQUEST['appkey']);
	
	$offerfee = trim($_REQUEST['offerfee']);
	$offerfeerate = trim($_REQUEST['offerfeerate']);

	$activetime = trim($_REQUEST['activetime']);
	$exechourmaxcnt = trim($_REQUEST['exechourmaxcnt']);
	$execdaymaxcnt = trim($_REQUEST['execdaymaxcnt']);
	$exectotmaxcnt = trim($_REQUEST['exectotmaxcnt']);
	
	if (!$publisher_code || !$appkey) return_die(false, null, '필요한 정보가 없습니다.');

	$db_publisher_code = mysql_real_escape_string($publisher_code);
	$db_appkey = mysql_real_escape_string($appkey);
	
	$db_offerfee = mysql_real_escape_string($offerfee);
	$db_offerfeerate = mysql_real_escape_string($offerfeerate);
	$db_activetime = mysql_real_escape_string($activetime);
	$db_exechourmaxcnt = mysql_real_escape_string($exechourmaxcnt);
	$db_execdaymaxcnt = mysql_real_escape_string($execdaymaxcnt);
	$db_exectotmaxcnt = mysql_real_escape_string($exectotmaxcnt);

	begin_trans($conn);
	
		$sql = "SELECT id FROM al_publisher_app_t WHERE pcode = '{$db_publisher_code}' AND app_key = '{$db_appkey}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row['id']) {
			$sql = "UPDATE al_publisher_app_t 
					SET 	app_offer_fee 		= IF('{$db_offerfee}' <> '', '{$db_offerfee}', NULL), 
							app_offer_fee_rate 	= IF('{$db_offerfeerate}' <> '', '{$db_offerfeerate}', NULL), 
							active_time			= IF('{$db_activetime}' <> '', '{$db_activetime}', NULL), 
							exec_hour_max_cnt 	= IF('{$db_exechourmaxcnt}' <> '', '{$db_exechourmaxcnt}', NULL), 
							exec_day_max_cnt 	= IF('{$db_execdaymaxcnt}' <> '', '{$db_execdaymaxcnt}', NULL), 
							exec_tot_max_cnt 	= IF('{$db_exectotmaxcnt}' <> '', '{$db_exectotmaxcnt}', NULL) 
					WHERE id = '{$row['id']}'";
		} else {
			$sql = "INSERT al_publisher_app_t (pcode, app_key, app_offer_fee, app_offer_fee_rate, active_time, exec_day_max_cnt, exec_tot_max_cnt) 
					VALUES ('{$db_publisher_code}', 
							'{$db_appkey}', 
							IF('{$db_offerfee}' <> '', '{$db_offerfee}', NULL), 
							IF('{$db_offerfeerate}' <> '', '{$db_offerfeerate}', NULL),
							IF('{$db_activetime}' <> '', '{$db_activetime}', NULL),
							IF('{$db_execdaymaxcnt}' <> '', '{$db_execdaymaxcnt}', NULL),
							IF('{$db_exectotmaxcnt}' <> '', '{$db_exectotmaxcnt}', NULL)
					);";
		}
		mysql_execute($sql, $conn);
	
	commit($conn);

	admindb_publisher_app_clear($publisher_code, $appkey, $conn);

	return_die(true);
	
?>