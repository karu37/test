<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '������ �����ϴ�.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	$publisher_code = trim($_REQUEST['pcode']);

	if (!$partner_id || !$publisher_code) return_die(false, null, '�ʿ��� ������ �����ϴ�.');
	
	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_publisher_code = mysql_real_escape_string($publisher_code);

	$sql = "DELETE FROM al_partner_mpcode_t WHERE partner_id = '{$db_partner_id}' AND pcode = '{$db_publisher_code}' AND type = 'P'";
	mysql_query($sql, $conn);

	return_die(true);

?>