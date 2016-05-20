<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$merchant_code = trim($_REQUEST['mcode']);
	
	if (!$merchant_code) return_die(false, null, '잘못된 요청입니다.');

	$db_merchant_code = mysql_real_escape_string($merchant_code);
	
	$sql = "SELECT mcode, name FROM al_merchant_t WHERE mcode = '{$db_merchant_code}';";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	$ar_data['merchant_code'] = $row['pcode'];
	$ar_data['merchant_name'] = $row['name'];
	return_die(true, $ar_data);

?>