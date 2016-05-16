<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	$publisher_code = trim($_REQUEST['pcode']);

	if (!$partner_id || !$publisher_code) return_die(false, null, '필요한 정보가 없습니다.');
	
	$db_partner_id = mysql_real_escape_string($partner_id);
	$db_publisher_code = mysql_real_escape_string($publisher_code);

	try {
		begin_trans($conn);
	
		$sql = "SELECT id FROM al_partner_mpcode_t WHERE partner_id = '{$db_partner_id}' AND pcode = '{$db_publisher_code}' AND type = 'P' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row && $row['id']) {
			rollback($conn);
			return_die(false, null, '이미 등록되어 Publisher 코드입니다..');	
		}

		$sql = "INSERT al_partner_mpcode_t (partner_id, type, pcode) VALUES('{$db_partner_id}', 'P', '{$db_publisher_code}');";
		mysql_execute($sql, $conn);
		commit($conn);
		
	} catch (Exception $e) { 
		rollback($conn); 
		return_die(false, null, '처리 중 오류로 취소되었습니다 - '.$e->getMessage());
	}
	return_die(true);

?>