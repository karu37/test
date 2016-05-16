<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$merchant_code = trim($_REQUEST['mcode']);
	$is_active = trim($_REQUEST['isactive']);
	if (!$merchant_code || !$is_active) return_die(false, null, '필요한 정보가 없습니다.');
	
	$db_merchant_code = mysql_real_escape_string($merchant_code);
	$db_is_active = mysql_real_escape_string($is_active);

	$sql = "UPDATE al_merchant_t 
			SET is_mactive = '{$db_is_active}'
			WHERE mcode = '{$db_merchant_code}'";
	mysql_execute($sql, $conn);

	return_die(true);
?>