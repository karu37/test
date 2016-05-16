<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	$mactive = trim($_REQUEST['mactive']);
	
	if (!$partner_id || !in_array($mactive, array('Y','N','D'))) return_die(false, null, '잘못된 요청입니다.');

	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_mactive = mysql_real_escape_string($mactive);
	
	$sql = "UPDATE al_partner_t SET is_mactive = '{$db_mactive}' WHERE partner_id = '{$db_partner_id}';";
	mysql_execute($sql, $conn);

	return_die(true);

?>