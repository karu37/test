<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');

	$merchant_code = trim($_REQUEST['mcode']);
	$appkey = trim($_REQUEST['appkey']);
	$is_mactive = trim($_REQUEST['ismactive']);
	if (!$merchant_code || !$appkey || !$is_mactive) return_die(false, null, '필요한 정보가 없습니다.');

	$db_merchant_code = mysql_real_escape_string($merchant_code);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_is_mactive = mysql_real_escape_string($is_mactive);

	begin_trans($conn);
	
		$sql = "SELECT * FROM al_app_t WHERE mcode = '{$db_merchant_code}' AND app_key = '{$db_appkey}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		
		if ($is_mactive == 'T' && $row['lib'] != 'LOCAL') {
			rollback($conn);
			return_die(false, null, '외부 연동 광고는 개발용으로 설정할 수 없습니다.');
		}
		
		if ($row['id']) {
			$sql = "UPDATE al_app_t SET is_mactive = '{$db_is_mactive}' WHERE id = '{$row['id']}'";
			mysql_execute($sql, $conn);
		} else {
			rollback($conn);
			return_die(false, null, '대상 광고가 존재하지 않습니다.');		
		}
	
	commit($conn);
	
	return_die(true, $ar_data);
	
?>