<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$publisher_code = trim($_REQUEST['pcode']);
	$appkey = trim($_REQUEST['appkey']);
	
	if (!$publisher_code) return_die(false, null, '잘못된 요청입니다.');

	$db_publisher_code = mysql_real_escape_string($publisher_code);
	$db_appkey = mysql_real_escape_string($appkey);
	
	$sql = "SELECT * FROM al_publisher_app_t WHERE pcode = '{$db_publisher_code}' AND app_key = '{$db_appkey}';";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	$ar_data = $row;
	return_die(true, $ar_data);

?>