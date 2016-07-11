<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$guest_id = trim($_REQUEST['guestid']);
	$appkey = trim($_REQUEST['appkey']);
	
	if (!$guest_id || !$appkey) return_die(false, null, '잘못된 요청입니다.');

	$db_guest_id = mysql_real_escape_string($guest_id);
	$db_appkey = mysql_real_escape_string($appkey);
	
	$sql = "SELECT id FROM  guest_app_list WHERE guest_id = '{$db_guest_id}' and app_key = '{$db_appkey}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row['id']) return_die(false, null, '이미 등록되어 있는 광고키입니다.');
	
	
	$sql = "SELECT id, lib FROM al_app_t WHERE app_key = '{$db_appkey}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	
	if (!$row) return_die(false, null, '광고키가 존재하지 않습니다.');
	if ($row['lib'] != 'LOCAL') return_die(false, null, 'ALINE 등록 광고가 아니면 등록할 수 없습니다..');
	
	$sql = "INSERT IGNORE guest_app_list (guest_id, app_key) VALUES ('{$db_guest_id}', '{$db_appkey}');";
	mysql_execute($sql, $conn);
		
	return_die(true, null, '등록되었습니다.');

?>