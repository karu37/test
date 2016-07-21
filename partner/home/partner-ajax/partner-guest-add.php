<?
	$partner_id = get_auth_partnerid();
	if (!$partner_id) return_die(false, null, '권한이 없습니다.');
	
	$guest_id = trim($_REQUEST['guestid']);
	$guest_pw = trim($_REQUEST['guestpw']);
	$guest_name = trim($_REQUEST['guestname']);
	$appkey = trim($_REQUEST['appkey']);
	$company = $_REQUEST['company'];
	$telno = $_REQUEST['telno'];
	$memo = $_REQUEST['memo'];
	
	if (!$guest_id || !$guest_pw || !$guest_name) return_die(false, null, '잘못된 요청입니다.');

	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_guest_id = mysql_real_escape_string($guest_id);
	$db_guest_pw = mysql_real_escape_string($guest_pw);
	$db_guest_name = mysql_real_escape_string($guest_name);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_company = mysql_real_escape_string($company);
	$db_telno = mysql_real_escape_string($telno);
	$db_memo = mysql_real_escape_string($memo);
	
	$sql = "SELECT id FROM guest_user_t WHERE guest_id = '{$db_guest_id}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row && $row['id']) {
		return_die(false, null, '사용할 수 없는 광고주 아이디입니다.');	
	}
	
	$sql = "SELECT app.id FROM al_app_t app INNER JOIN al_partner_mpcode_t pm ON app.mcode = pm.mcode AND pm.type = 'M' AND app.app_key = '{$db_appkey}' AND partner_id = '{$db_partner_id}'";
	$row = mysql_fetch_assoc(mysql_query($sql, $conn));
	if (!$row) {
		return_die(false, null, '광고에 대한 권한이 없습니다..');	
	}
	
	$sql = "INSERT guest_user_t (partner_id, guest_id, guest_pw, guest_name, company, telno, memo, reg_date)
			VALUES ('{$db_partner_id}', '{$db_guest_id}', '{$db_guest_pw}', '{$db_guest_name}', '{$db_company}', '{$db_telno}', '{$db_memo}', NOW());";
	mysql_execute($sql, $conn);
	if ($appkey) {
		$sql = "INSERT IGNORE guest_app_list (guest_id, app_key) VALUES ('{$db_guest_id}', '{$db_appkey}');";
		mysql_execute($sql, $conn);
	}

	$ar_data['guestid'] = $guest_id;
	return_die(true, $ar_data);

?>