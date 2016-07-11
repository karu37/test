<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$guest_id = $_REQUEST['guestid'];
	$is_deleted = $_REQUEST['deleted'];
	
	if (!$guest_id) return_die(false, null, '잘못된 파라미터 요청입니다.');
	if (!in_array($is_deleted, array('Y','N','H'))) return_die(false, null, '잘못된 상태 요청입니다.');
	
	$db_guest_id = mysql_real_escape_string($guest_id);
	$db_is_deleted = mysql_real_escape_string($is_deleted);
	
	$sql = "UPDATE guest_user_t SET is_deleted = '{$db_is_deleted}' WHERE guest_id = '{$db_guest_id}'";
	mysql_execute($sql, $conn);
	
	return_die(true);
?>