<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$partner_id = trim($_REQUEST['partnerid']);
	
	$publisher_code = trim($_REQUEST['pcode']);
	$publisher_name = trim($_REQUEST['publishername']);
	$offer_fee_rate = trim($_REQUEST['offerfeerate']);
	$group_level = trim($_REQUEST['grouplevel']);
	if (!$publisher_code || !$publisher_name || ($offer_fee_rate < 0 || $offer_fee_rate > 100) || ($group_level < 0 || $group_level > 10) ) return_die(false, null, '필요한 정보가 없습니다.');
	

	$db_partner_id = mysql_real_escape_string($partner_id);
	
	$db_publisher_name = mysql_real_escape_string($publisher_name);
	$db_publisher_code = mysql_real_escape_string($publisher_code);
	$db_offer_fee_rate = mysql_real_escape_string($offer_fee_rate);
	$db_group_level = mysql_real_escape_string($group_level);

	try {
		begin_trans($conn);
	
		$sql = "SELECT pcode, name FROM al_publisher_t WHERE pcode = '{$db_publisher_code}' OR name = '{$db_publisher_name}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row && $row['pcode']) {
			
			if ($publisher_code == $row['pcode']) {
				rollback($conn);
				return_die(false, null, '이미 등록되어 Publisher 코드입니다..');	
			}
			if ($publisher_name == $row['name']) {
				rollback($conn);
				return_die(false, null, '이미 등록되어 Publisher 명입니다.');	
			}
		}
		$sql = "INSERT al_publisher_t (pcode, name, offer_fee_rate, level, is_mactive ) VALUES ('{$db_publisher_code}', '{$db_publisher_name}', '{$db_offer_fee_rate}', '{$db_group_level}', 'T');";
		mysql_execute($sql, $conn);
		
		$sql = "INSERT al_partner_mpcode_t (partner_id, type, mcode, pcode) VALUES('{$db_partner_id}', 'P', NULL, '{$db_publisher_code}');";
		mysql_execute($sql, $conn);
		commit($conn);
		
	} catch (Exception $e) { 
		rollback($conn); 
		return_die(false, null, '처리 중 오류로 취소되었습니다 - '.$e->getMessage());
	}
	return_die(true);

?>