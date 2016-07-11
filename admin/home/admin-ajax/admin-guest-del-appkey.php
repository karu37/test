<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$guest_id = trim($_REQUEST['guestid']);
	$appkey = trim($_REQUEST['appkey']);
	
	if (!$guest_id || !$appkey) return_die(false, null, '잘못된 요청입니다.');

	$db_guest_id = mysql_real_escape_string($guest_id);
	$db_appkey = mysql_real_escape_string($appkey);
	
	$sql = "DELETE FROM guest_app_list WHERE guest_id = '{$db_guest_id}' AND app_key = '{$db_appkey}';";
	mysql_execute($sql, $conn);
		
	return_die(true);

?>