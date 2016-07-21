<?
	$partner_id = get_auth_partnerid();
	if (!$partner_id) return_die(false, null, '권한이 없습니다.');

	$guest_id = trim($_REQUEST['guestid']);
	$guest_pw = trim($_REQUEST['guestpw']);
	$guest_name = trim($_REQUEST['guestname']);
	$company = $_REQUEST['company'];
	$telno = $_REQUEST['telno'];
	$memo = $_REQUEST['memo'];
	
	if (!$guest_id || !$guest_pw) return_die(false, null, '잘못된 요청입니다.');

	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_guest_id = mysql_real_escape_string($guest_id);
	$db_guest_pw = mysql_real_escape_string($guest_pw);
	$db_guest_name = mysql_real_escape_string($guest_name);
	$db_company = mysql_real_escape_string($company);
	$db_telno = mysql_real_escape_string($telno);
	$db_memo = mysql_real_escape_string($memo);
	
	$sql = "UPDATE guest_user_t SET guest_pw = '{$guest_pw}', guest_name = '{$db_guest_name}', company = '{$db_company}', telno = '{$db_telno}', memo = '{$db_memo}' WHERE partner_id = '{$db_partner_id}' AND guest_id = '{$db_guest_id}';";
	mysql_execute($sql, $conn);

	return_die(true);

?>