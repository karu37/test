<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	$partner_pw = trim($_REQUEST['partnerpw']);
	$partner_name = trim($_REQUEST['partnername']);
	$appkey = trim($_REQUEST['appkey']);
	$company = $_REQUEST['company'];
	$telno = $_REQUEST['telno'];
	$memo = $_REQUEST['memo'];
	
	if (!$partner_id || !$partner_pw || !$partner_name) return_die(false, null, '잘못된 요청입니다.');

	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_partner_pw = mysql_real_escape_string($partner_pw);
	$db_partner_name = mysql_real_escape_string($partner_name);
	$db_appkey = mysql_real_escape_string($appkey);
	$db_company = mysql_real_escape_string($company);
	$db_telno = mysql_real_escape_string($telno);
	$db_memo = mysql_real_escape_string($memo);
	
	$sql = "SELECT id FROM al_partner_t WHERE partner_id = '{$db_partner_id}'";
	$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
	if ($row && $row['id']) {
		return_die(false, null, '이미 등록되어 있는 게스트 아이디입니다.');	
	}
	
	$sql = "INSERT al_partner_t (partner_id, partner_pw, name, company, telno, memo, reg_date)
			VALUES ('{$db_partner_id}', '{$db_partner_pw}', '{$db_partner_name}', '{$db_company}', '{$db_telno}', '{$db_memo}', NOW());";
	mysql_execute($sql, $conn);

	$ar_data['partnerid'] = $partner_id;
	return_die(true, $ar_data);
?>