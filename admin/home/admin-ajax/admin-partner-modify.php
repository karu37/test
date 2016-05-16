<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	$partner_pw = trim($_REQUEST['partnerpw']);
	$partner_name = trim($_REQUEST['partnername']);
	$company = $_REQUEST['company'];
	$telno = $_REQUEST['telno'];
	$memo = $_REQUEST['memo'];
	
	if (!$partner_id || !$partner_pw) return_die(false, null, '잘못된 요청입니다.');

	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_partner_pw = mysql_real_escape_string($partner_pw);
	$db_partner_name = mysql_real_escape_string($partner_name);
	$db_company = mysql_real_escape_string($company);
	$db_telno = mysql_real_escape_string($telno);
	$db_memo = mysql_real_escape_string($memo);
	
	$sql = "UPDATE al_partner_t SET partner_pw = '{$partner_pw}', name = '{$db_partner_name}', company = '{$db_company}', telno = '{$db_telno}', memo = '{$db_memo}' WHERE partner_id = '{$db_partner_id}';";
	mysql_execute($sql, $conn);

	return_die(true);

?>