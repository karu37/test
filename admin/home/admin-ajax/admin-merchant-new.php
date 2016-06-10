<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	
	$merchant_code = trim($_REQUEST['mcode']);
	$merchant_name = trim($_REQUEST['merchantname']);
	$exchange_fee_rate = trim($_REQUEST['exchangefeerate']);
	
	if (!$merchant_code || !$merchant_name || $exchange_fee_rate <= 0 ) return_die(false, null, '필요한 정보가 없습니다.');
	

	$db_partner_id = mysql_real_escape_string($partner_id);
	
	$db_merchant_name = mysql_real_escape_string($merchant_name);
	$db_merchant_code = mysql_real_escape_string($merchant_code);
	$db_exchange_fee_rate = mysql_real_escape_string($exchange_fee_rate);

	try {
		begin_trans($conn);
	
		$sql = "SELECT mcode, name FROM al_merchant_t WHERE mcode = '{$db_merchant_code}' OR name = '{$db_merchant_name}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row && $row['mcode']) {
			
			if ($merchant_code == $row['mcode']) {
				rollback($conn);
				return_die(false, null, '이미 등록되어 Merchant 코드입니다..');	
			}
			if ($merchant_name == $row['name']) {
				rollback($conn);
				return_die(false, null, '이미 등록되어 Merchant 명입니다.');	
			}
		}
		
		$sql = "INSERT al_merchant_t (mcode, name, exchange_fee_rate, is_mactive ) VALUES ('{$db_merchant_code}', '{$db_merchant_name}', '{$db_exchange_fee_rate}', 'T');";
		mysql_execute($sql, $conn);
		
		$sql = "INSERT al_partner_mpcode_t (partner_id, type, mcode, pcode) VALUES('{$db_partner_id}', 'M', '{$db_merchant_code}', NULL);";
		mysql_execute($sql, $conn);
		commit($conn);
		
	} catch (Exception $e) { 
		rollback($conn); 
		return_die(false, null, '처리 중 오류로 취소되었습니다 - '.$e->getMessage());
	}
	return_die(true);

?>