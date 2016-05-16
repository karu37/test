<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');

	$merchant_code = trim($_REQUEST['mcode']);
	$appkey = trim($_REQUEST['appkey']);
	$is_active = trim($_REQUEST['isactive']);
	if (!$merchant_code || !$appkey || !$is_active) return_die(false, null, '필요한 정보가 없습니다.');

	$db_merchant_code = mysql_real_escape_string($merchant_code);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_is_active = mysql_real_escape_string($is_active);

	begin_trans($conn);
	
		$sql = "SELECT * FROM al_app_t WHERE mcode = '{$db_merchant_code}' AND app_key = '{$db_appkey}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row['id']) {
			$sql = "UPDATE al_app_t SET is_mactive = '{$db_is_active}' WHERE id = '{$row['id']}'";
			mysql_execute($sql, $conn);
		} else {
			rollback($conn);
			return_die(false, null, '대상 광고가 존재하지 않습니다.');		
		}
	
	commit($conn);
	
	return_die(true, $ar_data);
	
?>