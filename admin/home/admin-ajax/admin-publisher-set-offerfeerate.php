<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$publisher_code = trim($_REQUEST['pcode']);
	$offerfeerate = trim($_REQUEST['offerfeerate']);
	
	if (!$publisher_code || !$offerfeerate) return_die(false, null, '필요한 정보가 없습니다.');
	
	$db_publisher_code = mysql_real_escape_string($publisher_code);
	$db_offerfeerate = mysql_real_escape_string($offerfeerate);

	$sql = "UPDATE al_publisher_t 
			SET offer_fee_rate = '{$db_offerfeerate}'
			WHERE pcode = '{$db_publisher_code}'";
	mysql_execute($sql, $conn);

	return_die(true, $ar_data);

?>