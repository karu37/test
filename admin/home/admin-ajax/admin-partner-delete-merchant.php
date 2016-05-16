<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	$merchant_code = trim($_REQUEST['mcode']);

	if (!$partner_id || !$merchant_code) return_die(false, null, '필요한 정보가 없습니다.');
	
	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_merchant_code = mysql_real_escape_string($merchant_code);

	$sql = "DELETE FROM al_partner_mpcode_t WHERE partner_id = '{$db_partner_id}' AND mcode = '{$db_merchant_code}' AND type = 'M'";
	mysql_query($sql, $conn);

	return_die(true);

?>