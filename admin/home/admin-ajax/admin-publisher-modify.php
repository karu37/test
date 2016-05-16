<?
	$admin_id = get_auth_adminid();
	if (!$admin_id) return_die(false, null, '권한이 없습니다.');
	
	$publisher_code = trim($_REQUEST['pcode']);
	$publisher_name = trim($_REQUEST['publishername']);
	$offer_fee_rate = trim($_REQUEST['offerfeerate']);
	$group_level = trim($_REQUEST['grouplevel']);
	if (!$publisher_code || !$publisher_name || ($offer_fee_rate < 0 || $offer_fee_rate > 100) || ($group_level < 0 || $group_level > 10) ) return_die(false, null, '필요한 정보가 없습니다.');
	

	$db_publisher_name = mysql_real_escape_string($publisher_name);
	$db_publisher_code = mysql_real_escape_string($publisher_code);
	$db_offer_fee_rate = mysql_real_escape_string($offer_fee_rate);
	$db_group_level = mysql_real_escape_string($group_level);

	try {
		begin_trans($conn);
	
		$sql = "SELECT pcode FROM al_publisher_t WHERE name = '{$db_publisher_name}' FOR UPDATE";
		$row = @mysql_fetch_assoc(mysql_query($sql, $conn));
		if ($row && $row['pcode']) {
			//  pcode가 존재하면서 pcode 값이 다른 경우 --> 다른 곳에서 이미 사용
			if ($publisher_code != $row['pcode']) {
				rollback($conn);
				return_die(false, null, '이미 사용중인 Publisher 코드입니다..');	
			}
		}
		
		$sql = "UPDATE al_publisher_t 
				SET
					name = '{$db_publisher_name}',
					offer_fee_rate = '{$db_offer_fee_rate}',
					level = '{$db_group_level}'
				WHERE pcode = '{$db_publisher_code}'";
		mysql_execute($sql, $conn);
		
		commit($conn);
		
	} catch (Exception $e) { 
		rollback($conn); 
		return_die(false, null, '처리 중 오류로 취소되었습니다 - '.$e->getMessage());
	}
	return_die(true);

?>