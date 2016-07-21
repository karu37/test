<?
	$partner_id = get_auth_partnerid();
	if (!$partner_id) return_die(false, null, '권한이 없습니다.');
	
	$guest_id = trim($_REQUEST['guestid']);
	$appkey = trim($_REQUEST['appkey']);
	
	if (!$guest_id || !$appkey) return_die(false, null, '잘못된 요청입니다.');

	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_guest_id = mysql_real_escape_string($guest_id);
	$db_appkey = mysql_real_escape_string($appkey);
	
	$sql = "SELECT app.id FROM al_app_t app INNER JOIN al_partner_mpcode_t pm ON app.mcode = pm.mcode AND pm.type = 'M' AND app.app_key = '{$db_appkey}' AND partner_id = '{$db_partner_id}'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$row) {
		return_die(false, null, '광고에 대한 권한이 없습니다..');	
	}

	// 앱 추가	
	$sql = "INSERT IGNORE guest_app_list (guest_id, app_key) VALUES ('{$db_guest_id}', '{$db_appkey}');";
	mysql_execute($sql, $conn);
	
	return_die(true, $ar_data);

?>