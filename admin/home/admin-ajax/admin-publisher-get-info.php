<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$publisher_code = trim($_REQUEST['pcode']);
	
	if (!$publisher_code) return_die(false, null, '잘못된 요청입니다.');

	$db_publisher_code = mysql_real_escape_string($publisher_code);
	
	$sql = "SELECT pcode, name, offer_fee_rate, level FROM al_publisher_t WHERE pcode = '{$db_publisher_code}';";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	$ar_data['publisher_code'] = $row['pcode'];
	$ar_data['publisher_name'] = $row['name'];
	$ar_data['offer_fee_rate'] = $row['offer_fee_rate'];
	$ar_data['publisher_level'] = $row['level'];
	return_die(true, $ar_data);

?>