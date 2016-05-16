<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$merchant_code = trim($_REQUEST['mcode']);
	$merchant_name = trim($_REQUEST['merchantname']);
	if (!$merchant_code || !$merchant_name) return_die(false, null, '필요한 정보가 없습니다.');
	

	$db_merchant_name = mysql_real_escape_string($merchant_name);
	$db_merchant_code = mysql_real_escape_string($merchant_code);

	try {
		begin_trans($conn);
	
		$sql = "SELECT mcode FROM al_merchant_t WHERE name = '{$db_merchant_name}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row && $row['mcode']) {
			//  pcode가 존재하면서 pcode 값이 다른 경우 --> 다른 곳에서 이미 사용
			if ($merchant_code != $row['mcode']) {
				rollback($conn);
				return_die(false, null, '이미 사용중인 Merchant 코드입니다..');	
			}
		}
		
		$sql = "UPDATE al_merchant_t 
				SET
					name = '{$db_merchant_name}'
				WHERE mcode = '{$db_merchant_code}'";
		mysql_execute($sql, $conn);
		
		commit($conn);
		
	} catch (Exception $e) { 
		rollback($conn); 
		return_die(false, null, '처리 중 오류로 취소되었습니다 - '.$e->getMessage());
	}
	return_die(true);

?>